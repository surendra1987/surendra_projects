<?php
/**
 * This file is part of Totara Core
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core_enrol
 */

namespace core_enrol\hook;

use context_course;
use enrol_plugin;
use stdClass;
use totara_core\hook\base;

/**
 * Common interface for the enrol_instance_extra_settings hooks.
 */
abstract class enrol_instance_extra_settings_base extends base {



    /**
     * The enrolment_instance that owns the settings.
     */
    protected stdClass $enrolment_instance;

    /**
     * The associated enrolment plugin
     *
     * @var enrol_plugin
     */
    protected enrol_plugin $plugin;

    /**
     * The context we're working in.
     *
     * @var context_course
     */
    protected context_course $context;

    /**
     * Get the enrolment instance, or instance data if not saved yet.
     *
     * @returns stdClass $enrolment_instance
     */
    public function get_enrolment_instance(): stdClass {
        return $this->enrolment_instance;
    }

    /**
     * Get the plugin associated with the enrolment instance.
     *
     * @return enrol_plugin
     */
    public function get_plugin(): enrol_plugin {
        return $this->plugin;
    }

    /**
     * Get the context of the course the enrolment instance is on.
     *
     * @return context_course
     */
    public function get_context(): context_course {
        return $this->context;
    }
}