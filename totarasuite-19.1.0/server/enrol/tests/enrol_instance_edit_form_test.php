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
 * @package core
 */

use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_display;
use core_enrol\hook\enrol_instance_extra_settings_validation;
use core_enrol\testing\enrol_instance_edit_form_test_setup;
use core_phpunit\testcase;

class core_enrol_enrol_instance_edit_form_test extends testcase {

    use enrol_instance_edit_form_test_setup;

    /**
     * Ensure that the enrol_instance_extra_settings_validation
     * hook is called exactly once by the validation method.
     */
    public function test_validation_calls_extra_settings_hook(): void {
        $sink = $this->redirectHooks();

        $form = $this->create_new_enrol_instance_edit_form();

        $sink->clear();
        $hooks = $sink->get_hooks();
        $this->assertCount(0, $hooks);

        $form->validation($this->get_validation_test_data(), []);

        $hooks = $sink->get_hooks_by_classname(enrol_instance_extra_settings_validation::class);
        $this->assertCount(1, $hooks);

        /** @var enrol_instance_extra_settings_validation $hook */
        $hook = $hooks[0];

        // Ensure that no errors related to the extra settings were found
        $this->assertEmpty($hook->get_errors());
    }

    /**
     * Ensure that the enrol_instance_extra_settings_definition
     * hook is called exactly once by the definition method.
     */
    public function test_definition_calls_extra_settings_hook(): void {
        $sink = $this->redirectHooks();

        $form = $this->create_new_enrol_instance_edit_form();

        $sink->clear();
        $hooks = $sink->get_hooks();
        $this->assertCount(0, $hooks);

        $form->definition();

        $hooks = $sink->get_hooks_by_classname(enrol_instance_extra_settings_definition::class);
        $this->assertCount(1, $hooks);
    }

    /**
     * Ensure that the enrol_instance_extra_settings_display
     * hook is called exactly once by the display method.
     */
    public function test_display_calls_extra_settings_display_hook(): void {
        $sink = $this->redirectHooks();

        $form = $this->create_new_enrol_instance_edit_form();

        $sink->clear();
        $hooks = $sink->get_hooks();
        $this->assertCount(0, $hooks);

        // Capture output, otherwise this is a risky test.
        ob_start();
        $form->display();
        ob_end_clean();

        $hooks = $sink->get_hooks_by_classname(enrol_instance_extra_settings_display::class);
        $this->assertCount(1, $hooks);
    }
}
