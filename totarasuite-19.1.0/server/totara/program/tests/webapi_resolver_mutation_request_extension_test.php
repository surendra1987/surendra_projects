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
 * @package totara_program
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_job\job_assignment;
use totara_program\assignment\individual;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_program_webapi_resolver_mutation_request_extension_test extends testcase {
    use webapi_phpunit_helper;

    public const MUTATION_NAME = 'totara_program_request_extension';

    /**
     * @var \core\testing\generator
     */
    protected $generator;

    /**
     * @var \totara_program\testing\generator
     */
    protected $generator_program;

    protected $program;

    protected $user;

    /**
     * @return void
     */
    public function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->generator_program = $this->generator->get_plugin_generator('totara_program');
        $this->program = $this->generator_program->create_program();

        $this->user = $this->generator->create_user();
        $manager_job = job_assignment::create_default(get_admin()->id);
        job_assignment::create_default($this->user->id, ['managerjaid' => $manager_job->id]);
        $this->generator_program->assign_program($this->program->id, [$this->user->id]);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->generator_program = null;
        $this->program = null;
        $this->user = null;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_create_request_anextension(): void {
        $this->setUser($this->user);

        // Update timedue.
        $sql = "UPDATE {prog_completion} SET timedue = :timedue WHERE coursesetid = 0 AND programid = ". $this->program->id;
        builder::get_db()->execute($sql, ['timedue' => 1708043758]);

        self::assertFalse(
            builder::get_db()->record_exists('notifiable_event_queue', ['resolver_class_name' => 'totara_program\totara_notification\resolver\extension_requested'])
        );

        $result = $this->execute_graphql_operation(self::MUTATION_NAME, [
            'input' => [
                'id' => $this->program->id,
                'extreason' => 'haha',
                'extdatetime' => 1897432558,
            ]
        ]);
        $data = $result->data;
        $data = $data[self::MUTATION_NAME];
        self::assertTrue($data['success']);
        self::assertEquals(get_string('pendingextension', 'totara_program'),  $data['message']);

        // Notification is created.
        self::assertTrue(
            builder::get_db()->record_exists('notifiable_event_queue', ['resolver_class_name' => 'totara_program\totara_notification\resolver\extension_requested'])
        );
    }

    /**
     * @return void
     */
    public function test_reslove_can_not_request_before_timedue(): void {
        $this->setUser($this->user);

        $timedue = 1708043758;
        // Update timedue.
        $sql = "UPDATE {prog_completion} SET timedue = :timedue WHERE coursesetid = 0 AND programid = ". $this->program->id;
        builder::get_db()->execute($sql, ['timedue' => $timedue]);

        $result = $this->resolve_graphql_mutation(self::MUTATION_NAME, [
            'input' => [
                'id' => $this->program->id,
                'extreason' => 'haha',
                'extdatetime' => $timedue - 1000,
            ]
        ]);

        self::assertFalse($result['success']);
        self::assertEquals(get_string('extensionearlierthanduedate', 'totara_program'),  $result['message']);
    }

    /**
     * @return void
     */
    public function test_resolve_advance_feature(): void {
        $user = $this->generator->create_user();
        $this->setUser($user);

        \totara_core\advanced_feature::disable('programs');
        $this->expectException(\totara_core\feature_not_available_exception::class);
        $this->expectExceptionMessage('Feature programs is not available.');
        $this->resolve_graphql_mutation(self::MUTATION_NAME, [
            'input' => [
                'id' => $this->program->id,
                'extreason' => 'haha',
                'extdatetime' => 1708043758,
            ]
        ]);
    }

    /**
     * @return void
     */
    public function test_resolve_can_not_request(): void {
        $user = $this->generator->create_user();
        $this->setUser($user);

        $this->expectException(\totara_program\ProgramException::class);
        $this->expectExceptionMessage('An error occurred when processing extension request');
        $this->resolve_graphql_mutation(self::MUTATION_NAME, [
            'input' => [
                'id' => $this->program->id,
                'extreason' => 'haha',
                'extdatetime' => 1708043758,
            ]
        ]);
    }

    /**
     * @return void
     */
    public function test_resolve_no_login(): void {
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');
        $this->resolve_graphql_mutation(self::MUTATION_NAME, [
            'input' => [
                'id' => 2,
                'extreason' => 'haha',
                'extdatetime' => 1708043758,
            ]
        ]);
    }

    /**
     * @return void
     */
    public function test_resolve_create_extenstion_repeatedly(): void {
        $this->setUser($this->user);
        $db = builder::get_db();
        // Update timedue.
        $sql = "UPDATE {prog_completion} SET timedue = :timedue WHERE coursesetid = 0 AND programid = ". $this->program->id;
        $db->execute($sql, ['timedue' => 1708043758]);

        $extension = new stdClass();
        $extension->programid = $this->program->id;
        $extension->userid = $this->user->id;
        $extension->extensiondate = 1897432558;
        $extension->extensionreason = 'haha';
        $extension->status = 0;

        $db->insert_record('prog_extension', $extension);

        $this->expectException(\totara_program\ProgramException::class);
        $this->expectExceptionMessage('Can not create the unapproved request extension repeatedly.');
        $this->resolve_graphql_mutation(
            self::MUTATION_NAME,
            [
                'input' => [
                    'id' => $this->program->id,
                    'extreason' => 'haha',
                    'extdatetime' => 1897432558,
                ]
            ]
        );
    }
}