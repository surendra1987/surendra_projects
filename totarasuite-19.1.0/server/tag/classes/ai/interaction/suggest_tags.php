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

namespace core_tag\ai\interaction;

use coding_exception;
use core_ai\feature\generative_prompt\generative_prompt_feature;
use core_ai\feature\generative_prompt\prompt;
use core_ai\feature\generative_prompt\request;
use core_ai\feature\generative_prompt\response;
use core_ai\feature\interaction_input_interface;
use core_ai\feature\interaction_output_interface;
use core_ai\interaction;
use core_tag\model\tag;
use core_tag_area;
use core_tag_tag;

/**
 * Suggest tags from the list of known tags.
 */
class suggest_tags extends interaction {
    /**
     * The minimum amount of tags required in the collection to trigger the API.
     */
    const MIN_TAGS = 3;

    /**
     * The maximum amount of tags we will feed through to the AI system at once.
     */
    const MAX_TAGS = 50;

    /**
     * @var string Which tag component we are making suggestions for.
     */
    protected string $tag_component;

    /**
     * @var string Which tag item type we are making suggestions for.
     */
    protected string $tag_item_type;

    /**
     * @var tag[]|null Collection of tags used to provide to the prompt
     */
    protected ?array $tag_collection = null;

    /**
     * @var generative_prompt_feature AI generative prompt feature.
     */
    protected generative_prompt_feature $generative_prompt_feature;

    /**
     * Interaction is created for a specific tag component and tag item type, which should point to an enabled tag area.
     *
     * @param string $tag_component
     * @param string $tag_item_type
     */
    public function __construct(string $tag_component, string $tag_item_type) {
        $this->tag_component = $tag_component;
        $this->tag_item_type = $tag_item_type;
    }

    /**
     * Localised interaction name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('ai_suggest_tags', 'core_tag');
    }

    /**
     * Localised interaction description.
     *
     * @return string
     */
    public static function get_description(): string {
        return get_string('ai_suggest_tags_description', 'core_tag');
    }

    /**
     * Feature class used by this interaction.
     *
     * @return string
     */
    public static function get_feature_class(): string {
        return generative_prompt_feature::class;
    }

    /**
     * Run the interaction using the given parameter.
     *
     * @param interaction_input_interface $params
     * @return interaction_output_interface
     * @throws coding_exception
     */
    public function run(interaction_input_interface $params): interaction_output_interface {
        // Immediately return nothing if tags are disabled for this area
        if (!core_tag_area::is_enabled($this->tag_component, $this->tag_item_type)) {
            return new suggest_tags_output([]);
        }

        // Confirm we have content to scan
        $content = $params->course_content ?? null;
        if (empty($content)) {
            throw new coding_exception('Must provide some content to pick tags out of');
        }

        // We only call the API if there are enough tags in the collection to make a recommendation.
        if (count($this->get_tag_collection()) < self::MIN_TAGS) {
            return new suggest_tags_output([]);
        }
        $existing = $params->existing_tags ?? [];

        $prompted_tags = $this->get_tags_for_prompt(self::MAX_TAGS, $existing);
        // Repeated, we only call the API if there are enough tags in the collection to make a recommendation.
        if (count($prompted_tags) < self::MIN_TAGS) {
            return new suggest_tags_output([]);
        }

        // Build up our prompt for tags
        $request = new request([
            new prompt('You are a tag suggester for a learning management system.', prompt::SYSTEM_ROLE),
            new prompt('The user will provide a list of tags to choose from and a document to judge. Each tag will be on a new line. The document will be delimited by triple quotes.', prompt::SYSTEM_ROLE),
            new prompt('You must examine the document and determine which are the 10 most relevant tags from the list of tags.', prompt::SYSTEM_ROLE),
            new prompt('Suggested tags must only be chosen from the provided list.', prompt::SYSTEM_ROLE),
            new prompt('Provide your output with one tag per line. Prefix each valid tag with the key TAG:', prompt::SYSTEM_ROLE),
            new prompt('If there are no relevant tags to show, say NULL with no key instead.', prompt::SYSTEM_ROLE),
            new prompt("From this list of tags, what are the most relevant tags for the document? \n" . implode("\n", $prompted_tags)),
            new prompt('Document: """' . str_replace('"""', '"', $content) . '"""'),
        ]);


        $prompter = $this->get_feature();
        $response = $prompter->generate($request);
        return $this->parse_response($response, $prompted_tags);
    }

