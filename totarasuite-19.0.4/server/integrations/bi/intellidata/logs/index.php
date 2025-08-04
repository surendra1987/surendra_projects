<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    bi
 * @subpackage intellidata
 * @copyright  2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use bi_intellidata\output\tables\exportfiles_table;
use bi_intellidata\helpers\StorageHelper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\services\database_service;
use bi_intellidata\services\export_service;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id           = optional_param('id', 0, PARAM_INT);
$action       = optional_param('action', '', PARAM_ALPHA);
$download     = optional_param('download', '', PARAM_ALPHA);
$query        = optional_param('query', '', PARAM_TEXT);

require_login();

$context = context_system::instance();
require_capability('bi/intellidata:viewlogs', $context);

$pageurl = new \moodle_url('/integrations/bi/intellidata/logs/index.php', ['query' => $query]);
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout(SettingsHelper::get_page_layout());

if (!empty($action) || !empty($query) || !empty($download)) {
    require_sesskey();
}
if ($id && $action == 'delete') {

    // Delete file by id.
    StorageHelper::delete_file($id);
    redirect($pageurl);

} else if ($action == 'export') {

    // Export static tables.
    $databaseservice = new database_service();
    $databaseservice->export_tables();

    // Export files to moodledata.
    $exportservice = new export_service();
    $exportservice->save_files();

    redirect($pageurl);
}

$title = get_string('exportfiles', 'bi_intellidata');
$filenamefordownload = $title;

// This will set the path in the nav bar for breadcrumbs to our page.
admin_externalpage_setup('bi_intellidataexportfiles');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js('/integrations/bi/intellidata/scripts/ib_dialog_confirm_form_submit.js');

$params = ['download' => $download, 'query' => $query];
$table = new exportfiles_table('exportfiles_table', $params);
$table->is_downloading($download, $filenamefordownload, $filenamefordownload);

if ($download) {
    $table->out(20, true);
}

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($title);
$table->out(20, true);

echo $OUTPUT->footer();