<?php
// This file is part of the Local plans plugin
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
 * This plugin sends users a plans message after logging in
 * and notify a moderator a new user has been added
 * it has a settings page that allow you to configure the messages
 * send.
 *
 * @note Automatically cleaned: 2024-09-24
 * @package    bi
 * @subpackage intellidata
 * @copyright  2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Actions';
$string['ajaxfrequency'] = 'Tracking frequency';
$string['ajaxfrequency_desc'] = 'Session storing frequency (in seconds) via AJAX. If set to zero, AJAX is disabled.';
$string['cleaner_duration'] = 'Cleaner duration';
$string['cleaner_task'] = 'Cleaner task';
$string['clientidentifier'] = 'API identifier';
$string['created'] = 'Created';
$string['database'] = 'Database';
$string['datatype'] = 'Data type';
$string['datatype_activities'] = 'Activities';
$string['datatype_activitycompletions'] = 'Activity completions';
$string['datatype_assignmentsubmissions'] = 'Assignment submissions';
$string['datatype_categories'] = 'Categories';
$string['datatype_cohortmembers'] = 'Cohort members';
$string['datatype_cohorts'] = 'Cohorts';
$string['datatype_coursecompletions'] = 'Course completions';
$string['datatype_courses'] = 'Courses';
$string['datatype_enrolments'] = 'Enrolments';
$string['datatype_forumdiscussions'] = 'Forum discussions';
$string['datatype_forumposts'] = 'Forum posts';
$string['datatype_gradecategories'] = 'Grade categories';
$string['datatype_intelliboarddetails'] = 'IntelliBoard details';
$string['datatype_intelliboardlogs'] = 'IntelliBoard logs';
$string['datatype_intelliboardtotals'] = 'IntelliBoard totals';
$string['datatype_intelliboardtracking'] = 'IntelliBoard tracking';
$string['datatype_migration_activities'] = 'Activities migration';
$string['datatype_migration_activitycompletions'] = 'Activity completions migration';
$string['datatype_migration_assignmentsubmissions'] = 'Assignment submissions migration';
$string['datatype_migration_categories'] = 'Categories migration';
$string['datatype_migration_cohortmembers'] = 'Cohort members migration';
$string['datatype_migration_cohorts'] = 'Cohorts migration';
$string['datatype_migration_coursecompletions'] = 'Course completions migration';
$string['datatype_migration_courses'] = 'Courses migration';
$string['datatype_migration_enrolments'] = 'Enrolments migration';
$string['datatype_migration_forumdiscussions'] = 'Forum discussions migration';
$string['datatype_migration_forumposts'] = 'Forum posts migration';
$string['datatype_migration_gradecategories'] = 'Grade categories migration';
$string['datatype_migration_gradeitems'] = 'Grade items migration';
$string['datatype_migration_intelliboarddetails'] = 'IntelliBoard details migration';
$string['datatype_migration_intelliboardlogs'] = 'IntelliBoard logs migration';
$string['datatype_migration_intelliboardtotals'] = 'IntelliBoard totals migration';
$string['datatype_migration_intelliboardtracking'] = 'IntelliBoard tracking migration';
$string['datatype_migration_modules'] = 'Modules migration';
$string['datatype_migration_participation'] = 'User participation migration';
$string['datatype_migration_quizattempts'] = 'Quiz attempts migration';
$string['datatype_migration_quizquestionanswers'] = 'Quiz question answers migration';
$string['datatype_migration_quizquestionattempts'] = 'Quiz question attempts migration';
$string['datatype_migration_quizquestionattemptsteps'] = 'Quiz question answers steps migration';
$string['datatype_migration_quizquestionattemptstepsdata'] = 'Quiz question answers steps data migration';
$string['datatype_migration_quizquestionrelations'] = 'Quiz question relations migration';
$string['datatype_migration_quizquestions'] = 'Quiz questions migration';
$string['datatype_migration_roleassignments'] = 'Role assignments migration';
$string['datatype_migration_roles'] = 'Roles migration';
$string['datatype_migration_tracking'] = 'User tracking migration';
$string['datatype_migration_trackinglog'] = 'User tracking log migration';
$string['datatype_migration_trackinglogdetail'] = 'User tracking lo details migration';
$string['datatype_migration_usergrades'] = 'Users grades migration';
$string['datatype_migration_userinfocategories'] = 'User info categories migration';
$string['datatype_migration_userinfodatas'] = 'User info data migration';
$string['datatype_migration_userinfofields'] = 'User info fields migration';
$string['datatype_migration_userlogins'] = 'User logins migration';
$string['datatype_migration_users'] = 'Users migration';
$string['datatype_modules'] = 'Modules';
$string['datatype_org'] = 'Organisation';
$string['datatype_orgtype'] = 'Organisation type';
$string['datatype_orgtypeinfofield'] = 'Organisation type info field';
$string['datatype_participation'] = 'User participation migration';
$string['datatype_pos'] = 'Position';
$string['datatype_posassignment'] = 'Position assignment';
$string['datatype_quizattempts'] = 'Quiz attempts';
$string['datatype_quizquestionanswers'] = 'Quiz question answers';
$string['datatype_quizquestionattempts'] = 'Quiz question attempts';
$string['datatype_quizquestionattemptsteps'] = 'Quiz question answers steps';
$string['datatype_quizquestionattemptstepsdata'] = 'Quiz question answers steps data';
$string['datatype_quizquestionrelations'] = 'Quiz question relations';
$string['datatype_quizquestions'] = 'Quiz questions';
$string['datatype_roleassignments'] = 'Role assignments';
$string['datatype_roles'] = 'Roles';
$string['datatype_tracking'] = 'User tracking';
$string['datatype_trackinglog'] = 'User tracking log by day';
$string['datatype_trackinglogdetail'] = 'User tracking log by hour';
$string['datatype_usergrades'] = 'Users grades';
$string['datatype_userinfocategories'] = 'User info categories';
$string['datatype_userinfodatas'] = 'User info data';
$string['datatype_userinfofields'] = 'User info fields';
$string['datatype_userlogins'] = 'User logins';
$string['datatype_users'] = 'Users';
$string['defaultlayout'] = 'Theme layout to display';
$string['deletefileconfirmation'] = 'Are you sure you want to delete this file?';
$string['enabled'] = 'Enabled';
$string['enabledtracking'] = 'Enabled tracking';
$string['encryptionkey'] = 'API key';
$string['export_data_task'] = 'Export data task';
$string['export_files_task'] = 'Export files task';
$string['exportdataformat'] = 'Export data format';
$string['exportdataformat_desc'] = 'Setting for migration files data format';
$string['exportfiles'] = 'Export files';
$string['exportfilesduringmigration'] = 'Export files during migration';
$string['exportfilesduringmigration_desc'] = 'If enabled, IntelliData will export file to Totara data after migration processed';
$string['exportlogs'] = 'Export logs';
$string['exportrecordslimit'] = 'Export process limit';
$string['exportrecordslimit_desc'] = 'The number of records that will be processed at once during export process.';
$string['failed_remove_file'] = 'Failed to remove file "{$a}"';
$string['failed_rename_tempfile'] = 'Failed renaming temp file';
$string['failed_write_file'] = 'Failed to write data in file "{$a}"';
$string['failed_zip_packing'] = 'An error was encountered while trying to zip file "{$a}"';
$string['file'] = 'File';
$string['filename'] = 'File name';
$string['filenotexists'] = 'File does not exist';
$string['filesize'] = 'File size';
$string['general'] = 'General';
$string['inactivity'] = 'Inactivity';
$string['inactivity_desc'] = 'User inactivity time (in seconds)';
$string['intellidata:trackdata'] = 'IntelliData track data';
$string['intellidata:viewlogs'] = 'IntelliData view logs';
$string['lastexportedid'] = 'Last exported ID';
$string['migrated'] = 'Migrated';
$string['migration_task'] = 'Migration task';
$string['migrationrecordslimit'] = 'Migration processing limit';
$string['migrationrecordslimit_desc'] = 'The number of records that will be processed at once';
$string['migrations'] = 'Migrations';
$string['migrationwriterecordslimit'] = 'Migration processing limit write to temp file';
$string['migrationwriterecordslimit_desc'] = 'The number of records that will be written to a temp file at once. When Totara data is located on local SSD we recommend 10,000 and on EFS we recommend 100,000.';
$string['pluginname'] = 'IntelliData';
$string['progress'] = 'Progress';
$string['required_data_format'] = 'Data format is required. Please configure admin settings.';
$string['resetdatatype'] = 'Are you sure you want to reset this data type?';
$string['resetimporttrackingprogress'] = 'Reset import tracking process';
$string['resetimporttrackingprogress_desc'] = 'Reset import process and start from beginning';
$string['resetmigrationprogress'] = 'Reset migration process';
$string['resetmigrationprogress_desc'] = 'Reset migration process and start from beginning';
$string['resettableexportsettings'] = 'Are you sure you want to reset table export settings?';
$string['status'] = 'Status';
$string['status_completed'] = 'Completed';
$string['status_inprogress'] = 'In progress';
$string['status_pending'] = 'Pending';
$string['timeend'] = 'Time end';
$string['timestart'] = 'Time start';
$string['trackadmin'] = 'Tracking admins';
$string['trackadmin_desc'] = 'Enable time tracking for admin users (not recommended)';
$string['trackingstorage'] = 'Tracking storage';
$string['trackmedia'] = 'Track HTML5 media';
$string['trackmedia_desc'] = 'Track HTML5 video and audio';

