<?php
// This file is part of Moodle - http://moodle.org/
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
 * CLI tool for system checks
 *
 * @package    core
 * @category   check
 * @copyright  2020 Brendan Heywood (brendan@catalyst-au.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/clilib.php');

use core\check\result;

list($options, $unrecognized) = cli_get_params([
    'help'    => false,
    'filter'  => '',
    'exclude' => '',
    'type'    => 'status',
    'verbose' => false,
], [
    'h' => 'help',
    'f' => 'filter',
    'e' => 'exclude',
    'v' => 'verbose',
    't' => 'type',
]);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$checks = \core\check\manager::get_checks($options['type']);

$help = "Run Totara system checks

Options:
 -h, --help      Print out this help
 -f, --filter    Filter to a subset of checks (one or more comma separated strings matched against check key)
 -e, --exclude   Same as filter but matching checks will be excluded instead of being included. If both filter
                    and exclude are specified, exclude takes prescedence.
 -t, --type      Which set of checks? Defaults to 'status'
                 One of security, status, performance
 -v, --verbose   Show details of all checks, not just failed checks

Example:

  sudo -u www-data php server/admin/cli/checks.php
  sudo -u www-data php server/admin/cli/checks.php -v
  sudo -u www-data php server/admin/cli/checks.php -v --filter=environment

";

if ($options['help']) {
    echo $help;
    die();
}

$filter = $options['filter'];
if ($filter) {
    $checks = array_filter($checks, function ($check) use ($filter) {
        $filters = explode(',', $filter);
        $ref = $check->get_ref();
        foreach ($filters as $filter) {
            if (strpos($ref, $filter) !== false) {
                return true;
            }
        }
        return false;
    }, 1);
}

$exclude = $options['exclude'];
if ($exclude) {
    $checks = array_filter($checks, function ($check) use ($exclude) {
        $excludes = explode(',', $exclude);
        $ref = $check->get_ref();
        foreach ($excludes as $exclude) {
            if (strpos($ref, $exclude) !== false) {
                return false;
            }
        }
        return true;
    }, 1);
}

// These shell exit codes and labels align with the NRPE standard.
$exitcodes = [
    result::NA        => 0,
    result::OK        => 0,
    result::INFO      => 0,
    result::UNKNOWN   => 3,
    result::WARNING   => 1,
    result::ERROR     => 2,
    result::CRITICAL  => 2,
];
$exitlabel = [
    result::NA        => 'OK',
    result::OK        => 'OK',
    result::INFO      => 'OK',
    result::UNKNOWN   => 'UNKNOWN',
    result::WARNING   => 'WARNING',
    result::ERROR     => 'CRITICAL',
    result::CRITICAL  => 'CRITICAL',
];

$format = "%      9s | % -60s\n";
$spacer = "----------+--------------------------------------------------------------------\n";
$prefix = '          |';

$output = '';
$header = $exitlabel[result::OK] . ': ' . get_string('checksok', '', $options['type']) . "\n";
$exitcode = $exitcodes[result::OK];

foreach ($checks as $check) {
    $ref = $check->get_ref();
    $result = $check->get_result();

    $status = $result->get_status();
    $checkexitcode = $exitcodes[$status];

    // Summary is treated as html.
    $summary = $result->get_summary();
    $summary = html_to_text($summary, 60, false);

    if ($checkexitcode > $exitcode) {
        $exitcode = $checkexitcode;
        $header = $exitlabel[$status] . ': ' . $check->get_name() . " (" . $check->get_ref() . ")\n";
    }

    if (empty($messages[$status])) {
        $messages[$status] = $result;
    }

    $len = strlen(get_string('status' . $status));

    if ($options['verbose'] ||
        $status == result::WARNING ||
        $status == result::CRITICAL ||
        $status == result::ERROR) {

        $output .= sprintf(
            $format,
            clean_text(get_string('status' . $result->get_status())),
            sprintf('%s (%s)', $check->get_name(), $ref)
        );

        $summary = str_replace("\n", "\n" . $prefix . '     ', $summary);
        $output .= sprintf( $format, '', '    ' . $summary);

        if ($options['verbose']) {
            $actionlink = $check->get_action_link();
            if ($actionlink) {
                $output .= sprintf( $format, '', '    ' . $actionlink->url);
            }
            $output .= sprintf( $format, '', '');
        }
    }
}

// Print NRPE header.
print $header;

// Only show the table header if there is anything to show.
if ($output) {
    print sprintf($format,
            get_string('status'). ' ',
            get_string('check')
        ) .  $spacer;
    print $output;
}

// NRPE shell exit code.
exit($exitcode);
