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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model;

use auth_ssosaml\provider\logging\contract;
use context;
use context_system;
use auth_ssosaml\entity\saml_log_entry as saml_log_entry_entity;
use core\orm\entity\model;

/**
 * Log entry model
 *
 * @property-read int $id
 * @property-read int $idp_id
 * @property-read string $request_id
 * @property-read string $session_id
 * @property-read bool $is_from_idp
 * @property-read string $type
 * @property-read string|null $content_request
 * @property-read string|null $content_response
 * @property-read string|null $error
 * @property-read string|null $notice
 * @property-read int $created_at
 * @property-read int $content_request_time
 * @property-read int $content_response_time
 * @property-read int $status
 * @property-read int|null $user_id
 * @property-read context $context
 * @property-read string $status_label
 */
class saml_log_entry extends model {
    /**
     * @inheritDoc
     */
    protected $model_accessor_whitelist = [
        'is_from_idp',
        'status_label',
        'context',
    ];

    /**
     * @inheritDoc
     */
    protected $entity_attribute_whitelist = [
        'id',
        'idp_id',
        'type',
        'content_request',
        'content_response',
        'error',
        'notice',
        'created_at',
        'content_request_time',
        'content_response_time',
        'status',
        'user_id',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return saml_log_entry_entity::class;
    }

    /**
     * Get the context relevant to the model.
     *
     * @return context
     */
    public function get_context(): context {
        return context_system::instance();
    }

    /**
     * @return string
     */
    protected function get_status_label(): string {
        switch ($this->entity->status) {
            case contract::STATUS_ERROR:
                return get_string('log_status_error', 'auth_ssosaml');
            case contract::STATUS_SUCCESS:
                return get_string('log_status_success', 'auth_ssosaml');
            default:
                return get_string('log_status_incomplete', 'auth_ssosaml');
        }
    }

    /**
     * @param int|null $user_id
     * @return void
     */
    public function update_user_id(?int $user_id): void {
        $this->entity->user_id = $user_id;
        $this->entity->save();
    }

    /**
     * Add an error to the error column.
     *
     * @param string $error
     * @return void
     */
    public function add_error(string $error) {
        $this->entity->status = contract::STATUS_ERROR;
        $this->entity->error .= ($this->entity->error ? "\n" : '') . $error;
        $this->entity->save();
    }

    /**
     * Add a notice to the notice column.
     *
     * @param string $notice
     * @return void
     */
    public function add_notice(string $notice) {
        $this->entity->notice .= ($this->entity->notice ? "\n" : '') . $notice;
        $this->entity->save();
    }
}
