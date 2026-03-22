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

use auth_ssosaml\exception\unknown_binding;
use auth_ssosaml\model\idp\config\bindings;
use auth_ssosaml\provider\data\saml_request;
use moodle_url;

/**
 * Handle a specific SAML process.
 */
class bindings_handler {
    /**
     * Take a SAML request object and process the expected binding behaviour.
     * This may render content or redirect depending on the situational context.
     *
     * @param saml_request $saml_request
     * @return void
     */
    public function submit(saml_request $saml_request) {
        // Redirect
        if ($saml_request->binding === bindings::SAML2_HTTP_REDIRECT_BINDING) {
            redirect(new moodle_url($saml_request->url, $saml_request->data));
            return;
        }

        // POST
        if ($saml_request->binding === bindings::SAML2_HTTP_POST_BINDING) {
            global $OUTPUT;

            $data = [];
            foreach ($saml_request->data as $key => $value) {
                $data[] = ['name' => $key, 'value' => $value];
            }

            $context = [
                'site' => get_site()->fullname,
                'url' => $saml_request->url,
                'data' => $data,
            ];

            echo $OUTPUT->render_from_template('auth_ssosaml/post_binding', $context);
            return;
        }

        throw new unknown_binding();
    }
}