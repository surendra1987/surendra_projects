<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Arshad Anwer <arshad.anwer@totara.com>
 * @package totara_reportbuilder
 * @subpackage reportbuilder
 */

global $CFG;
require_once($CFG->dirroot . '/totara/reportbuilder/filters/lib.php');

/**
 * Generic filter based on selecting multiple tenants from the list of tenants.
 * The user is required to have the "totara/tenant:view" capability to use this filter
 */
class rb_filter_grpconcat_tenant extends rb_filter_type {
    const TENANT_OPERATOR_ANY = 0;
    const TENANT_OPERATOR_CONTAINS = 1;
    const TENANT_OPERATOR_NOT_CONTAINS = 2;

    private $join = 'tenant';

    /**
     * Returns an array of comparison operators
     * @return array of comparison operators
     */
    public function get_operators(): array {
        // Basic operators that we allow users to use for choosing multiple tenants.
        return [
            self::TENANT_OPERATOR_ANY => get_string('isanyvalue', 'filters'),
            self::TENANT_OPERATOR_CONTAINS => get_string('filtercontains', 'totara_reportbuilder'),
            self::TENANT_OPERATOR_NOT_CONTAINS => get_string('filtercontainsnot', 'totara_reportbuilder'),
        ];
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    public function setupForm(&$mform) {
        global $SESSION, $DB;
        $label = format_string($this->label);
        $advanced = $this->advanced;

        // Get saved values.
        if (isset($SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name])) {
            $saved = $SESSION->reportbuilder[$this->report->get_uniqueid()][$this->name];
        }

        // Container for currently selected items.
        $objs = array();
        $objs['operator'] = $mform->createElement('select', $this->name.'_op', null, $this->get_operators());
        $objs['operator']->setLabel(get_string('limiterfor', 'filters', $label));


        $content = html_writer::tag('div', '', array('class' => 'list-' . $this->name));

        // Create list of saved items.
        if (isset($saved['value'])) {
            list($insql, $inparams) = $DB->get_in_or_equal(explode(',', $saved['value']));
            $items = $DB->get_records_select($this->join, "id {$insql}", $inparams);
            if (!empty($items)) {
                $list = html_writer::start_tag('div', array('class' => 'abclist-' . $this->name ));
                foreach ($items as $item) {
                    $list .= self::display_selected_tenant_item($item, $this->name);
                }
                $list .= html_writer::end_tag('div');
                $content .= $list;
            }
        }

        // Add choose link.
        $content .= $this->display_choose_tenant_items_link($this->name, $this->type);

        $objs['static'] = $mform->createElement('static', $this->name . '_list', null, $content)->set_allow_xss(true);

        $grp =& $mform->addElement('group', $this->name . '_grp', $label, $objs, '', false);
        $this->add_help_button($mform, $grp->_name, 'reportbuildertenantfilter', 'totara_reportbuilder');

        if ($advanced) {
            $mform->setAdvanced($this->name.'_grp');
        }

        $mform->addElement('hidden', $this->name);
        $mform->setType($this->name, PARAM_SEQUENCE);
        $mform->setType($this->name . '_op', PARAM_INT);

        // Set saved values.
        if (isset($saved)) {
            $mform->setDefault($this->name, $saved['value']);
            $mform->setDefault($this->name . '_op', $saved['operator']);
        }

        // Add maximum select value.
        if (!empty($this->options['selectionlimit'])) {
            $mform->addElement('hidden', $this->name . '_selection_limit', $this->options['selectionlimit']);
            $mform->setType($this->name . '_selection_limit', PARAM_INT);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    public function check_data($formdata) {
        $field = $this->name;
        $operator = $field . '_op';

        if (!empty($formdata->$field)) {
            return array(
                'operator' => $formdata->$operator,
                'value' => $formdata->$field,
            );
        }

        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     *
     * @param array $data filter settings
     * @return array containing filtering condition SQL clause and params
     */
    public function get_sql_filter($data) {
        global $DB;

        $tenantids = explode(',', $data['value']);
        $query = $this->get_field();
        $operator  = $data['operator'];

        switch ($operator) {
            case self::TENANT_OPERATOR_CONTAINS:
                $equal = true;
                break;
            case self::TENANT_OPERATOR_NOT_CONTAINS:
                $equal = false;
                break;
            default:
                // Return 1=1 instead of TRUE for MSSQL support.
                return array(' 1=1 ', array());
        }

        // None selected - match everything.
        if (empty($tenantids)) {
            // Using 1=1 instead of TRUE for MSSQL support.
            return array(' 1=1 ', array());
        }

        list($insql, $params) = $DB->get_in_or_equal($tenantids, SQL_PARAMS_NAMED, 'param', $equal);
        $sql = ' ('.$query.') '.$insql;

        return array($sql, $params);
    }

    /**
     * Is this filter performing the filtering of results?
     *
     * @param array $data element filtering data
     * @return bool
     */
    public function is_filtering(array $data): bool {
        $operator = $data['operator'] ?? 0;
        if ($operator == 0) {
            return false;
        }
        $value = $data['value'] ?? '';
        return !empty($value);
    }

    /**
     * Include Js for this filter
     *
     */
    public function include_js() {
        global $PAGE;

        $code = array();
        $code[] = TOTARA_JS_DIALOG;
        $code[] = TOTARA_JS_TREEVIEW;
        local_js($code);

        $jsdetails = new stdClass();
        $jsdetails->args = array('filter_to_load' => 'tenant', null, null, $this->name, 'reportid' => $this->report->_id);

        $PAGE->requires->js_call_amd('totara_reportbuilder/filter_dialogs', 'init', $jsdetails->args);
    }

    function display_choose_tenant_items_link($filtername, $type) {
        return html_writer::tag(
            'div',
            html_writer::link(
                '#',
                get_string("choosetenants", 'totara_reportbuilder'),
                array('id' => "show-{$filtername}-dialog")
            ),
            array('class' => "rb-{$type}-add-link")
        );
    }

    public static function display_selected_tenant_item($item, $filtername) {
        global $OUTPUT;

        $out = html_writer::start_tag('div', array('data-filtername' =>  $filtername,
            'data-id' => $item->id, 'class' => 'multiselect-selected-item'
        ));
        $out .= $item->name;
        $deleteicon = $OUTPUT->flex_icon('times-danger');
        $out .= html_writer::link('#', $deleteicon);
        $out .= html_writer::end_tag('div');
        return $out;
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;

        $values = explode(',', $data['value']);
        $operator = $data['operator'];

        if (empty($operator) || empty($values)) {
            return '';
        }

        $a = new stdClass();
        $a->label = $this->label;

        $selected = array();
        list($insql, $inparams) = $DB->get_in_or_equal($values);
        if ($tenants = $DB->get_records_select('tenant', "id ".$insql, $inparams, 'id')) {
            foreach ($tenants as $tenant) {
                $selected[] = '"' . format_string($tenant->name) . '"';
            }
        }

        $orstring = get_string('or', 'totara_reportbuilder');
        $a->value    = implode($orstring, $selected);
        $operators = $this->get_operators();
        $a->operator = $operators[$operator];

        return get_string('selectlabel', 'filters', $a);
    }
}
