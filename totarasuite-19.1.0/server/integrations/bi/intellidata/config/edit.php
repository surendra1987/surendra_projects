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

use bi_intellidata\output\forms\bi_intellidata_edit_config;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\config_service;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\task\create_index_adhoc_task;
use bi_intellidata\task\delete_index_adhoc_task;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$datatype = required_param('datatype', PARAM_TEXT);
$action = optional_param('action', '', PARAM_TEXT);

require_login();

$context = context_system::instance();
require_capability('bi/intellidata:editconfig', $context);

$returnurl = new \moodle_url('/integrations/bi/intellidata/config/index.php');
$pageurl = new \moodle_url('/integrations/bi/intellidata/config/edit.php', ['datatype' => $datatype]);
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout(SettingsHelper::get_page_layout());

if (!empty($action) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($datatype))) {
    require_sesskey();
}

$record = datatypeconfig::get_record(['datatype' => $datatype]);

if (!$record) {
    throw new \moodle_exception('wrongdatatype', 'bi_intellidata');
}

$exportlogrepository = new export_log_repository();
$configservice = new config_service();
if ($action == 'reset') {
    $configservice->reset_config_datatype($record);

    redirect($returnurl, get_string('resetmsg', 'bi_intellidata'));

} else if ($action == 'createindex') {

    $record->set('tableindex', $record->get('timemodified_field'));
    $record->save();

    $createindextask = new create_index_adhoc_task();
    $createindextask->set_custom_data([
        'datatype' => $record->get('datatype')
    ]);
    \core\task\manager::queue_adhoc_task($createindextask);

    redirect($returnurl, get_string('taskaddedforindexcreation', 'bi_intellidata'));

} else if ($action == 'deleteindex') {

    $deleteindextask = new delete_index_adhoc_task();
    $deleteindextask->set_custom_data([
        'datatype' => $record->get('datatype'),
        'tableindex' => $record->get('tableindex')
    ]);
    \core\task\manager::queue_adhoc_task($deleteindextask);

    $record->set('tableindex', '');
    $record->save();

    redirect($returnurl, get_string('taskaddedforindexdeletion', 'bi_intellidata'));
}

$isrequired = $record->is_required_by_default();

$datatypeconfig = datatypes_service::get_datatype($datatype);
if (!$isrequired) {
    $datatypeconfig['timemodifiedfields'] = config_service::get_available_timemodified_fields($datatypeconfig['table']);
}

$exportlog = $exportlogrepository->get_datatype_export_log($datatype);

$title = get_string('editconfigfor', 'bi_intellidata', $datatype);

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js('/integrations/bi/intellidata/scripts/ib_dialog_confirm_form_submit.js');

// This provides the sidebar nav menu & correct top nav breadcrumbs.
admin_externalpage_setup('bi_intellidataeditconfig');

$editform = new bi_intellidata_edit_config(null, [
    'data' => $record->to_record(),
    'config' => (object)$datatypeconfig,
    'exportlog' => $exportlog,
    'is_required' => $isrequired
]);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {

    if (!empty($data->reset)) {
        $data = $configservice->create_config($datatype, $datatypeconfig);
        $returnurl = $pageurl;
    } else {
        $configservice->save_config($record, $data);
    }

    redirect($returnurl, get_string('configurationsaved', 'bi_intellidata'));
}

$datatype_comment_description = $editform->get_datatype_schema_description($datatype);
if (!empty($datatype_comment_description)) {
    $html_description = html_writer::start_div('box generalbox formsettingheading');
    $html_description .= html_writer::start_tag('p');
    $html_description .= $datatype_comment_description;
    $html_description .= html_writer::end_tag('p');
    $html_description .= html_writer::end_div();
}

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($title);
echo $html_description ?? '';

echo $editform->display();

echo $OUTPUT->footer();