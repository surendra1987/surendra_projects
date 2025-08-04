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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_approval
 */

use core\entity\user;
use core_phpunit\testcase;
use mod_approval\entity\workflow\workflow;
use mod_approval\entity\workflow\workflow_stage_approval_level as workflow_stage_approval_level_entity;
use mod_approval\model\assignment\approver_type\user as user_approver_type;
use mod_approval\model\assignment\assignment as assignment_model;
use mod_approval\model\assignment\assignment_approver;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\model\workflow\workflow as workflow_model;
use mod_approval\model\workflow\workflow_stage_approval_level as workflow_stage_approval_level_model;
use mod_approval\model\workflow\workflow_version as workflow_version_model;
use mod_approval\testing\approval_workflow_test_setup;
use mod_approval\testing\generator as approval_generator;
use mod_approval\totara_notification\recipient\all_approvers;

defined('MOODLE_INTERNAL') || die();

/**
 * @group approval_workflow
 * @group totara_notification
 */
class mod_approval_totara_notification_recipient_all_approvers_test extends testcase {

    use approval_workflow_test_setup;

    public function test_get_name(): void {
        self::assertEquals(get_string('notification:recipient_all_approvers', 'mod_approval'), all_approvers::get_name());
    }

    public function test_get_user_ids(): void {
        $generator = $this->getDataGenerator();

        /** @var workflow $workflow */
        list($workflow, , $assignment_entity) = $this->create_workflow_and_assignment('Testing', false, false);
        $user = new user($generator->create_user()->id);
        $approval_generator = approval_generator::instance();
        $workflow_version = workflow_version_model::load_latest_by_workflow_id($workflow->id);
        $approval_generator->create_workflow_stage($workflow_version->id, 'Stage 4', approvals::get_enum());

        $application = $this->create_application($workflow, $assignment_entity, $user);

        $stage_1 = $workflow_version->stages->first();
        $stage_2 = $workflow_version->get_next_stage($stage_1->id);
        $stage_4 = $workflow_version->get_next_stage($stage_2->id); // Note that stage 4 was added before stage 3, because reasons.

        $approval_levels_feature = new \mod_approval\model\workflow\stage_feature\approval_levels($stage_2);
        $approval_levels_feature->add('Level 2');

        $approval_stage_2_levels = workflow_stage_approval_level_entity::repository()
            ->where('workflow_stage_id', '=', $stage_2->id)
            ->order_by('sortorder')
            ->get();

        /** @var workflow_stage_approval_level_entity $approval_stage_2_level_1 */
        $approval_stage_2_level_1 = $approval_stage_2_levels->first();
        /** @var workflow_stage_approval_level_entity $approval_stage_2_level_2 */
        $approval_stage_2_level_2 = $approval_stage_2_levels->last();
        /** @var workflow_stage_approval_level_entity $approval_stage_4 */
        $approval_stage_4 = workflow_stage_approval_level_entity::repository()
            ->where('workflow_stage_id', '=', $stage_4->id)
            ->order_by('id')
            ->first();

        // Initially there are no approvers.
        self::assertEmpty( all_approvers::get_user_ids([
            'application_id' => $application->id,
        ]));

        // Add some approvers.
        $approver_stage_2_level_1 = $generator->create_user();
        $approver_stage_2_level_2 = $generator->create_user();
        $approver_stage_4 = $generator->create_user();
        $approver_stage_2_level_1_and_stage_4 = $generator->create_user();

        $assignment_model = assignment_model::load_by_entity($assignment_entity);
        $approval_stage_2_level_1_model = workflow_stage_approval_level_model::load_by_entity($approval_stage_2_level_1);
        $approval_stage_2_level_2_model = workflow_stage_approval_level_model::load_by_entity($approval_stage_2_level_2);
        $approval_stage_4_model = workflow_stage_approval_level_model::load_by_entity($approval_stage_4);

        assignment_approver::create($assignment_model, $approval_stage_2_level_1_model, user_approver_type::TYPE_IDENTIFIER, $approver_stage_2_level_1->id);
        assignment_approver::create($assignment_model, $approval_stage_2_level_2_model, user_approver_type::TYPE_IDENTIFIER, $approver_stage_2_level_2->id);
        assignment_approver::create($assignment_model, $approval_stage_4_model, user_approver_type::TYPE_IDENTIFIER, $approver_stage_4->id);
        assignment_approver::create($assignment_model, $approval_stage_2_level_1_model, user_approver_type::TYPE_IDENTIFIER, $approver_stage_2_level_1_and_stage_4->id);
        assignment_approver::create($assignment_model, $approval_stage_4_model, user_approver_type::TYPE_IDENTIFIER, $approver_stage_2_level_1_and_stage_4->id);

        workflow_model::load_by_entity($workflow)->publish(workflow_version_model::load_by_entity($workflow_version->get_entity_copy()));
        $next_state = $application->current_stage->state_manager->get_next_state($application->current_state);
        $application->set_current_state($next_state);

        // Check that we get four unique approvers.
        self::assertEqualsCanonicalizing([
            $approver_stage_2_level_1->id,
            $approver_stage_2_level_2->id,
            $approver_stage_4->id,
            $approver_stage_2_level_1_and_stage_4->id,
        ], all_approvers::get_user_ids([
            'application_id' => $application->id,
        ]));
    }
}
