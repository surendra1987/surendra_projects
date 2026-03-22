<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_notification
 */

namespace totara_notification;

use coding_exception;
use container_course\module\course_module;
use core\orm\query\builder;
use core_container\local\module_supported;
use totara_core\extended_context;
use totara_core\identifier\component_area;

/**
 * This class can be called for other plugins
 *
 * Class supports_context_helper
 */
class supports_context_helper {
    /**
     * helper constructor.
     * Preventing this class from instantiation.
     */
    private function __construct() {
    }

    public static function supports_extended_component_area(
        extended_context $extended_context,
        component_area $supported_extended_component_area = null
    ): bool {
        if ($extended_context->is_natural_context()) {
            return true;
        }

        return !is_null($supported_extended_component_area) &&
            $extended_context->get_component() == $supported_extended_component_area->get_component() &&
            $extended_context->get_area() == $supported_extended_component_area->get_area();
    }

    public static function supports_context_level(
        extended_context $extended_context,
        int $supported_lowest_context_level
    ): bool {
        $actual_context_level = $extended_context->get_context_level();
        switch ($supported_lowest_context_level) {
            case CONTEXT_SYSTEM:
                $supported_context_levels = [CONTEXT_SYSTEM];
                break;
            case CONTEXT_COURSECAT:
                $supported_context_levels = [CONTEXT_SYSTEM, CONTEXT_COURSECAT];
                break;
            case CONTEXT_PROGRAM:
                $supported_context_levels = [CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_PROGRAM];
                break;
            case CONTEXT_COURSE:
                $supported_context_levels = [CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE];
                break;
            case CONTEXT_MODULE:
                $supported_context_levels = [CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE];
                break;
            default:
                $supported_context_levels = [];
        }
        return in_array($actual_context_level, $supported_context_levels);
    }

    public static function supports_coursecat_context(
        extended_context $extended_context,
        string $supported_container_type
    ): bool {
        $actual_category = builder::table('course_categories')
            ->select(['idnumber', 'iscontainer'])
            ->where('id', '=', $extended_context->get_context()->instanceid)
            ->one(true);

        if ($supported_container_type == 'container_course') {
            return $actual_category->iscontainer == 0;
        } else {
            return $actual_category->iscontainer == 1 &&
                substr($actual_category->idnumber, 0, strlen($supported_container_type)) == $supported_container_type;
        }
    }

    public static function supports_course_context(
        extended_context $extended_context,
        string $supported_container_type
    ): bool {
        $actual_course = builder::table('course')
            ->select('containertype')
            ->where('id', '=', $extended_context->get_context()->instanceid)
            ->one(true);

        return $actual_course->containertype == $supported_container_type;
    }

    public static function supports_module_context(
        extended_context $extended_context,
        string $supported_container_type,
        string $supported_module
    ): bool {
        $cm = course_module::from_id($extended_context->get_context()->instanceid);

        if ($supported_module == 'all_container_modules') {
            $container_modules = array_keys(module_supported::instance()->get_for_container($supported_container_type));
            return in_array($cm->get_modulename(), $container_modules);
        } else {
            // We don't check supported container type because each unique module name can belong to only one container type.
            return $cm->get_modulename() == $supported_module;
        }
    }

    /**
     * Determines if the given extended context is supported
     *
     * @param extended_context $extended_context the context to be checked
     * @param int $supported_lowest_context_level e.g. CONTEXT_COURSE
     * @param string|null $supported_container_type e.g. container_course, required for CONTEXT_COURSECAT, CONTEXT_COURSE and CONTEXT_MODULE
     * @param string|null $supported_module module name or 'all_container_modules'
     * @param component_area|null $supported_extended_component_area
     * @return bool
     */
    public static function supports_context(
        extended_context $extended_context,
        int $supported_lowest_context_level,
        string $supported_container_type = null,
        string $supported_module = null,
        component_area $supported_extended_component_area = null
    ): bool {
        // First check the extended context - it's fast and cheap.
        if (!self::supports_extended_component_area($extended_context, $supported_extended_component_area)) {
            return false;
        }

        // Check that the given context level is supported.
        if (!self::supports_context_level($extended_context, $supported_lowest_context_level, $supported_container_type)) {
            return false;
        }

        $actual_context_level = $extended_context->get_context_level();

        switch ($actual_context_level) {
            case CONTEXT_SYSTEM:
                // If the given context is system then of course it is supported!
                return true;

            case CONTEXT_PROGRAM:
                // If the given context is a program then we just need to check container specified isn't faulty.
                // We already know that the CONTEXT_PROGRAM is a supported context level, so we can check the required properties.
                if ($supported_container_type != 'container_course') {
                    throw new coding_exception('Supported container type must be \'container_course\' for notifications that support program context');
                }
                return true; // Program context is supported and given context is program, so no further checks required.

            case CONTEXT_COURSECAT:
                // If the given context is a category then make sure it is the correct container type.
                // We already know that the CONTEXT_COURSECAT is a supported context level, so we can check the required properties.
                if (is_null($supported_container_type)) {
                    throw new coding_exception('Supported container type must be specified for notifications that support category context');
                }
                return self::supports_coursecat_context($extended_context, $supported_container_type);

            case CONTEXT_COURSE:
                // If the given context is a course then make sure it is the correct container type.
                // We already know that the CONTEXT_COURSE is a supported context level, so we can check the required properties.
                if (is_null($supported_container_type)) {
                    throw new coding_exception('Supported container type must be specified for notifications that support course context');
                }
                return self::supports_course_context($extended_context, $supported_container_type);

            case CONTEXT_MODULE:
                // If the given context is a module then make sure that it is the correct module.
                // We already know that the CONTEXT_MODULE is a supported context level, so we can check the required properties.
                if (is_null($supported_module)) {
                    throw new coding_exception('Supported module must be specified for notifications that support module context');
                }
                if (is_null($supported_container_type)) {
                    throw new coding_exception('Supported container type must be specified for notifications that support module context');
                }
                return self::supports_module_context($extended_context, $supported_container_type, $supported_module);

            case CONTEXT_TENANT:
            case CONTEXT_USER:
            case CONTEXT_BLOCK:
            default:
                // These contexts are properly supported. They probably shouldn't even be passed in to this function. But we will just say "no".
                return false;
        }
    }
}