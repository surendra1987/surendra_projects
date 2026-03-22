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
 * @author David Curry <david.curry@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */
namespace container_workspace\totara_catalog;

use container_workspace\interactor\workspace\interactor;
use container_workspace\workspace as container_workspace;
use core\entity\course;
use core\orm\query\builder;
use core_container\factory as container_factory;
use totara_catalog\provider;
use totara_core\advanced_feature;

/**
 * Totara catalog provider for workspaces
 */
final class workspace extends provider {

    /**
     * @var array|null Caches configuration for this provider.
     */
    private ?array $config_cache = null;

    /**
     * @return bool
     */
    public static function is_plugin_enabled(): bool {
        return advanced_feature::is_enabled('container_workspace');
    }

    /**
     * @return string
     */
    public static function get_name(): string {
        return get_string('space', 'container_workspace');
    }

    /**
     * @return string
     */
    public static function get_object_type(): string {
        return 'workspace';
    }

    /**
     * @return string
     */
    public function get_object_table(): string {
        return '{course}';
    }

    /**
     * @return string
     */
    public function get_objectid_field(): string {
        return 'id';
    }

    /**
     * @param array $objects
     * @return array
     */
    public function can_see(array $objects): array {
        $results = [];

        if (is_siteadmin()) {
            foreach ($objects as $object) {
                $results[$object->objectid] = true;
            }
            return $results;
        }

        $object_ids = array_map(function ($object) {
            return $object->objectid;
        }, $objects);

        $records = builder::table(course::TABLE)
            ->where_in('id', $object_ids)
            ->get();

        foreach ($records as $record) {
            /** @var container_workspace $workspace */
            $workspace = container_factory::from_record($record);
            $interactor = new interactor($workspace);

            $results[$record->id] = $interactor->can_view_workspace();
        }

        return $results;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get_data_holder_config(string $key): mixed {
        if (is_null($this->config_cache)) {
            $this->config_cache = [
                'sort' => [
                    'text' => 'name',
                    'time' => 'timecreated',
                ],
                'fts' => [
                    'high' => [
                        'name',
                    ],
                    'medium' => [
                        'ftstags', // Note: Topics.
                        'ftssummary',
                    ],
                    'low' => [
                    ],
                ],
                'image' => 'image',
            ];
        }

        if (array_key_exists($key, $this->config_cache)) {
            return $this->config_cache[$key];
        }

        return null;
    }

    /**
     * @return array
     */
    public function get_all_objects_sql(): array {
        $sql = "
            SELECT c.id as objectid, 'workspace' as objecttype, con.id as contextid
              FROM {course} c
              JOIN {workspace} w
                ON w.course_id = c.id
        INNER JOIN {context} con
                ON con.contextlevel = :level
               AND con.instanceid = c.id
        INNER JOIN {user} u
                ON w.user_id = u.id
               AND u.deleted = 0
               AND u.confirmed = 1
        ";

        return [$sql, ['level' => CONTEXT_COURSE]];
    }

    /**
     * @param int $objectid
     * @return \stdClass|null
     */
    public function get_manage_link(int $objectid): ?object {
        return null;
    }

    /**
     * @param int $objectid
     * @return \stdClass|null
     */
    public function get_details_link(int $objectid): ?object {
        /** @var container_workspace $workspace */
        $workspace = container_factory::from_id($objectid);

        $link = new \stdClass();
        $link->button = new \stdClass();
        $url = $workspace->get_view_url();
        $url->param('from', 'catalog');
        $link->button->url = $url->out(false);
        $link->button->label = get_string('view_workspace', 'container_workspace');

        $interactor = new interactor($workspace);

        if ($interactor->is_owner()) {
            $link->description = get_string('you_are_owner', 'container_workspace');
        } elseif ($interactor->is_joined()) {
            $link->description = get_string('you_are_member', 'container_workspace');
        } else {
            $link->description = get_string('you_are_not_member', 'container_workspace');
        }

        return $link;
    }

    /**
     * These buttons show up to the left of the create button
     * @return array
     */
    public function get_buttons(): array {
        return [];
    }

    /**
     * @return array
     */
    public function get_create_buttons(): array {
        global $CFG;

        if ($CFG->catalogtype === 'explore' && advanced_feature::is_enabled('container_workspace')) {
            $categoryid = totara_get_categoryid_with_capability('container/workspace:create');
            if ($categoryid !== false) {
                $button = new \stdClass();
                $button->label = get_string('cachedef_workspace', 'container_workspace');
                $button->url = (new \moodle_url("/container/type/workspace/index.php", ['action' => 'cw']))->out();
                $buttons[] = $button;
            }
        }

        return $buttons ?? [];
    }
}
