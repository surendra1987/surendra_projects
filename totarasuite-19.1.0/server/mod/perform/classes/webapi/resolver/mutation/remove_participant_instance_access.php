<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\mutation;

use core\entity\user;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\execution_context;
use lang_string;
use mod_perform\webapi\middleware\require_manage_participants_capability;
use totara_api\exception\create_client_exception;
use totara_webapi\client_aware_exception;
use mod_perform\models\activity\participant_instance as participant_instance_model;
use \core\orm\query\exceptions\record_not_found_exception;

/**
 * Web API resolver of the AJAX API endpoint for the 'mod_perform_remove_participant_instance_access' mutation operation.
 */
class remove_participant_instance_access extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $args = $args['input'];
        $input_status = (int)$args['status'];

        try {
            $pi_model = participant_instance_model::load_by_id($args['participant_instance_id']);
        } catch (record_not_found_exception $e) {
            // This should not happen. Possible enumeration attack - give vague error message.
            $e = new create_client_exception("Invalid parameters or missing permissions");
            throw new client_aware_exception($e, ['category' => 'public']);
        }

        $can_change_access_error = $pi_model->user_can_change_access_find_errors(user::logged_in(), $input_status);

        if (isset($can_change_access_error)) {
            throw new client_aware_exception(new create_client_exception($can_change_access_error), ['category' => 'public']);
        }

        $pi_model->set_access_removed($input_status);

        return ['success' => true];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_login(),
        ];
    }
}
