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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\testing;

use auth_ssosaml\model\idp;
use auth_ssosaml\provider\logging\db_logger;
use core\testing\component_generator;
use auth_ssosaml\entity\saml_log_entry;

/**
 * Helper to generate test data.
 */
class generator extends component_generator {

    /**
     * Enable the auth_ssosaml plugin
     *
     * @return void
     */
    public function enable_plugin(): void {
        global $CFG;
        get_enabled_auth_plugins(true); // fix the list of enabled auths
        if (empty($CFG->auth)) {
            $enabled = [];
        } else {
            $enabled = explode(',', $CFG->auth);
        }

        $enabled[] = 'ssosaml';
        $CFG->auth = implode(',', $enabled);
    }

    /**
     * Disable the auth_ssosaml plugin
     *
     * @return void
     */
    public function disable_plugin(): void {
        global $CFG;
        get_enabled_auth_plugins(true); // fix the list of enabled auths
        if (empty($CFG->auth)) {
            $enabled = [];
        } else {
            $enabled = explode(',', $CFG->auth);
        }

        $key = array_search('ssosaml', $enabled);
        if (isset($enabled[$key])) {
            unset($enabled[$key]);
        }

        $CFG->auth = implode(',', $enabled);
    }

    /**
     * Create a new IdP. Will use default properties unless otherwise provided.
     *
     * @param array|null $properties
     * @param array|null $saml_config
     * @return idp
     */
    public function create_idp(?array $properties = null, ?array $saml_config = null): idp {
        $properties = $properties ?? [];
        $saml_config = $saml_config ?? [];

        $default_properties = [
            'status' => true,
            'label' => 'My Test IdP',
            'idp_user_field' => 'uid',
            'totara_user_id_field' => 'username',
        ];

        $default_saml_config = [

        ];

        return idp::create(
            array_merge($default_properties, $properties),
            array_merge($default_saml_config, $saml_config)
        );
    }

    /**
     * @param idp $idp
     * @return db_logger
     */
    public function create_db_logger(idp $idp): db_logger {
        return new db_logger($idp->id, $idp->sp_config->test_mode);
    }

    /**
     * Wrapper for behat
     *
     * @param array $data
     */
    public function create_saml_log_request(array $data): void {
        $entity = new saml_log_entry([
            'idp_id' => $data['idp_id'],
            'request_id' => $data['request_id'],
            'session_id' => $data['session_id'],
            'type' => $data['type'],
            'content_request' => $data['content_request'],
            'content_response' => $data['content_response'],
            'content_request_time' => time(),
            'test' => $data['test'],
            'status' => $data['status'],
        ]);
        $entity->save();
    }
}
