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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totaralearning.com>
 * @package mod_approval
 */

use core_phpunit\testcase;
use core\orm\collection;
use core\orm\query\builder;
use mod_approval\model\assignment\approver_type\user as user_approver_type;
use mod_approval\entity\assignment\assignment_approver as approver_entity;
use mod_approval\model\assignment\assignment_approver as approver_model;
use mod_approval\model\assignment\assignment as assignment_model;
use mod_approval\model\workflow\workflow;
use mod_approval\testing\approval_workflow_test_setup;
use mod_approval\model\assignment\assignment;
use mod_approval\userdata\approver_user as approver_user;
use totara_userdata\userdata\target_user;
use totara_userdata\userdata\item;

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\userdata\approver_user
 */
class mod_approval_userdata_approver_user_test extends testcase {

    use approval_workflow_test_setup;

    protected $workflow;
    /** @var target_user $target_user */
    protected $target_user;
    /** @var target_user $target_user */
    protected $target_user2;
    /** @var approver_model $approver */
    protected $approver;
    /** @var approver_model $approver2 */
    protected $approver2;

    /**
     * @inheritDoc
     */
    protected function setUp(): void {
        $this->setAdminUser();
        $core_generator = \core\testing\generator::instance();
        $user1 = $core_generator->create_user();
        $user2 = $core_generator->create_user();

        // Create a workflow with assignment overrides
        list($workflow_entity, $framework, $assignment_entity, $override_entities) = $this->create_workflow_and_assignment('Testing', true);
        $workflow = workflow::load_by_entity($workflow_entity);
        $stage1 = $workflow->latest_version->stages->first();
        $stage2 = $workflow->latest_version->get_next_stage($stage1->id);
        $assignment = assignment_model::load_by_entity($assignment_entity);
        $approval_level = $stage2->approval_levels->first();
        $overrides = new collection();
        foreach ($override_entities as $override_entity) {
            $overrides->append(assignment_model::load_by_entity($override_entity));
        }

        $approver = approver_model::create(
            $assignment,
            $approval_level,
            user_approver_type::TYPE_IDENTIFIER,
            $user1->id
        );
        $approver2 = approver_model::create(
            $overrides->find('assignment_identifier', $framework->agency->subagency_a->id),
            $approval_level,
            user_approver_type::TYPE_IDENTIFIER,
            $user2->id
        );

        // Storing approvers ID to be used later.
        $this->target_user = new target_user($user1);
        $this->target_user2 = new target_user($user2);
        $this->approver = $approver;
        $this->approver2 = $approver2;
        $this->workflow = workflow::load_by_id($workflow_entity->id);
        parent::setUp();
    }

    protected function tearDown(): void {
        $this->workflow = null;
        $this->target_user = null;
        $this->target_user2 = null;
        $this->approver = null;
        $this->approver2 = null;

        parent::tearDown();
    }

