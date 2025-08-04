<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2019 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralearning.com>
 * @package totara_mobile
 */

$string['applogin'] = 'Register new mobile device';
$string['apploginopen'] = 'Press to open mobile app';
$string['authenticationpage'] = 'Mobile authentication';
$string['authtype'] = 'Type of login';
$string['authtype_choice_browser'] = 'Mobile browser';
$string['authtype_choice_native'] = 'Native';
$string['authtype_choice_webview'] = 'Webview';
$string['authtype_desc'] = 'Determines how users can login to the mobile app.<ul>
<li><strong>Native</strong>: Users will be within a mobile app experience and will be able to complete a limited set of authentication actions.</li>
<li><strong>Webview</strong>: Users will be within a browser window within the app and will be able to complete most authentication actions.</li>
<li><strong>Mobile browser</strong>: Users will be directed to their mobile browser to complete authentication actions. Users will then be prompted to return to the app without the need to authenticate again.</li>
</ul>';
$string['colour_black'] = 'Black';
$string['colour_white'] = 'White';
$string['continueinbrowser'] = 'Continue in browser';
$string['coursecompat'] = 'Mobile-friendly course';
$string['coursecompat_help'] = 'This setting sets the default "mobile-friendly course" value for new courses created. It defines whether individual courses can be accessed from within the Totara Mobile App.';
$string['coursecompatible'] = 'Mobile-friendly course';
$string['coursecompatible_help'] = 'Mark the course as suitable for learning in the mobile app. For example, if you include SCORM content make sure it opens in the mobile app before you mark it as Mobile-friendly.';
$string['device_loggedout'] = 'Successfully logged out';
$string['devices'] = 'Mobile devices';
$string['devices_logoutall'] = 'Log out from all devices';
$string['devicetable_accessed'] = 'Last access';
$string['devicetable_index'] = '#';
$string['devicetable_logout'] = 'Log out';
$string['devicetable_registered'] = 'Registered';
$string['enabletotaramobile'] = 'Enable mobile app';
$string['enabletotaramobile_desc'] = 'Enable mobile web services for the Totara Mobile App or another app requesting them.';
$string['errorgeneral'] = 'Mobile access error';
$string['errormobileunavailable'] = 'Mobile support unavailable';
$string['event_fcmtoken_received'] = 'FCM device token registered';
$string['event_fcmtoken_removed'] = 'FCM device token deleted';
$string['gotomobile'] = 'Go to mobile app';
$string['label'] = 'Label';
$string['managedevices'] = 'Manage mobile devices';
$string['mobile:use'] = 'Connect and use mobile app';
$string['mobileicon'] = 'mobile icon';
$string['pluginname'] = 'Totara Mobile';
$string['profilecategory'] = 'Mobile app';
$string['settingscategory'] = 'Mobile';
$string['settingspage'] = 'Mobile settings';
$string['switchtoapp'] = 'Would you like to switch to the mobile app?';
$string['taskpurgeexpireddevices'] = 'Purge expired mobile device registrations';
$string['taskpurgeexpiredwebviews'] = 'Purge expired mobile WebView sessions';
$string['themepage'] = 'Mobile theme';
$string['themesetting_logo'] = 'Mobile app logo';
$string['themesetting_logodesc'] = 'Upload your logo here and it will appear on the authentication (login) screen.';
$string['themesetting_primarycolour'] = 'Primary Colour';
$string['themesetting_primarycolourdesc'] = 'This sets the primary colour for the app.';
$string['themesetting_textcolour'] = 'Text colour';
$string['themesetting_textcolourdesc'] = 'This sets the text colour to either black or white over the primary colour.';
$string['timeout'] = 'Time-out period';
$string['timeout_choice_0'] = 'Never';
$string['timeout_choice_1'] = '1 day';
$string['timeout_choice_30'] = '30 days';
$string['timeout_choice_60'] = '60 days';
$string['timeout_choice_90'] = '90 days';
$string['timeout_desc'] = 'Defines the length of time before the user is required to reauthenticate into the mobile app.';
$string['urlscheme'] = 'URL scheme';
$string['urlscheme_desc'] = 'If you want to allow only your custom branded app to be opened via a browser window, then specify its URL scheme here; otherwise leave the field empty.';

