<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

define('CLI_SCRIPT', true);

// This script will rebuild the webhooks cache
require_once __DIR__ . '/../../../config.php';
global $CFG, $DB, $USER;
require_once $CFG->libdir . '/clilib.php';

// find the dead letter queue item for the current
[$options, $unrecognised] = cli_get_params(['id'=>false, 'help'=>false], ['i' => 'id', 'h' => 'help']);

if ($unrecognised) {
    $unrecognised = implode('\n  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognised));
}

if ($options['help']) {
    $help =
"Requeue a dead letter queue item. This will move the item from the DLQ to the payload queue to be
processed the next time the scheduled job runs.

Options:
-i, --id=ID         The ID of the DLQ item to requeue
-h, --help          Print out this help

Example:
\$ sudo -u www-data /usr/bin/php totara/webhook/cli/webhook_requeue_dead_letter.php --id=1
";
}

if (!$options['id']) {
    cli_error('You must specify an ID');
}

$item_id = $options['id'];
$dlq_item = \totara_webhook\model\totara_webhook_dlq_item::load_by_id($item_id);
try {
    $requeued_item = $dlq_item->requeue();
    echo "Requeued item on totara_webhook_payload_queue - ID: $requeued_item->id\n";
} catch (coding_exception $e) {
    echo "Failed to requeue item $item_id: {$e->getMessage()}";
}
