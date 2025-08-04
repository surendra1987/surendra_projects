<?php
/**
 * This file is part of Totara Learn
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package totara_program
 */

defined('MOODLE_INTERNAL') || die();

use core\json_editor\helper\document_helper;
use core\json_editor\node\paragraph;
use core_phpunit\testcase;
use totara_core\extended_context;
use totara_notification\loader\notification_preference_loader;
use totara_notification\manager\notification_preference_manager;
use totara_notification\model\notification_preference;
use totara_notification\recipient\subject;
use totara_notification\testing\generator as notification_generator;
use totara_program\testing\generator as program_generator;
use totara_program\totara_notification\notification\completed_for_subject as subject_completed;
use totara_program\totara_notification\resolver\course_set_due_date as course_set_due_date_resolver;

/**
 * @group totara_notification
 */
class totara_program_totara_notification_cloning_test extends testcase {
    private $template = null;

    protected function setUp(): void {
        global $DB;

        // Stashing the generators up here.
        $prog_gen = program_generator::instance();
        $notif_gen = notification_generator::instance();

        // Create a program with custom/amended notifs
        $this->template = $prog_gen->create_program(['fullname' => 'Program notifications template']);

        // Create a custom notification in the programs context.
        $ext_context = extended_context::make_with_context(
            context_program::instance($this->template->id)
        );

        $notif_gen->create_notification_preference(
            course_set_due_date_resolver::class,
            $ext_context,
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

        // Amend a system notification for the programs context.
        $record = $DB->get_record(
            'notification_preference',
            [
                'notification_class_name' => subject_completed::class,
                'ancestor_id' => null
            ]
        );
        $preference = notification_preference::from_id($record->id);
        $notif_gen->create_overridden_notification_preference(
            $preference,
            $ext_context,
            ['subject' => 'Test notification subject'],
        );
    }

    protected function tearDown(): void {
        $this->template = null;
        parent::tearDown();
    }

    public function test_notification_duplication() {

        $from_context = extended_context::make_with_context(
            context_program::instance($this->template->id)
        );

        $prog_gen = program_generator::instance();
        $new_prog = $prog_gen->create_program(['fullname' => 'Program notifications template']);
        $to_context = extended_context::make_with_context(
            context_program::instance($new_prog->id)
        );

        $preferences = notification_preference_loader::get_notification_preferences($from_context, null, true);
        self::assertCount(2, $preferences);

        $preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        self::assertCount(0, $preferences);

        $manager = notification_preference_manager::get_instance();
        $manager->clone_context_notifications($to_context, $from_context);
        notification_preference_loader::clear_cache();

        $preferences = notification_preference_loader::get_notification_preferences($from_context, null, true);
        self::assertCount(2, $preferences);

        $preferences = notification_preference_loader::get_notification_preferences($to_context, null, true);
        self::assertCount(2, $preferences);

        foreach ($preferences as $preference) {
            self::assertSame('Test notification subject', $preference->get_subject());
        }
    }

}
