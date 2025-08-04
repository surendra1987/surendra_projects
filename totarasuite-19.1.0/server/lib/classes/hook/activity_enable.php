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

namespace core\hook;

use totara_core\hook\base;

/**
 * This hook is called during the action to enable a specific activity module,
 * immediately before the activity is enabled.
 *
 * The text shortname of the activity module being enabled is passed as $mod.
 * An array of text shortnames of the currently enabled activity modules is passed as $modsenabled.
 *
 * To prevent the plugin being enabled you can set $this->prevent_enable = true; and $this->prevent_enable_reason = '<some reason>';
 */
class activity_enable extends base {
    public string $mod;

    public bool $prevent_enable;

    public string $prevent_enable_reason;

    public bool $show;
    public function __construct(string $mod, bool $show = false) {
        $this->mod = $mod;
        $this->show = $show;
        $this->prevent_enable = false;
        $this->prevent_enable_reason = '';
    }
}