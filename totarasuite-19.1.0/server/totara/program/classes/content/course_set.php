<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\content;

use core\entity\course;
use totara_core\progressinfo\progressinfo;
use totara_program\event\program_courseset_completed;
use totara_program\program;
use totara_program\utils;

/**
 * Abstract class for course_set implementations.
 */
abstract class course_set {

    const COMPLETIONTYPE_ALL = 1;
    const COMPLETIONTYPE_ANY = 2;
    const COMPLETIONTYPE_SOME = 3;
    const COMPLETIONTYPE_OPTIONAL = 4;

    const NEXTSETOPERATOR_THEN = 1;
    const NEXTSETOPERATOR_OR = 2;
    const NEXTSETOPERATOR_AND = 3;

    public $id, $programid, $contenttype, $sortorder, $label;
    public $competencyid, $nextsetoperator, $completiontype;
    public $timeallowed, $timeallowednum, $timeallowedperiod;
    public $recurrencetime, $recurcreatetime;
    public $isfirstset, $islastset;
    public $uniqueid;
    public $certifpath;
    public $mincourses, $coursesumfield, $coursesumfieldtotal;

    /**
     * Constructor
     *
     * @param int $programid Note that programid is ignored if $setobj is present.
     * @param \stdClass|null $setob
     * @param string $uniqueid
     */
    public function __construct(int $programid, $setob = null, string $uniqueid = null) {
        if (is_object($setob)) {
            $this->id = $setob->id;
            $this->programid = $setob->programid;
            $this->sortorder = $setob->sortorder;
            $this->contenttype = $setob->contenttype;
            $this->label = $setob->label;
            $this->competencyid = $setob->competencyid;
            $this->nextsetoperator = $setob->nextsetoperator;
            $this->completiontype = $setob->completiontype;
            $this->timeallowed = $setob->timeallowed;
            $this->recurrencetime = $setob->recurrencetime;
            $this->recurcreatetime = $setob->recurcreatetime;
            $this->certifpath = $setob->certifpath;
        } else {
            $this->id = 0;
            $this->programid = $programid;
            $this->sortorder = 0;
            $this->contenttype = 0;
            $this->label = '';
            $this->competencyid = 0;
            $this->nextsetoperator = 0;
            $this->completiontype = 0;
            $this->timeallowed = 0;
            $this->recurrencetime = 0;
            $this->recurcreatetime = 0;
            $this->certifpath = 0;
        }

        $timeallowed = utils::duration_explode($this->timeallowed);
        $this->timeallowednum = $timeallowed->num;
        $this->timeallowedperiod = $timeallowed->period;

        if ($uniqueid) {
            $this->uniqueid = $uniqueid;
        } else {
            $this->uniqueid = rand();
        }
    }

    /**
     * Init form data
     *
     * @param string $formnameprefix
     * @param \stdClass $formdata
     * @return void
     */
    public function init_form_data(string $formnameprefix, $formdata): void {
        $defaultlabel = $this->get_default_label();

        $this->id = $formdata->{$formnameprefix.'id'};
        $this->programid = $formdata->id;
        $this->contenttype = $formdata->{$formnameprefix.'contenttype'};
        $this->sortorder = $formdata->{$formnameprefix.'sortorder'};
        $this->label = isset($formdata->{$formnameprefix.'label'})
        && ! empty($formdata->{$formnameprefix.'label'}) ? $formdata->{$formnameprefix.'label'} : $defaultlabel;
        $this->nextsetoperator = isset($formdata->{$formnameprefix.'nextsetoperator'}) ? $formdata->{$formnameprefix.'nextsetoperator'} : 0;
        $this->timeallowednum = $formdata->{$formnameprefix.'timeallowednum'};
        $this->timeallowedperiod = $formdata->{$formnameprefix.'timeallowedperiod'};
        $this->timeallowed = utils::duration_implode($this->timeallowednum, $this->timeallowedperiod);
    }

