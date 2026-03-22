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
 *  @author Michael Ivanov <michael.ivanov@totara.com>
 *  @package totara_webapi
 */

namespace totara_webapi\controllers;

use core\webapi\execution_context;
use totara_core\advanced_feature;
use totara_webapi\graphql;
use totara_webapi\hook\external_pluginfile_allowed_hook;

class external_pluginfile extends pluginfile {
    /**
     * @inheritDoc
     */
    public function init(): void {
        parent::init();

        if (!advanced_feature::is_enabled('api')) {
            $this->send_bad_request_error(
                get_string('feature_not_available', 'totara_core', 'api')
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function action_file_request() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $this->relativepath = get_file_argument();
        $relativepath_array = explode('/', $this->relativepath);

        if (!$relativepath_array) {
            $this->send_internal_server_error('Relative path is incorrect');
        }

        $hook = new external_pluginfile_allowed_hook($relativepath_array[2] ?? null);
        $hook->execute();
        if (!$hook->allowed()) {
            $this->send_internal_server_error('External pluginfile is denied.');
        }

        if ($hook->has_error()) {
            $this->send_internal_server_error($hook->get_error_msg());
        }

        parent::action_file_request();
    }

    /**
     * @inheritDoc
     */
    protected function get_execution_context(): execution_context {
        return execution_context::create(graphql::TYPE_EXTERNAL);
    }
}