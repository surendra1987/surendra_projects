<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 */

use core\entity\course_categories;
use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core\testing\component_generator;
use core_phpunit\event_sink;
use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\loader\notification_preference_loader;
use totara_notification\model\notification_preference;
use totara_notification\recipient\subject;
use totara_notification\testing\generator as notification_generator;
use totara_program\event\program_cloned;
use totara_program\totara_notification\notification\completed_for_subject;
use totara_program\totara_notification\resolver\course_set_due_date;
use totara_program\webapi\resolver\mutation\clone_program;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_certification\totara_notification\notification\completed_for_subject as cert_completed_for_subject;
use totara_certification\totara_notification\resolver\course_set_due_date as cert_course_set_due_date;

class totara_program_webapi_resolver_mutation_clone_program_test extends testcase {
    use webapi_phpunit_helper;

    public const MUTATION_NAME = 'totara_program_clone_program';
    protected ?\totara_program\program $origin_program;
    protected ?component_generator $program_generator;
    protected ?event_sink $event_sink;
    protected ?\totara_program\program $origin_certification;

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_without_permission(): void {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string('nopermissions', 'error', 'Clone Program')
        );
        $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES'],
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_without_sections(): void {
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $this->expectExceptionMessage('clone_sections must include at least one of: DETAILS, CONTENT, NOTIFICATION_PREFERENCES');
        $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => []
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_with_bad_sections(): void {
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $this->expectExceptionMessage('clone_sections must include at least one of: DETAILS, CONTENT, NOTIFICATION_PREFERENCES');
        $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['bad_value_1', 'bad_value_2']
                ],
            ]
        );
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_details_only(): void {
        $original_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['DETAILS']
                ],
            ]
        );

        $this->assertNotSame($this->origin_program->id, $cloned_program->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_program->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_program->shortname);

        // assert neither content nor notification preferences were cloned
        $this->assertCount(0, $cloned_program->get_content()->get_course_sets());
        $to_context = extended_context::make_with_context(
            context_program::instance($cloned_program->id)
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(0, $cloned_preferences);

        $this->check_clone_event_was_triggered($cloned_program, $clone_user);

        // check the program count was increased for the category
        $updated_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $this->assertEquals($original_program_count + 1, $updated_program_count);
    }

    /**
     * @param program $cloned_program
     * @param stdClass $user
     * @return void
     */
    protected function check_clone_event_was_triggered(\totara_program\program $cloned_program, stdClass $user): void {
        $events = $this->event_sink->get_events();
        $triggered = false;
        $expected_description = "Program #{$cloned_program->id} was cloned from Program #{$this->origin_program->id} by user {$user->id}";

        foreach ($events as $event) {
            if ($event instanceof program_cloned) {
                $this->assertSame($expected_description, $event->get_description());
                $this->assertSame($cloned_program->get_context()->id, $event->get_context()->id);
                $triggered = true;
            }
        }

        $this->assertTrue($triggered);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_content_only(): void {
        $original_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        /** @var program $cloned_program */
        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['CONTENT']
                ],
            ]
        );

        $this->assertNotSame($this->origin_program->id, $cloned_program->id);
        $this->assertCount(1, $cloned_program->get_content()->get_course_sets()[0]->courses);
        $this->assertCount(1, $cloned_program->get_content()->get_course_sets()[1]->courses);
        $this->assertCount(2, $cloned_program->get_content()->get_course_sets());

        // assert neither details (except for category, fullname, shortname) nor notification preferences were cloned
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_program->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_program->shortname);
        $this->assertEmpty($cloned_program->summary);
        $to_context = extended_context::make_with_context(
            context_program::instance($cloned_program->id)
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(0, $cloned_preferences);

        $this->check_clone_event_was_triggered($cloned_program, $clone_user);

        // check the program count was increased for the category
        $updated_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $this->assertEquals($original_program_count + 1, $updated_program_count);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_notification_preferences_only(): void {
        $original_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        /** @var program $cloned_program */
        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        $this->assertNotSame($this->origin_program->id, $cloned_program->id);

        $to_context = extended_context::make_with_context(
            $cloned_program->get_context(),
            'totara_program',
            'program',
            $cloned_program->id
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(2, $cloned_preferences);
        foreach ($cloned_preferences as $preference) {
            $this->assertSame('Test notification subject', $preference->get_subject());
        }

        // assert neither details (except for category, fullname, shortname) nor content were cloned
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_program->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_program->shortname);
        $this->assertEmpty($cloned_program->summary);
        $this->assertCount(0, $cloned_program->get_content()->get_course_sets());

        $this->check_clone_event_was_triggered($cloned_program, $clone_user);

        // check the program count was increased for the category
        $updated_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $this->assertEquals($original_program_count + 1, $updated_program_count);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_clone_program_all_sections(): void {
        $original_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        // details were cloned
        $this->assertNotSame($this->origin_program->id, $cloned_program->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_program->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_program->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_program->shortname);

        // content was cloned
        $this->assertNotSame($this->origin_program->id, $cloned_program->id);
        $this->assertCount(1, $cloned_program->get_content()->get_course_sets()[0]->courses);
        $this->assertCount(1, $cloned_program->get_content()->get_course_sets()[1]->courses);
        $this->assertCount(2, $cloned_program->get_content()->get_course_sets());

        // notification preferences were cloned
        $to_context = extended_context::make_with_context(
            $cloned_program->get_context(),
            'totara_program',
            'program',
            $cloned_program->id
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(2, $cloned_preferences);
        foreach ($cloned_preferences as $preference) {
            $this->assertSame('Test notification subject', $preference->get_subject());
        }

        $this->check_clone_event_was_triggered($cloned_program, $clone_user);

        // check the program count was increased for the category
        $updated_program_count = (int) course_categories::repository()
            ->find($this->origin_program->category)
            ->programcount;
        $this->assertEquals($original_program_count + 1, $updated_program_count);
    }

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function setup(): void {
        global $DB;

        /** @var totara_program\testing\generator $program_generator */
        $this->program_generator = $this->getDataGenerator()->get_plugin_generator('totara_program');
        $this->origin_program = $this->program_generator->create_program();
        $this->origin_certification = $this->program_generator->create_certification();
        prog_fix_program_sortorder($this->origin_program->category);

        // add event sink for capturing clone event
        $this->event_sink = $this->redirectEvents();
        $this->event_sink->clear();

        // add some program content
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $this->program_generator->add_courses_and_courseset_to_program($this->origin_program, [[$course1], [$course2]]);
        $this->program_generator->add_courses_and_courseset_to_program($this->origin_certification, [[$course1], [$course2]], CERTIFPATH_CERT);

        // add some notification preferences
        $origin_ext_context = extended_context::make_with_context(
            $this->origin_certification->get_context(),
            'totara_certification',
            'program',
            $this->origin_certification->id
        );
        $notif_gen = notification_generator::instance();

        // custom notification preference
        $notif_gen->create_notification_preference(
            cert_course_set_due_date::class,
            $origin_ext_context,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Test notification body'),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );

        // amended system notification
        $record = $DB->get_record(
            'notification_preference',
            [
                'notification_class_name' => cert_completed_for_subject::class,
                'ancestor_id' => null
            ]
        );
        $preference = notification_preference::from_id($record->id);
        $notif_gen->create_overridden_notification_preference(
            $preference,
            $origin_ext_context,
            ['subject' => 'Test notification subject'],
        );

        // add some notification preferences
        $origin_ext_context = extended_context::make_with_context(
            $this->origin_program->get_context(),
            'totara_program',
            'program',
            $this->origin_program->id
        );
        $notif_gen = notification_generator::instance();

        // custom notification preference
        $notif_gen->create_notification_preference(
            course_set_due_date::class,
            $origin_ext_context,
            [
                'schedule_offset' => 0,
                'recipient' => subject::class,
                'recipients' => [subject::class],
                'body_format' => FORMAT_JSON_EDITOR,
                'body' => document_helper::json_encode_document(
                    document_helper::create_document_from_content_nodes([
                        paragraph::create_json_node_from_text('Test notification body'),
                    ])
                ),
                'subject' => 'Test notification subject',
                'subject_format' => FORMAT_PLAIN,
            ]
        );

        // amended system notification
        $record = $DB->get_record(
            'notification_preference',
            [
                'notification_class_name' => completed_for_subject::class,
                'ancestor_id' => null
            ]
        );
        $preference = notification_preference::from_id($record->id);
        $notif_gen->create_overridden_notification_preference(
            $preference,
            $origin_ext_context,
            ['subject' => 'Test notification subject'],
        );

        parent::setUp();
    }

    /**
     * @return void
     */
    public function test_clone_propram_by_tenant_domain_manger(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();

        // tennant manager.
        $tenant_domain_manager = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);

        $origin_program = $this->program_generator->create_program(['category' => $tenant1->categoryid]);
        self::setUser($tenant_domain_manager);
        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $origin_program->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        $this->assertNotSame($origin_program->id, $cloned_program->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $origin_program->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $origin_program->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_program->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_program->shortname);
        $this->assertNotSame($origin_program->id, $cloned_program->id);

        // Login as tenant user
        self::setUser($user_t1);
        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string('nopermissions', 'error', 'Clone Program')
        );
        $cloned_program = $this->resolve_graphql_mutation(
            $this->get_graphql_name(clone_program::class),
            [
                'input' => [
                    'program_id' => $this->origin_program->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->origin_program = null;
        $this->program_generator = null;
        $this->event_sink = null;
        $this->origin_certification = null;

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_clone_certification_without_permission(): void {
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string('nopermissions', 'error', 'Clone Program')
        );
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES'],
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function test_clone_certification_without_sections(): void {
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $this->expectExceptionMessage('clone_sections must include at least one of: DETAILS, CONTENT, NOTIFICATION_PREFERENCES');
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => []
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function test_clone_certification_with_bad_sections(): void {
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $this->expectExceptionMessage('clone_sections must include at least one of: DETAILS, CONTENT, NOTIFICATION_PREFERENCES');
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['bad_value_1', 'bad_value_2']
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function test_clone_certification_details_only(): void {
        $original_cert_count = (int)course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_certif = $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['DETAILS']
                ],
            ]
        );

        $this->assertNotSame($this->origin_certification->id, $cloned_certif->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_certif->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_certif->shortname);

        // assert neither content nor notification preferences were cloned
        $this->assertCount(0, $cloned_certif->get_content()->get_course_sets());
        $to_context = extended_context::make_with_context(
            context_program::instance($cloned_certif->id)
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(0, $cloned_preferences);

        $this->check_certfication_clone_event_was_triggered($cloned_certif, $clone_user);

        // check the program count was increased for the category
        $updated_cert_count = course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;

        $this->assertEquals($original_cert_count + 1, $updated_cert_count);
    }

    /**
     * @param program $cloned_cert
     * @param stdClass $user
     * @return void
     */
    protected function check_certfication_clone_event_was_triggered(\totara_program\program $cloned_cert, stdClass $user): void {
        $events = $this->event_sink->get_events();
        $triggered = false;
        $expected_description = "Certification #{$cloned_cert->id} was cloned from Certification #{$this->origin_certification->id} by user {$user->id}";

        foreach ($events as $event) {
            if ($event instanceof program_cloned) {
                $this->assertSame($expected_description, $event->get_description());
                $this->assertSame($cloned_cert->get_context()->id, $event->get_context()->id);
                $triggered = true;
            }
        }

        $this->assertTrue($triggered);
    }

    /**
     * @return void
     */
    public function test_clone_certifcation_content_only(): void {
        $original_cert_count = (int)course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_certif = $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['CONTENT']
                ],
            ]
        );

        $this->assertNotSame($this->origin_certification->id, $cloned_certif->id);
        $this->assertCount(1, $cloned_certif->get_content()->get_course_sets()[0]->courses);
        $this->assertCount(1, $cloned_certif->get_content()->get_course_sets()[1]->courses);
        $this->assertCount(2, $cloned_certif->get_content()->get_course_sets());

        // assert neither details (except for category, fullname, shortname) nor notification preferences were cloned
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_certif->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_certif->shortname);
        $this->assertEmpty($cloned_certif->summary);
        $to_context = extended_context::make_with_context(
            context_program::instance($cloned_certif->id)
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(0, $cloned_preferences);

        $this->check_certfication_clone_event_was_triggered($cloned_certif, $clone_user);

        // check the certif count was increased for the category
        $updated_cert_count = (int)course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $this->assertEquals($original_cert_count + 1, $updated_cert_count);
    }

    /**
     * @return void
     */
    public function test_clone_certification_notification_preferences_only(): void {
        $original_certif_count = (int) course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_certif = $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        $this->assertNotSame($this->origin_certification->id, $cloned_certif->id);

        $to_context = extended_context::make_with_context(
            $cloned_certif->get_context(),
            'totara_certification',
            'program',
            $cloned_certif->id
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(2, $cloned_preferences);
        foreach ($cloned_preferences as $preference) {
            $this->assertSame('Test notification subject', $preference->get_subject());
        }

        // assert neither details (except for category, fullname, shortname) nor content were cloned
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_certif->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_certif->shortname);
        $this->assertEmpty($cloned_certif->summary);
        $this->assertCount(0, $cloned_certif->get_content()->get_course_sets());

        $this->check_certfication_clone_event_was_triggered($cloned_certif, $clone_user);

        // check the certification count was increased for the category
        $updated_certif_count = (int) course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $this->assertEquals($original_certif_count + 1, $updated_certif_count);
    }

    /**
     * @return void
     */
    public function test_clone_certification_all_sections(): void {
        $original_certiif_count = (int)course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $extended_context = extended_context::make_system();
        $clone_user = self::getDataGenerator()->create_user();
        $clone_role = self::getDataGenerator()->create_role();
        self::getDataGenerator()->role_assign($clone_role, $clone_user->id);

        assign_capability(
            'totara/program:cloneprogram',
            CAP_ALLOW,
            $clone_role,
            $extended_context->get_context(),
            true
        );

        $this->setUser($clone_user);

        $cloned_certif = $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        // details were cloned
        $this->assertNotSame($this->origin_certification->id, $cloned_certif->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $this->origin_certification->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_certif->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_certif->shortname);

        // content was cloned
        $this->assertNotSame($this->origin_certification->id, $cloned_certif->id);
        $this->assertCount(1, $cloned_certif->get_content()->get_course_sets()[0]->courses);
        $this->assertCount(1, $cloned_certif->get_content()->get_course_sets()[1]->courses);
        $this->assertCount(2, $cloned_certif->get_content()->get_course_sets());

        // notification preferences were cloned
        $to_context = extended_context::make_with_context(
            $cloned_certif->get_context(),
            'totara_certification',
            'program',
            $cloned_certif->id
        );
        $cloned_preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        $this->assertCount(2, $cloned_preferences);
        foreach ($cloned_preferences as $preference) {
            $this->assertSame('Test notification subject', $preference->get_subject());
        }

        $this->check_certfication_clone_event_was_triggered($cloned_certif, $clone_user);

        // check the program count was increased for the category
        $updated_certif_count = (int)course_categories::repository()
            ->find($this->origin_certification->category)
            ->certifcount;
        $this->assertEquals($original_certiif_count + 1, $updated_certif_count);
    }

    /**
     * @return void
     */
    public function test_clone_certification_by_tenant_domain_manger(): void {
        $gen = self::getDataGenerator();
        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $gen->get_plugin_generator('totara_tenant');

        $tenant_generator->enable_tenants();

        $tenant1 = $tenant_generator->create_tenant();

        // tennant manager.
        $tenant_domain_manager = $gen->create_user(['tenantid' => $tenant1->id, 'tenantdomainmanager' => $tenant1->idnumber]);
        $user_t1 = $gen->create_user(['tenantid' => $tenant1->id]);

        $origin_certif = $this->program_generator->create_certification(['category' => $tenant1->categoryid]);
        self::setUser($tenant_domain_manager);
        $cloned_certif = $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $origin_certif->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );

        $this->assertNotSame($origin_certif->id, $cloned_certif->id);
        $expected_fullname_clone = get_string('cloneprogramnameprefix', 'totara_program', $origin_certif->fullname);
        $expected_shortname_clone = get_string('cloneprogramnameprefix', 'totara_program', $origin_certif->shortname);
        $this->assertSame($expected_fullname_clone, $cloned_certif->fullname);
        $this->assertSame($expected_shortname_clone, $cloned_certif->shortname);
        $this->assertNotSame($origin_certif->id, $cloned_certif->id);

        // Login as tenant user
        self::setUser($user_t1);
        $this->expectException(required_capability_exception::class);
        $this->expectExceptionMessage(
            get_string('nopermissions', 'error', 'Clone Program')
        );
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );
    }

    /**
     * @return void
     */
    public function test_reslove_no_login(): void {
        $this->expectException(require_login_exception::class);
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'program_id' => $this->origin_certification->id,
                    'clone_sections' => ['DETAILS', 'CONTENT', 'NOTIFICATION_PREFERENCES']
                ],
            ]
        );
    }
}