/**
 * App language strings go here, see /totara/mobile/util/convert_lang_strings.php to generate
 */
$string['app:about:plugin_version'] = 'Plugin version: {{version}}';
$string['app:about:site_url'] = 'Site URL: {{url}}';
$string['app:about:version'] = 'VERSION {{version}}';
$string['app:activity_not_available:title'] = 'This activity is not available unless';
$string['app:additional_actions_modal:auth_model_description'] = 'You are required to visit a browser to complete some action(s).';
$string['app:additional_actions_modal:auth_model_go_to_browser'] = 'Go to browser';
$string['app:additional_actions_modal:auth_model_logout'] = 'Logout';
$string['app:additional_actions_modal:auth_model_title'] = 'Action required';
$string['app:browser_login:description'] = 'You need to login through a mobile browser to authenticate. Would you like us to take you there now?';
$string['app:browser_login:primary_title'] = 'Yes, go to browser';
$string['app:browser_login:tertiary_title'] = 'Cancel';
$string['app:browser_login:title'] = 'Authenticate';
$string['app:completed_learning_section:empty'] = 'You have no completed learning';
$string['app:completed_learning_section:header'] = 'Completed learning';
$string['app:content_error:description'] = 'Don\'t worry, you can still explore your downloaded content to continue your learning.';
$string['app:content_error:go_to_downloads'] = 'Go to Downloads';
$string['app:content_error:title'] = 'You\'re offline';
$string['app:course:activity:accessibility_image'] = 'Activity image';
$string['app:course:activity:accessibility_image_zoom'] = 'Activity image zoom';
$string['app:course:activity:downloads:cancel'] = 'Cancel download';
$string['app:course:activity:downloads:remove'] = 'Remove download';
$string['app:course:activity:invalid_file_data'] = 'Invalid file data';
$string['app:course:course_activity_section:not_available'] = 'Not available';
$string['app:course:course_complete:button_title'] = 'Continue learning';
$string['app:course:course_complete:description'] = 'You have successfully completed the course.';
$string['app:course:course_complete:title'] = 'Awesome!';
$string['app:course:course_complete_confirmation:description'] = 'Are you sure you want to mark this course as complete?';
$string['app:course:course_complete_confirmation:primary_button_title'] = 'Mark as complete';
$string['app:course:course_complete_confirmation:tertiary_button_title'] = 'Cancel';
$string['app:course:course_complete_confirmation:title'] = 'Confirm';
$string['app:course:course_criteria:bottom_sheet_header'] = 'Course completion criteria';
$string['app:course:course_criteria:bottom_sheet_header_unavailable'] = 'This activity is not available';
$string['app:course:course_criteria:title'] = 'Course criteria';
$string['app:course:course_details:accessibility_activity_unavailable'] = 'This activity is not available.';
$string['app:course:course_details:accessibility_auto_completion'] = 'Auto complete activity.';
$string['app:course:course_details:accessibility_expand_all'] = 'Expand all activities.';
$string['app:course:course_details:accessibility_failed'] = 'This activity completion is failed.';
$string['app:course:course_details:accessibility_manual_completion'] = 'Manual completion activity.';
$string['app:course:course_details:activities'] = 'ACTIVITIES';
$string['app:course:course_details:expand_or_collapse'] = 'Expand all';
$string['app:course:course_details:overview'] = 'OVERVIEW';
$string['app:course:course_overview:actions:complete'] = 'Mark as complete';
$string['app:course:course_overview:actions:completed'] = 'Completed';
$string['app:course:course_overview:actions:download'] = 'Download';
$string['app:course:course_overview:actions:downloaded'] = 'Downloaded';
$string['app:course:course_overview:actions:grade'] = 'Grade';
$string['app:course:course_overview:actions:progress'] = 'Progress';
$string['app:course:course_overview:actions:sync'] = 'Pending sync';
$string['app:course:course_overview:actions:sync_failed'] = 'Sync failed';
$string['app:course:course_overview:actions:syncing'] = 'Syncing';
$string['app:course:course_overview:actions:waiting'] = 'Waiting';
$string['app:course:course_overview:course_summary'] = 'Course summary';
$string['app:course:course_overview:popup:body'] = 'This course doesn\'t have any content available for offline access. You can still access it online anytime.';
$string['app:course:course_overview:popup:button_primary'] = 'OK';
$string['app:course:course_overview:popup:title'] = 'Download';
$string['app:course:download_sheet:buttons:cancel'] = 'Cancel';
$string['app:course:download_sheet:buttons:confirm'] = 'Download {{count}} activities';
$string['app:course:download_sheet:buttons:confirm_single'] = 'Download {{count}} activity';
$string['app:course:download_sheet:count'] = 'Downloadable activities: ';
$string['app:course:download_sheet:hide'] = 'Hide {{count}} activities';
$string['app:course:download_sheet:show'] = '{{count}} more activities';
$string['app:course:download_sheet:size'] = 'Total size: ';
$string['app:course:download_sheet:title'] = 'Download';
$string['app:course:grade:title'] = 'Your grade';
$string['app:course:mark_as_complete:title'] = 'Mark as complete';
$string['app:course:progress:title'] = 'Your progress';
$string['app:course:remove_activity:remove'] = 'Remove download';
$string['app:course_group:courses:compete'] = 'Successfully completed!';
$string['app:course_group:courses:current_learning_button_title'] = 'Current Learning';
$string['app:course_group:courses:unavailable_course'] = 'Unavailable course';
$string['app:course_group:courses:unavailable_sets'] = 'unavailable set';
$string['app:course_group:criteria:bottom_sheet_header'] = 'Course completion criteria';
$string['app:course_group:criteria:view_criteria'] = 'View criteria';
$string['app:course_group:overview:summary_title_certification'] = 'Certification Summary';
$string['app:course_group:overview:summary_title_program'] = 'Program Summary';
$string['app:course_group:tabs:courses'] = 'COURSES';
$string['app:course_group:tabs:overview'] = 'OVERVIEW';
$string['app:current_learning:accessibility_view_mode'] = '{{mode}} view selected.';
$string['app:current_learning:accessibility_view_mode_hint'] = 'Toggles between carousel view and list view.';
$string['app:current_learning:action_primary'] = 'Your learning';
$string['app:current_learning:carousel'] = 'Carousel';
$string['app:current_learning:due_in'] = 'Due in';
$string['app:current_learning:extend_date'] = 'Extend Date';
$string['app:current_learning:filter_sheet:buttons:clear'] = 'Clear all';
$string['app:current_learning:filter_sheet:buttons:view_results'] = 'View results';
$string['app:current_learning:filter_sheet:categories:all'] = 'All';
$string['app:current_learning:filter_sheet:categories:title'] = 'Categories';
$string['app:current_learning:filter_sheet:multi:collapse'] = 'Show less';
$string['app:current_learning:filter_sheet:multi:expand'] = 'View all ({{count}})';
$string['app:current_learning:filter_sheet:selected'] = 'Selected';
$string['app:current_learning:filter_sheet:title'] = 'Filters';
$string['app:current_learning:find_learning'] = 'Find learning';
$string['app:current_learning:list'] = 'List';
$string['app:current_learning:no_learning_message'] = 'No current learning';
$string['app:current_learning:overdue_by'] = 'Overdue by';
$string['app:current_learning:primary_info:one'] = 'You have {{count}} learning item to complete';
$string['app:current_learning:primary_info:other'] = 'You have {{count}} learning items to complete';
$string['app:current_learning:primary_info:zero'] = 'You have no learning';
$string['app:current_learning:restriction_view:description'] = 'This course is not compatible with the mobile app. Would you like to access this course in the browser?';
$string['app:current_learning:restriction_view:primary_button_title'] = 'Yes, go to browser';
$string['app:current_learning:restriction_view:tertiary_button_title'] = 'Cancel';
$string['app:current_learning:restriction_view:title'] = 'Sorry!';
$string['app:current_learning:section_title_continue_learn'] = 'Continue your learning';
$string['app:current_learning_section:header'] = 'Current learning';
$string['app:downloads:accessibility_icon'] = 'Download {{filename}}';
$string['app:downloads:confirmation:body'] = 'You have unsynced progress for this course. If you remove it now, any offline progress will be lost.';
$string['app:downloads:confirmation:body_footer'] = 'Are you sure you want to remove this course?';
$string['app:downloads:confirmation:body_heading'] = 'Warning';
$string['app:downloads:confirmation:buttons:cancel'] = 'Cancel';
$string['app:downloads:confirmation:buttons:confirm'] = 'Confirm';
$string['app:downloads:confirmation:heading'] = 'Remove download?';
$string['app:downloads:empty'] = 'No downloads yet!';
$string['app:downloads:selected'] = '{{count}} Selected';
$string['app:downloads:tap_to_launch_hint'] = 'Tap to launch the activity';
$string['app:downloads:title'] = 'Downloads';
$string['app:downloads:unrecoverable:message'] = 'Your previously downloaded content is no longer available due to the recent app update. Please redownload any learning materials you need.';
$string['app:downloads:unrecoverable:title'] = 'Downloaded contents removed';
$string['app:enrolment_options:enrol_me'] = 'Enrol me';
$string['app:enrolment_options:enrolment_key'] = 'Enrolment key (required)';
$string['app:enrolment_options:go_to_course'] = 'Go to course';
$string['app:enrolment_options:guest_access'] = 'Guest access';
$string['app:enrolment_options:loading_enrolment_data'] = 'Loading enrolment data...';
$string['app:enrolment_options:loading_enrolment_error'] = 'Error: Failed to load enrolment data. If this error persists please contact your site administrator';
$string['app:enrolment_options:password_required'] = 'Password (required)';
$string['app:enrolment_options:required'] = 'Required';
$string['app:enrolment_options:self_enrolment_title'] = 'Self enrolment ({{roleName}})';
$string['app:enrolment_options:title'] = 'Enrolment Options';
$string['app:find_learning:no_filtered_items_heading'] = 'No results found';
$string['app:find_learning:no_filtered_items_subheading'] = 'Try adjusting your search terms or removing some filters to get better results.';
$string['app:find_learning:no_learning_items'] = 'Learning catalogue is empty';
$string['app:find_learning:results:one'] = '{{count}} result';
$string['app:find_learning:results:other'] = '{{count}} results';
$string['app:find_learning:search'] = 'Search';
$string['app:find_learning:title'] = 'Find learning';
$string['app:find_learning_overview:course'] = 'Course';
$string['app:find_learning_overview:go_to_course'] = 'Go to course';
$string['app:find_learning_overview:overview'] = 'Overview';
$string['app:find_learning_overview:you_are_enrolled'] = 'You are enrolled in this course';
$string['app:find_learning_overview:you_are_not_enrolled'] = 'You can not enrol yourself in this course';
$string['app:find_learning_overview:you_can_enrol'] = 'You can enrol in this course';
$string['app:general:accessibility_go_back'] = 'Go back';
$string['app:general:back'] = 'Back';
$string['app:general:cancel'] = 'Cancel';
$string['app:general:copied_to_clipboard'] = 'Copied to clipboard';
$string['app:general:delete'] = 'Delete';
$string['app:general:done'] = "Done";
$string['app:general:enter'] = 'Enter';
$string['app:general:error'] = 'Error :';
$string['app:general:error_unknown'] = 'Something went wrong, please try again later.';
$string['app:general:loading'] = 'Loading...';
$string['app:general:no_internet'] = 'You\'re offline';
$string['app:general:not_internet_try_again'] = 'Check your internet connection and try again.';
$string['app:general:ok'] = 'Ok';
$string['app:general:refreshing'] = 'Refreshing...';
$string['app:general:timeout'] = 'Your session has expired due to inactivity. Please sign in again to continue your learning';
$string['app:general:try_again'] = 'Try again';
$string['app:general:version'] = 'Version';
$string['app:general:yes'] = 'Yes';
$string['app:general_error_feedback_modal:action_primary'] = 'Refresh';
$string['app:general_error_feedback_modal:action_tertiary'] = 'Try in browser';
$string['app:general_error_feedback_modal:description'] = 'Something went wrong';
$string['app:general_error_feedback_modal:title'] = 'Ooops!';
$string['app:incompatible_api:action_primary'] = 'Yes, try in browser';
$string['app:incompatible_api:action_tertiary'] = 'Logout';
$string['app:incompatible_api:action_tertiary_cancel'] = 'Cancel';
$string['app:incompatible_api:description'] = 'The URL is not compatible with the mobile app. Would you like to try in browser?';
$string['app:incompatible_api:title'] = 'Sorry';
$string['app:learning_items:certification'] = 'Certification';
$string['app:learning_items:course'] = 'Course';
$string['app:learning_items:engage_article'] = 'Resource';
$string['app:learning_items:playlist'] = 'Playlist';
$string['app:learning_items:program'] = 'Program';
$string['app:learning_items:resource'] = 'Resource';
$string['app:learning_items:totara_playlist'] = 'Playlist';
$string['app:native_login:auth_general_error:action_primary'] = 'OK';
$string['app:native_login:auth_general_error:description'] = 'Something went wrong';
$string['app:native_login:auth_general_error:title'] = 'Ooops!';
$string['app:native_login:error_unauthorized'] = 'Oops! Your username or password is incorrect.';
$string['app:native_login:forgot_username_password'] = 'Forgot your username or password?';
$string['app:native_login:header_title'] = 'Login';
$string['app:native_login:login_information'] = 'Enter your site username and password.';
$string['app:native_login:password_text_placeholder'] = 'Password';
$string['app:native_login:username_text_placeholder'] = 'Username';
$string['app:native_login:validation:enter_valid_password'] = 'Enter a valid password';
$string['app:native_login:validation:enter_valid_username'] = 'Enter a valid username';
$string['app:no_internet_alert:go_back'] = 'OK';
$string['app:no_internet_alert:message'] = 'To continue, you\'ll need to go online';
$string['app:no_internet_alert:title'] = 'You are offline';
$string['app:notifications:empty'] = 'No notifications yet!';
$string['app:notifications:mark_as_read'] = 'Mark as read';
$string['app:notifications:selected'] = '{{count}} Selected';
$string['app:notifications:tap_to_launch_hint'] = 'Tap to open the notification details';
$string['app:notifications:title'] = 'Notifications';
$string['app:offline:no_storage:buttons:primary'] = 'Try again';
$string['app:offline:no_storage:buttons:secondary'] = 'Cancel download';
$string['app:offline:no_storage:progress'] = 'You\'re running low on storage and your course downloads have been paused. Please free up space to continue downloading';
$string['app:offline:no_storage:title'] = 'Not enough storage';
$string['app:offline_download:activity_removed'] = 'Activity removed';
$string['app:offline_download:cancel_download'] = 'Cancel';
$string['app:offline_download:download_cancelled'] = 'Download cancelled';
$string['app:offline_download:download_complete'] = 'Download complete';
$string['app:offline_download:downloading'] = 'Downloading...';
$string['app:offline_download:file_not_supported'] = 'The file you\'re trying to open is not currently supported on the app';
$string['app:offline_download:not_supported'] = 'The site you\'re connected to doesn\'t support offline completion';
$string['app:offline_download:remove_course'] = 'Course removed';
$string['app:offline_error:action'] = 'Go to Downloads';
$string['app:offline_error:description'] = 'Don\'t worry, you can still explore your downloaded content to continue your learning.';
$string['app:offline_error:title'] = 'You\'re offline';
$string['app:offline_sync:sync_cancelled:title'] = 'Sync cancelled';
$string['app:offline_sync:sync_complete:title'] = 'Syncing completed';
$string['app:offline_sync:sync_failed:body'] = 'Something went wrong while syncing. Please try again later.';
$string['app:offline_sync:sync_failed:buttons:primary'] = 'Retry';
$string['app:offline_sync:sync_failed:buttons:secondary'] = 'Cancel';
$string['app:offline_sync:sync_failed:title'] = 'Sync failed';
$string['app:offline_sync:sync_required:body'] = 'Your offline progress hasn\'t been synced';
$string['app:offline_sync:sync_required:buttons:primary'] = 'Sync now';
$string['app:offline_sync:sync_required:buttons:secondary'] = 'Later';
$string['app:offline_sync:sync_required:title'] = 'Sync pending';
$string['app:offline_sync:sync_started:title'] = 'Syncing courses';
$string['app:offline_toast:offline_message'] = 'No internet connection';
$string['app:offline_toast:online_message'] = 'You\'re back online';
$string['app:scorm:ago'] = 'ago';
$string['app:scorm:attempts:attempt'] = 'Attempt';
$string['app:scorm:attempts:failed'] = 'Failed';
$string['app:scorm:attempts:passed'] = 'Passed';
$string['app:scorm:attempts:title'] = 'All attempts grades';
$string['app:scorm:confirmation:cancel'] = 'No';
$string['app:scorm:confirmation:message'] = 'You want to exit the activity.';
$string['app:scorm:confirmation:ok'] = 'Yes';
$string['app:scorm:confirmation:title'] = 'Are you sure?';
$string['app:scorm:data_sync_error'] = 'We could not sync your data. We will try again later.';
$string['app:scorm:feedback:completed_attempt'] = 'You have completed an attempt';
$string['app:scorm:feedback:completed_attempt_with_grade'] = 'You have completed an attempt with a grade';
$string['app:scorm:feedback:grade_title'] = 'Grade {{score}}';
$string['app:scorm:grading_method:0'] = 'Highest attempt';
$string['app:scorm:grading_method:1'] = 'Average attempts';
$string['app:scorm:grading_method:2'] = 'First attempt';
$string['app:scorm:grading_method:3'] = 'Last completed attempt';
$string['app:scorm:info_completed_attempts'] = 'You have reached the maximum number of attempts';
$string['app:scorm:info_upcoming_activity'] = 'Sorry, this activity is not available until';
$string['app:scorm:last_synced'] = 'Last synced';
$string['app:scorm:summary:attempt:completed_attempts'] = 'Total attempts done';
$string['app:scorm:summary:attempt:title'] = 'Attempt details';
$string['app:scorm:summary:attempt:total_attempts'] = 'Total attempts allowed';
$string['app:scorm:summary:attempt:unlimited'] = 'Unlimited';
$string['app:scorm:summary:grade:in_attempt'] = 'In attempt';
$string['app:scorm:summary:grade:method'] = 'Grading method';
$string['app:scorm:summary:grade:reported'] = 'Grade reported';
$string['app:scorm:summary:grade:title'] = 'Grade details';
$string['app:scorm:summary:grade:view_all'] = 'View all';
$string['app:scorm:summary:last_attempt'] = 'Last attempt';
$string['app:scorm:summary:new_attempt'] = 'New attempt';
$string['app:scorm:summary:summary'] = 'Summary';
$string['app:server_not_reachable:go_back'] = 'OK';
$string['app:server_not_reachable:message'] = "It looks like there’s a connectivity issue or the mobile feature may not be enabled on this site. Please check your network or contact your administrator for assistance.";
$string['app:server_not_reachable:title'] = 'Ooops!';
$string['app:site_url:auth_invalid_site:action_primary'] = 'OK';
$string['app:site_url:auth_invalid_site:description'] = 'The URL is not compatible. Please check the URL and try again.';
$string['app:site_url:auth_invalid_site:title'] = 'Sorry';
$string['app:site_url:title'] = 'Get started.';
$string['app:site_url:url_information'] = 'Enter your organisation\'s URL';
$string['app:site_url:url_text_placeholder'] = 'URL';
$string['app:site_url:validation:enter_valid_url'] = 'Enter a valid site address';
$string['app:user_profile:about'] = 'About';
$string['app:user_profile:accessibility_image'] = 'Profile image';
$string['app:user_profile:login_as'] = 'Logged in as: {{username}}';
$string['app:user_profile:logout:button_text'] = 'Logout';
$string['app:user_profile:logout:message_with_warn'] = 'Are you sure you want to log out of this account? If you log out, you will lose your downloaded files.';
$string['app:user_profile:logout:title'] = 'Log Out';
$string['app:user_profile:manage_section'] = 'Manage';
$string['app:user_profile:setting_cell'] = 'Settings';
$string['app:user_profile:title'] = 'Profile';

/**
 * End of app language strings.
 */

/**
 * Deprecated in 17.0
 */
$string['app:course_group:courses:unavailable_courses'] = 'unavailable course';

/**
 * Deprecated in 14.0
 */
$string['app:course:course_details:badge_title'] = 'Course';
$string['app:course_group:details:badge_title_certification'] = 'Certification';
$string['app:course_group:details:badge_title_program'] = 'Program';
$string['app:user_profile:logout:message'] = 'Are you sure you want to logout?';
