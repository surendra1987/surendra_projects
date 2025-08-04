<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

/**
 * CLI tool for resetting mfa factors for an admin user
 *
 * @package    core_mfa
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

global $CFG;
require_once($CFG->libdir.'/clilib.php');

cli_logo();
echo PHP_EOL;
cli_heading(get_string('reset_mfa_cli', 'mfa'));

$help =
    "Multi-factor authentication admin revoke script.

This script can be used to revoke all configured factors for a siteadmin.

Please be aware this will only work for siteadmins.

Options:
-u, --username        Username of admin user 
-h, --help            Print out this help

Example:
\$ php admin/cli/revoke_admin_mfa.php --username=admin
";

// now get cli options
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'username' => false,
    ],
    [
        'h' => 'help',
        'u' => 'username',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
    die;
}

if (!empty($options['help'])) {
    echo $help;
    die;
}

if (empty($options['username'])) {
    $prompt = get_string('admin_username_prompt', 'mfa');
    $username = core_text::strtolower(cli_input($prompt));
} else {
    $username = $options['username'];
}

$user = \core\entity\user::repository()->where('username', $username)->one();

if (!is_siteadmin($user->id)) {
    cli_error(get_string('not_site_admin', 'mfa'));
    die;
}

(new \core_mfa\framework($user->id))->revoke_factors();

cli_writeln(get_string('admin_mfa_reset_done', 'mfa'));