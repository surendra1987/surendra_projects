<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

defined('MOODLE_INTERNAL') || die;

/* @var admin_root $ADMIN */
$webhooks_url = new \moodle_url('/totara/webhook/');
$hidden = totara_core\advanced_feature::is_disabled('totara_webhook');
$context = $PAGE->context;

if (!$ADMIN->get_tree_map()->get('api')) {
    // If the API menu does not exist, we want to add it to the tree.
    $ADMIN->add(
        'development',
        new admin_category(
            'api',
            new lang_string('pluginname', 'totara_api')
        ),
        'experimental'
    );
}

$ADMIN->add(
    'api',
    new admin_externalpage(
        'totara_webhook_totara_webhook',
        new lang_string('totara_webhooks', 'totara_webhook'),
        $webhooks_url,
        ['totara/webhook:managetotara_webhooks', 'totara/webhook:viewtotara_webhooks'],
        $hidden,
        $context
    )
);
