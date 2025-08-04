<?php
/*
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */

namespace container_workspace\totara_catalog\workspace\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\dataholder_factory;
use totara_catalog\dataholder;
use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\fts;
use totara_catalog\dataformatter\ordered_list;


class tags extends dataholder_factory {

    /**
     * @return array
     */
    public static function get_dataholders(): array {
        global $CFG, $DB;

        if (empty($CFG->usetags)) {
            return [];
        }

        if (!\core_tag_area::is_enabled('container_workspace', 'course')) {
            return [];
        }

        return [
            new dataholder(
                'ftstags',
                new \lang_string('tags', 'tag'),
                [formatter::TYPE_FTS => new fts('ftstags.tags')],
                [
                    'ftstags' =>
                        "LEFT JOIN (SELECT ti.itemid, {$DB->sql_group_concat('t.name', ',')} AS tags
                                      FROM {tag_instance} ti
                                      JOIN {tag} t ON t.id = ti.tagid
                                     WHERE ti.component = 'container_workspace'
                                       AND ti.itemtype = 'course'
                                     GROUP BY ti.itemid) ftstags
                           ON ftstags.itemid = base.id"
                ]
            ),
            new dataholder(
                'tags',
                new \lang_string('tags', 'tag'),
                [
                    formatter::TYPE_PLACEHOLDER_TEXT => new ordered_list('tags.tags'),
                ],
                [
                    'tags' =>
                        "LEFT JOIN (SELECT ti.itemid, {$DB->sql_group_concat('t.rawname', ',')} AS tags
                                      FROM {tag_instance} ti
                                      JOIN {tag} t ON t.id = ti.tagid
                                     WHERE ti.component = 'container_workspace'
                                       AND ti.itemtype = 'course'
                                     GROUP BY ti.itemid) tags
                           ON tags.itemid = base.id"
                ]
            ),
        ];
    }
}
