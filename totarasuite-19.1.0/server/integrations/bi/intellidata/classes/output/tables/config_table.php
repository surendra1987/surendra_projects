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

use bi_intellidata\helpers\RestrictedTablesHelper;
use html_writer;
use totara_flavour\helper as flavour_helper;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\persistent\export_logs;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\helpers\SettingsHelper;

require_once($CFG->libdir.'/tablelib.php');

class config_table extends \table_sql {

    public $helper;

    public $fields     = [];
    public $tabletypes = null;
    protected $prefs   = [];
    protected $context = null;
    protected $datatypes = [];
    protected $params = [];
    protected $datatypestoignoreindex = [
        'tracking', 'trackinglog', 'trackinglogdetail'
    ];

    public function __construct($uniqueid, $params = []) {
        global $PAGE, $DB;

        $this->helper = new RestrictedTablesHelper();

        $this->context = \context_system::instance();
        $this->tabletypes = datatypeconfig::get_tabletypes();
        $this->datatypes = datatypes_service::get_all_datatypes();
        $this->params = $params;

        parent::__construct($uniqueid);

        $this->fields = $this->get_fields();
        $sqlparams = [];

        $this->sortable(true, 'datatype', SORT_ASC);
        $this->no_sorting('product');
        $this->no_sorting('classification');
        $this->no_sorting('actions');
        $this->is_collapsible = false;

        $this->define_columns(array_keys($this->fields));
        $this->define_headers($this->get_headers());

        $fields = "c.*, el.id as exportenabled";
        $from = "{" . datatypeconfig::TABLE . "} c
                LEFT JOIN {" . export_logs::TABLE . "} el ON el.datatype = c.datatype";

        $where = 'c.id > 0';

        // Find any disabled totara modules, based on the current totara flavour.
        // Note: the config.php 'forceflavour' setting can override the config_plugins table value for totara_flavour.
        $current_flavour = flavour_helper::get_active_flavour_component();
        $flavours_to_check = ['learn', 'engage', 'perform'];
        $flavours_to_exclude = [];
        foreach ($flavours_to_check as $flavour_to_check) {
            if (strpos($current_flavour, $flavour_to_check) === false) {
                $flavours_to_exclude[] = $flavour_to_check;
            }
        }

        // Add additional filter(s) for each disabled totara flavour. We don't want these tables / data sources showing up.
        foreach ($flavours_to_exclude as $flavour_to_exclude) {
            // If the learn module is disabled, also exclude tables related to learn.
            if ($flavour_to_exclude === 'learn') {
                $learn_related_tables_conditions = '';
                $learn_map = SettingsHelper::PRODUCTS_MAP['Learn'];

                foreach ($learn_map['string_matches'] as $partial_match) {
                    $learn_related_tables_conditions .= " AND NOT c.datatype LIKE '%" . $DB->sql_like_escape($partial_match) . "%' ";
                }

                foreach ($learn_map['exact_table_name_matches'] as $exact_match) {
                    $learn_related_tables_conditions .= " AND NOT c.datatype='$exact_match'";
                }
                $where .= $learn_related_tables_conditions;
            }
            // If the engage module is disabled, also exclude tables related to engage.
            if ($flavour_to_exclude === 'engage') {
                $engage_related_tables_conditions = '';
                $engage_map = SettingsHelper::PRODUCTS_MAP['Engage'];

                foreach ($engage_map['string_matches'] as $partial_match) {
                    $engage_related_tables_conditions .= " AND NOT c.datatype LIKE '%" . $DB->sql_like_escape($partial_match) . "%' ";
                }

                foreach ($engage_map['exact_table_name_matches'] as $exact_match) {
                    $engage_related_tables_conditions .= " AND NOT c.datatype='$exact_match'";
                }
                $where .= $engage_related_tables_conditions;
            }
            // If the perform module is disabled, also exclude tables related to perform.
            if ($flavour_to_exclude === 'perform') {
                $perform_related_tables_conditions = '';
                $perform_map = SettingsHelper::PRODUCTS_MAP['Perform'];

                foreach ($perform_map['string_matches'] as $partial_match) {
                    $perform_related_tables_conditions .= " AND NOT c.datatype LIKE '%" . $DB->sql_like_escape($partial_match) . "%' ";
                }

                foreach ($perform_map['exact_table_name_matches'] as $exact_match) {
                    $perform_related_tables_conditions .= " AND NOT c.datatype='$exact_match'";
                }
                $where .= $perform_related_tables_conditions;
            }
        }

        if (!empty($params['datatype'])) {
            $where .= " AND " . $DB->sql_like('c.datatype', ':datatype', false, false, false);
            $sqlparams += [
                'datatype' => '%' . $DB->sql_like_escape($params['datatype']) . '%'
            ];
        }
        if (isset($params['status']) && $params['status'] != "") {
            $where .= " AND " . $DB->sql_equal('c.status', ':status', true, false, false);
            $sqlparams += [
                'status' => $params['status']
            ];
        }
        if (isset($params['exportenabled']) && $params['exportenabled'] != "") {
            if ($params['exportenabled'] == datatypeconfig::STATUS_ENABLED) {
                $where .= " AND NOT el.id IS NULL";
            } else {
                $where .= " AND el.id IS NULL";
            }
        }

        $this->set_sql($fields, $from, $where, $sqlparams);
        $this->define_baseurl($PAGE->url);
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
            'product' => [
                'label' => get_string('product', 'bi_intellidata'),
            ],
            'tabletype' => [
                'label' => get_string('tabletype', 'bi_intellidata'),
            ],
            'events_tracking' => [
                'label' => get_string('events_tracking', 'bi_intellidata'),
            ],
            'classification' => [
                'label' => get_string('classification', 'bi_intellidata'),
            ],
            'status' => [
                'label' => get_string('status', 'bi_intellidata'),
            ],
            'exportenabled' => [
                'label' => get_string('export', 'bi_intellidata'),
            ],
            'actions' => [
                'label' => get_string('actions', 'bi_intellidata'),
            ],
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

        $headers[] = get_string('actions', 'bi_intellidata');

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
    public function col_product($values) {
        return SettingsHelper::get_product_for_table($values->datatype);
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
    public function col_events_tracking($values) {
        return $this->yes_or_now_column($values->events_tracking);
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_classification($values) {
        return ($this->helper->isRestricted($values->datatype))
            ? get_string('restricted', 'bi_intellidata')
            : get_string('unrestricted', 'bi_intellidata');
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_status($values) {
        return ($values->status)
            ? get_string('enabled', 'bi_intellidata')
            : get_string('disabled', 'bi_intellidata');
    }

    /**
     * @param $values
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function col_exportenabled($values) {
        return ($values->exportenabled)
            ? get_string('enabled', 'bi_intellidata')
            : get_string('disabled', 'bi_intellidata');
    }

    /**
     * @param $values
     * @return string
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function col_actions($values) {
        global $OUTPUT;

        if (!has_capability('bi/intellidata:editconfig', $this->context)) {
            return '';
        }

        $buttons = [];

        if ($values->tabletype == datatypeconfig::TABLETYPE_LOGS) {
            $urlparams = ['id' => $values->id];
            if ($values->exportenabled) {
                // Display a 'Reset export' (circle arrow) icon if a bi_intellidata_config record has a tabletype of 2 &
                // bi_intellidata_export_log is set as exportenabled.
                $aurl = '/integrations/bi/intellidata/config/editlogsentity.php?id=' . $values->id . '&action=reset';
                $question = get_string('resetcordconfirmation', 'bi_intellidata');
                $form_id = 'form-tbl-export-settings';
                $buttons[] = $OUTPUT->action_icon(
                    $aurl,
                    new \pix_icon('t/reset', get_string('resetexport', 'bi_intellidata'),
                        'core',
                        ['class' => 'iconsmall']),
                    null,
                    ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
                    ]
                );
            }

            // Display an' 'Edit' cog icon if a bi_intellidata_config record has a tabletype of 2.
            $aurl = new \moodle_url('/integrations/bi/intellidata/config/editlogsentity.php', $urlparams);
            $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/edit', get_string('edit'),
                'core', ['class' => 'iconsmall']), null
            );

            // Display a 'Delete' (X) icon if a bi_intellidata_config record has a tabletype of 2.
            $aurl = '/integrations/bi/intellidata/config/editlogsentity.php?id=' . $values->id . '&action=delete';
            $question = get_string('deletecordconfirmation', 'bi_intellidata');
            $form_id = 'form-tbl-export-settings';
            $buttons[] = $OUTPUT->action_icon(
                $aurl,
                new \pix_icon('t/delete', get_string('delete'),
                    'core',
                    ['class' => 'iconsmall']),
                null,
                ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
                ]
            );
        } else {
            // Display a 'Reset' (circle-arrow) icon if a bi_intellidata_export_log record has export_enabled.
            $urlparams = ['datatype' => $values->datatype];
            if ($values->exportenabled) {
                $aurl = '/integrations/bi/intellidata/config/edit.php?datatype=' . $values->datatype . '&action=reset';
                $question = get_string('resetcordconfirmation', 'bi_intellidata');
                $form_id = 'form-tbl-export-settings';
                $buttons[] = $OUTPUT->action_icon(
                    $aurl,
                    new \pix_icon('t/reset', get_string('resetexport', 'bi_intellidata'),
                        'core',
                        ['class' => 'iconsmall']),
                    null,
                    ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
                    ]
                );
            }

            $aurl = new \moodle_url('/integrations/bi/intellidata/config/edit.php', $urlparams);
            $buttons[] = $OUTPUT->action_icon($aurl, new \pix_icon('t/edit', get_string('edit'),
                'core', ['class' => 'iconsmall']), null
            );
        }

        $buttons = $this->index_actions($values, $buttons);

        return implode(' ', $buttons);
    }

    /**
     * Create or delete index if allowed.
     *
     * @param $values
     * @return void
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function index_actions($values, $buttons = []) {
        global $OUTPUT;

        if (!in_array($values->datatype, $this->datatypestoignoreindex) && !empty($this->datatypes[$values->datatype]['table'])) {

            if (!empty($values->tableindex)) {
                // Display a 'Delete Index' (-) icon if a bi_intellidata_export_log record has a tableindex populated.
                // On click, show a confirmation dialog. Do a POST request for security reasons.
                $aurl = '/integrations/bi/intellidata/config/edit.php?datatype=' . $values->datatype . '&action=deleteindex';
                $question = get_string('deleteindexcordconfirmation', 'bi_intellidata', $values->datatype);
                $form_id = 'form-tbl-export-settings';
                $buttons[] = $OUTPUT->action_icon(
                    $aurl,
                    new \pix_icon('t/switch_minus', get_string('deleteindex', 'bi_intellidata'),
                        'core',
                        ['class' => 'iconsmall']),
                    null,
                    ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
                    ]
                );
            } else if (!empty($values->timemodified_field)) {
                // Display a 'Create Index' (+) icon if a bi_intellidata_export_log record has a timemodified_field value populated.
                // On click, show a confirmation dialog. Do a POST request for security reasons.
                $aurl = '/integrations/bi/intellidata/config/edit.php?datatype=' . $values->datatype . '&action=createindex';
                $question = get_string('createindexcordconfirmation', 'bi_intellidata', $values->datatype);
                $form_id = 'form-tbl-export-settings';
                $buttons[] = $OUTPUT->action_icon(
                    $aurl,
                    new \pix_icon('t/switch_plus', get_string('createindex', 'bi_intellidata'),
                        'core',
                        ['class' => 'iconsmall']),
                    null,
                    ['onclick' => "ib_dialog_confirm_form_submit.init('" . $question . "', '" . $aurl . "', '" . $form_id . "'); return false;"
                    ]
                );
            }
        }

        return $buttons;
    }

    /**
     * Start html method.
     */
    public function start_html() {
        // Render button to allow user to reset table preferences.
        echo $this->render_reset_button();

        // Do we need to print initial bars?
        $this->print_initials_bar();

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            echo $this->download_buttons();
        }

        // Wrap the results table in a form, so the action buttons can perform POST requests for security reasons.
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo \html_writer::start_tag('form', [
            'id' => 'form-tbl-export-settings',
            'method' => 'POST',
            'action' => ''
        ]);

        echo \html_writer::tag('input', '', [
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey()
        ]);

        echo html_writer::start_tag('table', $this->attributes);
    }

    /**
     * @inheritDoc
     */
    public function end_html() {
        // End the form around the results table.
        echo \html_writer::end_tag('form');
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

        $html = html_writer::start_div('intellidata-configuration__nodata');
        $html .= get_string('norecordsmatchedtocriteria', 'bi_intellidata');
        $html .= html_writer::end_div();
        echo $html;

        // Render the dynamic table footer.
        if (method_exists($this, 'get_dynamic_table_html_end')) {
            echo $this->get_dynamic_table_html_end();
        }
    }

    /**
     * @param $value
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function yes_or_now_column($value) {
        return ($value) ? get_string('yes') : get_string('no');
    }
}