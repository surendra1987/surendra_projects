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

namespace core_tag\ai\interaction;

use coding_exception;
use core_ai\feature;
use core_ai\feature\generative_prompt;
use core_ai\feature\generative_prompt\prompt;
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
     * @var feature AI generative prompt feature.
     */
    protected feature $prompt;

    /**
     * @param string $tag_component
     * @param string $tag_item_type
     */
    public function __construct(string $tag_component, string $tag_item_type) {
        $this->tag_component = $tag_component;
        $this->tag_item_type = $tag_item_type;
    }

    /**
     * @return string
     */
    public static function get_name(): string {
        return get_string('ai_suggest_tags', 'core_tag');
    }

    /**
     * @return string
     */
    public static function get_description(): string {
        return get_string('ai_suggest_tags_description', 'core_tag');
    }

    /**
     * @param array $params
     * @return array
     */
    public function run(array $params): array {
        global $CFG;

        // Immediately return nothing if tags are disabled for this area
        if (!core_tag_area::is_enabled($this->tag_component, $this->tag_item_type)) {
            return [];
        }

        // Confirm we have content to scan
        $content = $params['content'] ?? null;
        if (empty($content)) {
            throw new coding_exception('Must provide some content to pick tags out of');
        }

        // We only call the API if the tags are enough
        if (count($this->get_tag_collection()) < self::MIN_TAGS) {
            return [];
        }

        // Now exclude the existing tags
        $existing = $params['existing'] ?? [];
        if (!is_array($existing)) {
            throw new coding_exception('Must provide an array of existing tag IDs');
        }
        $prompted_tags = $this->get_tags_for_prompt(self::MAX_TAGS, $existing);
        if (count($prompted_tags) < self::MIN_TAGS) {
            return [];
        }

        // Build up our prompt for tags
        $request = new generative_prompt\request([
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
        /** @var generative_prompt\response $response */

        $response = $prompter->generate($request);
        $found_tags = [];
        $prompted_tags = array_flip($prompted_tags);
        $tag_collection = $this->get_tag_collection();

        foreach ($response->get_data() as $reply) {
            $message = $reply->get_message();

            if (strtoupper($message) === 'NULL') {
                continue;
            }

            if (str_starts_with($message, 'TAG:')) {
                $split = explode("\n", $message);
                foreach ($split as $tag) {
                    $tag = trim(str_replace('TAG:', '', $tag));
                    if (isset($prompted_tags[$tag])) {
                        $tag_id = $prompted_tags[$tag];

                        // We return the proper tag entity
                        $found_tags[$tag_id] = $tag_collection[$tag_id];
                    }
                }
            }
        }

        return $found_tags;
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
     * @return feature
     */
    protected function get_feature(): feature {
        if (empty($this->prompt)) {
            $this->prompt = $this->get_ai_feature(generative_prompt::class);
        }
        return $this->prompt;
    }
}
