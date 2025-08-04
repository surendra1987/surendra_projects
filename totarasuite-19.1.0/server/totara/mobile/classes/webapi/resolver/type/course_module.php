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
 * @package totara_mobile
 */

namespace totara_mobile\webapi\resolver\type;

use cm_info;
use core\webapi\execution_context;
use core\webapi\resolver\type\course_module as core_course_module;
use totara_mobile\download\download_helper;

class course_module extends core_course_module {
    /**
     * @param string $field
     * @param cm_info $cm_info
     * @param array $args
     * @param execution_context $ec
     * @return array|mixed|string|string[]|null
     */
    public static function resolve(string $field, $cm_info, array $args, execution_context $ec) {
        if ($field == 'downloadable') {
            return download_helper::is_activity_downloadable($cm_info);
        }

        if ($field == 'attachments') {
            if (download_helper::is_activity_downloadable($cm_info) && plugin_supports('mod', $cm_info->modname, FEATURE_MOD_INTRO, true)) {
                return download_helper::get_attachments_for_content($cm_info->context, 'mod_'.$cm_info->modname, 'intro', 0);
            }
            return [];
        }

        return parent::resolve($field, $cm_info, $args, $ec);
    }

}