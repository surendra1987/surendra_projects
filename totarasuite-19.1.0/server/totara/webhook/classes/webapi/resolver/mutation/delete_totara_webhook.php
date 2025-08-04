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

use core\exception\unresolved_record_reference;
use core\webapi\execution_context;
use totara_webhook\middleware\require_totara_webhook;
use totara_webhook\middleware\require_totara_webhook_manage_capability;
use core\webapi\mutation_resolver;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\reference\totara_webhook_record_reference;

class delete_totara_webhook extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $input = $args['reference'] ?? [];

        $reference = new totara_webhook_record_reference();
        $record = $reference->get_record($input);
        $model = totara_webhook_model::load_by_id($record->id);
        $deleted_id = $model->id;
        $model->delete();

        return [
            'deleted_id' => $deleted_id,
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