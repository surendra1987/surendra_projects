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
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package mod_approval
 */
namespace mod_approval\totara_notification\resolver;

use lang_string;
use mod_approval\entity\workflow\workflow_stage;
use mod_approval\entity\workflow\workflow_stage_approval_level;
use mod_approval\model\workflow\stage_type\approvals;
use mod_approval\totara_notification\placeholder\workflow_stage as workflow_stage_placeholder_group;
use mod_approval\totara_notification\recipient\approvers;
use totara_core\extended_context;
use totara_notification\placeholder\placeholder_option;
use totara_notification\resolver\abstraction\additional_criteria_resolver;

abstract class level_base extends stage_base implements additional_criteria_resolver {

    /**
     * @inheritDoc
     */
    public static function get_notification_available_recipients(): array {
        $recipients = parent::get_notification_available_recipients();
        $recipients[] = approvers::class;
        return $recipients;
    }

    /**
     * @inheritDoc
     */
    public static function get_notification_available_placeholder_options(): array {
        $options = parent::get_notification_available_placeholder_options();
        $options[] = placeholder_option::create(
            'workflow_stage',
            workflow_stage_placeholder_group::class,
            new lang_string('notification:placeholder_group_stage_related', 'mod_approval'),
            function (array $event_data): workflow_stage_placeholder_group {
                return workflow_stage_placeholder_group::from_id($event_data['workflow_stage_id']);
            }
        );
        return $options;
    }

    public static function get_additional_criteria_component(): string {
        return 'mod_approval/components/notification/NotificationLevelPart';
    }

    public static function is_valid_additional_criteria(?array $additional_criteria, extended_context $extended_context): bool {
        // Empty level means "All levels".
        if (empty($additional_criteria['approval_level_id'])) {
            return true;
        }

        return workflow_stage_approval_level::repository()
            ->where('id', '=', $additional_criteria['approval_level_id'])
            ->where('workflow_stage_id', '=', $extended_context->get_item_id())
            ->exists();
    }

    public static function meets_additional_criteria(?array $additional_criteria, array $event_data): bool {
        // Empty level means "All levels".
        if (empty($additional_criteria['approval_level_id'])) {
            return true;
        }

        return $additional_criteria['approval_level_id'] == $event_data['approval_level_id'];
    }

    /**
     * @inheritDoc
     */
    public static function supports_context(extended_context $extended_context): bool {
        // If we're in a approval workflow stage context then make sure the stage type is applicable to this trigger.
        if ($extended_context->get_component() == 'mod_approval' && $extended_context->get_area() == 'workflow_stage') {
            if (!workflow_stage::repository()
                ->where('id', '=', $extended_context->get_item_id())
                ->where('type_code', '=', approvals::get_code())
                ->exists()) {
                return false;
            }
            // Else continue with parent check.
        }

        return parent::supports_context($extended_context);
    }
}