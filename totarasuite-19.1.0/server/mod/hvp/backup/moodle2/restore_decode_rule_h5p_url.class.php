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
 * @package mod_hvp
 */

class restore_decode_rule_h5p_url extends restore_decode_rule {

    /**
     * This class exists so that restore can run over URLs to H5P Scripts which
     * do not have an id referencing the course / course module - their soley static
     * scripts used across the activity - e.g. h5p-resizer.js - this is included when
     * embedding a h5p activity, but not specific to an activity - the url still needs
     * to be replaced with one relevant to the new restore site.
     *
     * @param $linkname
     * @param $urltemplate
     * @param $mappings
     * @return array
     */
    protected function validate_params($linkname, $urltemplate, $mappings) {
        return [];
    }
}