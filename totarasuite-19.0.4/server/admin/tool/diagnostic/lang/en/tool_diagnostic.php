<?php
/**
 * This file is part of Totara Core
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @note Automatically cleaned: 2024-09-24
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package tool_diagnostic
 */

$string['cache_information_description'] = 'Basic cache config information';
$string['cache_information_name'] = 'Cache information';
$string['cli_cleaning_up'] = 'Cleaning up...';
$string['cli_help_text'] = '
Generate diagnostic exports that can be used by support to resolve issues.

Only the diagnostic providers that are enabled in the \'config/config.json\' file will be executed.
If this file is not present the defaults from \'config/config.json.dist\' are used. Copy this file
to \'config/config.json\' and modify it to your needs if required. For most cases the defaults
should be enough.

Usage:
    php server/admin/tool/diagnostic/cli/run_diagnostics.php
    
Options:
    -h, --help               Print out this help
    -l, --list               List all the providers. Includes description and current enabled status.
';
$string['cli_zipping'] = 'Zipping...';
$string['countusage'] = 'Counting Mapping Usage';
$string['db_row_counts_description'] = 'Row counts of non-empty database tables';
$string['db_row_counts_name'] = 'Database table row counts';
$string['delete_diagnostic_files_task'] = 'Delete old diagnostic files';
$string['download_diagnostics'] = 'Download diagnostics';
$string['error_creating_directory'] = 'Failed in creating diagnostic directory';
$string['error_zip_failed'] = 'Failed in zipping files';
$string['error_zip_storage_failed'] = 'Error while storing zip file';
$string['filenotfound'] = 'The requested diagnostic file does not exist or has already been downloaded. Please generate a new one in the diagnostic tool if required.';
$string['performance_cache_description'] = 'Cache performance metrics as provided by the "Test performance" caching page.';
$string['performance_cache_name'] = 'Cache performance';
$string['performance_database_description'] = 'Basic database performance metrics (provide only when requested)';
$string['performance_database_name'] = 'Database performance';
$string['performance_io_description'] = 'Basic IO performance metrics (provide only when requested)';
$string['performance_io_name'] = 'IO performance';
$string['platform_database_description'] = 'Database vendor and version information';
$string['platform_database_name'] = 'Database information';
$string['platform_phpini_description'] = 'A selection of PHP settings. Only whitelisted settings are exported.';
$string['platform_phpini_name'] = 'PHP ini information';
$string['pluginname'] = 'Troubleshooting diagnostics';
$string['provider_disabled'] = 'Disabled';
$string['provider_done'] = '...DONE';
$string['provider_enabled'] = 'Enabled';
$string['provider_executing'] = 'Executing provider: {$a}';
$string['provider_execution_failed'] = '...Provider execution failed with message: {$a}';
$string['provider_not_configured'] = 'No config found';
$string['summary_description'] = 'Information about the Totara site containing version, environment (PHP, database, webserver), features and other commonly required information.';
$string['summary_name'] = 'Summary';
$string['totara_config_description'] = 'Selected config settings of your site. Only whitelisted settings are exported.';
$string['totara_config_name'] = 'Totara config information';
$string['totara_config_plugin_description'] = 'Selected config settings and information about your Totara site\'s plugins. Only whitelisted settings are exported.';
$string['totara_config_plugin_name'] = 'Totara plugin configuration';
$string['totara_plugins_description'] = 'List of plugins installed in your Totara site';
$string['totara_plugins_name'] = 'Totara plugins';
$string['totara_upgrade_history_description'] = 'Upgrade history of your Totara site. Only history of whitelisted plugins is exported.';
$string['totara_upgrade_history_name'] = 'Totara upgrade history';

/**
 * Admin setting strings.
 */
$string['support_diagnostics_tool'] = 'Diagnostics for support';

/**
 * Vue strings.
 */
$string['column_description'] = 'Description';
$string['column_include'] = 'Include';
$string['column_name'] = 'Name';
$string['column_whitelist'] = 'Whitelist';
$string['diagnostics'] = 'Diagnostics';
$string['file_created'] = 'File created. You can now download the file. Please note that the file can be downloaded only once and if not downloaded are only kept for a minimum of 15 and a maximum of 60 minutes.';
$string['file_created_cli'] = 'Open the following URL in the browser (you have to be logged in as an admin): {$a}';
$string['hide_whitelist'] = 'Hide whitelist';
$string['ok'] = 'OK';
$string['providers_page_heading'] = 'Diagnostics for support';
$string['run_diagnostics'] = 'Generate diagnostic file';
$string['show_whitelist'] = 'Show whitelist';
$string['success_modal_title'] = 'Diagnostics information successfully generated';
$string['toast_success_update'] = 'Settings successfully updated';
$string['tool_summary'] = 'This utility is designed to streamline the process of gathering information for our Support and Development teams. When requested by our support, an admin can export site information in order to aid our support and development teams in investigating reported issues.';
$string['tool_summary_feature_features1'] = 'Comprehensive Data Collection: The tool gathers a wide range of information about your site, ensuring that our Support and Development teams have everything they need to diagnose and resolve issues effectively.';
$string['tool_summary_feature_features2'] = 'Customizable Exports: While a standard set of data providers are enabled by default, you have the flexibility to include additional optional information, such as performance metrics.';
$string['tool_summary_feature_features3'] = 'Secure and Confidential: The exported files do not contain any sensitive information like credentials or user-identifiable data, ensuring that your privacy and security are maintained. For your convenience and security, the exported files are only kept for 15 minutes and will be automatically deleted after that period.';
$string['tool_summary_feature_features4'] = 'Easy Download and Sharing: The collected information is packaged into a set of text files, zipped, and made available for download. This zip file can then be easily shared with our Support team for further analysis.';
$string['tool_summary_feature_heading'] = 'Key features';
