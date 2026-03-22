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

use Throwable;
use totara_webapi\exception\api_exception;
use totara_webapi\hook\pluginfile_pre_hook;
use totara_webapi\request;

/**
 * An abstract class that serves as a parent for different pluginfile.php controllers implementations
 */
abstract class pluginfile extends api_controller {
    /**
     * @var array
     */
    protected $relativepath;

    /**
     * Process file request
     * @throws api_exception
     */
    protected function action_file_request() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        if (is_null($this->relativepath)) {
            $this->relativepath = get_file_argument();
        }

        $request = new request($this->get_execution_context()->get_endpoint_type());
        $pre_hook = new pluginfile_pre_hook($request, $this->get_execution_context(), $this->relativepath);
        $pre_hook->execute();

        if ($pre_hook->has_error()) {
            $this->send_internal_server_error($pre_hook->get_exception()->getMessage());
        }


        try {
            file_pluginfile($this->relativepath, false);
        } catch (Throwable $exception) {
            $this->send_internal_server_error($exception->getMessage());
        }
    }
}