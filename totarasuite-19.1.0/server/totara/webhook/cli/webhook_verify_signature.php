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

// This script will verify a signature sent out with webhooks
require_once __DIR__ . '/../../../config.php';
global $CFG, $DB, $USER;
require_once $CFG->libdir . '/clilib.php';

// verify a dead letter signature
[$options, $unrecognised] = cli_get_params(
    [
        'body'=>false,
        'signature'=>false,
        'secret'=>false,
        'help' => false
    ],
    [
        'b' => 'body',
        'x' => 'signature',
        's' => 'secret',
        'h' => 'help'
    ]
);

if ($unrecognised) {
    $unrecognised = implode('\n  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognised));
}

if ($options['help']) {
    $help =
"Verify a a signature to check it was sent from Totara.

Options:
-b, --body          the raw json string that was sent
-x, --signature     the signature string that was in the headers of the request
-s, --secret        the webhooks signing secret
-h, --help          Print out this help

Example:
\$ sudo -u www-data /usr/bin/php totara/webhook/cli/webhook_verify_signature.php --signature=ABCsdf123ACD --secret=XXXX --body=\'{\"key\": \"value\"}\'
";
}

if (!$options['body'] || !$options['signature'] || !$options['secret']) {
    cli_error('You must specify a body, signature and secret');
}

$body = $options['body'];
$secret = $options['secret'];
$signature = $options['signature'];

$calculated_signature = hash_hmac('sha256', $body, $secret);

$message = "Signature failed";

if (hash_equals($calculated_signature, $signature)) {
    $message = "Signature verified";
}

echo $message . PHP_EOL;