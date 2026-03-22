<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package core_my
 */

namespace totara_core\totara\menu;

use core_my\perform_overview_util;
use coding_exception;
use core_my\controllers\perform_overview;

class my_overview extends item {

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    protected function get_default_title() {
        return get_string('menu_title_my_overview', 'core_my');
    }

    /**
     * @inheritDoc
     */
    protected function get_default_url(): string {
        return perform_overview::get_base_url();
    }

    /**
     * @inheritDoc
     */
    public function get_default_sortorder(): int {
        return 50005;
    }

    /**
     * @inheritDoc
     * @throws coding_exception
     */
    protected function check_visibility(): bool {
        return perform_overview_util::has_any_permission();
    }

    /**
     * @inheritDoc
     */
    public function is_disabled(): bool {
        return perform_overview_util::is_overview_disabled();
    }

    /**
     * @inheritDoc
     */
    protected function get_default_parent(): string {
        return '\totara_core\totara\menu\perform';
    }

    /**
     * @inheritDoc
     */
    public function get_default_visibility(): bool {
        return true;
    }
}