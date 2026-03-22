<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package container_workspace
 */

use core_phpunit\testcase;

class container_workspace_upgrade_role_context_levels_test extends testcase {
    /**
     * Assert that the upgrade function only updates context levels when they are not set.
     *
     * @return void
     */
    public function test_context_levels_assigned(): void {
        global $CFG;
        require_once "{$CFG->dirroot}/container/type/workspace/db/upgradelib.php";

        $sink = $this->redirectEvents();

        // Role with an existing context level
        $role_1 = create_role('r1', 'r1', '', 'workspaceowner');
        set_role_contextlevels($role_1, [CONTEXT_USER, CONTEXT_BLOCK]); // Weird ones

        // Role with no context levels
        $role_2 = create_role('r2', 'r2', '', 'workspaceowner');

        // Different archetype, also not touched
        $role_3 = create_role('r3', 'r3', '', 'workspacecreator');

        // Assert custom contexts are set
        $levels = get_role_contextlevels($role_1);
        $this->assertEqualsCanonicalizing([CONTEXT_USER, CONTEXT_BLOCK], $levels);

        // Assert no contexts are set
        $levels = get_role_contextlevels($role_2);
        $this->assertEmpty($levels);

        // Assert no contexts are set
        $levels = get_role_contextlevels($role_3);
        $this->assertEmpty($levels);

        container_workspace_upgrade_role_context_levels('workspaceowner');

        // Assert custom contexts are still set
        $levels = get_role_contextlevels($role_1);
        $this->assertEqualsCanonicalizing([CONTEXT_USER, CONTEXT_BLOCK], $levels);

        // Assert correct contexts were added
        $levels = get_role_contextlevels($role_2);
        $this->assertEqualsCanonicalizing([CONTEXT_COURSE], $levels);

        // Assert no contexts are set
        $levels = get_role_contextlevels($role_3);
        $this->assertEmpty($levels);

        $sink->close();
    }

}
