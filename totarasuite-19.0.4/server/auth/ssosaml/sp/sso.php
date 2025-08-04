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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 */

use auth_ssosaml\local\util;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\process\login\make_request;

require_once(__DIR__ . '/../../../config.php');
global $CFG, $PAGE;

$context = context_system::instance();
$PAGE->set_context($context);

util::assert_saml_enabled();

$idp_id = required_param('id', PARAM_INT);
$wants_url = optional_param('wantsurl', $CFG->wwwroot, PARAM_LOCALURL);

$idp = idp::load_by_id($idp_id);

if (!$idp->status) {
    redirect(get_login_url());
}

(new make_request($idp, $wants_url))->execute();
