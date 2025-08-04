<?php
/*
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package ai_noai
 */

namespace ai_noai\remote_file;

use core_ai\configuration\config_collection;
use core_ai\remote_file\remote_file_provider as remote_file_provider_interface;
use core_ai\remote_file\remote_file_base;

/**
 * No AI file provider class, does not provide any remote files just pretends to.
 */
class remote_file_provider implements remote_file_provider_interface {
    /**
     * Get an instance of the No AI remote file implementation
     *
     * @param config_collection $config
     * @param string $interaction_class_name
     * @return remote_file_base
     */
    public function get_instance(config_collection $config, string $interaction_class_name): remote_file_base {
        return new remote_file($config, $interaction_class_name);
    }
}
