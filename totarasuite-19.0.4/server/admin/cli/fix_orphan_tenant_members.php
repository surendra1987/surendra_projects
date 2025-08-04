<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_tenant
 */

/**
 * This script fixes incorrectly unsuspended or undeleted users.
 *
 * @package    core
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\record\tenant;

define('CLI_SCRIPT', true);

global $CFG, $DB;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('help' => false, 'fix' => false),
    array('h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if (empty($options['fix'])) {
    $help = "Fix incorrectly unsuspended or undeleted users.

This script detects users that belong to a particular tenant (have a tenantid) 
but for some reason are not a member of the tenant audience.

Options:
--fix                 Add users to tenant audience
-h, --help            Print out this help

Example:
\$sudo -u www-data /usr/bin/php server/fix_orphan_tenant_members.php --fix
";

    echo $help;
    die;
}
if (empty($CFG->tenantsenabled)) {
    echo "Multitenancy is disabled. Nothing to fix.";
    die;
}

cli_heading('Add tenant members to tenant audience');

$trans = $DB->start_delegated_transaction();

$sql = "SELECT u.id AS userid, t.cohortid
            FROM {user} u
            JOIN {tenant} t ON u.tenantid = t.id
            LEFT JOIN {cohort_members} cm ON cm.userid = u.id AND cm.cohortid = t.cohortid
        WHERE u.suspended = 0 AND u.deleted = 0 AND cm.id IS NULL";

$users = $DB->get_records_sql($sql);
$affected = 0;

foreach ($users as $user) {
    if (cohort_add_member($user->cohortid, $user->userid) !== false) {
        $affected++;
    }
}

$trans->allow_commit();

cli_writeln($affected . ' users were affected.');
cli_writeln('...done');

exit(0);
