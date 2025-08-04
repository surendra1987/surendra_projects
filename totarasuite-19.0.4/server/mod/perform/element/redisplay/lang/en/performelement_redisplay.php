<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @note Automatically cleaned: 2024-09-24
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 */

$string['activity_instance_from_current'] = 'A question from a previous section in the current activity instance';
$string['activity_instance_from_current_desc'] = 'Response will redisplay in the current instance of this activity ';
$string['activity_instance_from_repeating'] = 'A question in the preceding instance of the current activity (only works for repeating activities)';
$string['activity_instance_from_repeating_desc'] = 'Response will redisplay in the next instance of this activity';
$string['activity_instance_from_value'] = 'Redisplay response from';
$string['activity_list_active'] = 'Active';
$string['activity_list_current'] = 'Current';
$string['activity_list_draft'] = 'Draft';
$string['activity_name_with_status'] = '{$a->activity_name} ({$a->activity_status})';
$string['anonymous_responses'] = 'Anonymous responses';
$string['current_activity'] = 'Current activity';
$string['instruction_text'] = 'Instruction text';
$string['name'] = 'Response redisplay';
$string['no_available_previous_sections'] = 'No previous sections available';
$string['no_available_questions'] = 'No available questions to select';
$string['no_responding_relationships'] = '{No responding relationships added yet}';
$string['pluginname'] = 'Redisplay element';
$string['redisplay_no_participants'] = 'Response redisplay cannot be shown, because there is no participation associated with the activity "{$a->activity_name} ({$a->subject_instance_date})".';
$string['redisplay_no_response_for_same_subject_instance'] = 'Response redisplay cannot be shown because there is no response for the referenced question.';
$string['redisplay_no_subject_instance_for_another_activity'] = 'Response redisplay cannot be shown, because there is no participation associated with the activity "{$a}".';
$string['redisplay_no_subject_instance_for_same_activity'] = 'Response redisplay to the following question cannot be shown, because there is no previous participation associated with the activity "{$a}".';
$string['redisplayed_element_admin_preview'] = 'Response redisplay from "{$a->activity_name} ({date source subject instance created})" – responses last updated {date last modified}.';
$string['redisplayed_summary'] = 'Response redisplay from "{$a->activity_name} ({$a->date_created})" – responses last updated {$a->date_updated}.';
$string['responses_from_anonymous_relationships'] = '{Anonymous responses}';
$string['responses_from_relationships'] = '{Responses from: {$a->relationships}}';
$string['select_activity'] = 'Select activity...';
$string['select_question_element'] = 'Select question element...';
$string['source_activity_missing'] = 'The activity containing the question that is being referenced by this response redisplay no longer exists.';
$string['source_activity_value'] = 'Source activity';
$string['source_activity_value_help'] = 'Select the activity which contains the question (and responses) you want to redisplay. The subject of this activity must also be assigned to the source activity in order for any responses to be redisplayed.';
$string['source_element_option'] = '{$a->element_title} ({$a->element_plugin_name})';
$string['source_question_element_value'] = 'Source question element';
$string['source_question_element_value_help'] = 'Only question elements can be selected for response redisplay. Responses from all responding participants will be displayed, regardless of who the responding participants are in the current activity, and the viewing permissions of the source activity. Where the source activity is anonymised, anonymity of the responses will be preserved when redisplayed.';