    /**
     * Get completion type string.
     *
     * @return string|bool Returns false if no completion type.
     */
    protected function get_completion_type_string() {
        switch ($this->completiontype) {
            case self::COMPLETIONTYPE_ANY:
            case self::COMPLETIONTYPE_SOME:
            case self::COMPLETIONTYPE_OPTIONAL:
                $completiontypestr = get_string('or', 'totara_program');
                break;
            case self::COMPLETIONTYPE_ALL:
                $completiontypestr = get_string('and', 'totara_program');
                break;
            default:
                return false;
        }

        return $completiontypestr;
    }

    /**
     * Get html definition list of completion settings details (any, some, or all courses, min. number)
     * This does not include list of courses itself.
     *
     * @return string
     */
    protected function get_completion_explanation_html(): string {
        $out = '';
        // Explain some or all courses must be completed.
        $typestr = get_string('completeallcourses', 'totara_program');
        if ($this->completiontype != self::COMPLETIONTYPE_ALL) {
            if ($this->completiontype == self::COMPLETIONTYPE_ANY) {
                // Only one course must be completed.
                $typestr = get_string('completeanycourse', 'totara_program');
            } else {
                $a = new \stdClass();
                $a->mincourses = $this->mincourses;
                $typestr = get_string('completemincourses', 'totara_program', $a);
            }
        }

        if ($this->timeallowed > 0) {
            $numperiod = utils::get_duration_num_and_period($this->timeallowed);
            $typestr .= \html_writer::tag('p', get_string('allowtimeforset' . $numperiod->periodkey, 'totara_program', $numperiod->num));
        }

        $out .= \html_writer::div($typestr);
        return $out;
    }

    /**
     * Get set unique id
     *
     * @return string|null
     */
    public function get_set_prefix(): ?string {
        return $this->uniqueid;
    }

    /**
     * Set certification path
     *
     * @param int $c
     * @return void
     */
    public function set_certifpath(int $c): void {
        $this->certifpath = $c;
    }

    /**
     * Get default label
     *
     * @return string
     */
    public function get_default_label(): string {
        return get_string('untitledset', 'totara_program');
    }

    /**
     * Is recurring?
     *
     * @return bool
     */
    public function is_recurring(): bool {
        return false;
    }

    /**
     * Check course action
     *
     * @param string $action
     * @param \stdClass $formdata
     * @return bool|int
     */
    public function check_course_action(string $action, $formdata) {
        return false;
    }

    /**
     * Save set
     *
     * @return bool
     */
    public function save_set(): bool {
        global $DB;

        // Make sure the course set is saved with a sensible label instead of the default
        if ($this->label == $this->get_default_label()) {
            $this->label = get_string('legend:courseset', 'totara_program', $this->sortorder);
        }

        if (empty($this->islastset)) {
            $nextsetoperator = $this->nextsetoperator;
        } else {
            // We should have an empty nextsetoperator if this is the last course set.
            $nextsetoperator = 0;
        }

        $todb = new \stdClass();
        $todb->programid = $this->programid;
        $todb->sortorder = $this->sortorder;
        $todb->competencyid = $this->competencyid;
        $todb->nextsetoperator = $nextsetoperator;
        $todb->completiontype = $this->completiontype;
        $todb->timeallowed = $this->timeallowed;
        $todb->recurrencetime = $this->recurrencetime;
        $todb->recurcreatetime = $this->recurcreatetime;
        $todb->contenttype = $this->contenttype;
        $todb->label = $this->label;
        $todb->certifpath = $this->certifpath;

        if ($this->id > 0) { // if this set already exists in the database
            $todb->id = $this->id;
            return $DB->update_record('prog_courseset', $todb);
        } else {
            if ($id = $DB->insert_record('prog_courseset', $todb)) {
                $this->id = $id;
                return true;
            }
            return false;
        }
    }

    /**
     * Returns true or false depending on whether or not this course set
     * contains the specified course
     *
     * @param int $courseid
     * @return bool
     */
    abstract public function contains_course(int $courseid): bool;

