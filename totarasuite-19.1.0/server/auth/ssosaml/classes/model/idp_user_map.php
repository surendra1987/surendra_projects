<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model;

use auth_ssosaml\entity\idp_user_map as entity;
use core\orm\entity\model;
use stdClass;

/**
 * @property-read int $id ID
 * @property-read int $idp_id
 * @property-read int $user_id
 * @property-read int $status One of the STATUS_* constants
 * @property-read string|null $code Validation code required to approve a user.
 * @property-read string|null $code_expiry Timestamp of when the code expires.
 * @property-read int $created_at
 * @property-read int $updated_at
 */
class idp_user_map extends model {
    public const STATUS_UNCONFIRMED = 0;
    public const STATUS_INVALID = 1;
    public const STATUS_CONFIRMED = 2;

    /**
     * How long the confirmation links are valid for (in seconds).
     */
    public const CONFIRMATION_EXPIRY = 60 * 30;

    /**
     * Fields that are directly accessed from the entity.
     *
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'idp_id',
        'user_id',
        'status',
        'code',
        'code_expiry',
        'created_at',
        'updated_at',
    ];

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @return bool
     */
    public function is_confirmed(): bool {
        return $this->entity->status === self::STATUS_CONFIRMED;
    }

    /**
     * Create a new idp_user mapping and remove all previous versions.
     *
     * @param int $idp_id
     * @param int $user_id
     * @param int|null $status
     * @return static
     */
    public static function create_and_invalidate(int $idp_id, int $user_id, int $status = null): self {
        global $DB;

        $map = $DB->transaction(function () use ($idp_id, $user_id, $status) {
            // Invalidate all previous records
            entity::repository()
                ->where('user_id', $user_id)
                ->where('idp_id', $idp_id)
                ->delete();

            return (new entity([
                'idp_id' => $idp_id,
                'user_id' => $user_id,
                'status' => $status ?? self::STATUS_UNCONFIRMED,
                'code' => random_string(32),
                'code_expiry' => time() + self::CONFIRMATION_EXPIRY,
            ]))->save();
        });

        return new self($map);
    }

    /**
     * Build and send the confirmation email to the user.
     *
     * @param string $user_identifier The username/identifier provided via the auth request.
     * @param stdClass $user User object for the association
     * @param string $idp_label Name of the IdP for the email.
     * @return bool
     */
    public function send_confirmation_email(string $user_identifier, \stdClass $user, string $idp_label): bool {
        $user = clone($user);
        $user->mailformat = 1;

        $site = get_site();
        $no_reply = \core_user::get_noreply_user();

        $data = new stdClass();
        $data->fullname = fullname($user);
        $data->sitename  = format_string($site->fullname);
        $data->admin     = generate_email_signoff();
        $data->idp_label = format_string($idp_label);
        $data->user_identifier = clean_string($user_identifier);

        $subject = get_string('confirm_verification_subject', 'auth_ssosaml', format_string($site->fullname));
        $confirmation_url = (new \moodle_url('/auth/ssosaml/verify.php', [
            'token' => $this->code,
            'id' => $this->id,
        ]))->out(false);

        $data->link = $confirmation_url;
        $message = get_string('confirm_verification_content', 'auth_ssosaml', $data);

        $data->link = "[$confirmation_url]($confirmation_url)";
        $message_html = markdown_to_html(get_string('confirm_verification_content', 'auth_ssosaml', $data));

        // Directly email rather than using the messaging system to ensure it's not routed to a popup or jabber.
        return email_to_user($user, $no_reply, $subject, $message, $message_html);
    }

    /**
     * @param string|null $code
     * @return stdClass|null
     */
    public function validate_user_with_code(?string $code): ?stdClass {
        global $DB;

        // Quick check, our codes are a fixed length.
        if (empty($code) || strlen($code) !== 32) {
            return null;
        }

        // Only verify if it hasn't expired
        if ($this->code_expiry < time()) {
            return null;
        }

        // Can only verify unconfirmed states
        if ($this->status !== self::STATUS_UNCONFIRMED) {
            return null;
        }

        // Only verify if we have a code and they match
        if (!$this->code || $this->code !== $code) {
            return null;
        }

        // Confirm the actual user is in a good state.
        $user = $DB->get_record('user', ['id' => $this->user_id]);
        if (!$user || !$user->confirmed || $user->deleted || $user->suspended || $user->auth === 'nologin') {
            return null;
        }

        // Mark them as confirmed & save.
        $this->entity->status = self::STATUS_CONFIRMED;
        $this->entity->code = null;
        $this->entity->code_expiry = null;
        $this->entity->save();

        return $user;
    }
}
