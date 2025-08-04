<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package mod_approval
 */

use core_phpunit\testcase;
use mod_approval\model\assignment\assignment;
use mod_approval\model\assignment\assignment_type\cohort as cohort_type;
use mod_approval\model\assignment\assignment_type\organisation as organisation_type;
use mod_approval\model\assignment\assignment_type\position as position_type;
use mod_approval\model\form\form;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_type;
use totara_cohort\hook\pre_delete_cohort_check;
use totara_hierarchy\hook\pre_delete_framework_check;
use totara_hierarchy\hook\pre_delete_item_check;

/**
 * @group approval_workflow
 * @coversDefaultClass \mod_approval\watcher\assignment_deletion_checker
 */
class mod_approval_watcher_assignment_deletion_check_test extends testcase {

    public static function hierarchy_item_provider(): array {
        return [
            ['organisation', organisation_type::get_code()],
            ['position', position_type::get_code()],
        ];
    }

    /**
     * @dataProvider hierarchy_item_provider
     * @param string $prefix
     * @return void
     */
    public function test_it_prevents_deleting_organisations_used_as_assignments(string $prefix, int $code) {
        $gen = self::getDataGenerator()->get_plugin_generator('totara_hierarchy');
        $framework = $gen->create_framework($prefix);

        $item = $gen->create_hierarchy(
            $framework->id,
            $prefix,
            [
                'frameworkid' => $framework->id,
                'fullname' => 'Unit',
                'idnumber' => '000',
                'shortname' => 'item'
            ]
        );

        $assignment = $this->create_workflow_with_assignment([
            'type' => $code,
            'identifier' => $item->id,
        ]);

        // check deleting hierarchy item hook
        $hook = new pre_delete_item_check($item->id, $prefix);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();

        $this->assertCount(1, $hook->get_reasons());
        $this->assertStringContainsString($prefix, $hook->get_reasons()[0]);

        // check deleting hierarchy item's framework hook
        $hook = new pre_delete_framework_check($item->frameworkid, $prefix);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();

        $this->assertCount(1, $hook->get_reasons());
        $this->assertStringContainsString($prefix, $hook->get_reasons()[0]);

        // archive the assignment
        $assignment->archive();

        // execute the hook again
        $hook = new pre_delete_item_check($item->id, $prefix);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();
        $this->assertEmpty($hook->get_reasons());

        // check deleting hierarchy item's framework hook
        $hook = new pre_delete_framework_check($item->frameworkid, $prefix);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();
        $this->assertEmpty($hook->get_reasons());
    }

    public function test_it_prevents_deleting_cohorts_used_as_assignments() {
        $item = $this->getDataGenerator()->create_cohort();
        $assignment = $this->create_workflow_with_assignment([
            'type' => cohort_type::get_code(),
            'identifier' => $item->id,
        ]);

        // check deleting cohort hook
        $hook = new pre_delete_cohort_check($item->id);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();

        $this->assertCount(1, $hook->get_reasons());
        $this->assertStringContainsString('audience', $hook->get_reasons()[0]);

        // archive the assignment
        $assignment->archive();

        // execute the hook again
        $hook = new pre_delete_cohort_check($item->id);
        $this->assertEmpty($hook->get_reasons());
        $hook->execute();
        $this->assertEmpty($hook->get_reasons());
    }


    private function create_workflow_with_assignment($data = []): assignment {
        $name = "Test" . '-' . $data['type'] . '-' . $data['identifier'];
        $workflow_type = workflow_type::create($name);
        $form = form::create('simple', $name);
        return workflow::create(
            $workflow_type,
            $form,
            'Test',
            '',
            $data['type'],
            $data['identifier']
        )->get_default_assignment();
    }
}
