<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\query;

use core\webapi\middleware\require_plugin_enabled;
use bi_intellidata\exception\invalid_credentials_exception;
use bi_intellidata\services\encryption_service;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use external_api;
use moodle_exception;

/**
 * Query to validate credentials for IB.
 */
class validate_credentials extends query_resolver {
    /**
     * @var string
     */
    const RESPONSE_DATA = 'ok';

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        external_api::validate_context($context);

        // Validate if credentials not empty.
        $encryptionservice = new encryption_service();
        if (!$encryptionservice->validate_credentials()) {
            throw new invalid_credentials_exception('Empty credentials.');
        }

        $code = $args['input'];
        if (empty(trim($code['code']))) {
            throw new moodle_exception('Code can not be decoded.');
        }

        if ($code['code'] != $encryptionservice->clientidentifier) {
            throw new invalid_credentials_exception('Wrong clientid.');
        }

        return [
            'data' => self::RESPONSE_DATA
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata')
        ];
    }
}