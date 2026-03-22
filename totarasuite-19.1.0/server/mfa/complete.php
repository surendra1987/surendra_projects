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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

use core_mfa\escalation\base;

require_once(__DIR__ . '/../config.php');

global $USER, $CFG;
require_once($CFG->dirroot . '/login/lib.php');

if (empty($USER->mfa_completed) || empty($USER->mfa_action) || !isset($USER->pseudo_id)) {
    redirect(get_login_url());
}

/** @var \core_mfa\escalation\base $action_class */
$action_class = $USER->mfa_action;

$action_class = $USER->mfa_action;
if (!class_exists($action_class)) {
    throw new coding_exception("Invalid escalation class");
}

$escalation = new $action_class($USER->pseudo_id);
if (!$escalation instanceof base) {
    throw new coding_exception("Class provided is not an instance of escalation");
}

$escalation->complete();
