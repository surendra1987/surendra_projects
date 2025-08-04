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
 * bi_intellidata
 *
 * @package    bi_intellidata
 * @author     IntelliBoard Inc.
 * @copyright  2020 intelliboard.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace bi_intellidata\output\tables;
defined('MOODLE_INTERNAL') || die;

use core_plugin_manager;
use html_writer;
use bi_intellidata\helpers\StorageHelper;
use moodle_url;

require_once($CFG->libdir.'/tablelib.php');

class exportfiles_table extends \table_sql {

    public $download    = false;
    public $fields      = [];
    protected $prefs      = [];

    /**
     * Table configuration constructor.
     *
     * @param $uniqueid
     * @param $params
     * @throws \coding_exception
     */
    public function __construct($uniqueid, $params) {
        global $PAGE, $DB;

        parent::__construct($uniqueid);
        $this->download = $params['download'];
        $this->fields = $this->get_fields();
        $sqlparams = [];

        $this->sortable(true, 'timecreated', SORT_DESC);
        $this->no_sorting('actions');
        $this->is_collapsible = false;

        $this->define_columns(array_keys($this->fields));
        $this->define_headers($this->get_headers());

        $fields = "f.*";
        $from = "{files} f";

        $where = 'f.id > 0 AND f.component = :component AND f.mimetype IS NOT NULL';
        $sqlparams['component'] = 'bi_intellidata';

        if (!empty($params['query'])) {
            $where .= " AND " . $DB->sql_like('f.filearea', ':searchquery', false, false, false);
            $sqlparams += [
                'searchquery' => '%' . $DB->sql_like_escape($params['query']) . '%'
            ];
        }

        $this->set_sql($fields, $from, $where, $sqlparams);
        $this->define_baseurl($PAGE->url);
        $this->set_no_records_message(get_string('norecordsmatchedtocriteria', 'bi_intellidata'));
    }

    /**
     * Method to get columns labels.
     *
     * @return array[]
     * @throws \coding_exception
     */
    public function get_fields() {
        $fields = [
            'filearea' => [
                'label' => get_string('datatype', 'bi_intellidata'),
            ],
            'filename' => [
                'label' => get_string('filename', 'bi_intellidata'),
            ],
            'filesize' => [
                'label' => get_string('filesize', 'bi_intellidata'),
            ],
            'timecreated' => [
                'label' => get_string('created', 'bi_intellidata'),
            ],
            'actions' => [
                'label' => get_string('actions', 'bi_intellidata'),
            ],
        ];

        return $fields;
    }

    /**
     * Setup table header.
     *
     * @return array
     * @throws \coding_exception
     */
    public function get_headers() {

        $headers = [];

        if (count($this->fields)) {
            foreach ($this->fields as $field => $options) {
                $headers[] = $options['label'];
            }
        }

        $headers[] = get_string('actions', 'bi_intellidata');

        return$headers;
    }

    /**
     * Generate date field.
     *
     * @param $values
     * @return string
     * @throws \coding_exception
     */
    public function col_timecreated($values) {
        return ($values->timecreated) ? userdate($values->timecreated, get_string('strftimedatetime', 'langconfig')) : '-';
    }

    /**
     * Generate human readable filearea name.
     *
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_filearea($values) {
        if (get_string_manager()->string_exists('datatype_' . $values->filearea, 'bi_intellidata')) {
            return get_string('datatype_' . $values->filearea, 'bi_intellidata');
        } else {
            return $values->filearea;
        }
    }

    /**
     * Generate files size.
     *
     * @param $values
     * @return string
     */
    public function col_filesize($values) {
        return StorageHelper::convert_filesize($values->filesize);
    }

    /**
     * Display actions column.
     *
     * @param $values
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($values) {
        global $OUTPUT;

        $buttons = array();

        // Action download.
        $aurl = StorageHelper::make_pluginfile_url($values)->out(false);
        $buttons[] = $OUTPUT->action_icon(
            $aurl,
            new \pix_icon('t/download', get_string('download'), 'core', array('class' => 'iconsmall'))
        );

        // Action delete - display a red Delete (X) icon.
        $aurl = '/integrations/bi/intellidata/logs/index.php?id=' . $values->id . '&action=delete';
        $question = get_string('deletefileconfirmation', 'bi_intellidata');
        $form_id = 'form-tbl-export-files';
        $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/delete', get_string('delete'),
            'core', array('class' => 'iconsmall')), null,
            ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
            ]
        );

        return implode(' ', $buttons);
    }

    /**
     * Start table output
     */
    public function start_html() {
        // Note: we must close this tag later after the table is output.
        echo html_writer::start_tag('div', array('class' => 'custom-filtering-table'));

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        // Do we need to print initial bars?
        $this->print_initials_bar();

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            echo $this->download_buttons();
        }

