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

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['actions_for_x'] = 'Actions for {$a}';
$string['cachedef_totara_webhook_event_subscriptions'] = 'This cache is used for storing the mapping of events to subscribed webhooks';
$string['create_totara_webhook'] = 'Create Webhook';
$string['created_at'] = 'Created at';
$string['delete_totara_webhook_confirm_message'] = 'Are you sure you want to delete this Webhook?';
$string['edit_totara_webhook'] = 'Edit Webhook';
$string['enable_totara_webhook'] = 'Enable Webhooks';
$string['enable_totara_webhook_description'] = 'Allow your Totara site to send payloads to external systems when events are triggered. When enabled, you can access settings and configure webooks from the <a href="{$a}">development menu</a>.';
$string['endpoint'] = 'Endpoint';
$string['event_totara_webhook_created'] = 'Webhook created';
$string['event_totara_webhook_created_description'] = 'The user with id \'{$a->userid}\' created the Webhook with id \'{$a->id}\'.';
$string['event_totara_webhook_deleted'] = 'Webhook deleted';
$string['event_totara_webhook_deleted_description'] = 'The user with id \'{$a->userid}\' deleted the Webhook with id \'{$a->id}\'.';
$string['event_totara_webhook_dlq_item_created'] = 'Totara webhook dead letter queue item created';
$string['event_totara_webhook_dlq_item_created_description'] = 'The user with id \'{$a->userid}\' created the Totara webhook dead letter queue item with id \'{$a->id}\'.';
$string['event_totara_webhook_dlq_item_deleted'] = 'Totara webhook dead letter queue item deleted';
$string['event_totara_webhook_dlq_item_deleted_description'] = 'The user with id \'{$a->userid}\' deleted the Totara webhook dead letter queue item with id \'{$a->id}\'.';
$string['event_totara_webhook_dlq_item_updated'] = 'Totara webhook dead letter queue item updated';
$string['event_totara_webhook_dlq_item_updated_description'] = 'The user with id \'{$a->userid}\' updated the Totara webhook dead letter queue item with id \'{$a->id}\'.';
$string['event_totara_webhook_event_subscription_created'] = 'Webhook Event Subscription created';
$string['event_totara_webhook_event_subscription_created_description'] = 'The user with id \'{$a->userid}\' created the Webhook Event Subscription with id \'{$a->id}\'.';
$string['event_totara_webhook_event_subscription_deleted'] = 'Webhook Event Subscription deleted';
$string['event_totara_webhook_event_subscription_deleted_description'] = 'The user with id \'{$a->userid}\' deleted the Webhook Event Subscription with id \'{$a->id}\'.';
$string['event_totara_webhook_event_subscription_updated'] = 'Webhook Event Subscription updated';
$string['event_totara_webhook_event_subscription_updated_description'] = 'The user with id \'{$a->userid}\' updated the Webhook Event Subscription with id \'{$a->id}\'.';
$string['event_totara_webhook_updated'] = 'Webhook updated';
$string['event_totara_webhook_updated_description'] = 'The user with id \'{$a->userid}\' updated the Webhook with id \'{$a->id}\'.';
$string['events'] = 'Events';
$string['hide'] = 'Hide';
$string['hide_signing_secret_aria'] = 'Hide signing secret for {$a}';
$string['invalid_endpoint'] = 'You must provide a URL';
$string['invalid_totara_webhook'] = 'Invalid Webhook';
$string['manage_totara_webhooks'] = 'Manage Webhooks';
$string['name'] = 'Name';
$string['pluginname'] = 'Webhooks';
$string['rotate_signing_secret'] = 'Rotate signing secret';
$string['rotate_signing_secret_aria'] = 'Rotate signing secret';
$string['rotate_signing_secret_cancel'] = 'Cancel';
$string['rotate_signing_secret_confirm'] = 'Rotate';
$string['rotate_signing_secret_modal_1'] = 'The new signing secret will take effect immediately.';
$string['rotate_signing_secret_modal_2'] = 'The current secret will be revoked, and consumers must be updated to use the new secret.';
$string['rotate_signing_secret_modal_3'] = 'Are you sure you want ot rotate this signing secret?';
$string['rotate_signing_secret_title'] = 'Rotate signing secret';
$string['secret_rotated_success'] = 'Signing secret updated';
$string['show'] = 'Show';
$string['show_signing_secret_aria'] = 'Show signing secret for {$a}';
$string['signing_secret'] = 'Signing secret';
$string['sort_by'] = 'Sort by';
$string['totara_webhook'] = 'Webhook';
$string['totara_webhook_created'] = 'Webhook created';
$string['totara_webhook_disable_webhook'] = 'Disable Webhook';
$string['totara_webhook_enable_webhook'] = 'Enable Webhook';
$string['totara_webhook_saved'] = 'Webhook saved';
$string['totara_webhook_status'] = 'Status';
$string['totara_webhook_status_disabled'] = 'Disabled';
$string['totara_webhook_status_enabled'] = 'Enabled';
$string['totara_webhooks'] = 'Webhooks';
$string['updated_at'] = 'Updated at';
$string['webhook:managetotara_webhooks'] = 'Manage Webhooks';
$string['webhook:viewtotara_webhooks'] = 'View Webhooks';
$string['webhook_default_queue_consumer'] = 'Totara webhook default queue consumer';
$string['webhook_default_queue_purger'] = 'Totara webhook default queue purger';
$string['webhook_dispatch_timing'] = 'Dispatch timing';
$string['webhook_dlq_purger'] = 'Totara webhook dead letter queue purger';
$string['webhook_immediate'] = 'Immediate';
$string['webhook_scheduled'] = 'Scheduled';
