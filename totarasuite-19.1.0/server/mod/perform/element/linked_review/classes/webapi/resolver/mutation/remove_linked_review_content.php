<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

namespace performelement_linked_review\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\mutation_resolver;
use performelement_linked_review\content_type_factory;
use performelement_linked_review\entity\linked_review_content as linked_review_content_entity;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\helper\lifecycle\removal_conditions;

/**
 * Resolver for the performelement_linked_review_remove_linked_review_content GraphQL mutation.
 */
class remove_linked_review_content extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $linked_review_content = linked_review_content_entity::repository()
            ->where('id', $args['input']['id'])
            ->get()
            ->map(linked_review_content::load_by_entity(...))
            ->first();

        if (!$linked_review_content) {
            return [
                'success' => false,
                'error' => get_string(
                    'cannot_remove_content_not_found_or_permission',
                    'performelement_linked_review',
                    get_string('generic_review_item_type', 'performelement_linked_review')
                )
            ];
        }

        if ($linked_review_content->can_remove) {
            $linked_review_content->delete();
            return ['success' => true, 'error' => ''];
        }

        $section_element = $linked_review_content->section_element;
        $element_data = json_decode($section_element->element->data, true);
        $type = content_type_factory::get_from_identifier(
            $element_data['content_type'],
            $linked_review_content->subject_instance->get_context()
        );

        $removal_condition_error = $linked_review_content->get_can_remove_error();
        $lang_key = 'cannot_remove_content_not_found_or_permission';
        if ($removal_condition_error === removal_conditions::ERR_OTHERS_ALREADY_PROGRESSED) {
            $lang_key = $section_element->section->activity->multisection_setting
                ? 'cannot_remove_content_multiple_section_others_have_progress'
                : 'cannot_remove_content_single_section_others_have_progress';
        }

        return [
            'success' => false,
            'error' =>  get_string(
                $lang_key,
                'performelement_linked_review',
                $type::get_generic_display_name()
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_authenticated_user()
        ];
    }
}
