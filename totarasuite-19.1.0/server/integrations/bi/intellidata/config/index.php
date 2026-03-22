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

use bi_intellidata\output\forms\bi_intellidata_filters_config;
use bi_intellidata\output\tables\config_table;
use bi_intellidata\services\config_service;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\helpers\SettingsHelper;

global $SESSION;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();
admin_externalpage_setup('bi_intellidataconfig');

$action = optional_param('action', '', PARAM_TEXT);
$page = optional_param('page', '', PARAM_INT);

$title = get_string('table_export_settings', 'bi_intellidata');

$context = context_system::instance();
require_capability('bi/intellidata:viewconfig', $context);
$pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php');

// Validate config table setup.
if (!empty($action)) {
    // The only action submitted to this page is for 'Reset'.
    // Prevent application state change if there is no valid sesskey.
    require_sesskey();
    $configservice = new config_service(datatypes_service::get_all_datatypes());
    $configservice->setup_config(true);
    redirect($pageurl, get_string('configurationsaved', 'bi_intellidata'));
}

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout(SettingsHelper::get_page_layout());

$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js('/integrations/bi/intellidata/scripts/ib_dialog_confirm_form_submit.js');

$createurl = new \moodle_url('/integrations/bi/intellidata/config/editlogsentity.php');
$output = \html_writer::tag('button', get_string('createlogsdatatype', 'bi_intellidata'), [
    'class' => 'btn btn-secondary',
    'onclick' => "window.location.replace('" . $createurl . "');"
]);

$html = html_writer::start_div('intellidata-configuration clearfix');
$html .= $title;
$html .= html_writer::start_div();
$html .= $output;
$html .= html_writer::end_div();

$params = null;
if (isset($SESSION->filters_config_param)) {
    $params = $SESSION->filters_config_param;
}

// Set this form's submit method to POST so we don't unnecessarily expose the sesskey in the URL.
$filter_form = new bi_intellidata_filters_config(null, $params, 'POST', '', array('class' => 'rb-search'));
if ($filter_form->is_cancelled()) {
    unset($SESSION->filters_config_param);
    redirect($pageurl);
} else if ($data = $filter_form->get_data()) {
    $params['datatype'] = $data->datatype;
    $params['status'] = $data->status;
    $params['exportenabled'] = $data->exportenabled;
    $SESSION->filters_config_param = $params;
}

$table = new config_table('config_table', $params);

$refreshbutton = $OUTPUT->single_button($pageurl, get_string('refreshconfig', 'bi_intellidata'), 'get');

// Make a mini-form for the top Reset button, so that it will be a POST request (for security) & contain the form-data for action = 'reset'.
$contents = \html_writer::tag('input', '', [
    'type' => 'hidden',
    'name' => 'sesskey',
    'value' => sesskey()
]);
$contents .= \html_writer::tag('input', '', [
    'type' => 'hidden',
    'name' => 'action',
    'value' => 'reset'
]);

// Show a confirmation dialog when the Reset button is clicked.
$question = get_string('resettableexportsettings', 'bi_intellidata');
$form_id = 'form-export-reset-action';
$aurl = '';
$contents .= \html_writer::tag('input', '', [
    'type' => 'submit',
    'name' => 'input-reset',
    'value' => 'Reset',
    'onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
]);

$reset_button_form = \html_writer::tag('form', $contents, [
    'method' => 'POST',
    'action' => $pageurl,
    'id' => $form_id
]);
$form_start = \html_writer::start_div('singlebutton');
$form_end = \html_writer::end_div();
$reset_button_form = $form_start . $reset_button_form . $form_end;
// End of mini-form.

$PAGE->set_button($refreshbutton . $reset_button_form . $PAGE->button);

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($html);
echo $filter_form->display();

$table->out(30, true);
echo $OUTPUT->footer();
