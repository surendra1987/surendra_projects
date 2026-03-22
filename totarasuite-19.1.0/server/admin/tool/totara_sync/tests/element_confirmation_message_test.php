<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package tool_totara_sync
 */

use core_phpunit\testcase;

global $CFG;
require_once($CFG->dirroot . '/admin/tool/totara_sync/lib.php');
require_once($CFG->dirroot . '/admin/tool/totara_sync/elements/org.php');
require_once($CFG->dirroot . '/admin/tool/totara_sync/elements/user.php');

/**
 * Tests totara_sync_element::get_confirmation_message and related methods.
 *
 * @group tool_totara_sync
 */
class tool_totara_sync_element_confirmation_message_test extends testcase {

    public function test_get_confirmation_message_when_there_are_less_records_to_import_than_existing() {
        $element = $this->getMockBuilder(totara_sync_element_org::class)
            ->onlyMethods(['get_current_import_count', 'get_existing_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(892);
        $element->method('get_existing_import_count')
            ->willReturn(1234);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;

        $this->assertEquals('The upload contains 28% less organisations than the site currently has. This action will delete any missing organisations.',
            $element->get_confirmation_message());
    }

    public function test_get_confirmation_message_when_there_are_more_or_equal_records_to_import_than_existing() {
        $element = $this->getMockBuilder(totara_sync_element_org::class)
            ->onlyMethods(['get_current_import_count', 'get_existing_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(10);
        $element->method('get_existing_import_count')
            ->willReturn(10);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_when_there_are_no_records_to_import() {
        $element = $this->getMockBuilder(totara_sync_element_org::class)
            ->onlyMethods(['get_current_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(0);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_when_sourceallrecords_is_false() {
        $element = new totara_sync_element_org();
        $element->config = new stdClass();
        $element->config->sourceallrecords = 0;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_for_users_element_when_there_are_less_records_to_import_than_existing() {
        $element = $this->getMockBuilder(totara_sync_element_user::class)
            ->onlyMethods(['get_current_import_count', 'get_existing_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(892);
        $element->method('get_existing_import_count')
            ->willReturn(1234);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;

        $element->config->allow_delete = totara_sync_element_user::SUSPEND_USERS;
        $this->assertEquals('The upload contains 28% less users than the site currently has. This action will suspend any missing users.',
            $element->get_confirmation_message());

        $element->config->allow_delete = totara_sync_element_user::DELETE_USERS;
        $this->assertEquals('The upload contains 28% less users than the site currently has. This action will delete any missing users.',
            $element->get_confirmation_message());
    }

    public function test_get_confirmation_message_for_users_element_when_there_are_more_or_equal_records_to_import_than_existing() {
        $element = $this->getMockBuilder(totara_sync_element_user::class)
            ->onlyMethods(['get_current_import_count', 'get_existing_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(10);
        $element->method('get_existing_import_count')
            ->willReturn(10);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;
        $element->config->allow_delete = totara_sync_element_user::DELETE_USERS;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_for_users_element_when_there_are_no_records_to_import() {
        $element = $this->getMockBuilder(totara_sync_element_user::class)
            ->onlyMethods(['get_current_import_count'])
            ->getMock();
        $element->method('get_current_import_count')
            ->willReturn(0);
        $element->config = new stdClass();
        $element->config->sourceallrecords = 1;
        $element->config->allow_delete = totara_sync_element_user::DELETE_USERS;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_for_users_element_when_sourceallrecords_is_false() {
        $element = new totara_sync_element_user();
        $element->config = new stdClass();
        $element->config->sourceallrecords = 0;

        $this->assertNull($element->get_confirmation_message());
    }

    public function test_get_confirmation_message_for_users_element_when_keep_users_is_enabled() {
        $element = new totara_sync_element_user();
        $element->config = new stdClass();
        $element->config->allow_delete = totara_sync_element_user::KEEP_USERS;

        $this->assertNull($element->get_confirmation_message());
    }
}