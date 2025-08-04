<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use mod_perform\models\activity\activity_setting;
use mod_perform\data_providers\activity\activity_settings;

class activity_closure_settings extends type_resolver {

    /**
     * @param string $field
     * @param activity_settings $settings
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $settings, array $args, execution_context $ec): mixed {

        if (!$settings instanceof activity_settings) {
            throw new coding_exception('Expected activity settings model');
        }

        return match ($field) {
            'close_on_section_submission' => (bool) $settings->lookup(activity_setting::CLOSE_ON_SECTION_SUBMISSION),
            'close_on_completion' => (bool) $settings->lookup(activity_setting::CLOSE_ON_COMPLETION),
            'close_on_due_date' => (bool) $settings->lookup(activity_setting::CLOSE_ON_DUE_DATE),
            'manual_close' => (bool) $settings->lookup(activity_setting::MANUAL_CLOSE),
            default => throw new coding_exception('Unknown field ' . $field .
                ' requested in activity_closure_settings type resolver'),
        };
    }
}
