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

use auth_ssosaml\exception\duplicate_idp_entity_id_exception;
use auth_ssosaml\exception\invalid_metadata_exception;
use auth_ssosaml\webapi\middleware\translate_exceptions;
use totara_webapi\client_aware_exception;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\middleware\translate_exceptions
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_middleware_translate_exceptions_test extends base_saml_testcase {
    /**
     * Assert invalid metadata is translated
     *
     * @return void
     */
    public function test_invalid_metadata(): void {
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage(get_string('error:invalid_metadata', 'auth_ssosaml'));

        $this->call_middleware(new translate_exceptions(), null, function () {
            throw new invalid_metadata_exception('');
        });
    }

    /**
     * Assert duplicate ids is translated
     *
     * @return void
     */
    public function test_duplicate_entity_id(): void {
        $this->expectException(client_aware_exception::class);
        $this->expectExceptionMessage(get_string('error:duplicate_idp_entity_id', 'auth_ssosaml'));

        $this->call_middleware(new translate_exceptions(), null, function () {
            throw new duplicate_idp_entity_id_exception('');
        });
    }
}
