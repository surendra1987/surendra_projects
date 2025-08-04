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
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\mutation;

use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use external_api;
use core\webapi\middleware\require_plugin_enabled;
use bi_intellidata\helpers\ParamsHelper;
use bi_intellidata\helpers\SettingsHelper;

use bi_intellidata\exception\invalid_processing_exception;

/**
 * This endpoint is for enabling/disabling Data processing for the Intelliboard plugin.
 */
class enable_processing extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        external_api::validate_context($context);

        $args = $args['input'];

        // Validation.
        // The 'status' parameter is compulsory and locked down to a few values only.
        if (!isset($args['status'])) {  // This is just for unit tests checking.
            throw new invalid_processing_exception('Invalid plugin status.');
        }
        $plugin_status = trim($args['status']);
        if (!in_array((int)$plugin_status, [SettingsHelper::STATUS_ENABLE, SettingsHelper::STATUS_DISABLE])) {
            throw new invalid_processing_exception('Invalid plugin status.');
        }
        // Make sure callbackurl is a properly formatted URL.
        if (isset($args['callbackurl'])) {
            if (!filter_var(trim($args['callbackurl']), FILTER_VALIDATE_URL)) {
                throw new invalid_processing_exception('Invalid callbackurl.');
            }
        }

        // Set plugin settings.
        set_config('ispluginsetup', $plugin_status, ParamsHelper::PLUGIN);
        if (isset($args['callbackurl'])) {
            SettingsHelper::set_setting('migrationcallbackurl', $args['callbackurl']);
        }

        return [
            'data' => 'ok'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:editconfig'),
        ];
    }
}
