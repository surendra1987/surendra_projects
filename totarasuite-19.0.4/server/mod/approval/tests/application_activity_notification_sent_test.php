<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package mod_approval
 */

use core\entity\user;
use mod_approval\entity\application\application_activity as application_activity_entity;
use mod_approval\model\application\activity\notification_sent;
use mod_approval\model\application\application_activity;
use mod_approval\totara_notification\recipient\applicant;
use mod_approval\totara_notification\recipient\applicant_manager;
use mod_approval\totara_notification\resolver\stage_submitted;

require_once(__DIR__ . '/testcase.php');

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\model\application\activity\notification_sent
 */
class mod_approval_application_activity_notification_sent_test extends mod_approval_testcase {

    /**
     * From Totara 17.1 the `notification_sent` activity stored recipient classes in an array called `recipient_class_names`.
     * Before that they were stored as a single array entry called `recipient_class_name`.
     *
     * This test asserts that both formats resolve back to the expected recipient_class_names => array format as
     * it's possible for history unconverted records to still exist.
     *
     * It covers both the model change but also loading the notification_sent object.
     *
     * @return void
     */
    public function test_resolves_legacy_notification_resolvers(): void {
        $submitter_user = new user($this->getDataGenerator()->create_user());

        $this->setAdminUser();
        $application = $this->create_application_for_user();

        $activity = application_activity::create(
            $application,
            $submitter_user->id,
            notification_sent::class,
            [
                'resolver_class_name' => stage_submitted::class,
                'recipient_class_names' => [applicant::class, applicant_manager::class],
            ]
        );

        $parsed = $activity->activity_info_parsed;
        $this->assertArrayHasKey('recipient_class_names', $parsed);
        $this->assertEqualsCanonicalizing([applicant::class, applicant_manager::class], $parsed['recipient_class_names']);

        $notification_sent = notification_sent::from_activity($activity);
        $this->assertTrue($notification_sent->from_system());
        $this->assertTrue($notification_sent->for_system());
        $this->assertStringContainsStringIgnoringCase('Applicant, Manager', $notification_sent->get_description());

        // Now forcibly override the $activity data back to the old style
        /** @var application_activity_entity $activity_entity */
        $activity_entity = $activity->get_entity_copy();
        $activity_entity->activity_info = json_encode([
            'resolver_class_name' => stage_submitted::class,
            'recipient_class_name' => applicant_manager::class,
        ]);
        $activity_entity->save();

        // Reload it - we now only have the Manager to confirm it did change
        $activity = application_activity::load_by_id($activity_entity->id);
        $parsed = $activity->activity_info_parsed;
        $this->assertArrayHasKey('recipient_class_names', $parsed);
        $this->assertEqualsCanonicalizing([applicant_manager::class], $parsed['recipient_class_names']);

        $notification_sent = notification_sent::from_activity($activity);
        $this->assertTrue($notification_sent->from_system());
        $this->assertTrue($notification_sent->for_system());
        $this->assertStringContainsStringIgnoringCase('Manager', $notification_sent->get_description());
    }
}
