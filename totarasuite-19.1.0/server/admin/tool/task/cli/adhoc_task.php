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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package tool_task
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once("$CFG->libdir/cronlib.php");

list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'list' => false,
        'execute' => false,
        'showsql' => false,
        'showdebugging' => false
    ],
    [
        'h' => 'help',
        'l' => 'list',
        'e' => 'execute'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] or (!$options['list'] and !$options['execute'])) {
    $help =
"Run queued adhoc cron tasks.

Options:
-e --execute=classname   Run all the given instances of a adhoc class, or 'all' / '*' to run all existing queued adhoc tasks
-l --list                List all queued adhoc tasks
--showsql                Show sql queries before they are executed
--showdebugging          Show developer level debugging information
-h, --help               Print out this help

Example:
\$sudo -u www-data /usr/bin/php admin/tool/task/cli/adhoc_task.php --execute=\\\\core\\\\task\\\\session_cleanup_task

";

    echo $help;
    die;
}

if ($options['showdebugging']) {
    set_debugging(DEBUG_DEVELOPER, true);
}

if ($options['showsql']) {
    $DB->set_debug(true);
}

// List all adhoc tasks that have a queue'd item in the database (i.e. would run on the next cron)
if ($options['list']) {
    cli_heading("List of queue'd adhoc tasks ($CFG->wwwroot)");

    $shorttime = get_string('strftimedatetimeshort');

    $tasks = \core\task\manager::get_all_queued_adhoc_tasks();
    foreach ($tasks as $task) {
        $class = '\\' . get_class($task);
        $nextrun = $task->get_next_run_time();

        $plugininfo = core_plugin_manager::instance()->get_plugin_info($task->get_component());
        $plugindisabled = $plugininfo && $plugininfo->is_enabled() === false;

        if ($nextrun > time()) {
            $nextrun = userdate($nextrun);
        } else {
            $nextrun = get_string('asap', 'tool_task');
        }

        echo str_pad($class, 50, ' ') . ' '  . $nextrun . "\n";
    }
    exit(0);
}

if ($execute = $options['execute']) {

    // Load up any adhoc tasks specified.
    if ($execute == 'all' || $execute == '*') {
        $tasks = \core\task\manager::get_all_queued_adhoc_tasks();
    } else {
        $tasks = \core\task\manager::get_adhoc_tasks($execute);
    }

    // If nothing was loaded, theres nothing to do here :)
    if (empty($tasks)) {
        mtrace("No queued tasks matching '$execute' found");
        exit(1);
    }

    if (moodle_needs_upgrading()) {
        mtrace("Upgrade pending, tasks cannot be executed. You will need to restore the previous version of Totara to clear the task queue.");
        exit(1);
    }

    // Increase memory limit.
    raise_memory_limit(MEMORY_EXTRA);

    foreach ($tasks as $task) {
        // We aren't using get next so we need to set up a cron/task lock.
        $cronlockfactory = \core\lock\lock_config::get_lock_factory('cron');
        if (!$cronlock = $cronlockfactory->get_lock('core_cron', 10)) {
            mtrace('Cannot obtain cron lock');
            exit(129);
        }
        if (!$lock = $cronlockfactory->get_lock('\\' . get_class($task), 10)) {
            $cronlock->release();
            mtrace('Cannot obtain task lock');
            exit(130);
        }

        $task->set_lock($lock);
        if (!$task->is_blocking()) {
            $cronlock->release();
        } else {
            $task->set_cron_lock($cronlock);
        }

        // Use the cron function to make sure users get set properly etc.
        cron_run_inner_adhoc_task($task);
    }
    exit(0);
}
