<?php
/**
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */
namespace container_workspace\totara_catalog\workspace\feature_factory;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\feature_factory;
use totara_catalog\datasearch\all;
use totara_catalog\feature;
use totara_catalog\local\config;

class topics extends feature_factory {

    /**
     * This is mostly taken from core_tag\totara_catalog\feature_factory::get_features
     * with a few tweaks to display the topics collection correctly
     */
    public static function get_features(): array {
        global $CFG, $DB;

        $component = 'container_workspace';
        $itemtype = 'course';
        $objecttype = 'workspace';

        if (empty($CFG->usetags)) {
            return [];
        }

        if (!\core_tag_area::is_enabled($component, $itemtype)) {
            return [];
        }

        $collectionid = \core_tag_area::get_collection($component, $itemtype);
        $coll = $DB->get_record('tag_coll', ['id' => $collectionid], '*', MUST_EXIST);
        $displayname = \core_tag_collection::display_name($coll);

        $datafilter = new all(
            'tag_featured_' . $collectionid,
            'catalog',
            ['objecttype', 'objectid'],
            'LEFT JOIN'
        );

        $tagidparamkey = 'tfe_' . $collectionid . '_tagid_' . $objecttype;
        $itemtypeparamkey = 'tfe_' . $collectionid . '_type_' . $objecttype;
        $componentparamkey = 'tfe_' . $collectionid . '_component_' . $objecttype;
        $alias = 'tfe_' . $objecttype;

        $datafilter->add_source(
            'notused',
            "(SELECT tag_instance.itemid, 1 AS featured
                      FROM {tag_instance} tag_instance
                     WHERE tag_instance.tagid = :{$tagidparamkey}
                       AND tag_instance.itemtype = :{$itemtypeparamkey}
                       AND tag_instance.component = :{$componentparamkey})",
            $alias,
            [
                'objecttype' => "'{$objecttype}'",
                'objectid' => "{$alias}.itemid",
            ],
            "",
            [
                $tagidparamkey => config::instance()->get_value('featured_learning_value'),
                $itemtypeparamkey => $itemtype,
                $componentparamkey => $component,
            ],
            [
                'featured' => 1
            ]
        );

        $feature = new feature(
            'tag_' . $collectionid,
            $displayname,
            $datafilter
        );

        $feature->add_options_loader(self::get_options_loader($itemtype, $component));
        return [$feature];
    }

    /**
     * @param string $itemtype
     * @param string $component
     * @return callable
     */
    private static function get_options_loader(string $itemtype, string $component): callable {
        return function () use ($itemtype, $component) {
            global $DB;

            $sql = "
                SELECT DISTINCT tag.id, tag.name
                  FROM {tag_instance} tag_instance
                  JOIN {tag} tag
                    ON tag_instance.tagid = tag.id
                 WHERE tag_instance.itemtype = :itemtype
                   AND tag_instance.component = :component
            ";
            $params = ['itemtype' => $itemtype, 'component' => $component];

            $records = $DB->get_records_sql($sql, $params);

            $systemcontext = \context_system::instance();

            $options = [];
            foreach ($records as $record) {
                $options[$record->id] = format_string($record->name, true, ['context' => $systemcontext]);
            }

            return $options;
        };
    }

}
