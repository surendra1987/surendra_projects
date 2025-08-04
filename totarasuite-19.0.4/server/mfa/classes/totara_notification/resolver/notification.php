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
 * @package core_mfa
 */

namespace core_mfa\totara_notification\resolver;

use core_mfa\totara_notification\placeholder\factor_instance as factor_placeholder;
use core_user\totara_notification\placeholder\user as user_placeholder;
use lang_string;
use totara_core\extended_context;
use totara_notification\placeholder\placeholder_option;
use totara_notification\recipient\manager;
use totara_notification\recipient\subject;
use totara_notification\resolver\abstraction\permission_resolver;
use totara_notification\resolver\notifiable_event_resolver;
use totara_notification\supports_context_helper;

/**
 * Base class for notifications in MFA that share common behaviour.
 */
abstract class notification extends notifiable_event_resolver implements permission_resolver {
    /**
     * @return string|null
     */
    public static function get_plugin_name(): ?string {
        return get_string('name', 'core_mfa');
    }

    /**
     * @param extended_context $context
     * @param int $user_id
     * @return bool
     */
    public static function can_user_manage_notification_preferences(extended_context $context, int $user_id): bool {
        $natural_context = $context->get_context();
        $capability = 'totara/notification:managenotifications';
        return has_capability($capability, $natural_context, $user_id);
    }

    /**
     * @return string[]
     */
    public static function get_notification_available_recipients(): array {
        return [
            subject::class,
            manager::class,
        ];
    }

    /**
     * We currently limit to system context
     *
     * @param extended_context $extended_context
     * @return bool
     */
    public static function supports_context(extended_context $extended_context): bool {
        return supports_context_helper::supports_context(
            $extended_context,
            CONTEXT_SYSTEM
        );
    }

    /**
     * @return string[]
     */
    public static function get_notification_default_delivery_channels(): array {
        return ['email', 'popup'];
    }

    /**
     * Default placeholders for all MFA notifications.
     *
     * @return array|placeholder_option[]
     */
    public static function get_notification_available_placeholder_options(): array {
        return [
            placeholder_option::create(
                'recipient',
                user_placeholder::class,
                new lang_string('placeholder_group_recipient', 'totara_notification'),
                function (array $event_data, int $target_user_id): user_placeholder {
                    return user_placeholder::from_id($target_user_id);
                }
            ),
            placeholder_option::create(
                'subject',
                user_placeholder::class,
                new lang_string('placeholder_group_subject', 'totara_notification'),
                function (array $event_data): user_placeholder {
                    return user_placeholder::from_id($event_data['subject_user_id']);
                }
            ),
            placeholder_option::create(
                'factor',
                factor_placeholder::class,
                new lang_string('placeholder_group_instance', 'core_mfa'),
                function (array $event_data): factor_placeholder {
                    return factor_placeholder::from_event_data($event_data);
                }
            ),
        ];
    }

    /**
     * MFA currently lives within the system context
     *
     * @return extended_context
     */
    public function get_extended_context(): extended_context {
        return extended_context::make_system();
    }
}
