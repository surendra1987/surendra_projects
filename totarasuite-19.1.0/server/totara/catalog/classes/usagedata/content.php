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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\usagedata;

use core\orm\query\builder;
use tool_usagedata\export;

class content implements export {

    /**
     * @inheritdoc
     */
    public function get_summary(): string {
        return get_string('usagedata_content', 'totara_catalog');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritdoc
     */
    public function export(): array {
        $object_type_counts = builder::table('catalog')
            ->reset_select()
            ->select_raw('objecttype, COUNT(*) AS c')
            ->group_by('objecttype')
            ->get()
            ->all();

        $processed_object_types = [
            'course' => 0,
            'program' => 0,
            'certification' => 0,
            'workspace' => 0,
            'playlist' => 0,
            'engage_article' => 0,
            'other' => 0,
        ];

        $total = 0;

        foreach ($object_type_counts as $object_type_count) {
            if (array_key_exists($object_type_count->objecttype, $processed_object_types)) {
                $processed_object_types[$object_type_count->objecttype] += $object_type_count->c;
            } else {
                $processed_object_types['other'] += $object_type_count->c;
            }
            $total += $object_type_count->c;
        }

        return [
            'total_items' => $total,
            'courses' => $processed_object_types['course'],
            'programs' => $processed_object_types['program'],
            'certifications' => $processed_object_types['certification'],
            'workspaces' => $processed_object_types['workspace'],
            'playlists' => $processed_object_types['playlist'],
            'resources' => $processed_object_types['engage_article'],
            'other' => $processed_object_types['other'],
        ];
    }
}
