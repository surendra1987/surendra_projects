<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

use totara_webhook\entity\totara_webhook as totara_webhook_entity;
use totara_webhook\model\totara_webhook as totara_webhook_model;

class behat_totara_webhook extends behat_base {

    /**
     * @Given I force webhook :webhook_name auth_config to :auth_config
     *
     * @param string $webhook_name
     * @param string $auth_config
     */
    public function i_force_auth_config_to(string $webhook_name, string $auth_config): void {
        $webhook = totara_webhook_entity::repository()
            ->where('name', $webhook_name)
            ->one(true);
        /** @var totara_webhook_model $webhook */
        $webhook = totara_webhook_model::load_by_entity($webhook);
        $webhook->set_encrypted_attribute('auth_config', $auth_config);
    }
}
