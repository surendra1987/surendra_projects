<?php
/*
 * This file is part of Totara LMS
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_core
 */

defined('MOODLE_INTERNAL') || die();

class totara_core_event_user_unsuspended_test extends \core_phpunit\testcase {
    public function test_event() {

        $user = $this->getDataGenerator()->create_user();

        $event = \totara_core\event\user_unsuspended::create_from_user($user);
        $event->trigger();

        $this->assertSame('user', $event->objecttable);
        $this->assertSame($user->id, $event->objectid);
        $this->assertSame('u', $event->crud);
        $this->assertSame($event::LEVEL_OTHER, $event->edulevel);
        $this->assertSame(CONTEXT_USER, $event->contextlevel);
        $this->assertSame($user->id, $event->contextinstanceid);
        $this->assertSame($user->username, $event->other['username']);

        $this->assertEventContextNotUsed($event);

        $data = array(
            'objectid' => $user->id,
            'context' => \context_user::instance($user->id),
            'other' => array()
        );
        try {
            $event = \totara_core\event\user_unsuspended::create($data);
            $this->fail('coding_exception expected when username missing');
        } catch (moodle_exception $ex) {
            $this->assertInstanceOf('coding_exception', $ex);
            $this->assertEquals('Coding error detected, it must be fixed by a programmer: username must be set in $other.', $ex->getMessage());
        }
    }

    public function test_unsuspended_user() {
        global $DB;

        /** @var \totara_tenant\testing\generator $tenantgenerator */
        $tenantgenerator = \totara_tenant\testing\generator::instance();
        $tenantgenerator->enable_tenants();
        $this->setAdminUser();
        $tenant = $tenantgenerator->create_tenant();

        $user = $this->getDataGenerator()->create_user(['suspended' => 1, 'tenantid' => $tenant->id]);
        $sink = $this->redirectEvents();
        $result = user_unsuspend_user($user->id);
        $this->assertTrue($result);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(core\event\user_updated::class, $events[0]);
        $this->assertInstanceOf(totara_core\event\user_unsuspended::class, $events[1]);
        $newuser = $DB->get_record('user', ['id' => $user->id]);
        $this->assertSame($user->timecreated, $newuser->timecreated);
        $this->assertTimeCurrent($newuser->timemodified);
        $this->assertSame('0', $newuser->suspended);

        // Check if observer does his job.
        $user2 = $this->getDataGenerator()->create_user(['suspended' => 1, 'tenantid' => $tenant->id]);
        $DB->delete_records('cohort_members', ['cohortid' => $tenant->cohortid, 'userid' => $user2->id]);

        // We are mocking the situation when tenant member not in the tenant audience.
        $this->assertFalse($DB->record_exists('cohort_members', ['cohortid' => $tenant->cohortid, 'userid' => $user2->id]));
        user_unsuspend_user($user2->id);
        $newuser = $DB->get_record('user', ['id' => $user2->id]);
        $this->assertSame($user2->timecreated, $newuser->timecreated);
        $this->assertTimeCurrent($newuser->timemodified);
        $this->assertSame('0', $newuser->suspended);
        // User is a member of tenant audience now.
        $this->assertTrue($DB->record_exists('cohort_members', ['cohortid' => $tenant->cohortid, 'userid' => $user2->id]));
    }
}