    /**
     * Checks whether or not the specified user has completed all the criteria
     * necessary to complete this course set and adds a record to the database
     * if so or returns false if not
     *
     * @param int $userid
     * @return int|bool Returns int if new record created, otherwise bool if updated
     */
    abstract public function check_courseset_complete(int $userid);

    /**
     * Updates the completion record in the database for the specified user
     *
     * @param int $userid
     * @param array $completionsettings Contains the field values for the record
     * @return bool|int Returns int if new record created, otherwise bool if updated
     */
    public function update_courseset_complete(int $userid, array $completionsettings) {
        global $DB;
        $eventtrigger = false;

        // if the course set is being marked as complete we need to trigger an
        // event to any listening modules
        if (array_key_exists('status', $completionsettings)) {
            if ($completionsettings['status'] == program::STATUS_COURSESET_COMPLETE) {

                // flag that we need to trigger the courseset_completed event
                $eventtrigger = true;
            }
        }

        if ($completion = $DB->get_record('prog_completion',
            array('coursesetid' => $this->id, 'programid' => $this->programid, 'userid' => $userid))) {

            // Do not update record if we have not received any data
            // (generally because we just want to make sure a record exists)
            if (empty($completionsettings)) {
                return true;
            }

            foreach ($completionsettings as $key => $val) {
                $completion->$key = $val;
            }

            if (empty($completion->timestarted)) {
                $completion->timestarted = !empty($completion->timecompleted) ? $completion->timecompleted : time();

                $certid = $DB->get_field('prog', 'certifid', array('id' => $this->programid));
                if (empty($certid)) {
                    $progcomp = prog_load_completion($this->programid, $userid, true);
                } else {
                    list($certcomp, $progcomp) = certif_load_completion($this->programid, $userid, true);
                }

                if (empty($progcomp->timestarted)) {
                    $progcomp->timestarted = $completion->timestarted;
                    if (empty($certid)) {
                        prog_write_completion($progcomp, 'Marked program as started');
                    } else {
                        certif_write_completion($certcomp, $progcomp, 'Marked program as started');
                    }
                }
            }

            if ($update_success = $DB->update_record('prog_completion', $completion)) {
                if ($eventtrigger) {
                    // trigger an event to notify any listeners that this course
                    // set has been completed
                    $event = program_courseset_completed::create(
                        array(
                            'objectid' => $this->programid,
                            'context' => \context_program::instance($this->programid),
                            'userid' => $userid,
                            'other' => array(
                                'coursesetid' => $this->id,
                                'certifid' => 0,
                            )
                        )
                    );
                    $event->trigger();
                }
            }

            return $update_success;

        } else {
            $now = time();

            $completion = new \stdClass();
            $completion->programid = $this->programid;
            $completion->userid = $userid;
            $completion->coursesetid = $this->id;
            $completion->status = program::STATUS_COURSESET_INCOMPLETE;
            $completion->timecreated = $now;
            $completion->timedue = 0;

            foreach ($completionsettings as $key => $val) {
                $completion->$key = $val;
            }

            if (empty($completion->timestarted)) {
                $completion->timestarted = !empty($completion->timecompleted) ? $completion->timecompleted : $now;
            }

            if ($insert_success = $DB->insert_record('prog_completion', $completion)) {
                if ($eventtrigger) {
                    // trigger an event to notify any listeners that this course
                    // set has been completed
                    $event = program_courseset_completed::create(
                        array(
                            'objectid' => $this->programid,
                            'context' => \context_program::instance($this->programid),
                            'userid' => $userid,
                            'other' => array(
                                'coursesetid' => $this->id,
                                'certifid' => 0,
                            )
                        )
                    );
                    $event->trigger();
                }
            }

            return $insert_success;
        }
    }

