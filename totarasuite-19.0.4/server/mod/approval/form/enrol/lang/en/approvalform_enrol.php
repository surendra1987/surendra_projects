<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

$string['course_link_label'] = 'Requested course';
$string['course_name_placeholder'] = 'Course name';
$string['form_title'] = 'Course enrolment request - {$a}';
$string['pluginname'] = 'Course enrolment approval form';

/**
 * Enrolment request approved for applicant.
 */
$string['notification:enrolment_request_approved_for_applicant_body'] = 'You have been approved for enrolment on course [application:title].

The completed request is available here: [application:title_linked]';
$string['notification:enrolment_request_approved_for_applicant_subject'] = 'Enrolment approved – [application:title]';
$string['notification:enrolment_request_approved_for_applicant_title'] = 'Enrolment request approved (for applicant)';

/**
 * Enrolment comment added for applicant.
 */
$string['notification:enrolment_comment_added_for_applicant_body'] = 'A comment has been added to your pending request to enrol on course [application:title]:

[comment:content_text]

The request and comment(s) are available here: [application:title_linked]';
$string['notification:enrolment_comment_added_for_applicant_subject'] = 'Enrolment approval – comment added to your request for [application:title]';
$string['notification:enrolment_comment_added_for_applicant_title'] = 'Comment added to enrolment request (for applicant)';

/**
 * Enrolment comment added for others.
 */
$string['notification:enrolment_comment_added_for_others_body'] = 'A comment has been added to [applicant:full_name]\'s pending request to enrol on course [application:title]:

[comment:content_text]

The request and comment(s) are available here: [application:title_linked]';
$string['notification:enrolment_comment_added_for_others_subject'] = 'Enrolment approval – comment added to [applicant:full_name]\'s request for [application:title]';
$string['notification:enrolment_comment_added_for_others_title'] = 'Comment added to enrolment request (for others)';

/**
 * Enrolment request approved for others.
 */
$string['notification:enrolment_request_approved_for_others_body'] = '[applicant:full_name] has been approved for enrolment on course [application:title].

The completed request is available here: [application:title_linked]';
$string['notification:enrolment_request_approved_for_others_subject'] = 'Enrolment approved – [applicant:full_name] approved for [application:title]';
$string['notification:enrolment_request_approved_for_others_title'] = 'Enrolment request approved (for others)';

/**
 * Enrolment request awaiting approval.
 */
$string['notification:enrolment_level_started_for_level_approvers_body'] = '[applicant:full_name] is requesting enrolment on course [application:title], and the request is pending your approval.

Please view and approve or reject the request here: [application:title_linked]';
$string['notification:enrolment_level_started_for_level_approvers_subject'] = 'Approval requested – [applicant:full_name] to enrol on [application:title]';
$string['notification:enrolment_level_started_for_level_approvers_title'] = 'Enrolment request awaiting approval';

/**
 * Enrolment request denied for applicant.
 */
$string['notification:enrolment_request_denied_for_applicant_body'] = 'You have been denied enrolment on course [application:title].

The request is available here: [application:title_linked]';
$string['notification:enrolment_request_denied_for_applicant_subject'] = 'Enrolment approval denied – [application:title]';
$string['notification:enrolment_request_denied_for_applicant_title'] = 'Enrolment request denied (for applicant)';

/**
 * Enrolment request denied for others.
 */
$string['notification:enrolment_request_denied_for_others_body'] = '[applicant:full_name] has been denied enrolment on course [application:title].

The request is available here: [application:title_linked]';
$string['notification:enrolment_request_denied_for_others_subject'] = 'Enrolment approval denied – [applicant:full_name] for [application:title]';
$string['notification:enrolment_request_denied_for_others_title'] = 'Enrolment request denied (for others)';

/**
 * Enrolment request submitted for applicant.
 */
$string['notification:enrolment_request_submitted_for_applicant_body'] = 'You requested enrolment on course [application:title].

The request is available here: [application:title_linked]';
$string['notification:enrolment_request_submitted_for_applicant_subject'] = 'Enrolment approval requested – [application:title]';
$string['notification:enrolment_request_submitted_for_applicant_title'] = 'Enrolment request submitted (for applicant)';

/**
 * Enrolment request submitted for manager.
 */
$string['notification:enrolment_request_submitted_for_manager_body'] = '[applicant:full_name] is requesting enrolment on course [application:title].

The request is available here: [application:title_linked]';
$string['notification:enrolment_request_submitted_for_manager_subject'] = 'Enrolment approval – [applicant:full_name] requesting [application:title]';
$string['notification:enrolment_request_submitted_for_manager_title'] = 'Enrolment request submitted (for others)';

/**
 * Enrolment request withdrawn for applicant.
 */
$string['notification:enrolment_request_withdrawn_for_applicant_body'] = 'You have withdrawn your request to enrol on course [application:title].

The request is available here for your records: [application:title_linked]';
$string['notification:enrolment_request_withdrawn_for_applicant_subject'] = 'Your request to enrol on course [application:title] is withdrawn';
$string['notification:enrolment_request_withdrawn_for_applicant_title'] = 'Enrolment request withdrawn before submission';
$string['rationale_label'] = 'Reason for enrolling in this course';
$string['workflow_verification_warning:no_approval_transition'] = "'Approved' end stage is not the target of any 'On approved' transitions";
$string['workflow_verification_warning:no_approved_stage'] = "Missing end stage named 'Approved'";
$string['workflow_verification_warning:no_archived_stage'] = "Missing end stage named 'Archived'";