        $this->wrap_html_start();
        // Start of main data table.

        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::start_tag('table', $this->attributes);

    }

    /**
     * Get the html for the download buttons
     *
     * Usually only use internally
     */
    public function download_buttons() {
        global $OUTPUT, $PAGE;

        $output = '';

        if ($this->is_downloadable() && !$this->is_downloading()) {
            $renderer = $PAGE->get_renderer('bi_intellidata');

            // This form template for the 'Download' button has been adjusted to use a POST method, for security reasons.
            $data = $this->download_dataformat_selector(get_string('downloadas', 'table'),
                $this->baseurl->out_omit_querystring(), 'download', $this->baseurl->params());
            $output .= $OUTPUT->render_from_template('bi_intellidata/data_format_selector', $data);

            $output .= \html_writer::start_tag('div', ['class' => 'ib-form-group flex justify-content-end']);
            // Set the 'Export files' button to be inside a mini-form with a POST method, for security.
            $exporturl = new \moodle_url('/integrations/bi/intellidata/logs/index.php', ['action' => 'export']);

            $contents = \html_writer::tag('input', '', [
                'type' => 'hidden',
                'name' => 'sesskey',
                'value' => sesskey()
            ]);

            $btn_export_files = \html_writer::tag('button', get_string('exportfiles', 'bi_intellidata'), [
                'class' => 'btn btn-secondary ib-small-indent',
                'onclick' => 'document.getElementById("form-export-files-action").submit(); return false;'
            ]);
            $contents .= \html_writer::tag('span', $btn_export_files, [
                'class' => 'ib-single-button'
            ]);

            $output .= \html_writer::tag('form', $contents, [
                'method' => 'POST',
                'action' => $exporturl,
                'id' => 'form-export-files-action'
            ]);
            $output .= \html_writer::end_div();

            $output .= \html_writer::start_tag('div', ['class' => 'd-flex justify-content-end']);
            // Render search form.
            $output .= $this->search_form();

            $output .= \html_writer::end_tag('div');
        }

        return $output;
    }

    /**
     * This function is not part of the public api.
     */
    public function print_nothing_to_display() {
        global $OUTPUT;

        // Render the dynamic table header.
        if (method_exists($this, 'get_dynamic_table_html_start')) {
            echo $this->get_dynamic_table_html_start();
        }

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        $this->print_initials_bar();

        echo html_writer::start_tag('div', ['class' => 'd-flex justify-content-end']);

        // Render search form.
        echo $this->search_form();

        echo html_writer::end_tag('div');

        echo html_writer::div($this->get_no_records_message(), 'table-no-entries intellidata-configuration__nodata');

        // Render the dynamic table footer.
        if (method_exists($this, 'get_dynamic_table_html_end')) {
            echo $this->get_dynamic_table_html_end();
        }
    }

    /**
     * Get the html for the search form
     *
     * Usually only use internally
     */
    public function search_form() {
        global $PAGE;

        $renderer = $PAGE->get_renderer('bi_intellidata');

        return $renderer->render_from_template(
            'bi_intellidata/header_search_input',
            [
                'action' => $PAGE->url,
                'query' => $PAGE->url->get_param('query'),
                'sesskey' => sesskey()
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function wrap_html_start() {
        // Close the div.custom-filtering-table tag.
        echo html_writer::end_tag('div');

        // Wrap the results table in a form, so the action buttons can perform POST requests for security reasons.
        echo \html_writer::start_tag('form', [
            'id' => 'form-tbl-export-files',
            'method' => 'POST',
            'action' => ''
        ]);

        echo \html_writer::tag('input', '', [
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey()
        ]);
        parent::wrap_html_start();
    }

    /**
     * @inheritDoc
     */
    public function wrap_html_finish() {
        global $OUTPUT;
        // End the form around the results table.
        echo \html_writer::end_tag('form');

        // This form template for the 'Download' button has been adjusted to use a POST method, for security reasons.
        $data = $this->download_dataformat_selector(get_string('downloadas', 'table'),
        $this->baseurl->out_omit_querystring(), 'download', $this->baseurl->params());
        echo $OUTPUT->render_from_template('bi_intellidata/data_format_selector', $data);
    }

    /**
     * Returns a dataformat selection and download form. This is similar code to a block in the Moodle Core 'outputrenderers.php'
     * file that we need to give us some 'Download' button select options data.
     *
     * @param string $label A text label
     * @param moodle_url|string $base The download page url
     * @param string $name The query param which will hold the type of the download
     * @param array $params Extra params sent to the download page
     * @return array
     */
    public function download_dataformat_selector($label, $base, $name = 'dataformat', $params = array()): array {

        $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
        $options = array();
        foreach ($formats as $format) {
            if ($format->is_enabled()) {
                $options[] = array(
                    'value' => $format->name,
                    'label' => get_string('dataformat', $format->component),
                );
            }
        }
        $hiddenparams = array();
        foreach ($params as $key => $value) {
            $hiddenparams[] = array(
                'name' => $key,
                'value' => $value,
            );
        }
        $data = array(
            'label' => $label,
            'base' => $base,
            'name' => $name,
            'params' => $hiddenparams,
            'options' => $options,
            'sesskey' => sesskey(),
            'submit' => get_string('download'),
        );

        return $data;
    }
}
