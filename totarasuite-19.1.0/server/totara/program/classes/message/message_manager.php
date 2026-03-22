<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 * @deprecated since Totara 18.0
 */

namespace totara_program\message;

use coding_exception;
use dml_exception;
use dml_transaction_exception;
use stdClass;
use totara_program\program;

/**
 * Class message_manager
 *
 * @package totara_program\message
 * @deprecated since Totara 18.0
 */
class message_manager {

    const MESSAGETYPE_ENROLMENT = 1;
    const MESSAGETYPE_EXCEPTION_REPORT = 2;
    const MESSAGETYPE_UNENROLMENT = 3;
    const MESSAGETYPE_PROGRAM_DUE = 4;
    const MESSAGETYPE_EXTENSION_REQUEST = 5;
    const MESSAGETYPE_PROGRAM_OVERDUE = 6;
    const MESSAGETYPE_PROGRAM_COMPLETED = 7;
    const MESSAGETYPE_COURSESET_DUE = 8;
    const MESSAGETYPE_COURSESET_OVERDUE = 9;
    const MESSAGETYPE_COURSESET_COMPLETED = 10;
    const MESSAGETYPE_LEARNER_FOLLOWUP = 11;
    const MESSAGETYPE_RECERT_WINDOWOPEN = 12;
    const MESSAGETYPE_RECERT_WINDOWDUECLOSE = 13;
    const MESSAGETYPE_RECERT_FAILRECERT = 14;

    /**
     * Static cache of program message managers.
     */
    protected static array $managers_cache = [];

    // The $formdataobject is an object that will contains the values of any
    // submitted data so that the message edit form can be populated when it
    // is first displayed
    public stdClass $formdataobject;

    protected int $program_id;
    protected array $messages;
    protected array $messages_deleted_ids;

    // Used to determine if the messages have been changed since it was last saved
    protected bool $messages_changed = false;

    private array $message_classnames = [
        self::MESSAGETYPE_ENROLMENT => 'totara_program\message\non_eventbased\enrolment_message',
        self::MESSAGETYPE_EXCEPTION_REPORT => 'totara_program\message\non_eventbased\exception_report_message',
        self::MESSAGETYPE_UNENROLMENT => 'totara_program\message\non_eventbased\unenrolment_message',
        self::MESSAGETYPE_PROGRAM_DUE => 'totara_program\message\eventbased\program_due_message',
        self::MESSAGETYPE_PROGRAM_OVERDUE => 'totara_program\message\eventbased\program_overdue_message',
        self::MESSAGETYPE_EXTENSION_REQUEST => 'totara_program\message\non_eventbased\extension_request_message',
        self::MESSAGETYPE_PROGRAM_COMPLETED => 'totara_program\message\non_eventbased\program_completed_message',
        self::MESSAGETYPE_COURSESET_DUE => 'totara_program\message\eventbased\courseset_due_message',
        self::MESSAGETYPE_COURSESET_OVERDUE => 'totara_program\message\eventbased\courseset_overdue_message',
        self::MESSAGETYPE_COURSESET_COMPLETED => 'totara_program\message\non_eventbased\courseset_completed_message',
        self::MESSAGETYPE_LEARNER_FOLLOWUP => 'totara_program\message\eventbased\learner_followup_message',
        self::MESSAGETYPE_RECERT_WINDOWOPEN => 'totara_certification\message\eventbased\recert_windowopen_message',
        self::MESSAGETYPE_RECERT_WINDOWDUECLOSE => 'totara_certification\message\eventbased\recert_windowdueclose_message',
        self::MESSAGETYPE_RECERT_FAILRECERT => 'totara_certification\message\eventbased\recert_failrecert_message',
    ];

    /**
     * singleton - should only be called from get_program_messages_manager
     *
     * @param int $program_id
     * @param bool $is_new_program
     * @throws ProgramMessageException
     * @throws coding_exception
     * @throws dml_exception
     * @deprecated since Totara 18.0
     */
    protected function __construct(int $program_id, bool $is_new_program = false) {
        global $DB;
        $this->program_id = $program_id;
        $this->messages = [];
        $this->messages_deleted_ids = [];
        $this->formdataobject = new stdClass();

        $messages = $DB->get_records('prog_message', ['programid' => $program_id], 'sortorder ASC');
        if (count($messages) > 0) {
            foreach ($messages as $message) {

                if (!array_key_exists($message->messagetype, $this->message_classnames)) {
                    throw new ProgramMessageException(get_string('meesagetypenotfound', 'totara_program'));
                }

                $message_class = $this->message_classnames[$message->messagetype];
                $messageob = new $message_class($program_id, $message);

                $this->messages[] = $messageob;
            }
        } else if ($is_new_program) {
            // We no longer create legacy messages. Instead, new built-in notifications will be inherited.

            // The default message must be saved at this point.
            $this->save_messages();
        }

        $this->fix_message_sortorder($this->messages);
    }

