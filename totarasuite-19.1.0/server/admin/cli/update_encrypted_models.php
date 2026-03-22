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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

use core\cipher\task\rollover_encryption_manager;
use core\event\cipher_encryption_queued;

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

global $CFG;
require_once($CFG->libdir . '/clilib.php');

cli_logo();
echo PHP_EOL;

cli_heading(get_string('cipher_key_rollover_title', 'core'));

$help = "This script allows you to check and update any encrypted models using old keys.

Options:
-c, --count          Optional. Count only, don't execute. Just show how many are out of date.
-o, --outdated       Optional. Only show the models with outdated records.
-f, --force          Optional. Don't prompt for confirmation, just run the migration.
-q, --queue          Optional. Queue the update as adhoc tasks instead. If set the updates will be run with the next cron.
-h, --help           Print out this help

Example:
\$ php admin/cli/update_encrypted_models.php --count
";

// now get cli options
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'outdated' => null,
        'force' => null,
        'count' => null,
        'queue' => null,
    ],
    [
        'h' => 'help',
        'o' => 'outdated',
        'f' => 'force',
        'c' => 'count',
        'q' => 'queue',
    ]
);

if (!empty($unrecognized)) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if (!empty($options['help'])) {
    echo $help;
    die;
}

$only_outdated = !empty($options['outdated']);

$manager = rollover_encryption_manager::instance();
$encrypted_models = $manager->get_encrypted_models();

$outdated_models = [];

cli_writeln('');

$width = 0;
foreach ($encrypted_models as $model_class) {
    $outdated = $manager->count_outdated_records($model_class);

    if ($outdated === 0 && $only_outdated) {
        continue;
    }

    $outdated_models[$model_class] = $outdated;
    if (strlen($model_class) > $width) {
        $width = strlen($model_class);
    }
}

$overall = 0;
foreach ($outdated_models as $model_class => $outdated) {
    cli_writeln(sprintf('%s: %d', str_pad($model_class, $width, ' ', STR_PAD_LEFT), $outdated));
    $overall += $outdated;
}

cli_writeln('');

if (!empty($options['count'])) {
    exit(0);
}

if (!empty($options['queue'])) {
    foreach ($outdated_models as $model_class => $outdated) {
        $label = str_pad($model_class, $width, ' ', STR_PAD_LEFT);
        if ($outdated > 0) {
            \core\task\update_encrypted_models_task::enqueue_one($model_class);
            cli_writeln(get_string('cipher_key_rollover_model_queued', 'core', $label));
        } else {

            cli_writeln(get_string('cipher_key_rollover_model_skipped', 'core', $label));
        }
    }
    exit(0);
}

if ($overall === 0) {
    exit(0);
}

if (empty($options['force'])) {
    cli_writeln(get_string('cipher_key_rollover_confirm', 'core'));
    $prompt = get_string('cliyesnoprompt', 'admin');
    $input = cli_input($prompt, '', array(get_string('clianswerno', 'admin'), get_string('cliansweryes', 'admin')));
    if ($input == get_string('clianswerno', 'admin')) {
        exit(1);
    }

    cli_writeln('');
}

cli_writeln(get_string('cipher_key_rollover_updating_records', 'core'));
cli_writeln('');

foreach ($outdated_models as $model_class => $outdated) {
    $label = str_pad($model_class, $width, ' ', STR_PAD_LEFT);
    if ($outdated === 0) {
        cli_writeln(get_string('cipher_key_rollover_skipped', 'core', $label));
        continue;
    }
    $updated = $manager->update_outdated_records($model_class);
    cli_writeln(sprintf('%s: %d', $label, $updated));
}

// Fire the event
cipher_encryption_queued::create_anonymous()->trigger();

exit(0);