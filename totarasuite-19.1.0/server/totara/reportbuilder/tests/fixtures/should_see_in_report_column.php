<?php
/**
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @package totara_reportbuilder
 */

require(__DIR__ . '/../../../../config.php');

global $USER;

$displaydebugging = false;
if (!defined('BEHAT_SITE_RUNNING') || !BEHAT_SITE_RUNNING) {
    if (debugging()) {
        $displaydebugging = true;
    } else {
        throw new coding_exception('Invalid access detected.');
    }
}
$title = 'Table';

require_login();
$context = context_user::instance($USER->id);
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_url('/totara/reportbuilder/tests/fixtures/should_see_in_report_column.php');
$PAGE->set_pagelayout('noblocks');
$PAGE->set_title($title);

echo $OUTPUT->header();
if ($displaydebugging) {
    // This is intentionally hard coded - this page is not in the navigation and should only ever be used by behat.
    $msg = 'This page only exists to facilitate acceptance testing.';
    echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_SUCCESS);
}


echo $OUTPUT->heading('Table');

$table = new totara_table('table_a');
$cols = ['col_th' => 'TH', 'col_td' => 'TD', 'col_value' => 'VALUE'];

$table->define_columns(array_keys($cols));
$table->define_headers(array_values($cols));
$table->define_baseurl($PAGE->url);

foreach ($cols as $key => $col) {
    $table->column_class($key, 'c_' . $key);
    if ($key == 'col_th') {
        $table->column_header($key, true);
    }
}
$table->set_attribute('id', 'rt_a');
$table->set_attribute('class', 'logtable generalbox reportbuilder-table');
$table->pageable(false);
$table->setup();

$table->add_data(array('Row1TH', 'Row1TD', 'Row1Value'));
$table->add_data(array('Row2TH', 'Row2TD', 'Row2Value'), 'last');

$table->finish_html();

echo $OUTPUT->footer();
