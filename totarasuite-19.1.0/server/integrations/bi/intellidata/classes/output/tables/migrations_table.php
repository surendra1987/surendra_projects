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

use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_log_repository;

class migrations_table {

    private $fields;
    private $headers;
    private $statuses;

    private $data = [];

    public function __construct() {
        $this->fields = $this->get_fields();
        $this->headers = $this->get_headers();
        $this->statuses = $this->get_statuses();
    }

    /**
     * @return array[]
     * @throws \coding_exception
     */
    private function get_fields() {
        $fields = [
            'datatype' => [
                'label' => get_string('datatype', 'bi_intellidata'),
            ],
            'status' => [
                'label' => get_string('status', 'bi_intellidata'),
            ],
            'progress' => [
                'label' => get_string('progress', 'bi_intellidata'),
            ],
            'timestart' => [
                'label' => get_string('timestart', 'bi_intellidata'),
            ],
            'timeend' => [
                'label' => get_string('timeend', 'bi_intellidata'),
            ],
        ];

        return $fields;
    }

    /**
     * @return array
     * @throws \coding_exception
     */
    private function get_headers() {
        $headers = [];

        if (count($this->fields)) {
            foreach ($this->fields as $field => $options) {
                $headers[$field] = $options['label'];
            }
        }

        return $headers;
    }

    /**
     * @return array
     * @throws \coding_exception
     */
    private function get_statuses() {
        $statuses = [
            'completed' => get_string('status_completed', 'bi_intellidata'),
            'inprogress' => get_string('status_inprogress', 'bi_intellidata'),
            'pending' => get_string('status_pending', 'bi_intellidata'),
        ];

        return $statuses;
    }

    /**
     * @param $migrated
     * @return mixed
     */
    private function get_status($migrated) {
        return $migrated == '0' ? $this->statuses['inprogress'] :
            ($migrated == '1' ? $this->statuses['completed'] : $this->statuses['pending']);
    }

    /**
     * @param $datatypename
     * @param array $datatype
     * @return array|null
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_record($datatypename, $datatype = []) {
        $exportlogrepository = new export_log_repository();
        $tabledatatypes = $exportlogrepository->get_assoc_datatypes('datatype');

        if (!empty($datatype['migration'])) {

            $item = array_fill_keys(array_keys($this->headers), '-');
            $item['datatype'] = $datatypename;

            if (isset($tabledatatypes[$datatypename])) {
                $tablerecord = $tabledatatypes[$datatypename];

                if ($tablerecord->get('recordscount') || $tablerecord->get('last_exported_id') || $tablerecord->get('migrated')) {
                    $item['status'] = $this->get_status($tablerecord->get('migrated'));
                    $item['progress'] = (($tablerecord->get('migrated'))
                            ? $tablerecord->get('recordscount')
                            : $tablerecord->get('recordsmigrated')) . '/' . $tablerecord->get('recordscount');
                    $item['timestart'] = $this->col_datetime($tablerecord->get('timestart'));
                    $item['timeend'] = $this->col_datetime($tablerecord->get('last_exported_time'));
                }
            }

            return $item;
        }

        return null;
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
     * @param $datafiles
     * @param array $datatypes
     */
    public function generate($datafiles, $datatypes = []) {
        foreach ($datafiles as $datatypename => $params) {
            $datatype = isset($datatypes[$datatypename]) ? $datatypes[$datatypename] : [];
            $dataitem = $this->get_record($datatypename, $datatype);

            if ($dataitem) {
                $this->data[] = $dataitem;
            }
        }
    }

    /**
     * @return string
     */
    public function out() {
        $output = '';

        $output .= \html_writer::start_tag('div', ['class' => 'ib-form-group d-flex justify-content-end']);

        // Make a mini-form to handle 'Enable Migration' & 'Calculate Progress' button clicks as POST requests for security.
        $url = new \moodle_url('/integrations/bi/intellidata/migrations/index.php', []);

        $contents = \html_writer::tag('input', '', [
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey()
        ]);

        $contents .= \html_writer::start_div();
        // Display an Enable Migration button, that when clicked will show a confirmation dialog.
        $aurl = $url . '?action=enablemigration';
        $question = get_string('resetmigrationmsg', 'bi_intellidata');
        $form_id = 'form-migration-action';
        $btn_enable_migration = \html_writer::tag('button', get_string('enablemigration', 'bi_intellidata'), [
            'class' => 'btn btn-secondary',
            'onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
        ]);
        $contents .= \html_writer::span($btn_enable_migration, 'ib-single-button');
        $contents .= \html_writer::span('', 'ib-single-button');

        $contents .= $this->calculate_progress_button($url);

        $contents .= \html_writer::end_div(); // End of buttons row.

        $output .= \html_writer::tag('form', $contents, [
            'method' => 'POST',
            'action' => $url,
            'id' => 'form-migration-action'
        ]);

        $output .= \html_writer::end_tag('div');

        $table = new \html_table();
        $table->head = array_values($this->headers);
        $table->data = $this->data;
        $output .= \html_writer::table($table);

        return $output;
    }

    /**
     * Get the html for the calculate progress button.
     *
     * @param string $aurl
     * @return string
     */
    public function calculate_progress_button($aurl) {
        $output = '';

        if (!SettingsHelper::get_setting('enableprogresscalculation')) {
            // Show a confirmation dialog if clicked.
            $aurl = $aurl . '?action=calculateprogress';
            $question = get_string('calculateprogressmsg', 'bi_intellidata');
            $form_id = 'form-migration-action';
            $btn_calc_progress = \html_writer::tag('button', get_string('calculateprogress', 'bi_intellidata'), [
                'class' => 'btn btn-secondary',
                'onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
            ]);

            $output .= \html_writer::tag('span', $btn_calc_progress, [
                'class' => 'ib-single-button'
            ]);
        }

        return $output;
    }
}
