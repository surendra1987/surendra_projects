<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package mod_certificate
 */

namespace mod_certificate\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_login_course_via_coursemodule;
use core\webapi\query_resolver;
use mod_certificate\download\certificate;
use totara_mobile\download\download_helper;
use totara_mobile\webapi\resolver\middleware\require_mobile_friendly_course;

class download extends query_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return certificate
     */
    public static function resolve(array $args, execution_context $ec): certificate {
        return download_helper::get_downloadable_activity_content('certificate', $args['cm']);
    }

    /**
     * @return array
     */
    public static function get_middleware(): array {
        return [
            new require_login_course_via_coursemodule('input.cm_id'),
            new require_mobile_friendly_course('course')
        ];
    }
}