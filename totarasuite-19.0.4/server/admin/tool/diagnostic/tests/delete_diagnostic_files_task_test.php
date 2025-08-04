<?php
/*
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
 * @author Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use tool_diagnostic\task\delete_diagnostic_files_task;

/**
 * @group tool_diagnostic
 */
class tool_diagnostic_delete_diagnostic_files_task_test extends testcase {

    public function test_execute_task_time_limit(): void {
        [
            $file_to_be_deleted_1,
            $file_to_be_deleted_2,
            $file_to_be_kept,
        ] = $this->create_test_files();

        $task = new delete_diagnostic_files_task();
        $task->execute();

        $this->assertFalse(
            builder::table('files')->where('id', $file_to_be_deleted_1->get_id())->exists()
        );
        $this->assertFalse(
            builder::table('files')->where('id', $file_to_be_deleted_2->get_id())->exists()
        );
        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_kept->get_id())->exists()
        );
    }

    public static function does_not_delete_provider(): array {
        return [
            ['component', 'course'],
            ['filearea', 'something'],
            ['contextid', (string)context_user::instance(get_admin()->id)->id],
        ];
    }

    /**
     * Make sure the task doesn't delete too much.
     *
     * @dataProvider does_not_delete_provider
     * @param string $adjust_field
     * @param string $adjust_value
     * @return void
     */
    public function test_execute_task_does_not_delete(string $adjust_field, string $adjust_value): void {
        [
            $file_to_be_deleted_1,
            $file_to_be_deleted_2,
            $file_to_be_kept,
        ] = $this->create_test_files();

        // Adjust one column for one file record to make sure it doesn't get deleted with this change.
        builder::table('files')
            ->where('id', $file_to_be_deleted_1->get_id())
            ->update([$adjust_field => $adjust_value]);

        $task = new delete_diagnostic_files_task();
        $task->execute();

        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_deleted_1->get_id())->exists()
        );
        $this->assertFalse(
            builder::table('files')->where('id', $file_to_be_deleted_2->get_id())->exists()
        );
        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_kept->get_id())->exists()
        );
    }

    private function create_test_files(): array {
        /** @var file_storage $fs */
        $fs = get_file_storage();

        $file_record_to_be_deleted_1 = [
            'contextid' => SYSCONTEXTID,
            'component' => 'tool_diagnostic',
            'filearea' => 'export',
            'filepath' => '/',
            'filename' => 'filename_does_not_matter.txt',
            'itemid' => 111,
        ];

        $file_record_to_be_deleted_2 = [
            'contextid' => SYSCONTEXTID,
            'component' => 'tool_diagnostic',
            'filearea' => 'export',
            'filepath' => '/',
            'filename' => 'filename_does_not_matter.txt',
            'itemid' => 222,
        ];

        $file_record_to_be_kept = [
            'contextid' => SYSCONTEXTID,
            'component' => 'tool_diagnostic',
            'filearea' => 'export',
            'filepath' => '/',
            'filename' => 'filename_does_not_matter.txt',
            'itemid' => 333,
        ];

        $file_to_be_deleted_1 = $fs->create_file_from_string($file_record_to_be_deleted_1, 'test content');
        $file_to_be_deleted_2 = $fs->create_file_from_string($file_record_to_be_deleted_2, 'test content');
        $file_to_be_kept = $fs->create_file_from_string($file_record_to_be_kept, 'test content');

        // Make the first two files old enough to be deleted.
        builder::table('files')
            ->where_in('id', [$file_to_be_deleted_1->get_id(), $file_to_be_deleted_2->get_id()])
            ->update(['timecreated' => time() - 16 * 60]);

        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_deleted_1->get_id())->exists()
        );
        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_deleted_2->get_id())->exists()
        );
        $this->assertTrue(
            builder::table('files')->where('id', $file_to_be_kept->get_id())->exists()
        );

        return [
            $file_to_be_deleted_1,
            $file_to_be_deleted_2,
            $file_to_be_kept,
        ];
    }
}
