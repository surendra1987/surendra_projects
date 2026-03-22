<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\middleware\require_totara_webhook;
use totara_webhook\middleware\require_totara_webhook_manage_capability;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\reference\totara_webhook_record_reference;

class rotate_auth_config_totara_webhook extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $reference = $args['reference'] ?? null;

        $reference_class = new totara_webhook_record_reference();
        $record = $reference_class->get_record($reference);
        $model = totara_webhook_model::load_by_id($record->id);

        $new_secret = webhook_hmac_auth::generate_config();
        $model->set_encrypted_attribute('auth_config', $new_secret);

        return [
            'item' => $model,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_totara_webhook::by_totara_webhook_id('reference.id', true),
            require_totara_webhook_manage_capability::class,
        ];
    }
}