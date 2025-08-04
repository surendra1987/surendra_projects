<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\controllers;

use context;
use context_system;
use moodle_exception;
use totara_mvc\admin_controller;
use totara_mvc\tui_view;

class index extends admin_controller {

    /**
     * @inheritDoc
     */
    protected $layout = 'standard';

    /**
     * @inheritdoc
     */
    protected $admin_external_page_name = 'tool_diagnostic_providers';

    /**
     * @inheritDoc
     */
    protected function setup_context(): context {
        return context_system::instance();
    }

    /**
     * @return void
     * @throws moodle_exception
     */
    protected function authorize(): void {
        global $USER;
        parent::authorize();

        if (!is_siteadmin($USER)) {
            throw new moodle_exception('adminaccessrequired');
        }
    }

    /**
     * @inheritDoc
     */
    public function action(): tui_view {
        $tui_view = tui_view::create('tool_diagnostic/pages/Providers');
        $tui_view->set_title(get_string('support_diagnostics_tool', 'tool_diagnostic'));

        return $tui_view;
    }

}
