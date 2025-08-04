<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package core_tag
 */

use core_ai\feature\generative_prompt;
use core_ai\feature\generative_prompt\response;
use core_phpunit\testcase;
use core_tag\ai\interaction\suggest_tags;
use core_tag\output\tagareacollection;

/**
 * Tests for the experimental suggest tags feature.
 *
 * @group ai
 */
class core_tag_ai_interaction_suggest_tags_test extends testcase {
    /**
     * @return void
     */
    public function test_tag_no_content(): void {
        $suggest_tags = new suggest_tags('core', 'course');

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Must provide some content to pick tags out of');

        $suggest_tags->run([]);
    }

    /**
     * @return void
     */
    public function test_tag_suggestions(): void {
        global $CFG;

        $suggest_tags = new suggest_tags('core', 'course');

        // Area is disabled
        $CFG->usetags = false;
        // $this->assert_no_results($suggest_tags);
        $CFG->usetags = true;

        // Not enough tags
        $params = ['content' => 'The quick brown fox jumps over the lazy dog.', 'existing' => []];
        // $this->assert_no_results($suggest_tags, $params);

        $collection = core_tag_area::get_collection('core', 'course');
        $create_tags = suggest_tags::MIN_TAGS + 5;
        $tags = [];
        for ($i = 0; $i <= $create_tags; $i++) {
            $tags[] = $this->getDataGenerator()->create_tag(['tagcollid' => $collection]);
        }

        // Nothing from the prompt
        $this->set_mock_response($suggest_tags, []);
        $this->assert_no_results($suggest_tags, $params);

        // Nothing from the prompt
        $this->set_mock_response($suggest_tags, ['NULL']);
        $this->assert_no_results($suggest_tags, $params);

        // This time let's pick a tag to return
        $response = [
            'TAG: ' . core_tag_tag::make_display_name($tags[0]),
            'INCORRECT',
            'TAG: ' . core_tag_tag::make_display_name($tags[2]),
        ];
        $this->set_mock_response($suggest_tags, $response);
        $result = $suggest_tags->run($params);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        // Make sure they're the tags we expect
        $expected = [
            $tags[0]->id,
            $tags[2]->id,
        ];
        $actual = array_map(fn($tag) => $tag->id, $result);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function setUp(): void {
        global $CFG;

        parent::setUp();

        $this->setAdminUser();

        // Create a new tag collection with no tags in it and assign it to course
        $areas = core_tag_area::get_areas();
        $course_area = $areas['course']['core'];
        $collection = core_tag_collection::create((object) ['name' => 'test']);
        tagareacollection::update($course_area->id, $collection->id);

        $CFG->usetags = true;
    }

    /**
     * Helper method to configure what prompt response we're testing.
     *
     * @param suggest_tags $suggest_tags
     * @param array $return_prompts
     * @return void
     */
    protected function set_mock_response(suggest_tags $suggest_tags, array $return_prompts): void {

        $prompts = [];
        foreach ($return_prompts as $prompt) {
            $prompts[] = new generative_prompt\prompt($prompt, generative_prompt\prompt::ASSISTANT_ROLE);
        }
        $response = new response($prompts);

        $mock_ai = $this->createMock(generative_prompt::class);
        $mock_ai
            ->expects($this->once())
            ->method('generate')
            ->willReturn($response);

        $reflect = new ReflectionProperty(suggest_tags::class, 'prompt');
        $reflect->setAccessible(true);
        $reflect->setValue($suggest_tags, $mock_ai);
    }

    /**
     * Helper method to assert that the run returns no results.
     *
     * @param suggest_tags $suggest_tags
     * @param array $params
     * @return void
     */
    protected function assert_no_results(suggest_tags $suggest_tags, array $params = []): void {
        $result = $suggest_tags->run($params);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