    /**
     * Returns true or false depending on whether or not the specified user has
     * completed this course set
     *
     * @param int|null $userid
     * @return bool
     */
    public function is_courseset_complete(?int $userid): bool {
        global $DB;
        if (!$userid) {
            return false;
        }
        $completion_status = $DB->get_field('prog_completion', 'status',
            array('coursesetid' => $this->id, 'programid' => $this->programid, 'userid' => $userid));
        if ($completion_status === false) {
            return false;
        }

        return ($completion_status == program::STATUS_COURSESET_COMPLETE);
    }

    /**
     * Returns true if ths course set is optional.
     *
     * @return bool
     */
    public function is_considered_optional(): bool {
        return false;
    }

    /**
     * Returns the HTML suitable for displaying a course set to a learner.
     *
     * @param int $userid
     * @param array $previous_sets
     * @param array $next_sets
     * @param bool $accessible Indicates whether or not the courses in the set are accessible to the user
     * @param bool $viewinganothersprogram Indicates if you are viewing another persons program
     * @param bool $hide_progress Indicates if progress should be hidden
     *
     * @return string
     */
    abstract public function display(
        int $userid=null,
        array $previous_sets = [],
        array $next_sets = [],
        bool $accessible = true,
        bool $viewinganothersprogram = false,
        bool $hide_progress = false
    ): string;

    /**
     * Returns the HTML suitable for display the nextsetoperator in a friendly/informative manner
     *
     * @return string
     */
    public function display_nextsetoperator(): string {
        $out = '';
        if (!isset($this->islastset) || $this->islastset === false) {
            $out .= \html_writer::start_tag('div', array('class' => 'nextsetoperator'));
            switch ($this->nextsetoperator) {
                case self::NEXTSETOPERATOR_THEN:
                    $out .= \html_writer::tag('div', get_string('then', 'totara_program'), array('class' => 'operator-then'));
                    break;
                case self::NEXTSETOPERATOR_OR:
                    $out .= \html_writer::tag('div', get_string('or', 'totara_program'), array('class' => 'operator-or'));
                    break;
                case self::NEXTSETOPERATOR_AND:
                    $out .= \html_writer::tag('div', get_string('and', 'totara_program'), array('class' => 'operator-and'));
                    break;
            }

            $out .= \html_writer::end_tag('div');
        }

        return $out;
    }

    /**
     * Returns an HTML string suitable for displaying as the label for a course
     * set in the program overview form
     *
     * @return string
     */
    public function display_form_label(): string {
        return $this->label;
    }

    /**
     * Returns an HTML string suitable for displaying as the element body
     * for a course set in the program overview form
     *
     * @return string
     */
    abstract public function display_form_element(): string;

    /**
     * This method must be overrideen by sub-classes
     *
     * @return string
     */
    abstract public function print_set_minimal(): string;

    /**
     * Defines the form elements for a course set
     *
     * @param $mform A moodleform-like object
     * @param array $template_values
     * @param \stdClass $formdataobject
     * @param bool $updateform
     * @return string
     */
    abstract public function get_courseset_form_template(&$mform, array &$template_values, &$formdataobject, bool $updateform = true): string;

    /**
     * TBD
     *
     * @param $mform A moodleform-like object
     * @param array $template_values
     * @param \stdClass $formdataobject
     * @param string $prefix
     * @param bool $updateform
     * @return string
     */
    public function get_nextsetoperator_select_form_template(&$mform, array &$template_values, $formdataobject, string $prefix, bool $updateform = true): string {
        $templatehtml = '';
        $hidden = false;

        if ($updateform) {
            if (isset($this->islastset) && $this->islastset) {
                $hidden = true;
                $mform->addElement('hidden', $prefix.'nextsetoperator', 0);
            } else {
                $options = array(
                    self::NEXTSETOPERATOR_THEN => get_string('then', 'totara_program'),
                    self::NEXTSETOPERATOR_OR => get_string('or', 'totara_program'),
                    self::NEXTSETOPERATOR_AND => get_string('and', 'totara_program')
                );
                $mform->addElement('select', $prefix.'nextsetoperator', get_string('label:nextsetoperator', 'totara_program'), $options);
                $mform->setDefault($prefix.'nextsetoperator', $this->nextsetoperator);
            }

            $mform->setType($prefix.'nextsetoperator', PARAM_INT);
            $template_values['%'.$prefix.'nextsetoperator%'] = array('name'=>$prefix.'nextsetoperator', 'value'=>null);
        }

        if ($hidden) {
            $operatorclass = '';
        } else {
            if ($this->nextsetoperator == self::NEXTSETOPERATOR_THEN) {
                $operatorclass = 'nextsetoperator-then';
            } else if ($this->nextsetoperator == self::NEXTSETOPERATOR_OR) {
                $operatorclass = 'nextsetoperator-or';
            } else {
                $operatorclass = 'nextsetoperator-and';
            }
        }

        $templatehtml .= \html_writer::tag('div', '%' . $prefix . 'nextsetoperator%', array('class' => $operatorclass)) . "\n";
        $formdataobject->{$prefix.'nextsetoperator'} = $this->nextsetoperator;

        return $templatehtml;
    }

