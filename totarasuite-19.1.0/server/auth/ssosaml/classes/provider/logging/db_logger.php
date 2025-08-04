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
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\logging;

use auth_ssosaml\data_provider\saml_log;
use auth_ssosaml\entity\saml_log_entry;

/**
 * Logger implementation using DB for storage.
 */
class db_logger implements contract {
    /**
     * @var int|null
     */
    protected ?int $idp_id;

    /**
     * @var bool
     */
    protected bool $test_logger;

    /**
     * @param int|null $idp_id
     * @param bool $test_logger
     */
    public function __construct(?int $idp_id, bool $test_logger = false) {
        $this->idp_id = $idp_id;
        $this->test_logger = $test_logger;
    }

    /**
     * Clear log entries for the specified session & test situation.
     *
     * @param string $session_id
     * @return void
     */
    public static function clear_test_entries(string $session_id): void {
        saml_log_entry::repository()
            ->where('test', 1)
            ->where('session_id', $session_id)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function log_request(string $request_id, string $type, string $content, ?int $user_id = null): int {
        // We're creating a new instance here
        $entity = new saml_log_entry([
            'idp_id' => $this->idp_id,
            'request_id' => $request_id,
            'session_id' => session_id(),
            'type' => $type,
            'content_request' => $content,
            'content_request_time' => time(),
            'user_id' => $user_id,
            'test' => $this->test_logger,
        ]);

        $entity->save();
        return $entity->id;
    }

    /**
     * @inheritDoc
     */
    public function log_response(string $type, string $content, int $status, ?log_context $log_info = null): int {
        if (!is_null($log_info)) {
            $entity = $log_info->get_type() === log_context::TYPE_LOG_ID
                ? saml_log::find_by_id($log_info->get_id())
                : saml_log::find_by_request_id($log_info->get_id());
        } else {
            $entity = new saml_log_entry([
                'idp_id' => $this->idp_id,
                'request_id' => null,
                'session_id' => session_id(),
                'type' => $type,
                'test' => $this->test_logger,
            ]);
        }

        $entity->content_response = $content;
        $entity->content_response_time = time();
        $entity->status = $status;

        $entity->save();

        return $entity->id;
    }

    /**
     * @inheritDoc
     */
    public function log_error(int $log_id, string $error): void {
        $log = \auth_ssosaml\model\saml_log_entry::load_by_id($log_id);
        $log->add_error($error);
    }

    /**
     * @inheritDoc
     */
    public function log_notice(int $log_id, string $notice): void {
        $log = \auth_ssosaml\model\saml_log_entry::load_by_id($log_id);
        $log->add_notice($notice);
    }

    /**
     * @inheritDoc
     */
    public function update_log_entry_user_id(int $log_id, ?int $user_id): void {
        $log = \auth_ssosaml\model\saml_log_entry::load_by_id($log_id);
        $log->update_user_id($user_id);
    }

    /**
     * @inheritDoc
     */
    public function clear(): void {
        saml_log_entry::repository()->where('idp_id', $this->idp_id)->delete();
    }
}
