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
 * @package core
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');

global $CFG;
require_once($CFG->libdir . '/clilib.php');

cli_logo();
echo PHP_EOL;

cli_heading(get_string('cipher_add_encryption_key', 'core'));

$help = "This script can be used to add a new encryption key.

After added, records will start to use the new key. Older records will be updated on their next save
or immediately if you use the --update option or run the command php admin/cli/update_encrypted_models.php script.

Options:
-v, --value          Optional, key value
-c, --cipherid       Optional, Cipher id to be used with key 
-u, --update         Optional, Queue adhoc tasks to update old records with the new key. 
-h, --help           Print out this help

Example:
\$ php admin/cli/add_encryption_keys.php
\$ php admin/cli/add_encryption_keys.php --value=value_of_key --cipherid=aes256
";

// now get cli options
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'value' => null,
        'cipherid' => null,
        'update' => null,
    ],
    [
        'h' => 'help',
        'v' => 'value',
        'u' => 'update',
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
$key_value = $options['value'];
$cipher_id = $options['cipherid'];
$key_added = \core\cipher\key\manager::instance()->add_key($cipher_id, $key_value);

if (empty($key_added)) {
    // Failed adding the key
    cli_error(get_string('cipher_add_encryption_key_failed', 'core'));
}

cli_writeln(get_string('cipher_add_encryption_key_success', 'core'));

if (!empty($options['update'])) {
    // Schedule the tasks to update the models
    \core\task\update_encrypted_models_task::enqueue_all();
    cli_writeln(get_string('cipher_add_encryption_key_queued', 'core'));
} else {
    cli_writeln(get_string('cipher_add_encryption_key_update', 'core', 'php admin/cli/update_encrypted_models.php'));
}
