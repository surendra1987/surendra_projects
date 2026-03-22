<?php
/**
 * This file is part of Totara Core
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

use ai_noai\feature\generative_prompt as noai_generative_prompt_feature;
use core_ai\configuration\config_collection;
use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\interaction_input_interface;
use core_phpunit\testcase;
use core_tag\ai\interaction\suggest_tags;
use core_tag\ai\interaction\suggest_tags_input;
use core_tag\output\tagareacollection;
use function PHPUnit\Framework\assertEmpty;

/**
 * Tests for the experimental suggest tags feature.
 *
 * @group ai_integrations
 */
class core_tag_ai_interaction_suggest_tags_test extends testcase {
    /**
     * @return void
     */
    public function test_tag_no_content(): void {
        $suggest_tags = new suggest_tags('core', 'course');
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Must provide some content to pick tags out of');
        $suggest_tags->run(new suggest_tags_input('', []));
    }

    public function test_missing_course_content_handling(): void {
        $suggest_tags = new suggest_tags('core', 'course');
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Must provide some content to pick tags out of');
        $test_input_dto = new class implements interaction_input_interface {
        };
        $suggest_tags->run($test_input_dto);
    }

    public function test_missing_suggested_tags_handling(): void {
        $suggest_tags = new suggest_tags('core', 'course');
        $test_input_dto = new class implements interaction_input_interface {
            public string $course_content;
        };
        $test_input_dto->course_content = 'mocked content';
        $result = $suggest_tags->run($test_input_dto)->suggested_tags;
        assertEmpty($result);
    }

    /**
     * Test string 0 as content.
     * Because empty() is used for checking the string content, string zero '0' comes up as invalid.
     * Whether it's a bug or not this is the current behaviour.
     *
     * @return void
     */
    public function test_zero_as_content(): void {
        $suggest_tags = new suggest_tags('core', 'course');
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Must provide some content to pick tags out of');
        $suggest_tags->run(new suggest_tags_input('0', []));
    }

    /**
     * @return void
     */
    public function test_tag_suggestions(): void {
        global $CFG;

        $suggest_tags = new suggest_tags('core', 'course');

        // Area is disabled
        $CFG->usetags = false;
        $CFG->usetags = true;

        // Not enough tags
        $params = new suggest_tags_input('The quick brown fox jumps over the lazy dog.', []);
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

        $this->assertIsArray($result->suggested_tags);
        $this->assertEquals(2, $result->get_count());

        // Make sure they're the tags we expect
        $expected = [
            $tags[0]->id,
            $tags[2]->id,
        ];
        $actual = array_map(fn($tag) => $tag->id, $result->suggested_tags);
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
            $prompts[] = new prompt($prompt, prompt::ASSISTANT_ROLE);
        }
        $response = new response($prompts);

        $mock_ai = $this->createMock(generative_prompt_feature::class);
        $mock_ai
            ->expects($this->once())
            ->method('generate')
            ->willReturn($response);

        $reflect = new ReflectionProperty(suggest_tags::class, 'generative_prompt_feature');
        $reflect->setAccessible(true);
        $reflect->setValue($suggest_tags, $mock_ai);
    }

    /**
     * Helper method to assert that the run returns no results.
     *
     * @param suggest_tags $suggest_tags
     * @param suggest_tags_input $params
     * @return void
     * @throws coding_exception
     */
    protected function assert_no_results(suggest_tags $suggest_tags, suggest_tags_input $params): void {
        $result = $suggest_tags->run($params);
        $this->assertIsArray($result->suggested_tags);
        $this->assertEmpty($result->suggested_tags);
        $this->assertEquals(0, $result->get_count());
    }

    public function test_noai_response(): void {
        // Create a tag.
        $default_tag_collection_id = core_tag_collection::get_default();
        core_tag_tag::create_if_missing($default_tag_collection_id, ['Adaptability'], true);

        // Create a realistic prompt.
        $prompts = [
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt('The goat is in the red barn', prompt::USER_ROLE),
            new prompt("From this list of tags, what are the most relevant tags for the document? \nAdaptability", prompt::USER_ROLE),
        ];
        $request = new \core_ai\feature\generative_prompt\request($prompts);
        $feature = new noai_generative_prompt_feature(new config_collection([]), suggest_tags::class);

        $response = suggest_tags::noai_response($request, $feature);

        $this->assertInstanceOf(response::class, $response);
        $this->assertNull($response->get_error());
        $result = $response->to_array();
        $this->assertCount(1, $result);
        $this->assertEquals(prompt::ASSISTANT_ROLE, $result[0]->get_role());
        $this->assertEquals('TAG:Adaptability', $result[0]->get_message());
    }
}
