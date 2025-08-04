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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\model;

use core\orm\entity\model;
use core\session\manager;
use core_ai\entity\interaction_log as interaction_log_entity;
use core_ai\entity\interaction_log as interaction_log_log;
use core_ai\event\feature_interaction;
use core_ai\feature\feature_base;
use core_ai\feature\request_base;
use core_ai\feature\response_base;
use core_ai\interaction;
use core_ai\remote_file\remote_file_base;
use core_ai\remote_file\remote_file_request;

/**
 * AI Interaction log model
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string|interaction $interaction
 * @property-read string $request
 * @property-read string $response
 * @property-read string $plugin
 * @property-read string|feature_base $feature
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

    /**
     * Create a new interaction log entry.
     *
     * @param request_base $request
     * @param feature_base $feature
     * @return self
     */
    public static function create(request_base $request, feature_base $feature): self {
        $interaction_class = $feature->get_interaction_class_name();
        $configs = ['feature' => $feature->get_config()];
        if (method_exists($interaction_class, 'get_config')) {
            $configs['interaction'] = $interaction_class::get_config();
        }
        $entity = (new interaction_log_entity([
            'user_id' => manager::get_realuser()->id,
            'interaction' => $interaction_class,
            'request' => $request->to_json(),
            'plugin' => static::find_plugin(get_class($feature)),
            'feature' => get_parent_class($feature),
            'configuration' => json_encode($configs),
        ]))->save();

        // trigger event
        feature_interaction::create_from_log($entity)->trigger();

        return static::load_by_entity($entity);
    }

    /**
     * Create a new interaction log entry from a remote file request.
     *
     * @param remote_file_request $request
     * @param remote_file_base $remote_file
     * @return self
     */
    public static function create_from_remote_file(remote_file_request $request, remote_file_base $remote_file): self {
        // Note the use of the remote file action for the interaction field, which normally expects an interaction class name.
        // We have no way to know which interaction this file action is part of.
        $entity = (new interaction_log_entity([
            'user_id' => manager::get_realuser()->id,
            'interaction' => $request->get_action(),
            'request' => $request->to_json(),
            'plugin' => static::find_plugin(get_class($remote_file)),
            'feature' => get_class($remote_file),
            'configuration' => json_encode($remote_file->get_config()),
        ]))->save();

        // trigger event
        feature_interaction::create_from_log($entity)->trigger();

        return static::load_by_entity($entity);
    }

    /**
     * Frankenstyle name resolver
     *
     * @param string $classname
     * @return string
     */
    protected static function find_plugin(string $classname): string {
        $plugin_class_parts = explode('\\', $classname);
        // Component is the first part of any class namespace.
        $component = $plugin_class_parts[0];
        // Plugin is the second part of the component name.
        $namestart = strpos($component, '_');
        if ($namestart) {
            return substr($component, $namestart + 1);
        }
        // Shouldn't happen, but this is for logging purposes so return it.
        return $component;
    }

    /**
     * Log the reponse associated with this interaction log entry.
     *
     * @param response_base $response
     * @return $this
     */
    public function log_response(response_base $response): self {
        $this->entity->response = $response->to_json();
        $this->entity->save();
        return $this;
    }
}
