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
 * A table of check results
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace core\check;

defined('MOODLE_INTERNAL') || die();

/**
 * A table of check results
 *
 * @copyright  2020 Brendan Heywood <brendan@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table implements \renderable {

    /**
     * @var \moodle_url|string $url
     */
    protected $url = '';

    /**
     * @var string $type What type of checks
     */
    protected $type = '';

    /**
     * @var check $detail a specific check to focus on
     */
    public $detail = '';

    /**
     * @var array $checks shown in this table
     */
    public $checks = [];

    /**
     * Constructor
     *
     * @param string $type of check One of \core\check
     * @param string $url of report
     * @param string $detail check to focus on
     */
    public function __construct(string $type, $url, string $detail = '') {

        // We may need a bit more memory and this may take a long time to process.
        \raise_memory_limit(MEMORY_EXTRA);
        \core_php_time_limit::raise();

        $this->type = $type;
        $this->url = $url;
        $this->checks = manager::get_checks($type);

        if ($detail) {
            $this->checks = array_filter($this->checks, function($check) use ($detail) {
                return $detail == $check->get_ref();
            });
            if (!empty($this->checks)) {
                $this->detail = reset($this->checks);
            }
        }
    }

    /**
     * Render a table of checks
     *
     * @param \core_renderer $output Renderer to use
     * @return string html output
     */
    public function render($output) {

        $table = new \html_table();
        $table->data = [];
        $table->head  = [
            get_string('status'),
            get_string('check'),
            get_string('summary'),
            get_string('action'),
        ];
        $table->colclasses = [
            'rightalign status',
            'leftalign check',
            'leftalign summary',
            'leftalign action',
        ];
        $table->id = $this->type . 'reporttable';
        $table->attributes = ['class' => 'admintable ' . $this->type . 'report generaltable'];

        $result = false;

        // Sort the checks alphabetically by name.
        \core_collator::asort_objects_by_method($this->checks, 'get_name');

        foreach ($this->checks as $check) {
            $ref = $check->get_ref();
            $result = $check->get_result();
            $actionlink = $check->get_action_link();

            $link = new \moodle_url($this->url, ['detail' => $ref]);

            $row = [];
            $row[] = $output->check_result($result);
            $row[] = $output->action_link($link, $check->get_name(), null, ['id' => $ref]);

            $summary = $result->get_summary();
            if (!$this->detail) {
                $summary .= '<br>'
                . \html_writer::start_tag('small')
                . $output->action_link($link, get_string('moreinfo'))
                . \html_writer::end_tag('small');
            }

            $row[] = $summary;
            if ($actionlink) {
                $row[] = $output->render($actionlink);
            } else {
                $row[] = '';
            }

            $table->data[] = $row;
        }
        $html = $output->render($table);

        if ($this->detail && $result) {
            $html .= $output->heading(get_string('details'), 3);
            $html .= $output->box($result->get_details(), 'generalbox boxwidthwide boxaligncenter');
            $html .= '<br>';
            $html .= $output->continue_button($this->url . '#' . $ref);
        }

        return $html;
    }
}

