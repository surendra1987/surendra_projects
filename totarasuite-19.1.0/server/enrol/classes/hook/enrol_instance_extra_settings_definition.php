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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @author David Curry <david.curry@totara.com>
 * @package core_enrol
 */

namespace core_enrol\hook;

use context_course;
use enrol_plugin;
use moodleform;
use stdClass;

/**
 * This hook injects the extra settings for an enrolment plugin into
 * the form definition where necessary.
 */
class enrol_instance_extra_settings_definition extends enrol_instance_extra_settings_base {

    /**
     * The enrolment form shared by different enrolment plugins
     */
    public moodleform $enrolment_form;

    /**
     * Create a new enrol_instance_extra_settings hook
     *
     * @param moodleform $enrolment_form
     * @param stdClass $enrolment_instance
     * @param enrol_plugin $plugin
     * @param context_course $context
     */
    public function __construct(moodleform &$enrolment_form, stdClass $enrolment_instance, enrol_plugin $plugin, context_course $context) {
        $this->enrolment_form =& $enrolment_form;
        $this->enrolment_instance = $enrolment_instance;
        $this->plugin = $plugin;
        $this->context = $context;
    }
}
