<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Arshad Anwer <arshad.anwer@totaralms.com>
 * @package totara_tenant
 * @subpackage tenant
 */
global $CFG, $PAGE, $DB;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');
require_once($CFG->dirroot .'/totara/core/dialogs/dialog_content.class.php');
require_once($CFG->dirroot.'/totara/core/js/lib/setup.php');

// Page title
$pagetitle = 'assigntenants';

// Restrict content according to definition in the report
$reportid = optional_param('reportid', 0, PARAM_INT);

///
/// Permissions checks
///

require_login();
require_capability("totara/tenant:view", context_system::instance());
$PAGE->set_context(context_system::instance());

if (isguestuser()) {
    echo html_writer::tag('div', get_string('noguest', 'error'), array('class' => 'notifyproblem'));
    die;
}

// Check if tenant are enabled.
if (!$CFG->tenantsenabled) {
    echo html_writer::tag('div', get_string('tenantsdisabled', 'totara_tenant'), array('class' => 'notifyproblem'));
    die();
}

///
/// Display page
///
$items = $DB->get_records_sql(
    "
        SELECT
            t.id, t.name
        FROM
            {tenant} t
        WHERE
            t.suspended = 0
    ");

// Load dialog content generator; skip access, since it's checked above.
$dialog = new totara_dialog_content();
$dialog->type = totara_dialog_content::TYPE_CHOICE_MULTI;
$dialog->items = $items;

// Set title.
$dialog->selected_title = 'itemstoadd';

// Setup search.
$dialog->searchtype = 'tenant';

// Display.
echo $dialog->generate_markup();