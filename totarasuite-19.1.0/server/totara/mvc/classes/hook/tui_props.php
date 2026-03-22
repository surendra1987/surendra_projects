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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package totara_mvc
 */

namespace totara_mvc\hook;

use totara_core\hook\base;
use stdClass;

/**
 * Hook to allow plugins to override page props for specific controllers / components.
 */
class tui_props extends base {

    /**
     * @param stdClass|string $controller (totara_mvc\controller::class)
     * @param array $props
     */
    public function __construct(
        /** @var stdClass|\totara_mvc\controller::class $controller */
        private string $controller,
        /** @var string $component */
        private string $component,
        /** @var array $props */
        private array $props
    ) {
    }

    /**
     * Find the class of the controller which is creating a Tui view.
     *
     * @return stdClass|\totara_mvc\controller::class
     */
    public function get_controller_class(): stdClass|string {
        return $this->controller;
    }

    /**
     * Find the Tui component which is to be used for the view.
     *
     * @return string
     */
    public function get_tui_component(): string {
        return $this->component;
    }

    /**
     * Get the full array of page props.
     *
     * @return array
     */
    public function get_props(): array {
        return $this->props;
    }

    /**
     * Set the full array of page props.
     *
     * @param array $props
     * @return void
     */
    public function set_props(array $props): void {
        $this->props = $props;
    }
}
