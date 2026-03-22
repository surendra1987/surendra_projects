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
 * @copyright  2022 intelliboard.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

namespace bi_intellidata\output\tables;
defined('MOODLE_INTERNAL') || die;


use html_writer;
use bi_intellidata\persistent\export_logs;
use bi_intellidata\persistent\datatypeconfig;

require_once($CFG->libdir.'/tablelib.php');

class exportlogs_table extends \table_sql {

    public $fields     = [];
    public $tabletypes = null;
    protected $prefs   = [];
    protected $context = null;

    public function __construct($uniqueid, $params = '') {
        global $PAGE, $DB;

        $this->context = \context_system::instance();
        $this->tabletypes = datatypeconfig::get_tabletypes();
        parent::__construct($uniqueid);

        $this->fields = $this->get_fields();
        $sqlparams = [];

        $this->sortable(true, 'datatype', SORT_ASC);
        $this->is_collapsible = false;

        $this->define_columns(array_keys($this->fields));
        $this->define_headers($this->get_headers());

        $fields = "el.*, el.recordsmigrated as progress";
        $from = "{" . export_logs::TABLE . "} el";

        $where = 'el.id > 0';

        if (!empty($params['query'])) {
            $where .= " AND " . $DB->sql_like('el.datatype', ':searchquery', false, false, false);
            $sqlparams += [
                'searchquery' => '%' . $DB->sql_like_escape($params['query']) . '%'
            ];
        }

        $this->set_sql($fields, $from, $where, $sqlparams);

        $this->define_baseurl($PAGE->url);
        $this->set_no_records_message(get_string('norecordsmatchedtocriteria', 'bi_intellidata'));
    }

    /**
     * @return array[]
     * @throws \coding_exception
     */
    public function get_fields() {
        $fields = [
            'datatype' => [
                'label' => get_string('datatype', 'bi_intellidata'),
            ],
            'tabletype' => [
                'label' => get_string('tabletype', 'bi_intellidata'),
            ],
            'migrated' => [
                'label' => get_string('migrated', 'bi_intellidata'),
            ],
            'progress' => [
                'label' => get_string('progress', 'bi_intellidata'),
            ],
            'last_exported_id' => [
                'label' => get_string('lastexportedid', 'bi_intellidata'),
            ],
            'timestart' => [
                'label' => get_string('timestart', 'bi_intellidata'),
            ],
            'last_exported_time' => [
                'label' => get_string('timeend', 'bi_intellidata'),
            ]
        ];

        return $fields;
    }

    /**
     * @return array
     * @throws \coding_exception
     */
    public function get_headers() {

        $headers = [];

        if (count($this->fields)) {
            foreach ($this->fields as $options) {
                $headers[] = $options['label'];
            }
        }

        return$headers;
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_datatype($values) {
        if (get_string_manager()->string_exists('datatype_' . $values->datatype, 'bi_intellidata')) {
            return get_string('datatype_' . $values->datatype, 'bi_intellidata');
        } else {
            return $values->datatype;
        }
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_tabletype($values) {
        return isset($this->tabletypes[$values->tabletype])
            ? $this->tabletypes[$values->tabletype]
            : '';
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_migrated($values) {
        return ($values->migrated)
            ? get_string('yes')
            : get_string('no');
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_progress($values) {
        return (($values->migrated)
                ? $values->recordscount
                : $values->recordsmigrated) . '/' . $values->recordscount;
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_timestart($values) {
        return $this->col_datetime($values->timestart);
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_last_exported_time($values) {
        return $this->col_datetime($values->last_exported_time);
    }

    /**
     * @param $timestamp
     * @return string
     * @throws \coding_exception
     */
    private function col_datetime($timestamp) {
        return ($timestamp) ? userdate($timestamp, get_string('strftimedatetime', 'langconfig')) : '-';
    }

    /**
     * Start html method.
     */
    public function start_html() {
        // Note: we must close this tag later after the table is output.
        echo html_writer::start_tag('div', ['class' => 'custom-filtering-table']);

        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        // Do we need to print initial bars?
        $this->print_initials_bar();

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            echo $this->download_buttons();
        }

        // Render search.
        echo html_writer::start_tag('div', ['class' => 'd-flex justify-content-end']);

        // Render search form.
        echo $this->search_form();

        echo html_writer::end_tag('div');

        $this->wrap_html_start();
        // Start of main data table.

        echo html_writer::start_tag('div', ['class' => 'no-overflow']);
        echo html_writer::start_tag('table', $this->attributes);
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

        // Render search.
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
        parent::wrap_html_start();
    }
}
