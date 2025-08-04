<?php
/**
 * This file is part of Totara Core
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
 * @note Automatically cleaned: 2024-09-24
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

$string['a11y_filter_goals'] = 'Filter goals';
$string['a11y_goal_additional_details'] = 'Additional details';
$string['a11y_goal_comment_label'] = 'Goal comments:';
$string['a11y_goal_current_status'] = 'Goal current status:';
$string['a11y_goal_date_range'] = 'Date range:';
$string['a11y_goal_overall_progress'] = 'Goal overall progress';
$string['a11y_goal_task_actions_delete'] = 'Delete task';
$string['a11y_goal_task_actions_edit'] = 'Edit task';
$string['a11y_goal_task_attached_resource'] = 'Attached resource: ';
$string['a11y_goal_task_resource_remove'] = 'Remove';
$string['a11y_goal_task_resource_type'] = 'Resource type: ';
$string['a11y_goal_tasks_completed_label'] = 'Goal tasks completed:';
$string['a11y_goal_tasks_to_be_completed'] = 'Tasks to be completed';
$string['a11y_goals_filter_search'] = 'Search by goal';
$string['cachedef_goal_task_resource_type'] = 'Goal task resource type cache';
$string['count_of_goal_comments_summary'] = 'Count of goal comments';
$string['count_of_goal_tasks_summary'] = 'Count of goal tasks (total and completed). Counts of goals with at least one task, at least one task resource. Total count of task resources and by resource type.';
$string['count_of_goal_users'] = 'Count of users with and without goals. Maximum number of goals for a single user.';
$string['count_of_goals_by_status_summary'] = 'Count of goals by status, total count of goals and count of recently changed goals.';
$string['enable_perform_goal_legacy'] = 'Legacy goals';
$string['enable_perform_goal_none'] = 'None';
$string['enable_perform_goal_totara'] = 'Totara goals';
$string['enable_perform_goal_totara_transition_mode'] = 'Totara goals transition mode';
$string['enable_perform_goals'] = 'Enable goals';
$string['enable_perform_goals_description'] = '
* None: Goals are not available for use.
* Legacy goals: Legacy goals will still be accessible, but users will not be able to create new goals within performance activities.
* Totara goals: Users can create goals within performance activities, but will not have access to legacy goals.
* Totara goals transition mode: Legacy goals will still be accessible, but users will not be able to create or edit legacy personal goals.';
$string['event_goal_category_activated'] = 'Goal category activated';
$string['event_goal_category_created'] = 'Goal category created';
$string['event_goal_category_deactivated'] = 'Goal category deactivated';
$string['event_goal_created'] = 'Personal goal created';
$string['event_goal_deleted'] = 'Personal goal deleted';
$string['event_goal_details_updated'] = 'Personal goal details updated';
$string['event_goal_status_updated'] = 'Personal goal status updated';
$string['event_goal_task_created'] = 'Goal task created';
$string['event_goal_task_deleted'] = 'Goal task deleted';
$string['event_goal_task_progress_changed'] = 'Goal task progress changed';
$string['event_goal_task_updated'] = 'Goal task updated';
$string['event_goal_updated'] = 'Personal goal updated';
$string['event_goal_viewed'] = 'Personal goal viewed';
$string['event_perform_goal_perform_status_changed'] = 'Goal status updated during performance activity review';
$string['filtered_goals_count'] = '{$a->total} goal';
$string['filtered_goals_count_plural'] = '{$a->total} goals';
$string['filtered_goals_empty'] = 'No matching items found.';
$string['goal:manage_comment_notifications'] = 'Manage comment notifications';
$string['goal:manageownpersonalgoals'] = 'Create/edit/delete own goal';
$string['goal:managepersonalgoals'] = 'Create/edit/delete a goal for another user';
$string['goal:setownpersonalgoalprogress'] = 'Set current value and/or status of own goal';
$string['goal:setpersonalgoalprogress'] = 'Set current value and/or status of a goal for another user';
$string['goal:viewownpersonalgoals'] = 'View own goals';
$string['goal:viewpersonalgoals'] = 'View goals for another user';
$string['goal_adder_filter_name'] = 'Search';
$string['goal_adder_filter_title'] = 'Filter goals';
$string['goal_adder_label_name'] = 'Goal';
$string['goal_adder_no_items'] = 'No items to display';
$string['goal_adder_title'] = 'Select goals';
$string['goal_assignment_type_label'] = 'Assignment via:';
$string['goal_awaiting_progress_update'] = 'Awaiting your update on goal progress';
$string['goal_cannot_be_displayed'] = 'You don\'t have permission to view this goal, or the goal has been deleted and can\'t be displayed here.';
$string['goal_comments'] = 'Comments';
$string['goal_create'] = 'Create goal';
$string['goal_create_modal_create_and_close'] = 'Create and close';
$string['goal_create_modal_create_and_view'] = 'Create and view';
$string['goal_create_modal_error'] = 'The goal could not be created because something went wrong. Please try again later.';
$string['goal_create_modal_title'] = 'Create goal';
$string['goal_created_success'] = 'The goal was successfully created.';
$string['goal_date_range'] = '{$a->start} - {$a->target}';
$string['goal_delete_modal_error'] = 'The goal could not be deleted because something went wrong. Please try again later.';
$string['goal_delete_modal_message_1'] = 'This goal will be deleted permanently. The deleted goal cannot be recovered.';
$string['goal_delete_modal_message_2'] = 'Are you sure you would like to delete this goal?';
$string['goal_delete_modal_success'] = 'The goal was successfully deleted.';
$string['goal_delete_modal_title'] = 'Delete goal';
$string['goal_details_modal_title'] = 'Goal';
$string['goal_due_date'] = 'Due';
$string['goal_due_date_label'] = 'Due:';
$string['goal_edit_modal_error'] = 'The goal could not be edited because something went wrong. Please try again later.';
$string['goal_edit_modal_title'] = 'Edit goal';
$string['goal_enable_goal_progress_change'] = 'Allow updating progress in activity';
$string['goal_enable_goal_progress_change_help'] = 'Only one goal progress change will be submitted to the goal. If there are multiple people in the participant role, the first change submitted will be applied.';
$string['goal_enable_goal_progress_change_participant'] = 'Who can update goal progress?';
$string['goal_example_goal_description'] = 'This is an example of how a goal will display after a participant has selected it.';
$string['goal_example_goal_status'] = 'Goal status';
$string['goal_example_goal_title'] = 'Goals example';
$string['goal_example_progress_message'] = 'Current: 0 / 100 (Not started)';
$string['goal_form_description_message'] = 'Describe how this goal is SMART: specific, measurable, achievable, relevant, and time-bound.';
$string['goal_form_label_description'] = 'Description';
$string['goal_form_label_due_date'] = 'Due date';
$string['goal_form_label_measurement'] = 'Measurement';
$string['goal_form_label_measurement_helpmsg'] = 'A measure of progress towards achieving this goal.';
$string['goal_form_label_name'] = 'Goal';
$string['goal_form_label_start_date'] = 'Start date';
$string['goal_form_label_target_value'] = 'Target';
$string['goal_form_label_timeframe'] = 'Time frame';
$string['goal_form_label_timeframe_due'] = 'Due';
$string['goal_form_label_timeframe_start'] = 'Start';
$string['goal_form_start_date_error'] = 'Start date cannot be after the due date';
$string['goal_form_update'] = 'Update';
$string['goal_form_update_label_progress'] = 'Progress';
$string['goal_form_update_label_status'] = 'Status';
$string['goal_form_update_modal_error'] = 'The goal could not be updated because something went wrong. Please try again later.';
$string['goal_form_update_progress_target'] = 'of {$a} Target';
$string['goal_generic_display_name'] = 'goal';
$string['goal_last_updated'] = 'Last updated {$a}';
$string['goal_manage_actions'] = 'Manage goal actions';
$string['goal_manage_delete'] = 'Delete';
$string['goal_manage_edit'] = 'Edit';
$string['goal_plugin_name_label'] = 'Goal type:';
$string['goal_progress'] = '{$a->current} / {$a->target}';
$string['goal_progress_current'] = 'Current: {$a->current} / {$a->target} ({$a->status})';
$string['goal_progress_label'] = 'Progress';
$string['goal_progress_of_target'] = '<b>{$a->current}</b> of {$a->target} Target';
$string['goal_progress_update_no_permission'] = 'You don\'t have permission to update progress for this goal.';
$string['goal_progress_updated'] = 'The goal progress was updated.';
$string['goal_progress_updated_by'] = 'Updated by: {$a->name} ({$a->subject}) on {$a->date}';
$string['goal_progress_updated_by_unknown'] = 'Updated by: Unknown';
$string['goal_progression_update'] = 'Goal progression update';
$string['goal_start_date'] = 'Start';
$string['goal_start_date_label'] = 'Start:';
$string['goal_status_label'] = 'Status';
$string['goal_status_response_subject'] = '{$a} response';
$string['goal_task_actions'] = 'Actions for {$a}';
$string['goal_task_actions_delete'] = 'Delete';
$string['goal_task_actions_edit'] = 'Edit';
$string['goal_task_add_course'] = 'Add course';
$string['goal_task_add_error'] = 'The goal task could not be created because something went wrong. Please try again later.';
$string['goal_task_add_prompt'] = 'Add new task or add course';
$string['goal_task_cancel'] = 'Cancel';
$string['goal_task_completion_error'] = 'The goal task completion could not be changed because something went wrong. Please try again later.';
$string['goal_task_course_no_access'] = 'You don\'t have permission to view this course.';
$string['goal_task_course_not_available'] = 'This course is no longer available.';
$string['goal_task_course_progress'] = '{$a}%';
$string['goal_task_create_error_can_manage'] = 'Sorry, but you do not currently have permissions to do that (add a goal task in this context).';
$string['goal_task_delete_error'] = 'The goal task could not be deleted because something went wrong. Please try again later.';
$string['goal_task_edit_prompt'] = 'Enter task';
$string['goal_task_error_no_resource'] = 'Both resource type and resource id must be specified.';
$string['goal_task_error_no_resource_exists'] = 'Resource with specified id does not exist.';
$string['goal_task_error_not_empty'] = 'A goal task can not be empty.';
$string['goal_task_save'] = 'Save';
$string['goal_task_update_error'] = 'The goal task could not be updated because something went wrong. Please try again later.';
$string['goal_task_update_error_can_manage'] = 'Sorry, but you do not currently have permissions to do that (update a goal task in this context).';
$string['goal_tasks'] = 'Tasks';
$string['goal_tasks_completed'] = '{$a->completed}/{$a->total} completed';
$string['goal_tasks_completed_count'] = '{$a->completed}/{$a->total}';
$string['goal_update_progress'] = 'Update progress';
$string['goal_update_progress_modal_message'] = 'After submitting this update, you cannot remove the goal or change its progress within this activity.';
$string['goal_update_progress_modal_title'] = 'Update progress';
$string['goal_updated_at_label'] = 'Updated:';
$string['goal_view_personal_goals'] = 'View goals for another user';
$string['goals_error_sort_option_invalid'] = 'Invalid sort option \'{$a}\'.';
$string['goals_error_sort_option_invalid_with_cursor'] = 'The sorting option \'{$a}\' is invalid when it is combined with a cursor.';
$string['goals_filter_search'] = 'Search';
$string['goals_filter_status'] = 'Status';
$string['goals_overview_menu'] = 'Goals';
$string['goals_overview_title'] = 'Your goals';
$string['goals_overview_title_for_user'] = '{$a}\'s goals';
$string['goals_title'] = 'Goals';
$string['no_goals'] = 'You don\'t currently have any goals';
$string['no_goals_for_user'] = '{$a} does not currently have any goals';
$string['notification_placeholder_commenter'] = 'Goal commenter {$a}';
$string['notification_placeholder_commenters'] = 'Goal commenters {$a}';
$string['notification_placeholder_goal'] = 'Goal {$a}';
$string['notification_placeholder_goal_name'] = 'Name';
$string['notification_placeholder_goal_name_linked'] = 'Name (with link)';
$string['notification_placeholder_goal_subject'] = 'Goal subject {$a}';
$string['notification_recipient_commenters'] = 'Goal Commenters';
$string['notification_recipient_goal_subject'] = 'Goal Subject';
$string['notification_resolver_comment_created_title'] = 'Comment added';
$string['notification_resolver_comment_updated_title'] = 'Comment updated';
$string['overview_last_update_descr_goal_updated_at'] = 'Goal was updated';
$string['overview_last_update_descr_status'] = 'Goal status was updated to \'{$a}\'';
$string['overview_last_update_descr_status_and_value'] = 'Goal progress was updated to \'{$a->status}\' ({$a->value}/{$a->target})';
$string['overview_last_update_descr_value'] = 'Goal progress was updated to {$a}';
$string['perform_goal'] = 'Totara goals';
$string['perform_review_goal_missing'] = 'The goal no longer exists';
$string['perform_review_goal_status_changer_you'] = 'You';
$string['personal_goals_heading'] = 'Your personal goals';
$string['personal_goals_heading_for_user'] = '{$a}\'s personal goals';
$string['pluginname'] = 'Totara goals';
$string['remove_condition_failed:already_rated'] = 'Cannot remove review item; already rated';
$string['review_add_goals'] = 'Add goals';
$string['review_awaiting_goal_selection'] = 'Awaiting goal selection from a {$a}.';
$string['review_remove_goal'] = 'Remove goal';
$string['show_less_details'] = 'Less details';
$string['show_more_details'] = 'More details';
$string['sort_option_label_created_at'] = 'Creation date';
$string['sort_option_label_least_complete'] = 'Least complete';
$string['sort_option_label_most_complete'] = 'Most complete';
$string['sort_option_label_target_date'] = 'Due date';
$string['status_cancelled'] = 'Cancelled';
$string['status_completed'] = 'Completed';
$string['status_in_progress'] = 'In progress';
$string['status_not_started'] = 'Not started';
$string['target_type_date'] = 'Date';
$string['updated_as_of'] = 'As of {$a}';
$string['userdataitemexport_user_goals'] = 'User goals';
$string['userdataitemperform_goal_status_change_other'] = 'Performance activity status change on other users\' goals';
$string['userdataitemperform_goal_status_change_other_help'] = "When purging this item, the status change will be anonymised (changer's name replaced with text indicating it has been removed). The change itself (status, role in which change was made) will remain.";
$string['userdataitemperform_goal_status_change_self'] = 'Performance activity status change on own goals';
$string['userdataitempurge_user_goals'] = 'User goals';
