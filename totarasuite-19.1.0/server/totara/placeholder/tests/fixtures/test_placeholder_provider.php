<?php
/**
 * This file is part of Totara LMS
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_placeholder
 */

use core_user\totara_notification\placeholder\user;
use totara_notification\placeholder\placeholder_option;
use totara_placeholder\placeholder_provider;

// We only allow this within unit tests.
(defined('MOODLE_INTERNAL') && PHPUNIT_TEST) || die();

class test_placeholder_provider implements placeholder_provider {

    public static function get_available_placeholder_options(): array {
        return [
            placeholder_option::create(
                'user',
                user::class,
                new \lang_string('user_placeholder_group', 'block_totara_featured_links'),
                function(array $data): user {
                    $user = $data['user'];
                    return user::from_id($user->id);
                }
            )
        ];
    }
}