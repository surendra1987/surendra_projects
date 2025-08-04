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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity\settings\controls;

use mod_perform\models\activity\activity_setting;

/**
 * Class sync_participation control
 *
 * @package mod_perform\models\activity\settings\control
 */
class sync_participation extends control {

    /**
     * @inheritDoc
     */
    public function get(): array {
        $settings = $this->activity->get_settings();
        $override_global_participation_settings = (bool)$settings->lookup(activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS);

        $pi_close_type  = $override_global_participation_settings
            ? (int)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE)
            : (int)get_config(null, 'perform_sync_participant_instance_closure');

        return [
            'override_global_participation_settings' => $override_global_participation_settings,
            'sync_participant_instance_creation_type' => $override_global_participation_settings
                ? (int)$settings->lookup(activity_setting::SYNC_PARTICIPANT_INSTANCE_CREATION)
                : (int)get_config(null, 'perform_sync_participant_instance_creation'),
            'sync_participant_instance_closure_type' =>
                sync_participant_instance_closure_option::from($pi_close_type)->name,
            'sync_participant_instance_closure_options' => self::closure_options(),
            'sync_participant_instance_creation_options' => self::creation_options()
        ];
    }

    /**
     * Returns the details of the available participant instance closure options.
     *
     * @return array<array<string,string>> a list of options containing these
     *         details for each option:
     *         - 'id' => option name
     *         - 'desc' => localized option description
     *         - 'label' => localized option label
     */
    private static function closure_options(): array {
        $config_for_sync_participant_instance_closure = (int)get_config(null, 'perform_sync_participant_instance_closure');
        // We need to show this suffix in the local P.A Admin view setting option label if it matches the current global
        // Perform setting for 'Close participant instances for removed participants on role change'.
        $global_default_suffix = get_string('perform_sync_participant_instance_global_default', 'mod_perform');

        return array_map(
            fn(sync_participant_instance_closure_option $it): array => [
                'id' => $it->name,
                'desc' => $it->description(),
                'label' => ($config_for_sync_participant_instance_closure === $it->value ?
                    $it->label() . $global_default_suffix :
                    $it->label()
                )
            ],
            sync_participant_instance_closure_option::cases()
        );
    }

    /**
     * Returns the details of the available participant instance creation options.
     *
     * @return array<array<string,string>> a list of options containing these
     *         details for each option:
     *         - 'id' => integer value of the option
     *         - 'desc' => localized option description
     *         - 'label' => localized option label
     */
    private static function creation_options(): array {
        $config_for_sync_participant_instance_creation = (int)get_config(null, 'perform_sync_participant_instance_creation');
        // We need to show this suffix in the local P.A Admin view option label if it matches the current global Perform
        // setting for 'Add participant instances for new participants on role change'.
        $global_default_suffix = get_string('perform_sync_participant_instance_global_default', 'mod_perform');

        return array_map(
            fn(sync_participant_instance_creation_option $it): array => [
                'id' => $it->value,
                'desc' => $it->description(),
                'label' => ($config_for_sync_participant_instance_creation === $it->value ?
                    $it->label() . $global_default_suffix :
                    $it->label()
                )
            ],
            sync_participant_instance_creation_option::cases()
        );
    }
}