    public function test_count() {
        $assignment = assignment::load_by_id($this->workflow->default_assignment->id);

        // Test module context
        $context_module = $assignment->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_module));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_module));

        // Test system context
        $this->assertEquals(5, approver_user::execute_count($this->target_user, context_system::instance()));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, context_system::instance()));

        // Test course context
        $context_course = $this->workflow->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, context_system::instance()));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_course));

        // Test course category context
        $course = get_course($this->workflow->course_id);
        $context_coursecat = context_coursecat::instance($course->category);
        $this->assertEquals(5, approver_user::execute_count($this->target_user, context_system::instance()));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_coursecat));
    }

    public function test_export() {
        $assignment = assignment::load_by_id($this->workflow->default_assignment->id);

        // Test module context
        $context_module = $assignment->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_module));
        $export = approver_user::execute_export($this->target_user, $context_module);
        $data = $export->data;
        $this->assertCount(5, $data);
        $this->assertEquals($this->target_user->id, $data[0]->identifier);
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_module));
        $export = approver_user::execute_export($this->target_user2, $context_module);
        $data = $export->data;
        $this->assertCount(3, $data);
        $this->assertEquals($this->target_user2->id, $data[0]->identifier);

        // Test system context
        $this->assertEquals(5, approver_user::execute_count($this->target_user, context_system::instance()));
        $export = approver_user::execute_export($this->target_user, context_system::instance());
        $data = $export->data;
        $this->assertCount(5, $data);
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, context_system::instance()));
        $export = approver_user::execute_export($this->target_user2, context_system::instance());
        $data = $export->data;
        $this->assertCount(3, $data);

        // Test course context
        $context_course = $this->workflow->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_course));
        $export = approver_user::execute_export($this->target_user, $context_course);
        $data = $export->data;
        $this->assertCount(5, $data);
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_course));
        $export = approver_user::execute_export($this->target_user2, $context_course);
        $data = $export->data;
        $this->assertCount(3, $data);

        // Test course category context
        $course = get_course($this->workflow->course_id);
        $context_coursecat = context_coursecat::instance($course->category);
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_coursecat));
        $export = approver_user::execute_export($this->target_user, $context_coursecat);
        $data = $export->data;
        $this->assertCount(5, $data);
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_coursecat));
        $export = approver_user::execute_export($this->target_user2, $context_coursecat);
        $data = $export->data;
        $this->assertCount(3, $data);
    }

    public function test_purge_module_context() {
        $assignment = assignment::load_by_id($this->workflow->default_assignment->id);

        // Test module context
        $context_module = $assignment->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_module));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_module));

        $this->assertCount(1, $this->approver->descendants);
        $this->assertCount(2, $this->approver2->descendants);

        $status = approver_user::execute_purge($this->target_user, $context_module);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);

        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user->id)
                ->count()
        );

        // Check approver's descendants has been deleted as well.
        $hasdescendantsapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver->id)->exists();
        $this->assertFalse($hasdescendantsapprover);

        // Override approver is still there
        $this->assertEquals(
            3,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertTrue($hasdescendantsnewapprover);

        // Purge override
        $status = approver_user::execute_purge($this->target_user2, $context_module);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertFalse($hasdescendantsnewapprover);
    }

    public function test_purge_course_context() {
        $context_course = $this->workflow->get_context();
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_course));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_course));

        $status = approver_user::execute_purge($this->target_user, $context_course);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user->id)
                ->count()
        );
        // Check approver's descendants has been deleted as well.
        $hasdescendantsapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver->id)->exists();
        $this->assertFalse($hasdescendantsapprover);


        // Override approver is still there
        $this->assertEquals(
            3,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertTrue($hasdescendantsnewapprover);

        // Purge override
        $status = approver_user::execute_purge($this->target_user2, $context_course);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertFalse($hasdescendantsnewapprover);
    }

    public function test_purge_category_context() {
        $course = get_course($this->workflow->course_id);
        $context_coursecat = context_coursecat::instance($course->category);
        $this->assertEquals(5, approver_user::execute_count($this->target_user, $context_coursecat));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, $context_coursecat));

        $status = approver_user::execute_purge($this->target_user, $context_coursecat);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user->id)
                ->count()
        );
        // Check approver's descendants has been deleted as well.
        $hasdescendantsapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver->id)->exists();
        $this->assertFalse($hasdescendantsapprover);

        // Override approver is still there
        $this->assertEquals(
            3,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertTrue($hasdescendantsnewapprover);


        // Purge override
        $status = approver_user::execute_purge($this->target_user2, $context_coursecat);
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertFalse($hasdescendantsnewapprover);
    }

    public function test_purge_system_context_ancestor() {
        $this->assertEquals(5, approver_user::execute_count($this->target_user, context_system::instance()));
        $this->assertEquals(3, approver_user::execute_count($this->target_user2, context_system::instance()));
        $status = approver_user::execute_purge($this->target_user, context_system::instance());
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user->id)
                ->count()
        );
        // Check approver's descendants has been deleted as well.
        $hasdescendantsapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver->id)->exists();
        $this->assertFalse($hasdescendantsapprover);

        // Override approver is still there
        $this->assertEquals(
            3,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertTrue($hasdescendantsnewapprover);

        // Purge override
        $status = approver_user::execute_purge($this->target_user2, context_system::instance());
        $this->assertEquals(item::RESULT_STATUS_SUCCESS, $status);
        $this->assertEquals(
            0,
            approver_entity::repository()
                ->where('identifier', '=', $this->target_user2->id)
                ->count()
        );
        $hasdescendantsnewapprover = builder::table('approval_approver')->where('ancestor_id', $this->approver2->id)->exists();
        $this->assertFalse($hasdescendantsnewapprover);
    }
}