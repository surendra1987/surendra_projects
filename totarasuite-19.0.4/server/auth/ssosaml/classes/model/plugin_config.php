<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\model;

use core\collection;
use auth_ssosaml\data_provider\user_fields;

/**
 * Model class for plugin-level configuration.
 */
class plugin_config {
    /**
     * Map of the config value to a relevant enum value.
     * @var array|string[]
     */
    private array $lock_db_to_enum = [
        'unlocked' => 'UNLOCKED',
        'unlockedifempty' => 'UNLOCKED_IF_EMPTY',
        'locked' => 'LOCKED',
    ];

    /**
     * Get the list of currently configured fields.
     *
     * @return array
     */
    public function get_fields(): array {
        $all_fields = user_fields::get_info();
        $fields = [];

        $unlocked_visible = explode(',', get_config('auth_ssosaml', 'field_unlocked_visible'));

        foreach ($all_fields as $id => $field) {
            if (!$field['lockable']) {
                continue;
            }
            $config_key = "field_lock_{$id}";
            $lock_value = get_config('auth_ssosaml', $config_key);
            if (!$lock_value) {
                $lock_value = 'unlocked';
            }
            if ($lock_value === 'unlocked' && !in_array($id, $unlocked_visible)) {
                continue;
            }
            $fields[] = array_merge($field, [
                'locked' => $this->lock_db_to_enum[$lock_value] ?? 'UNLOCKED',
            ]);
        }

        return $fields;
    }

    /**
     * Set the configured fields.
     *
     * @param array $fields Array of field arrays, each with keys 'id' and 'locked'
     * @return void
     */
    public function set_fields(array $fields): void {
        $lock_enum_to_db = array_flip($this->lock_db_to_enum);

        $all_fields = user_fields::get_info();
        $lockable_fields = collection::new($all_fields)->filter('lockable', true);
        $in_fields = collection::new($fields)->key_by('id');

        $unlocked_visible = collection::new($fields)
            ->filter(function ($field) use ($lockable_fields) {
                return $field['locked'] === 'UNLOCKED' && isset($lockable_fields[$field['id']]);
            })
            ->pluck('id');
        set_config('field_unlocked_visible', implode(',', $unlocked_visible), 'auth_ssosaml');

        foreach ($lockable_fields as $id => $field) {
            $config_key = "field_lock_{$id}";
            $lock_value = get_config('auth_ssosaml', $config_key);
            if (!$lock_value) {
                $lock_value = 'unlocked';
            }
            if (isset($in_fields[$id])) {
                $new_lock_value = $lock_enum_to_db[$in_fields[$id]['locked']];
                if ($new_lock_value !== $lock_value) {
                    set_config($config_key, $new_lock_value, 'auth_ssosaml');
                }
            } else if ($lock_value !== 'unlocked') {
                set_config($config_key, 'unlocked', 'auth_ssosaml');
            }
        }
    }
}
