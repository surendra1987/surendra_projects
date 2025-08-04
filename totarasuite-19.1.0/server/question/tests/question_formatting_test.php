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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_question
 */

use core_phpunit\testcase;

class core_question_question_formatting_test extends testcase {
    /**
     * Assert that question text gets cleaned with consistent cleaning.
     *
     * @return void
     */
    public function test_questiontext_formatted(): void {
        global $CFG;

        $question = test_question_maker::make_a_multichoice_single_question();
        $attempt = test_question_maker::get_a_qa($question, 1);

        $CFG->disableconsistentcleaning = 0;

        $question->questiontext = 'My text';
        $text = $question->format_questiontext($attempt);
        $this->assertSame('My text', $text);

        $question->questiontext = '<script>alert()</script>text';
        $text = $question->format_questiontext($attempt);
        $this->assertSame('text', $text);

        $CFG->disableconsistentcleaning = 1;

        $question->questiontext = '<script>alert()</script>text';
        $text = $question->format_questiontext($attempt);
        $this->assertSame('<script>alert()</script>text', $text);
    }

    protected function setUp(): void {
        parent::setUp();

        global $CFG;
        require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
    }
}