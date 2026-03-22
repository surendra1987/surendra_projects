<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package mod_approval
 */

use core\format;
use mod_approval\model\assignment\assignment as assignment_model;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\testing\assignment_generator_object;
use mod_approval\testing\approval_workflow_test_setup;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__ . '/testcase.php');

/**
 * @coversDefaultClass \mod_approval\webapi\resolver\type\workflow_assignment
 *
 * @group approval_workflow
 */
class mod_approval_webapi_type_assignment_test extends mod_approval_testcase {

    use webapi_phpunit_helper;
    use approval_workflow_test_setup;

    private const TYPE = 'mod_approval_workflow_assignment';

    /**
     * Gets the approval workflow generator instance
     *
     * @return \mod_approval\testing\generator
     */
    protected function generator(): \mod_approval\testing\generator {
        return \mod_approval\testing\generator::instance();
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Expected assignment model');

        $this->resolve_graphql_type(self::TYPE, 'id', new stdClass());
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_field(): void {
        list($workflow, , $assignment) = $this->create_workflow_and_assignment();
        $workflow = workflow_model::load_by_entity($workflow);
        $assignment = assignment_model::load_by_entity($assignment);

        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessageMatches("/$field/");

        $this->resolve_graphql_type(self::TYPE, $field, $assignment, [], $workflow->get_context());
    }

    /**
     * @covers ::resolve
     */
    public function test_resolve(): void {
        list($workflow, , $assignment) = $this->create_workflow_and_assignment();
        $workflow = workflow_model::load_by_entity($workflow);
        $assignment = assignment_model::load_by_entity($assignment);

        $testcases = [
            'id' => [null, $assignment->id],
            'assignment_type' => [null, $assignment->get_assignment_type_enum()],
            'assignment_type_label' => [null, $assignment->get_assignment_type_label()],
        ];

        foreach ($testcases as $field => $testcase) {
            [$format, $expected] = $testcase;
            $args = $format ? ['format' => $format] : [];

            $value = $this->resolve_graphql_type(self::TYPE, $field, $assignment, $args, $workflow->get_context());
            $expected instanceof stdClass
                ? $this->assertInstanceOf(get_class($expected), $value, "[$field] wrong value")
                : $this->assertEquals($expected, $value, "[$field] wrong value");
        }
    }
}
