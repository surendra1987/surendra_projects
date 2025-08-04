<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\process;

use auth_ssosaml\provider\data\saml_request;

/**
 * Handle a specific SAML process.
 */
abstract class base {
    /**
     * @var bindings_handler
     */
    private bindings_handler $bindings_handler;

    /**
     * Core logic of the process.
     *
     * @return mixed
     */
    abstract public function execute();

    /**
     * Override the available bindings handler. Used when we want to override the bindings behaviour.
     *
     * @param bindings_handler $bindings_handler
     * @return void
     */
    public function set_bindings_handler(bindings_handler $bindings_handler): void {
        $this->bindings_handler = $bindings_handler;
    }

    /**
     * Take a SAML request object and process the expected binding behaviour.
     * This may render content or redirect depending on the situational context.
     *
     * @param saml_request $saml_request
     * @return void
     */
    protected function submit_request_binding(saml_request $saml_request) {
        ($this->bindings_handler ?? new bindings_handler())->submit($saml_request);
    }
}