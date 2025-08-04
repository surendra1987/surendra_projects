<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as activateed by
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

namespace mod_approval\hook;

use mod_approval\model\workflow\workflow_version;
use totara_core\hook\base as hook_base;

/**
 * Hook that allows plugins to prevent activation (publishing) of a workflow_version.
 */
class workflow_version_pre_activate extends hook_base {

    private $workflow_version;

    private $warnings = [];

    private $activate = true;

    public function __construct(workflow_version $workflow_version) {
        $this->workflow_version = $workflow_version;
    }

    /**
     * Get the workflow_version that is about to be activated.
     *
     * @return workflow_version
     */
    public function get_workflow_version(): workflow_version {
        return $this->workflow_version;
    }

    /**
     * Register a warning about the workflow_version. It does not have to prevent publishing.
     *
     * @param string $warning
     * @return void
     */
    public function add_warning(string $warning): void {
        $this->warnings[] = $warning;
    }

    /**
     * Get all warnings about the workflow_version that have been collected by the hook.
     *
     * @return array
     */
    public function get_warnings(): array {
        return $this->warnings;
    }

    /**
     * Tell the hook that the workflow_version is not ready or allowed to be activated.
     *
     * @return void
     */
    public function do_not_activate(): void {
        $this->activate = false;
    }

    /**
     * Discover whether any hook watchers want to prevent activation of the workflow, or if it is ok to activate.
     *
     * @return bool
     */
    public function ok_to_activate(): bool {
        return $this->activate;
    }
}