    /**
     * @return bool
     * @throws dml_exception
     */
    public function save_messages(): bool {
        global $DB;
        $this->fix_message_sortorder($this->messages);

        // first delete any messages from the database that have been marked for deletion
        foreach ($this->messages_deleted_ids as $messageid) {

            if ($DB->get_record('prog_message', array('id' => $messageid))) {
                // delete any logged messages sent for this message
                $DB->delete_records('prog_messagelog', array('messageid' => $messageid));
                // delete the message
                $DB->delete_records('prog_message', array('id' => $messageid));
            }
        }

        // then save the new and changed messages
        foreach ($this->messages as $message) {
            if (!$message->save_message()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Makes sure that an array of messages is in order in terms of each
     * message's sortorder property and resets the sortorder properties to ensure
     * that it begins from 1 and there are no gaps in the order.
     *
     * Also adds properties to enable the first and last set in the array to be
     * easily detected.
     *
     * @param ?array $messages
     */
    public function fix_message_sortorder(?array &$messages = null): void {

        if ($messages == null) {
            $messages = $this->messages;
        }

        usort($messages, array(self::class, 'cmp_message_sortorder'));

        $pos = 1;
        foreach ($messages as $message) {

            $message->sortorder = $pos;

            unset($message->isfirstmessage);
            if ($pos == 1) {
                $message->isfirstmessage = true;
            }

            unset($message->islastmessage);
            if ($pos == count($messages)) {
                $message->islastmessage = true;
            }

            $pos++;
        }
    }

    /**
     * Retrieves a programs message manager from cache or creates and caches it.
     *
     * @param int $program_id The id of the program
     * @param bool $refreshcache Whether to use the managers_cache or ignore it
     * @return message_manager An instance of message_manager
     * @throws ProgramMessageException
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_program_messages_manager(int $program_id, bool $refreshcache = false): message_manager {
        if ($refreshcache || !isset(self::$managers_cache[$program_id])) {
            self::$managers_cache[$program_id] = new  message_manager($program_id);
        }

        return self::$managers_cache[$program_id];
    }

    /**
     * Resets the program message managers static cache. Called during testing to prevent leaking between tests.
     */
    public static function reset_cache(): void {
        self::$managers_cache = array();
    }

    /**
     * Used by usort to sort the messages in the $messages array
     * by their sortorder properties
     *
     * @param <type> $a
     * @param <type> $b
     * @return <type>
     */
    static function cmp_message_sortorder($a, $b) {
        if ($a->sortorder == $b->sortorder) {
            return 0;
        }
        return ($a->sortorder < $b->sortorder) ? -1 : 1;
    }

    /**
     * Get the messages
     *
     * @return array
     */
    public function get_messages(): array {
        return $this->messages;
    }

    /**
     * Deletes all messages for this program and removes all traces of sent
     * messages from the message log
     *
     * @return bool
     * @throws dml_transaction_exception
     * @throws dml_exception
     */
    public function delete(): bool {
        global $DB;

        $transaction = $DB->start_delegated_transaction();

        // delete the history of all sent messages
        foreach ($this->messages as $message) {
            $DB->delete_records('prog_messagelog', array('messageid' => $message->id));
        }
        // delete all messages
        $DB->delete_records('prog_message', array('programid' => $this->program_id));

        $transaction->allow_commit();

        return true;
    }

    /**
     * Recieves the data submitted from the program messages form and sets up
     * the messages in an array so that they can be manipulated and/or
     * re-displayed in the form
     *
     * @param <type> $formdata
     * @return bool
     * @throws ProgramMessageException
     * @throws coding_exception
     */
    public function setup_messages($formdata): bool {

        $message_prefixes = $this->get_message_prefixes($formdata);

        // If the form has been submitted then it's likely that some changes are
        // being made to the messages so we mark the messages as changed (this
        // is used by javascript to determine whether or not to warn te user
        // if they try to leave the page without saving first
        $this->messages_changed = true;

        $this->messages = array();

        foreach ($message_prefixes as $prefix) {

            $messagetype = $formdata->{$prefix . 'messagetype'};

            if (!array_key_exists($messagetype, $this->message_classnames)) {
                throw new ProgramMessageException(get_string('meesagetypenotfound', 'totara_program'));
            }

            $message_class = $this->message_classnames[$messagetype];
            $message = new $message_class($this->program_id, null, $prefix);

            $message->init_form_data($prefix, $formdata);
            $this->messages[] = $message;
        }

        $this->messages_deleted_ids = $this->get_deleted_messages($formdata);
        return true;
    }

    /**
     * Retrieves the form name prefixes of all the existing messages from
     * the submitted data and returns an array containing all the form name
     * prefixes
     *
     * @param object $formdata The submitted form data
     * @return array
     */
    public function get_message_prefixes($formdata): array {
        if (!isset($formdata->messageprefixes) || empty($formdata->messageprefixes)) {
            return array();
        } else {
            return explode(',', $formdata->messageprefixes);
        }
    }

    /**
     * Retrieves the ids of any deleted messages from the submitted data and
     * returns an array containing the id numbers or an empty array
     *
     * @param <type> $formdata
     * @return <type>
     */
    public function get_deleted_messages($formdata): array {
        if (!isset($formdata->deletedmessages) || empty($formdata->deletedmessages)) {
            return array();
        }
        return explode(',', $formdata->deletedmessages);
    }

    /**
     * Determines whether or not an action button was clicked and, if so,
     * determines which message the action refers to (based on the message sortorder)
     * and returns the message order number.
     *
     * @param string $action The action that this relates to (moveup, movedown, delete, etc)
     * @param object $formdata The submitted form data
     * @return int|obj|false Returns message order number if a matching action was found or false for no action
     */
    public function check_message_action($action, $formdata) {

        $message_prefixes = $this->get_message_prefixes($formdata);

        // if a submit button was clicked, try to determine if it relates to a
        // message and, if so, return the message sort order
        foreach ($message_prefixes as $prefix) {
            if (isset($formdata->{$prefix . $action})) {
                return $formdata->{$prefix . 'sortorder'};
            }
        }

        return false;
    }

    /**
     * Moves a message up one place in the array of messages
     *
     * @param <type> $messagetomove_sortorder
     * @return bool
     */
    public function move_message_up($messagetomove_sortorder): bool {

        foreach ($this->messages as $current_message) {

            if ($current_message->sortorder == $messagetomove_sortorder) {
                $messagetomoveup = $current_message;
            }

            if ($current_message->sortorder == $messagetomove_sortorder - 1) {
                $messagetomovedown = $current_message;
            }
        }

        if ($messagetomoveup && $messagetomovedown) {
            $moveup_sortorder = $messagetomoveup->sortorder;
            $movedown_sortorder = $messagetomovedown->sortorder;
            $messagetomoveup->sortorder = $movedown_sortorder;
            $messagetomovedown->sortorder = $moveup_sortorder;
            $this->fix_message_sortorder($this->messages);
            return true;
        }

        return false;
    }

    /**
     * Moves a message down one place in the array of message
     *
     * @param <type> $messagetomove_sortorder
     * @return bool
     */
    public function move_message_down($messagetomove_sortorder): bool {

        foreach ($this->messages as $current_message) {

            if ($current_message->sortorder == $messagetomove_sortorder) {
                $messagetomovedown = $current_message;
            }

            if ($current_message->sortorder == $messagetomove_sortorder + 1) {
                $messagetomoveup = $current_message;
            }
        }

        if ($messagetomovedown && $messagetomoveup) {
            $movedown_sortorder = $messagetomovedown->sortorder;
            $moveup_sortorder = $messagetomoveup->sortorder;
            $messagetomovedown->sortorder = $moveup_sortorder;
            $messagetomoveup->sortorder = $movedown_sortorder;
            $this->fix_message_sortorder($this->messages);
            return true;
        }

        return false;
    }

    /**
     * Adds a new message to the array of messages.
     *
     * @param $messagetype
     */
    public function add_message($messagetype) {

        $lastmessagepos = $this->get_last_message_pos();

        if (!array_key_exists($messagetype, $this->message_classnames)) {
            throw new ProgramMessageException(get_string('meesagetypenotfound', 'totara_program'));
        }

        $message_class = $this->message_classnames[$messagetype];
        $message = new $message_class($this->program_id);

        if ($lastmessagepos !== null) {
            $message->sortorder = $lastmessagepos + 1;
        } else {
            $message->sortorder = 1;
        }

        $this->messages[] = $message;
        $this->fix_message_sortorder($this->messages);
        return true;
    }

    /**
     * Returns the sort order of the last message.
     *
     * @return <type>
     */
    public function get_last_message_pos() {
        $sortorder = null;
        foreach ($this->messages as $message) {
            $sortorder = max($sortorder, $message->sortorder);
        }
        return $sortorder;
    }

    /**
     * Deletes a message from the array of messages. If the message
     * has no id number (i.e. it does not yet exist in the database) it is
     * removed from the array but if it has an id number it is marked as
     * deleted but not actually removed from the array until the messages are
     * saved
     *
     * @param <type> $messagetodelete_sortorder
     * @return bool
     */
    public function delete_message($messagetodelete_sortorder): bool {

        $new_messages = array();
        $messagefound = false;

        foreach ($this->messages as $message) {
            if ($message->sortorder == $messagetodelete_sortorder) {
                $messagefound = true;
                if ($message->id > 0) { // if this message already exists in the database
                    $this->messages_deleted_ids[] = $message->id;
                }
            } else {
                $new_messages[] = $message;
            }
        }

        if ($messagefound) {
            $this->messages = $new_messages;
            $this->fix_message_sortorder($this->messages);
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    public function update_messages(): void {
        $this->fix_message_sortorder($this->messages);
    }

    /**
     * Returns an HTML string suitable for displaying as the label for the
     * messages in the program overview form
     *
     * @param program $program
     * @return string
     */
    public function display_form_label(program $program = null): string {
        $out = '';
        $out .= get_string('instructions:messages1', $program && $program->is_certif() ? 'totara_certification' : 'totara_program');
        return $out;
    }

    /**
     * Returns an HTML string suitable for displaying as the element body
     * for the messages in the program overview form
     *
     * @return string
     */
    public function display_form_element() {

        $out = '';

        if (count($this->messages)) {
            $messagecount = 0;
            foreach ($this->messages as $message) {
                $messageclassname = $this->message_classnames[$message->messagetype];
                $styleclass = ($messagecount % 2 == 0) ? 'even' : 'odd';
                $component = (substr($messageclassname, 0, 11) == 'prog_recert' ? 'totara_certification' : 'totara_program');
                $out .= html_writer::tag('p', get_string($messageclassname, $component), array('class' => $styleclass));
                $messagecount++;
            }
        } else {
            $out .= get_string('noprogrammessages', 'totara_program');
        }

        return $out;
    }

    public function get_message_form_template(&$mform, &$template_values, $messages = null, $updateform = true) {
        global $DB, $OUTPUT;

        $iscertif = $DB->get_field('prog', 'certifid', array('id' => $this->program_id)) ? true : false;

        if ($messages == null) {
            $messages = $this->messages;
        }

        $templatehtml = '';
        $nummessages = count($messages);
        $canaddmessage = true;

        // This update button is at the start of the form so that it catches any
        // 'return' key presses in text fields and acts as the default submit
        // behaviour. This is not official browser behaviour but in most browsers
        // this should result in this button being submitted (where a form has
        // multiple submit buttons like this one)
        if ($updateform) {
            $mform->addElement('submit', 'update', get_string('update', 'totara_program'));
            $template_values['%update%'] = array('name' => 'update', 'value' => null);
        }
        $templatehtml .= '%update%' . "\n";

        // Add the program id
        if ($updateform) {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
            $template_values['%programid%'] = array('name' => 'id', 'value' => null);
        }
        $templatehtml .= '%programid%' . "\n";
        $this->formdataobject->id = $this->program_id;

        // Add a hidden field to show if the messages have been changed
        // (used by javascript to determine whether or not to display a
        // dialog when the user leaves the page)
        $messageschanged = $this->messages_changed ? '1' : '0';
        if ($updateform) {
            $mform->addElement('hidden', 'messageschanged', $messageschanged);
            $mform->setType('messageschanged', PARAM_BOOL);
            $mform->setConstant('messageschanged', $messageschanged);
            $template_values['%messageschanged%'] = array('name' => 'messageschanged', 'value' => null);
        }
        $templatehtml .= '%messageschanged%' . "\n";
        $this->formdataobject->messageschanged = $messageschanged;

        // Add the deleted message ids
        if ($this->messages_deleted_ids) {
            $deletedmessageidsarray = array();
            foreach ($this->messages_deleted_ids as $deleted_message_id) {
                $deletedmessageidsarray[] = $deleted_message_id;
            }
            $deletedmessageidsstr = implode(',', $deletedmessageidsarray);
            if ($updateform) {
                $mform->addElement('hidden', 'deletedmessages', $deletedmessageidsstr);
                $mform->setType('deletedmessages', PARAM_SEQUENCE);
                $mform->setConstant('deletedmessages', $deletedmessageidsstr);
                $template_values['%deletedmessages%'] = array('name' => 'deletedmessages', 'value' => null);
            }
            $templatehtml .= '%deletedmessages%' . "\n";
            $this->formdataobject->deletedmessages = $deletedmessageidsstr;
        }

        $str = $iscertif ? get_string('certificationmessages', 'totara_certification') : get_string('programmessages', 'totara_program');
        $templatehtml .= $OUTPUT->heading($str);
        $str = $iscertif ? get_string('instructions:certificationmessages', 'totara_certification') : get_string('instructions:programmessages', 'totara_program');
        $templatehtml .= html_writer::tag('p', $str);

        $templatehtml .= html_writer::start_tag('div', array('id' => 'messages'));

        if ($nummessages == 0) { // if there are no messages yet
            $templatehtml .= html_writer::tag('p', get_string('noprogrammessages', 'totara_program'));
        } else {
            $messageprefixesarray = array();
            foreach ($messages as $message) {
                $messageprefixesarray[] = $message->get_message_prefix();
                // Add the messages
                $templatehtml .= $message->get_message_form_template($mform, $template_values, $this->formdataobject, $updateform);
            }

            // Add the set prefixes
            $messageprefixesstr = implode(',', $messageprefixesarray);
            if ($updateform) {
                $mform->addElement('hidden', 'messageprefixes', $messageprefixesstr);
                $mform->setType('messageprefixes', PARAM_TEXT);
                $mform->setConstant('messageprefixes', $messageprefixesstr);
                $template_values['%messageprefixes%'] = array('name' => 'messageprefixes', 'value' => null);
            }
            $templatehtml .= '%messageprefixes%' . "\n";
            $this->formdataobject->messageprefixes = $messageprefixesstr;
        }

        $templatehtml .= html_writer::end_tag('div');

        if ($canaddmessage) {
            $templatehtml .= html_writer::start_tag('div', array('id' => 'addtoselect'));

            // Add the add message drop down
            if ($updateform) {
                $messageoptions = array();

                // Add extra messages if a certification.
                if ($iscertif) {
                }

                $mform->addElement('select', 'messagetype', get_string('addnew', 'totara_program'), $messageoptions, array('id' => 'messagetype'));
                $mform->setType('messagetype', PARAM_INT);
                $template_values['%messagetype%'] = array('name' => 'messagetype', 'value' => null);
            }
            $templatehtml .= html_writer::tag('label', get_string('addnew', 'totara_program'), array('for' => 'messagetype'));
            $templatehtml .= '%messagetype%';
            $str = $iscertif ? get_string('tocertification', 'totara_certification') : get_string('toprogram', 'totara_program');
            $templatehtml .= html_writer::tag('span', $str);

            // Add the add content button
            if ($updateform) {
                $mform->addElement('submit', 'addmessage', get_string('add'), array('id' => 'addmessage'));
                $template_values['%addmessage%'] = array('name' => 'addmessage', 'value' => null);
            }
            $templatehtml .= '%addmessage%' . "\n";

            $templatehtml .= html_writer::end_tag('div');
        }

        $templatehtml .= html_writer::empty_tag('br');

        // Add the save and return button
        if ($updateform) {
            $mform->addElement('submit', 'savechanges', get_string('savechanges'), array('class' => 'return-overview'));
            $template_values['%savechanges%'] = array('name' => 'savechanges', 'value' => null);
        }
        $templatehtml .= '%savechanges%' . "\n";

        // Add the cancel button
        if ($updateform) {
            $mform->addElement('cancel', 'cancel', get_string('cancel', 'totara_program'));
            $template_values['%cancel%'] = array('name' => 'cancel', 'value' => null);
        }
        $templatehtml .= '%cancel%' . "\n";

        return $templatehtml;
    }

}