    /**
     * Returns text such as 'all courses from Course set 1'
     *
     * @param course_set $courseset
     */
    abstract public function get_course_text(course_set $courseset): string;

    /**
     * Get courses
     *
     * @return array
     */
    abstract public function get_courses(): array;

    /**
     * Delete course from course set.
     *
     * @param int $courseid
     * @return bool
     */
    abstract public function delete_course(int $courseid): bool;

    /**
     * Build progressinfo hierarchy for this course set
     *
     * @return progressinfo
     */
    abstract public function build_progressinfo(): progressinfo;

    /**
     * Set progressinfo course scores for this course set
     *
     * @param progressinfo $progressinfo
     * @param int $userid
     * @return void
     */
    public function set_progressinfo_course_scores(progressinfo $progressinfo, int $userid): void {
        $sets = $progressinfo->search_criteria($this->get_progressinfo_key());
        if (empty($sets)) {
            return;
        }

        foreach ($sets as $setinfo) {
            foreach ($this->get_courses() as $course) {
                // For now all criteria have the same weight - 1
                $weight = 1;

                // Get user's progress in completing this course
                $params = array(
                    'userid' => $userid,
                    'course' => $course->id,
                );
                $ccompletion = new \completion_completion($params);
                // Need score between 0 and 1 where 1 == complete
                $percentagecomplete = $ccompletion->get_percentagecomplete();
                if ($percentagecomplete === false) {
                    $score = 0;
                } else {
                    $score = $percentagecomplete / 100.0;
                }

                // Each course can only appear once in a course set
                $courseinfo = $setinfo->get_criteria($this->get_progressinfo_course_key($course));
                if ($courseinfo !== false) {
                    $courseinfo->set_weight($weight);
                    $courseinfo->set_score($score);

                    // We store the course timestarted and timecompleted in courses' customdata to
                    // allow us to use it when saving course set completion status
                    $customdata = array ('timestarted' => $ccompletion->timestarted,
                        'timecompleted' => $ccompletion->timecompleted);

                    if ($this->completiontype == self::COMPLETIONTYPE_SOME) {
                        if ($sumfield = customfield_get_field_instance($course, $this->coursesumfield, 'course', 'course')) {
                            $sumfieldval = $sumfield->display_data();
                            if ($sumfieldval === (string)(int)$sumfieldval) {
                                $customdata['coursepoints'] = (int)$sumfieldval;
                            } else {
                                $customdata['coursepoints'] = 0;
                            }
                        }
                    }

                    $courseinfo->set_customdata($customdata);
                }
            }
        }
    }

    /**
     * Get progress info key
     *
     * @return string
     */
    public function get_progressinfo_key(): string {
        return 'courseset_' . $this->id . '_' . $this->label;
    }

    /**
     * Get progress course key
     *
     * @param course|\stdClass $course
     * @return string
     */
    public function get_progressinfo_course_key($course): string {
        return 'course_' . $course->id . '_' . $course->fullname;
    }
}