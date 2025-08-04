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
 * Class for migration Course Sections.
 *
 * @package    bi_intellidata
 * @author     IntelliBoard
 * @copyright  2023 intelliboard.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace bi_intellidata\entities\coursesections;

use bi_intellidata\helpers\DebugHelper;


/**
 * Class for migration Course Sections.
 *
 * @package    bi_intellidata
 * @author     IntelliBoard
 * @copyright  2023 intelliboard.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class migration extends \bi_intellidata\entities\migration {
    public $entity      = '\bi_intellidata\entities\coursesections\sections';
    public $eventname   = '\core\event\course_section_created';
    public $table       = 'course_sections';


    /**
     * @param $records
     * @return \Generator
     * @throws \coding_exception
     */
    public function prepare_records_iterable($records) {
        global $CFG;

        require_once($CFG->dirroot . '/course/lib.php');

        foreach ($records as $record) {
            try {
                $record->name = $record->name ? : get_section_name($record->course, $record->section);
            } catch (\Exception $e) {
                DebugHelper::error_log($e->getMessage());
            }

            $entity = new $this->entity($record);
            $recorddata = $entity->export();

            yield $recorddata;
        }
    }
}
