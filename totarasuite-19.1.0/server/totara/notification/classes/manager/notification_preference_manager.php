<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  David Curry <david.curry@totara.com>
 * @package totara_notification
 */

namespace totara_notification\manager;

use coding_exception;
use totara_core\extended_context;
use totara_notification\loader\notification_preference_loader;

class notification_preference_manager {

    private static notification_preference_manager $instance;

    /**
     * Preventing this class from instantiation.
     */
    protected function __construct() {
        // This constructor is empty on purpose.
    }

    /**
     * Returns an instance of the manager.
     *
     * @param bool $reset If set to true then a new instance is created
     * @return self
     */
    public static function get_instance(bool $reset = false): self {
        if (!isset(self::$instance) || $reset) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Used for testing, to allow a mock instance to be set.
     *
     * @param self $instance
     * @return void
     * @throws coding_exception
     */
    public function set_instance(self $instance): void {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('set_instance should only be used by phpunit tests');
        }

        self::$instance = $instance;
    }

    /**
     * Clone existing notifications from one context to another,
     *
     * @param extended_context $to_context - The context to copy notifications to
     * @param extended_context $from_context - The context to copy notifications from
     * @return void
     * @throws coding_exception
     */
    public function clone_context_notifications($to_context, $from_context): void {

        // Cloning into the same context would need more checks, for now limit it.
        if ($to_context->get_context_id() == $from_context->get_context_id()) {
            throw new coding_exception("Preference already exists in target context");
        }

        // Cloning between context levels would need more checks, for now limit it.
        if ($to_context->get_context_level() !== $from_context->get_context_level()) {
            throw new coding_exception("Preferences currently only support cloning between contexts on the same level");
        }

        // Cloning between parent contexts could affect inheritance, for now limit it to the same parent context.
        $parents_from = $from_context->get_context()->get_parent_context_ids();
        $parents_to   = $to_context->get_context()->get_parent_context_ids();
        if (array_shift($parents_from) !== array_shift($parents_to)) {
            throw new coding_exception("Preferences currently only support cloning between contexts within the same parent context");
        }

        // Get all the custom/amended notification preferences at the original context.
        $preferences = notification_preference_loader::get_notification_preferences($from_context, null, true);

        // Do the actual cloning.
        foreach ($preferences as $preference) {
            $preference->clone_for_context($to_context);
        }
    }
}
