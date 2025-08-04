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

use bi_intellidata\helpers\MigrationHelper;
use bi_intellidata\services\export_service;
use bi_intellidata\output\tables\migrations_table;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\helpers\TasksHelper;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$datatype     = optional_param('datatype', '', PARAM_ALPHA);
$action       = optional_param('action', '', PARAM_ALPHA);

require_login();

$context = context_system::instance();
require_capability('bi/intellidata:viewlogs', $context);

$pageurl = new \moodle_url('/integrations/bi/intellidata/migrations/index.php');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout(SettingsHelper::get_page_layout());

if (!empty($action)) {
    require_sesskey();
}
if ($action == 'enablemigration') {
    // Reset migration.
    set_config('resetmigrationprogress', 1, 'bi_intellidata');

    // Enable cron task.
    MigrationHelper::enabled_migration_task();

    redirect($pageurl, get_string('migrationenabled', 'bi_intellidata'));
} else if ($action == 'calculateprogress') {
    TasksHelper::init_refresh_export_progress_adhoc_task();
    redirect($pageurl, get_string('calculateprogresssuccessmsg', 'bi_intellidata'));
}

$title = get_string('migrations', 'bi_intellidata');
$filenamefordownload = $title;

// This will set the path in the nav bar for breadcrumbs to our page.
admin_externalpage_setup('bi_intellidatamigrations');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js('/integrations/bi/intellidata/scripts/ib_dialog_confirm_form_submit.js');

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($title);

$exportservice = new export_service();
$datafiles = $exportservice->get_files();
$datatypes = $exportservice->datatypes;

$migrationstable = new migrations_table();
$migrationstable->generate($datafiles, $datatypes);

echo $migrationstable->out();

echo $OUTPUT->footer();