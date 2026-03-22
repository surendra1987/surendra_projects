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
 * @package core
 */

namespace core\webapi\middleware;

use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_plugin_manager;
use moodle_exception;

/**
 * Interceptor that checks if the plugin specified in the constructor is enabled before allowing further graphql operations.
 */
class require_plugin_enabled implements middleware {
    /**
     * @var string
     */
    protected $plugin;

    /**
     * @param string $plugin
     */
    public function __construct(string $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        $plugin = core_plugin_manager::instance()->get_plugin_info($this->plugin);
        if (empty($plugin)) {
            throw new moodle_exception('invalidplugin');
        }

        if (!$plugin->is_enabled()) {
            throw new moodle_exception('disabledplugin', '', '', $plugin->displayname);
        }

        return $next($payload);
    }
}