/**
 * IB Next LTI.
 */
$string['custommenuitem'] = 'Display in custom menu';
$string['debugenabled'] = 'Enable debug for migration and export';
$string['intellidata:viewlti'] = 'IntelliData view LTI';
$string['lti_basiclti_endpoint'] = 'LTI endpoint';
$string['lti_basiclti_parameters'] = 'LTI parameters';
$string['lti_toggle_debug_data'] = 'Toggle debug data';
$string['lticonfiguration'] = 'LTI configuration';
$string['lticonsumerkey'] = 'Key';
$string['ltidebug'] = 'Debug mode';
$string['ltimenutitle'] = 'Analytics';
$string['ltisharedsecret'] = 'Secret';
$string['ltititle'] = 'LTI menu title';
$string['ltitoolurl'] = 'Tool URL';

/**
 * Tracking.
 */
$string['cache_compresstracking'] = 'Save to Totara cache';
$string['cachedef_tracking'] = 'IntelliData user tracking data';
$string['compresstracking'] = 'Compress tracking';
$string['compresstracking_desc'] = 'Write time tracking data to file or Redis and transfer data to database with cron job. Totara cache and Redis are recommended.';
$string['do_not_use_compresstracking'] = 'Do not use compress tracking';
$string['file_compresstracking'] = 'Save to Totara data';
$string['trackdetails'] = 'TTrack time for user by hour';
$string['tracklogs'] = 'Track time for user by day';
$string['usertracking'] = 'User tracking';

