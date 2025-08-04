<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Marco Song <marco.song@totaralearning.com>
 * @package performelement_linked_review
 */

namespace performelement_linked_review\webapi\resolver\mutation;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use mod_perform\models\activity\section_element;
use mod_perform\models\activity\participant_instance;
use mod_perform\webapi\middleware\require_activity;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\entity\linked_review_content as linked_review_content_entity;

class update_linked_review_content extends mutation_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        $content = $args['input']['content'] ?? null;
        if (!empty($args['input']['content_ids'])) {
            debugging('The content_ids input field for mutation performelement_linked_review_update_linked_review_content is deprecated please only pass content', DEBUG_DEVELOPER);
            $content_arr = array_map(
                function ($content_id) {
                    return ['id' => $content_id];
                },
                $args['input']['content_ids']
            );
        }

        $section_element_id = $args['input']['section_element_id'];
        $participant_instance_id = $args['input']['participant_instance_id'];

        if (!isset($content_arr)) {
            $content_arr = self::parse_content($content);
        }

        // Avoid bad, empty data input.
        if (empty($content_arr)) {
            return [
                'validation_info' => [
                    'can_update'  => false,
                    'description' => get_string('can_not_add_nothing', 'performelement_linked_review'),
                ],
            ];
        }

        // Avoid duplicates.
        // Check whether a review item (probably with the content type) and same ID already exists in the section element.
        $content_ids = array_map(function($content_item) {
            return $content_item['id'];
        }, $content_arr);
        $participant_instance = participant_instance::load_by_id($participant_instance_id);
        if (linked_review_content_entity::repository()
            ->where('section_element_id', $section_element_id)
            ->where('subject_instance_id', $participant_instance->subject_instance_id)
            ->where_in('content_id', $content_ids)
            ->exists()
        ) {
            return [
                'validation_info' => [
                    'can_update'  => false,
                    'description' => get_string('can_not_readd_review_item_to_section', 'performelement_linked_review'),
                ],
            ];
        }

        $result = linked_review_content::allowed_to_select_content(
            section_element::load_by_id($section_element_id),
            $participant_instance
        );

        if (!$result->is_fulfilled) {
            return [
                'validation_info' => [
                    'can_update' => false, 'description' => $result->error
                ]
            ];
        }

        linked_review_content::create_multiple(
            $content_arr,
            $section_element_id,
            $participant_instance_id,
            true
        );

        return [
            'validation_info' => [
                'can_update'  => true,
                'description' => '',
            ],
        ];
    }

    /**
     * Parse the content to an array, making sure we have at least an id field
     *
     * @param string $content
     * @return array
     */
    private static function parse_content(string $content): array {
        // Now the review type can pass additional content,
        // by default we at least have the id, let's validate it
        $content = json_decode($content, true);
        if (!is_array($content)) {
            throw new coding_exception('Content should be a json encoded array');
        }

        foreach ($content as $item) {
            if (!isset($item['id'])) {
                throw new coding_exception('Invalid content given. Need at least the id.');
            }
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_login(),
            require_activity::by_participant_instance_id('input.participant_instance_id', true),
        ];
    }
}