    /**
     * Parse response to get tags
     *
     * @param object $response
     * @param array $prompted_tags
     * @return suggest_tags_output
     */
    protected function parse_response($response, $prompted_tags): suggest_tags_output {
        $found_tags = [];
        $prompted_tags = array_flip($prompted_tags);
        $tag_collection = $this->get_tag_collection();

        // Parse the response data.
        foreach ($response->to_array() as $reply) {
            $message = $reply->get_message();

            // NULL response means no tags suggested.
            if (strtoupper($message) === 'NULL') {
                continue;
            }

            // Positive reponse will have at least one line with TAG: at the start.
            if (str_starts_with($message, 'TAG:')) {
                $split = explode("\n", $message);
                foreach ($split as $tag) {
                    // Remove prefix TAG: and any space.
                    $tag = trim(substr($tag, 4));
                    // Hallucination check, only accept prompted tags.
                    if (isset($prompted_tags[$tag])) {
                        $tag_id = $prompted_tags[$tag];
                        // We return the proper tag entity
                        $found_tags[$tag_id] = $tag_collection[$tag_id];
                    }
                }
            }
        }

        return new suggest_tags_output($found_tags);
    }

    /**
     * Generate a No AI response using one of the input tags.
     *
     * @param request $request
     * @param generative_prompt_feature $feature
     * @return void
     */
    public static function noai_response(request $request, generative_prompt_feature $feature): response {
        $error = null;
        // Hard-coded prompt number is not great, but it's all we have in this static method.
        $tag_prompt = $request->get_prompts()[6];
        $all_tags = explode("\n", $tag_prompt->get_message());
        unset($all_tags[0]);;
        $prompts = [];
        if (count($all_tags)) {
            $random_tag = $all_tags[array_rand($all_tags)];
            $prompts[] = new prompt('TAG:' . $random_tag, prompt::ASSISTANT_ROLE);
        }
        return new response($prompts, $error);
    }

    /**
     * Load the collection of tags
     *
     * @return tag[]
     */
    protected function get_tag_collection(): array {
        if ($this->tag_collection === null) {
            $collection_id = core_tag_area::get_collection($this->tag_component, $this->tag_item_type);
            $this->tag_collection = tag::get_tags_by_collection($collection_id);
        }

        return $this->tag_collection;
    }

    /**
     * Return the list of tags to provide the prompt, removing known tags
     * and limiting to the maximum number provided.
     *
     * @param int $max_tags
     * @param array $existing_tag_ids
     * @return string[]
     */
    protected function get_tags_for_prompt(int $max_tags, array $existing_tag_ids): array {
        $tags = $this->get_tag_collection();

        // Strip out existing tags
        foreach ($existing_tag_ids as $tag_id) {
            if (isset($tags[$tag_id])) {
                unset($tags[$tag_id]);
            }
        }

        // Hard-limit the number of tags we provide the prompt
        if (count($tags) > $max_tags) {
            $tag_ids = array_rand($tags, $max_tags);
            $tags = array_intersect_key($tags, array_flip($tag_ids));
        }

        $tag_list = [];
        foreach ($tags as $tag) {
            $tag_list[$tag->id] = core_tag_tag::make_display_name($tag, false);
        }

        return $tag_list;
    }

    /**
     * This interaction uses the generative_prompt feature.
     *
     * This method finds the best implementation to use from the enabled AI connector plugins.
     *
     * @return generative_prompt_feature
     */
    protected function get_feature(): generative_prompt_feature {
        if (empty($this->generative_prompt_feature)) {
            $this->generative_prompt_feature = $this->get_ai_feature();
        }
        return $this->generative_prompt_feature;
    }
}
