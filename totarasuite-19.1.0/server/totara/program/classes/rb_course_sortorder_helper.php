<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_program
 */

namespace totara_program;

use cache_definition;

defined('MOODLE_INTERNAL') || die();

/**
 * Report Builder program course sortorder helper
 *
 * This class is designed to aid report builder report sources that are displaying concatenated
 * course information and need to ensure that the courses are correctly ordered across all columns.
 *
 * This class also acts as a cache data source so that it can seamlessly load
 *
 * @internal
 * @deprecated since Totara 12, will be removed once MSSQL 2017 is the minimum required version.
 * TODO: This is just a placeholder. Please remove this once TL-36064 is done.
 */
final class rb_course_sortorder_helper implements \cache_data_source {
    
    public static function get_instance_for_cache(cache_definition $definition) {

    }

    public function load_for_cache($key) {

    }

    public function load_many_for_cache(array $keys) {

    }
}