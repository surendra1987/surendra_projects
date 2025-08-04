<?php

/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\model\status;

use core_component;

class status_helper {

    /**
     * Given a status code (from the database, for example) returns a matching status instance.
     *
     * @param string $status_code
     * @return status
     * @throws \coding_exception
     */
    public static function status_from_code(string $status_code): status {
        foreach (self::status_classes() as $status_class) {
            /** @var status $status_class */
            if ($status_class::get_code() == $status_code) {
                return new $status_class();
            }
        }
        throw new \coding_exception('Status code ' . $status_code . ' is not implemented.');
    }

    /**
     * Get all possible goal status codes as an array
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return array
     */
    public static function all_status_codes(string $namespace = ''): array {
        return self::status_codes(self::status_classes($namespace));
    }

    /**
     * Get an array of all possible goal status labels keyed by status code.
     *
     * @return array
     */
    public static function all_status_labels(): array {
        $result = [];
        /** @var status $status_class */
        foreach (self::status_classes() as $status_class) {
            $result[$status_class::get_code()] = $status_class::get_label();
        }
        return $result;
    }

    /**
     * Get all not started goal status codes.
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return array
     */
    public static function not_started_status_codes(string $namespace = ''): array {
        return self::status_code_group('not_started', $namespace);
    }

    /**
     * Get all in progress goal status codes.
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return array
     */
    public static function in_progress_status_codes(string $namespace = ''): array {
        return self::status_code_group('in_progress', $namespace);
    }

    /**
     * Get all achieved goal status codes.
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return array
     */
    public static function achieved_status_codes(string $namespace = ''): array {
        return self::status_code_group('achieved', $namespace);
    }

    /**
     * Get all closed goal status codes.
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return array
     */
    public static function closed_status_codes(string $namespace = ''): array {
        return self::status_code_group('closed', $namespace);
    }

    /**
     * Load all the perform_goal\model\status implementations by namespace.
     *
     * Note that the result is cached by core_component::get_namespace_classes()
     *
     * @param string $namespace Pass the goaltype component, or 'perform_goal' for core, or leave blank for all.
     * @return string[]
     */
    protected static function status_classes(string $namespace = ''): array {
        if (!empty($namespace) && !substr($namespace, -1) == '\\') {
            $namespace .= '\\';
        }

        $status_classes =  core_component::get_namespace_classes(
            $namespace . 'model\status',
            status::class,
            'perform_goal'
        );

        // Sort statuses by their sort_order values.
        usort($status_classes, fn ($a, $b) => $a::get_sort_order() <=> $b::get_sort_order());

        return $status_classes;
    }

    /**
     * Given an array of status classes, an array of [code => label] pairs.
     *
     * @param array $status_classes
     * @return array
     */
    protected static function status_codes(array $status_classes): array {
        $codes = [];
        foreach ($status_classes as $status_class) {
            /** @var status $status_class */
            $codes[] = $status_class::get_code();
        }
        return $codes;
    }

    /**
     * Look up status codes by well-known group, by asking each status implementation if it is a member.
     *
     * @param string $group
     * @param string $namespace
     * @return array
     */
    protected static function status_code_group(string $group, string $namespace = ''): array {
        $codes = [];
        foreach (self::status_classes($namespace) as $status_class) {
            /** @var status $status_class */
            if ($status_class::{'is_' . $group}()) {
                $codes[] = $status_class::get_code();
            }
        }
        return $codes;
    }
}