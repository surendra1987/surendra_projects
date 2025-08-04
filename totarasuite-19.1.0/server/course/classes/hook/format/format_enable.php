<?php
/*
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 *
 *  @author Simon Coggins <simon.coggins@totara.com>
 *
 */

namespace core_course\hook\format;

use totara_core\hook\base;

/**
 * This hook is called during the action to enable a specific course format plugin,
 * immediately before the plugin is enabled.
 *
 * The text shortname of the plugin being enabled is passed as $format.
 *
 * To prevent the plugin being enabled you can set $this->prevent_enable = true; and $this->prevent_enable_reason = '<some reason>';
 */
class format_enable extends base {
    public string $format;

    public bool $prevent_enable;

    public string $prevent_enable_reason;

    public function __construct(string $format) {
        $this->format = $format;
        $this->prevent_enable = false;
        $this->prevent_enable_reason = '';
    }

}