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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package tool_diagnostic
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_webapi_mutation_run_diagnostics_test extends testcase {
    private const MUTATION = 'tool_diagnostic_run_diagnostics';

    use webapi_phpunit_helper;

    public function test_run_diagnostics_by_admin() {
        $this->setAdminUser();

        $result = $this->resolve_graphql_mutation(static::MUTATION, ['excluded_provider_ids' => []]);
        $this->assertNotNull($result['download_url']);
        $this->assertNotNull($result['download_filesize']);
    }

    public function test_run_diagnostics_by_system_user() {
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();

        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        $this->resolve_graphql_mutation(static::MUTATION);
    }
}
