<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\model;

use core\orm\entity\model;
use core\session\manager;
use core_ai\entity\interaction_log as interaction_log_entity;
use core_ai\entity\interaction_log as interaction_log_log;
use core_ai\event\feature_interaction;
use core_ai\feature;
use core_ai\feature\request;
use core_ai\feature\response;
use core_ai\interaction;

/**
 * AI Interaction log model
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string|interaction $interaction
 * @property-read string $request
 * @property-read string $response
 * @property-read string $plugin
 * @property-read string|feature $feature
 * @property-read string $configuration
 * @property-read int $created_at
 */
class interaction_log extends model {

    protected $entity;

    protected $entity_attribute_whitelist = [
        'id',
        'user_id',
        'interaction',
        'request',
        'response',
        'plugin',
        'feature',
        'configuration',
        'created_at',
    ];

    protected static function get_entity_class(): string {
        return interaction_log_log::class;
    }

    public static function create(request $request, feature $feature): self {
        $plugin_class_parts = explode('\\', get_class($feature));
        $component_plugin = explode('_', $plugin_class_parts[0]);

        $entity = (new interaction_log_entity([
            'user_id' => manager::get_realuser()->id,
            'interaction' => $feature->get_interaction_class_name(),
            'request' => json_encode($request),
            'plugin' => $component_plugin[1] ?? '',
            'feature' => get_parent_class($feature),
            'configuration' => json_encode($feature->get_config()),
        ]))->save();

        // trigger event
        feature_interaction::create_from_log($entity)->trigger();

        return static::load_by_entity($entity);
    }

    public function log_response(response $response): self {
        $this->entity->response = json_encode($response);
        $this->entity->save();
        return $this;
    }
}