/**
 * Configuration.
 */
$string['advancedsettings'] = 'Advanced settings';
$string['cache'] = 'Cache';
$string['cachedef_events'] = 'IntelliData events data';
$string['calculateprogress'] = 'Calculate progress';
$string['calculateprogressmsg'] = 'Are you sure you want to calculate the progress?';
$string['calculateprogresssuccessmsg'] = 'Ad hoc task created to calculate the progress. Progress will be updated soon.';
$string['classification'] = 'Classification';
$string['clear'] = 'Clear';
$string['configuration'] = 'Table export settings';
$string['configurationsaved'] = 'Configuration saved';
$string['copyintelliboardtracking'] = 'Divide export by data type';
$string['createindex'] = 'Create index';
$string['createindexcordconfirmation'] = 'Are you sure you want to create database index for table "{$a}?"';
$string['createlogsdatatype'] = 'Create logs data type';
$string['customdata'] = 'Custom data';
$string['datatypealreadyexists'] = 'The data type already exists.';
$string['deletecordconfirmation'] = 'Are you sure you want to delete this datatype?';
$string['deleteindex'] = 'Delete index';
$string['deleteindexcordconfirmation'] = 'Are you sure you want to delete database index for table "{$a}"?';
$string['deletemsg'] = 'Record deleted successfully';
$string['deletetask'] = 'Delete task';
$string['deletetaskconfirmation'] = 'Are you sure want to delete this task?';
$string['directsqlenabled'] = 'Enable direct SQL';
$string['directsqlenabled_desc'] = '';
$string['disabled'] = 'Disabled';
$string['divideexportbydatatype'] = 'Divide export by data type';
$string['editconfigfor'] = 'Edit configuration for {$a}';
$string['enabled'] = 'Enabled';
$string['enabledatacleaning'] = 'Enable data cleaning';
$string['enabledatavalidation'] = 'Enable data validation';
$string['enableexport'] = 'Enable export';
$string['enablemigration'] = 'Enable migration';
$string['enableprogresscalculation'] = 'Enable progress calculation during migration';
$string['event_sql_request'] = 'SQL request event';
$string['events_tracking'] = 'Events tracking';
$string['eventstracking'] = 'Events tracking';
$string['export'] = 'Export';
$string['export_help'] = 'Determines whether the database table is exported to IntelliBoard. The table must also be enabled.';
$string['exportadhoctasks'] = 'Export ad-hoc tasks';
$string['exportdeletedrecords'] = 'Export deleted records';
$string['exportids'] = 'Export deleted IDS';
$string['exportlogs'] = 'Export logs';
$string['faildelay'] = 'Fail delay';
$string['filterbyid'] = 'Filter by ID';
$string['importconfig'] = 'Import configuration';
$string['intelliboard'] = 'IntelliBoard';
$string['intellidata:deleteadhoctasks'] = 'Delete ad-hoc tasks';
$string['intellidata:editconfig'] = 'IntelliData edit configuration';
$string['intellidata:managefiles'] = 'IntelliData manage files';
$string['intellidata:viewadhoctasks'] = 'View ad-hoc tasks';
$string['intellidata:viewconfig'] = 'IntelliData view configuration';
$string['intellidata:viewdbschema'] = 'IntelliData view database schema';
$string['intellidata:viewlivedata'] = 'IntelliData view live data';
$string['logs'] = 'Logs';
$string['ltisubmittion'] = 'LTI submissions';
$string['migrationenabled'] = 'Migration enabled';
$string['nextruntime'] = 'Next run time';
$string['norecordsmatchedtocriteria'] = 'There are no records that match your selected criteria';
$string['option_all'] = 'All';
$string['optional'] = 'Optional';
$string['paramsshouldbespecified'] = 'Params should be specified.';
$string['pid'] = 'PID';
$string['pluginnotconfigured'] = 'The plugin is disabled or not configured.';
$string['privacy:metadata:local_intellidata_config'] = 'IntelliData configuration table';
$string['privacy:metadata:local_intellidata_config:datatype'] = 'Data type.';
$string['privacy:metadata:local_intellidata_config:events_tracking'] = 'Events tracking flag.';
$string['privacy:metadata:local_intellidata_config:filterbyid'] = 'Filter records by ID flag.';
$string['privacy:metadata:local_intellidata_config:rewritable'] = 'Rewritable table flag.';
$string['privacy:metadata:local_intellidata_config:status'] = 'Table status.';
$string['privacy:metadata:local_intellidata_config:tabletype'] = 'Table type.';
$string['privacy:metadata:local_intellidata_config:timecreated'] = 'Timestamp record creation.';
$string['privacy:metadata:local_intellidata_config:timemodified'] = 'Timestamp when record updated.';
$string['privacy:metadata:local_intellidata_config:timemodified_field'] = 'Timemodified field name.';
$string['privacy:metadata:local_intellidata_config:usermodified'] = 'ID of admin who modified the record.';
$string['privacy:metadata:local_intellidata_details'] = 'IntelliBoard alt/logs/by-hour table';
$string['privacy:metadata:local_intellidata_details:logid'] = 'Table ID [local_intellidata_logs].';
$string['privacy:metadata:local_intellidata_details:timepoint'] = 'The hour.';
$string['privacy:metadata:local_intellidata_details:timespend'] = 'The amount of time spent per hour.';
$string['privacy:metadata:local_intellidata_details:visits'] = 'The number of visits, mouse clicks, per day.';
$string['privacy:metadata:local_intellidata_logs'] = 'IntelliBoard alt/logs/by-day table';
$string['privacy:metadata:local_intellidata_logs:timepoint'] = 'Timestamp of day in year.';
$string['privacy:metadata:local_intellidata_logs:timespend'] = 'Time spent, per day.';
$string['privacy:metadata:local_intellidata_logs:trackid'] = 'The ID of the table [bi_intellidata_tracking].';
$string['privacy:metadata:local_intellidata_logs:visits'] = 'Visits, mouse clicks, per day.';
$string['privacy:metadata:local_intellidata_tracking'] = 'IntelliBoard alt/logs/all-time table';
$string['privacy:metadata:local_intellidata_tracking:instance'] = 'Tracking instance ID.';
$string['privacy:metadata:local_intellidata_tracking:rel'] = 'Relation.';
$string['privacy:metadata:local_intellidata_tracking:timecreated'] = 'Record timestamp.';
$string['privacy:metadata:local_intellidata_tracking:type'] = 'Tracking type.';
$string['privacy:metadata:local_intellidata_tracking:userid'] = 'ID of user who visits Totara page.';
$string['product'] = 'Product';
$string['product_core'] = 'Core';
$string['product_engage'] = 'Engage';
$string['product_learn'] = 'Learn';
$string['product_perform'] = 'Perform';
$string['refreshconfig'] = 'Refresh';
$string['required'] = 'Required';
$string['resetcordconfirmation'] = 'Are you sure you want to reset this datatype? All data will be regenerated.';
$string['resetexport'] = 'Reset export';
$string['resetmigrationmsg'] = 'Are you sure you want to enable or reset migration?';
$string['resetmsg'] = 'Export reset successful';
$string['resettodefault'] = 'Reset';
$string['restricted'] = 'Restricted';
$string['rewritable'] = 'Rewritable';
$string['search'] = 'Search';
$string['search_by'] = 'Search by';
$string['status_help'] = 'Determines whether the database table shows in the visual report builder for IntelliBoard. Disabled tables can’t be exported.';
$string['storage_not_exits'] = 'Storage does not exist';
$string['table_export_settings'] = 'Table export settings';
$string['tabletype'] = 'Table type';
$string['taskaddedforindexcreation'] = 'Task for database index creation added';
$string['taskaddedforindexdeletion'] = 'Task for database index deletion added';
$string['taskdeleted'] = 'Task deleted successfully';
$string['taskname'] = 'Task name';
$string['timecreated'] = 'Created at';
$string['timemodified_field'] = 'Timemodified field';
$string['timestarted'] = 'Started at';
$string['trackevents'] = 'Track events';
$string['trackingstorage'] = 'Tracking storage';
$string['tracklogsdatatypes'] = 'Track logs data types';
$string['tracklogsdatatypes_desc'] = 'Enable tracking each event to export logs data types';
$string['unrestricted'] = 'Unrestricted';
$string['wrongdatatype'] = 'Wrong data type';
