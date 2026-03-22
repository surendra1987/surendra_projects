<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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

global $CFG, $PAGE, $OUTPUT, $DB;
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/totara/core/utils.php');
require_once($CFG->dirroot.'/totara/reportbuilder/filters/lib.php');
require_once($CFG->dirroot.'/totara/reportbuilder/filters/grpconcat_tenant.php');

$ids = required_param('ids', PARAM_SEQUENCE);
$ids = array_filter(explode(',', $ids));
$filtername = required_param('filtername', PARAM_ALPHANUMEXT);

if (isguestuser()) {
    echo html_writer::tag('div', get_string('noguest', 'error'), array('class' => 'notifyproblem'));
    die;
}

require_login();
require_capability("totara/tenant:view", context_system::instance());
/// Check if tenant are enabled.
if (!$CFG->tenantsenabled) {
    echo html_writer::tag('div', get_string('tenantsdisabled', 'totara_tenant'), array('class' => 'notifyproblem'));
    die();
}

$PAGE->set_context(context_system::instance());

echo $OUTPUT->container_start('tenant');
if (!empty($ids)) {
    list($ids_sql, $ids_params) = $DB->get_in_or_equal($ids);
    if ($items = $DB->get_records_select('tenant', "id {$ids_sql}", $ids_params)) {
        foreach ($items as $item) {
            echo rb_filter_grpconcat_tenant::display_selected_tenant_item($item, $filtername);
        }
    }
}
echo $OUTPUT->container_end();
