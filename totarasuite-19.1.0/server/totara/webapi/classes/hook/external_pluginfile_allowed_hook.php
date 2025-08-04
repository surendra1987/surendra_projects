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
 * @package totara_webapi
 */

namespace totara_webapi\hook;

use totara_core\hook\base;
class external_pluginfile_allowed_hook extends base {
    /**
     * @var null|bool
     */
    protected $allowed;

    /**
     * @var string|null
     */
    protected $error_msg;

    /**
     * @var string|null
     */
    protected $component;

    /**
     * @param string|null $component
     */
    public function __construct(string $component = null) {
        $this->allowed = null;
        $this->error_msg = null;
        $this->component = $component;
    }

    /**
     * Denied when no watcher is registered.
     *
     * @return bool
     */
    public function allowed(): bool {
        return !is_null($this->allowed) && is_bool($this->allowed) && $this->allowed;
    }

    /**
     * @param bool $is_register
     * @return void
     */
    public function set_allowed(bool $allowed): void {
        $this->allowed = $allowed;
    }

    /**
     *
     * @return bool
     */
    public function has_error(): bool {
        return !empty($this->error_msg);
    }

    /**
     * @param string $error_msg
     * @return void
     */
    public function set_error_msg(string $error_msg): void {
        $this->error_msg = $error_msg;
    }

    /**
     * @return string|null
     */
    public function get_error_msg(): ?string {
        return $this->error_msg;
    }

    /**
     * @return string|null
     */
    public function get_component(): ?string {
        return $this->component;
    }
}