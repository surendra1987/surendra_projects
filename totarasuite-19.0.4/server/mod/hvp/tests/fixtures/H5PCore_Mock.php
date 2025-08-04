<?php
/**
 * This file is part of Totara LMS
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package mod_hvp
 */

class h5pf {
    public function t($message) {
        return $message;
    }
}

class H5PCore_Mock extends H5PCore {
    public function __construct() {
        $this->h5pF = new h5pf();
    }

    protected static $response = null;
    protected static $valid_token = false;

    protected static $ajax_error = null;

    public $h5pF = null;

    public static function ajaxError($message = NULL, $error_code = NULL, $status_code = NULL, $details = NULL) {
        static::$ajax_error = $message;
    }

    public static function getAjaxError() {
        return static::$ajax_error;
    }
    public static function ajaxSuccess($data = NULL, $only_data = FALSE) {
        static::$response = $data;
    }

    public static function validToken($action, $token) {
        return static::$valid_token;
    }

    public static function setValidToken($valid = false) {
        static::$valid_token = $valid;
    }

    public static function getResponse() {
        return static::$response;
    }
}

class framework {
    public static function instance($type = null) {
        return new H5PCore();
    }
}