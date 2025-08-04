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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_auth
 */

namespace core_auth\controllers;

use context;
use context_system;
use totara_mvc\controller;
use totara_mvc\tui_view;

/**
 * Login page controller.
 */
class login extends controller {
    /** @inheritDoc */
    protected $url = '/login/index.php';

    /** @inheritDoc */
    protected $layout = 'viewport';

    /** @var string */
    private $error_message = null;

    /** @var object */
    private $form_inputs = null;

    /** @inheritDoc */
    protected function setup_context(): context {
        return context_system::instance();
    }

    /** @inheritDoc */
    protected function require_login(): bool {
        return false;
    }

    /** @inheritDoc */
    public function action() {
        global $OUTPUT, $SITE, $CFG;
        $this->get_page()->set_main_skip_target_id('main-header');

        $logo = (new \totara_core\output\masthead_logo())->export_for_template($OUTPUT);

        $image = new \core\theme\file\login_background($this->get_page()->theme);
        $tenantid = \core\theme\helper::get_prelogin_tenantid();
        $image->set_tenant_id($tenantid);

        $langs = $CFG->langmenu ? get_string_manager()->get_list_of_translations() : [];

        $logo_url = $logo['logourl'];
        if ($logo_url instanceof \moodle_url) {
            $logo_url = $logo_url->out(false);
        }

        $cookie_help_text = get_formatted_help_string('cookiesenabled', 'core');

        return tui_view::create('core_auth/pages/Login', [
            'layoutData' => [
                'logoUrl' => $logo_url,
                'homeUrl' => $logo['siteurl'],
                'siteName' => $SITE->shortname,
                'lang' => [
                    'current' => current_language(),
                    'showMenu' => $CFG->langmenu && count($langs) > 1,
                    'langs' => $langs,
                ],
                'footerHtml' => $OUTPUT->custom_footer_content(),
                'backgroundImage' => $image->is_available() ? [
                    'url' => $image->get_current_or_default_url()->out(false),
                ] : null,
            ],
            'cookieHelpContent' => $cookie_help_text,
            'error' => $this->error_message ? ['message' => $this->error_message] : null,
            'formData' => [
                'username' => $this->form_inputs->username ?? null,
                'remember' => $this->form_inputs->rememberusernamechecked ?? null,
            ],
            'loginOptions' => [
                'guest' => !empty($CFG->guestloginbutton),
                'email' => !empty($CFG->authloginviaemail),
                'signup' => !empty($CFG->registerauth),
                'rememberMode' => $this->get_remember_mode(),
                'idps' => $this->get_idps(),
                'autofocus' => !empty($CFG->loginpageautofocus),
            ],
        ]);
    }

    /**
     * Set the error message to show on the page.
     *
     * @param string $msg
     * @return void
     */
    public function set_error(string $msg): void {
        $this->error_message = $msg;
    }

    /**
     * Set the content of the login form inputs.
     *
     * @param object $frm
     * @return void
     */
    public function set_form_inputs($frm): void {
        $this->form_inputs = $frm;
    }

    /**
     * Get the remember mode -- either 'ME', 'USERNAME', or null.
     *
     * @return null|string
     */
    private function get_remember_mode(): ?string {
        global $CFG;
        if ($CFG->persistentloginenable ?? false) {
            return 'ME';
        } else if (($CFG->rememberusername ?? 0) == 2) {
            return 'USERNAME';
        } else {
            return null;
        }
    }

    /**
     * Get IdP list for frontend.
     *
     * @return array
     */
    private function get_idps(): array {
        $idps = \auth_plugin_base::get_identity_providers(get_enabled_auth_plugins());
        return array_map(function (array $idp) {
            $url = $idp['url'];
            if ($url instanceof \moodle_url) {
                $url = $url->out(false);
            } else if (strpos($url, '&amp;') !== false) {
                // URLs are not supposed to be escaped, but handle any escaped ones we come across.
                debugging('IdP URL is HTML escaped, please fix your code: ' . $url, DEBUG_DEVELOPER);
                $url = html_entity_decode($url);
            }
            return [
                'name' => $idp['name'],
                'url' => $url,
            ];
        }, $idps);
    }
}
