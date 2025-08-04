<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package performelement_linked_review
 */

use core\orm\query\exceptions\record_not_found_exception;
use core\testing\generator;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\models\activity\participant_instance;
use mod_perform\state\participant_section\open;
use mod_perform\testing\generator as perform_generator;
use pathway_perform_rating\models\perform_rating;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as linked_review_generator;
use totara_competency\models\assignment;
use totara_competency\performelement_linked_review\competency_assignment;
use totara_core\advanced_feature;
use totara_core\relationship\relationship;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\state\participant_instance\in_progress as pi_in_progress;
use mod_perform\state\participant_instance\not_started as pi_not_started;
use performelement_linked_review\testing\generator as lrc_generator;
use totara_competency\testing\generator as competency_generator;

/**
 * @group perform
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 */
class performelement_linked_review_webapi_resolver_query_content_items_test extends \core_phpunit\testcase {

    use webapi_phpunit_helper;

    private const QUERY = 'performelement_linked_review_content_items';

    public function test_get_content_items(): void {
        self::setAdminUser();
        [$activity1, $section1, $element1, $section_element1] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$activity2, $section2, $element2, $section_element2] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user1, $subject_instance1, $participant_instance1, $participant_section1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1, 'section' => $section1,
        ]);
        [$user2, $subject_instance2, $participant_instance2, $participant_section2] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity2, 'section' => $section2,
        ]);

        $reporting_user = generator::instance()->create_user();

        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_other_user = linked_review_generator::instance()->create_competency_assignment(['user' => $user2])->id;

        $content_items1 = linked_review_content::create_multiple(
            [$content_id1, $content_id2],
            $section_element1->id,
            $participant_instance1->id, false
        );

        $content_items2 = linked_review_content::create_multiple(
            [$content_other_user],
            $section_element1->id,
            $participant_instance2->id, false
        );

        self::setUser($user1);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        // The result should be explicitly ordered by id.
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items1->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items1->pluck('id');
        $this->assertEquals($expected_ids, $actual_ids);

        $assignment1 = assignment::load_by_id($content_id1);
        /** @var linked_review_content $matched_content_item1 */
        $matched_content_item1 = $result['items'][0];
        $this->assertInstanceOf(linked_review_content::class, $matched_content_item1);
        $content1 = $matched_content_item1->content;
        $this->assertIsArray($content1);
        $this->assertEquals($assignment1->get_id(), $content1['id']);
        $this->assertNotEmpty($content1['competency']);
        $this->assertNotEmpty($content1['achievement']);
        $this->assertNotEmpty($content1['assignment']);
        // We expect this to be empty because the review item does not have the content_type of 'learning_X'.
        $this->assertEmpty($matched_content_item1->get_unique_id());

        $assignment2 = assignment::load_by_id($content_id2);
        /** @var linked_review_content $matched_content_item2 */
        $matched_content_item2 = $result['items'][1];
        $this->assertInstanceOf(linked_review_content::class, $matched_content_item2);
        $content2 = $matched_content_item2->content;
        $this->assertIsArray($content2);
        $this->assertEquals($assignment2->get_id(), $content2['id']);
        $this->assertNotEmpty($content2['competency']);
        $this->assertNotEmpty($content2['achievement']);
        $this->assertNotEmpty($content2['assignment']);
    }

    /**
     * Make sure for a disabled feature we return an empty 'items' element and don't throw an exception.
     *
     * @return void
     */
    public function test_get_content_items_for_disabled_feature(): void {
        self::setAdminUser();
        [$activity1, $section1, $element1, $section_element1] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element(['content_type' => 'totara_competency']);
        [$user1, $subject_instance1, $participant_instance1, $participant_section1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1, 'section' => $section1,
        ]);

        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;

        $content_items1 = linked_review_content::create_multiple(
            [$content_id1, $content_id2],
            $section_element1->id,
            $participant_instance1->id, false
        );

        self::setUser($user1);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        // With competencies enabled, there should be items in the result.
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(2, $result['items']);

        // With competencies disabled, items field should be empty.
        advanced_feature::disable('competencies');
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(0, $result['items']);
    }

    public function test_error_cases() {
        self::setAdminUser();
        [$activity1, $section1, $element1, $section_element1] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$activity2, $section2, $element2, $section_element2] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user1, $subject_instance1, $participant_instance1, $participant_section1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1, 'section' => $section1,
        ]);
        [$user2, $subject_instance2, $participant_instance2, $participant_section2] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity2, 'section' => $section2,
        ]);

        $element3 = perform_generator::instance()->create_element(['title' => 'Another one']);
        $section_element3 = perform_generator::instance()->create_section_element($section1, $element3);

        $content_id1 = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;
        $content_id2 = linked_review_generator::instance()->create_competency_assignment(['user' => $user2])->id;

        $content_items1 = linked_review_content::create_multiple(
            [$content_id1],
            $section_element1->id,
            $participant_instance1->id, false
        );

        $content_items2 = linked_review_content::create_multiple(
            [$content_id2],
            $section_element1->id,
            $participant_instance2->id, false
        );

        self::setUser($user1);

        // Try section_element not part of given participant instance
        $args = [
            'section_element_id' => $section_element2->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        try {
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('User does not participate in the section with ID', $exception->getMessage());
        }

        // Test non existing section_element
        try {
            $args['section_element_id'] = 666;
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (record_not_found_exception $exception) {
            $this->assertStringContainsString('Can not find data record in database', $exception->getMessage());
        }

        // Test non-linked_review section_element
        try {
            $args['section_element_id'] = $section_element3->id;
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('Invalid section element ID: '.$section_element3->id, $exception->getMessage());
        }

        // Given participant section does not match
        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section2->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        try {
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('User does not participate in the section with ID', $exception->getMessage());
        }

        // Test user is not a participant

        self::setUser($user2);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        try {
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('User does not participate in the section with ID', $exception->getMessage());
        }
    }

    public function test_external_participant() {
        self::setAdminUser();
        [$activity1, $section1, $element1, $section_element1] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$activity2, $section2, $element2, $section_element2] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user1, $subject_instance1, $participant_instance1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1, 'section' => $section1,
        ]);
        [$user2, $subject_instance2, $participant_instance2] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity2, 'section' => $section2,
        ]);

        $content_id = linked_review_generator::instance()->create_competency_assignment(['user' => $user1])->id;

        $content_items = linked_review_content::create_multiple(
            [$content_id],
            $section_element1->id,
            $participant_instance1->id, false
        );

        perform_generator::instance()->create_section_relationship(
            $section1,
            ['relationship' => constants::RELATIONSHIP_EXTERNAL]
        );

        /** @var participant_instance_entity $external_participant_instance1 */
        [$external_participant_instance1] = perform_generator::instance()->generate_external_participant_instances(
            $subject_instance1->id,
            [
                'fullname' => 'External user',
                'email' => 'mytest@example.com',
            ]
        );

        $participant_section1 = $external_participant_instance1->participant_sections->first();

        perform_generator::instance()->create_section_relationship(
            $section2,
            ['relationship' => constants::RELATIONSHIP_EXTERNAL]
        );

        [$external_participant_instance2] = perform_generator::instance()->generate_external_participant_instances(
            $subject_instance2->id,
            [
                'fullname' => 'Other External user',
                'email' => 'anothertest@example.com',
            ]
        );

        /** @var participant_instance $external_participant_instance1 */
        $external_participant_instance1 = participant_instance::load_by_entity($external_participant_instance1);
        /** @var participant_instance $external_participant_instance2 */
        $external_participant_instance2 = participant_instance::load_by_entity($external_participant_instance2);

        $token1 = $external_participant_instance1->get_participant()->get_user()->token;
        $token2 = $external_participant_instance2->get_participant()->get_user()->token;
        $this->assertTrue(strlen($token1) > 0);

        $this->setUser(null);

        // Missing token should throw an exception
        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $participant_instance1->subject_instance_id,
        ];

        try {
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (require_login_exception $exception) {
            $this->assertStringContainsString('You are not logged in', $exception->getMessage());
        }

        // Now try with invalid token
        try {
            $args['token'] = 'abcdefg';
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('Token validation for external participant failed', $exception->getMessage());
        }

        // Now try with token from a different instance
        try {
            $args['token'] = $token2;
            $result = $this->resolve_graphql_query(self::QUERY, $args);
            $this->fail('expected query to fail');
        } catch (coding_exception $exception) {
            $this->assertStringContainsString('Invalid subject instance for given token', $exception->getMessage());
        }

        // Now try the successful one
        $args['token'] = $token1;

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);
    }

    public function test_user_who_can_report_on_responses_can_load_the_items() {
        self::setAdminUser();
        [$activity, $section, $element, $section_element] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$user, $subject_instance, $participant_instance] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity, 'section' => $section,
        ]);

        $reporting_user = generator::instance()->create_user();

        $this->assign_reporter_cap_over_subject('mod/perform:report_on_subject_responses', $reporting_user, $user);

        $content_id = linked_review_generator::instance()->create_competency_assignment(['user' => $user->id])->id;

        $content_items = linked_review_content::create_multiple(
            [$content_id],
            $section_element->id,
            $participant_instance->id, false
        );

        self::setUser($reporting_user);

        $args = [
            'section_element_id' => $section_element->id,
            'subject_instance_id' => $participant_instance->subject_instance_id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);
    }

    public function test_get_content_items_with_rating_enabled() {
        self::setAdminUser();

        $subject_relationship = relationship::load_by_idnumber(constants::RELATIONSHIP_SUBJECT);
        $manager_relationship = relationship::load_by_idnumber(constants::RELATIONSHIP_MANAGER);
        $appraiser_relationship = relationship::load_by_idnumber(constants::RELATIONSHIP_APPRAISER);

        [$activity1, $section1, $element1, $section_element1] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element([
                'content_type_settings' => [
                    'enable_rating' => true,
                    'rating_relationship' => $subject_relationship->id
                ]
            ]);
        [$activity2, $section2, $element2, $section_element2] = linked_review_generator::instance()
            ->create_activity_with_section_and_review_element();
        [$subject, $subject_instance1, $participant_instance1, $participant_section1] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1,
            'section' => $section1,
            'create_section_relationship' => false,
        ]);
        [$manager, $subject_instance1, $participant_instance2, $participant_section2] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1,
            'section' => $section1,
            'subject_instance' => $subject_instance1,
            'relationship' => $manager_relationship,
            'create_section_relationship' => false,
        ]);
        [$appraiser, $subject_instance1, $participant_instance3, $participant_section3] = linked_review_generator::instance()->create_participant_in_section([
            'activity' => $activity1,
            'section' => $section1,
            'subject_instance' => $subject_instance1,
            'relationship' => $appraiser_relationship,
            'create_section_relationship' => false,
        ]);

        perform_generator::instance()->create_section_relationship(
            $section1,
            ['relationship' => constants::RELATIONSHIP_SUBJECT],
            true,
            true
        );

        perform_generator::instance()->create_section_relationship(
            $section1,
            ['relationship' => constants::RELATIONSHIP_MANAGER],
            true,
            true
        );

        perform_generator::instance()->create_section_relationship(
            $section1,
            ['relationship' => constants::RELATIONSHIP_APPRAISER],
            false,
            true
        );

        $assignment1 = linked_review_generator::instance()->create_competency_assignment(['user' => $subject]);
        $content_id1 = $assignment1->id;

        $content_items1 = linked_review_content::create_multiple(
            [$content_id1],
            $section_element1->id,
            $participant_instance1->id, false
        );

        $this->setUser($subject);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items1->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items1->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $this->assertInstanceOf(linked_review_content::class, $item);
        $content = $item->get_content();
        $this->assertTrue($content['can_rate']);
        $this->assertTrue($content['can_view_rating']);
        $this->assertNull($content['rating']);

        // Now as the manager
        $this->setUser($manager);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section2->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items1->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items1->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $this->assertInstanceOf(linked_review_content::class, $item);
        $content = $item->get_content();
        $this->assertFalse($content['can_rate']);
        $this->assertTrue($content['can_view_rating']);
        $this->assertNull($content['rating']);

        // Now as an appraiser who cannot see others responses
        $this->setUser($appraiser);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section3->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items1->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items1->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $this->assertInstanceOf(linked_review_content::class, $item);
        $content = $item->get_content();
        $this->assertFalse($content['can_rate']);
        $this->assertFalse($content['can_view_rating']);
        $this->assertNull($content['rating']);

        $expected_assignment1 = assignment::load_by_id($assignment1->id);

        $expected_scale_values1 = $expected_assignment1
            ->get_assignment_specific_scale()
            ->values
            ->sort('sortorder', 'asc', false);
        $rating_created_at = time();
        $rating_scale_value = $expected_scale_values1->first();

        $this->setUser($subject);

        // Now give a rating
        perform_rating::create(
            $assignment1->competency_id,
            $rating_scale_value->id,
            $participant_instance1->id,
            $section_element1->id,
            $rating_created_at
        );

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $expected_rating = [
            'created_at' => trim(date('j F Y', $rating_created_at)),
            'rater_user' => [
                'fullname' => fullname($subject),
            ],
            'scale_value' => [
                'name' => $rating_scale_value->name,
                'id' => $rating_scale_value->id
            ]
        ];

        $content = $item->get_content();
        $this->assertFalse($content['can_rate']);
        $this->assertTrue($content['can_view_rating']);
        $this->assertEquals($expected_rating, $content['rating']);

        // Now as the manager
        $this->setUser($manager);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section2->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $content = $item->get_content();
        $this->assertFalse($content['can_rate']);
        $this->assertTrue($content['can_view_rating']);
        $this->assertEquals($expected_rating, $content['rating']);

        // Now as an appraiser who cannot see others responses
        $this->setUser($appraiser);

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section3->id,
            'subject_instance_id' => $subject_instance1->id,
        ];

        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertArrayHasKey('items', $result);
        $this->assertCount($content_items1->count(), $result['items']);
        $this->assertContainsOnlyInstancesOf(linked_review_content::class, $result['items']);
        $actual_ids = array_column($result['items'], 'id');
        $expected_ids = $content_items1->pluck('id');
        $this->assertEqualsCanonicalizing($expected_ids, $actual_ids);

        /** @var linked_review_content $item */
        $item = array_shift($result['items']);

        $this->assertInstanceOf(linked_review_content::class, $item);
        $content = $item->get_content();
        $this->assertFalse($content['can_rate']);
        $this->assertFalse($content['can_view_rating']);
        $this->assertNull($content['rating']);
    }

    public function test_check_content_item_for_can_remove_field(): void {
        [$linked_review, $assignment, $subject_pi, , $section_element1, $participant_section1] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Competency has not been rated so can remove.
        $competency_id = $assignment->competency_id;
        $rating = perform_rating::get_existing_rating(
            $competency_id, $subject_pi->subject_instance_id
        );

        $args = [
            'section_element_id' => $section_element1->id,
            'participant_section_id' => $participant_section1->id,
            'subject_instance_id' => $subject_pi->subject_instance_id,
        ];
        // Operate
        $result = $this->resolve_graphql_query(self::QUERY, $args);

        // Assert
        self::assertTrue(isset($result['items'][0]->can_remove));
        self::assertTrue($result['items'][0]->can_remove);

        // Rated, now cannot remove.
        perform_rating::create(
            $competency_id,
            null,
            $subject_pi->id,
            $linked_review->section_element_id,
        );

        $result = $this->resolve_graphql_query(self::QUERY, $args);

        // Assert
        self::assertFalse($result['items'][0]->can_remove);
    }

    public function test_check_can_add_field(): void {
        [, , $spi, $ppi, $section_element, $participant_section] = $this->setup_env();
        $this->setUser($spi->participant_user->to_record());

        // Manager has not started so can still add more linked review content
        $args = [
            'section_element_id' => $section_element->id,
            'participant_section_id' => $participant_section->id,
            'subject_instance_id' => $spi->subject_instance_id,
        ];
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertTrue($result['can_add'] ?? false);

        // Manager looks at PA.
        $this->set_type_independent_conditions(
            $ppi, $section_element->section_id, pi_in_progress::class
        );

        // Now cannot add more linked review content
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        self::assertFalse($result['can_add'] ?? true);
    }

    /**
     * @param string $cap
     * @param stdClass $reporter
     * @param stdClass $subject
     */
    private function assign_reporter_cap_over_subject(string $cap, stdClass $reporter, stdClass $subject): void {
        $reporter_role_id = create_role(
            'Perform Reporter Role',
            'perform_reporter_role',
            'Can report on perform data'
        );

        $system_context = context_system::instance();
        assign_capability(
            $cap,
            CAP_ALLOW,
            $reporter_role_id,
            $system_context
        );

        generator::instance()->role_assign(
            $reporter_role_id,
            $reporter->id,
            context_user::instance($subject->id)
        );
    }

    private function setup_env(): array {
        self::setAdminUser();

        $data = [
            'content_type' => competency_assignment::get_identifier(),
            'content_type_settings' => [
                'enable_rating' => true,
                'rating_relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_SUBJECT
                )->id
            ],
        ];

        $lrc_generator = lrc_generator::instance();
        [$activity, $section, , $section_element] = $lrc_generator
            ->create_activity_with_section_and_review_element($data);

        // Set a subject as a participant in the activity.
        [, $si, $spi, $participant_section] = $lrc_generator->create_participant_in_section(
            ['activity' => $activity, 'section' => $section]
        );

        // Set another user as a manager participant in the activity.
        [, , $ppi, ] = $lrc_generator->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $si,
                'relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_MANAGER
                )
            ]
        );

        $competency_generator = competency_generator::instance();
        $competency = $competency_generator->create_competency();
        $competency_assignment = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency->id, $spi->participant_id);

        // Note this test is for removing already selected content; it does not
        // matter who originally "selected" the competency. What matters is the
        // subject is "recorded" as being the selector. Hence the false as the
        // last parameter.
        $content = linked_review_content::create(
            $competency_assignment->id, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $spi = $this->set_type_independent_conditions(
            $spi, $section_id, pi_in_progress::class
        );

        $ppi = $this->set_type_independent_conditions(
            $ppi, $section_id, pi_not_started::class
        );

        return [$content, $competency_assignment, $spi, $ppi, $section_element, $participant_section];
    }

    /**
     * Sets up the content type independent conditions so that they do not trip
     * for the tests in this file.
     *
     * See performelement_linked_review\helper\lifecycle\removal_conditions for
     * content type agnostic conditions that have to be fulfilled first.
     *
     * @param participant_instance_entity $pi participant instance to update.
     * @param int $section_id section for which to set the availability.
     * @param string $progress_class new progress to set.
     *
     * @return participant_instance the updated participant instance.
     */
    private function set_type_independent_conditions(
        participant_instance_entity $pi,
        int $section_id,
        string $progress_class
    ): participant_instance_entity {
        $pi->participant_sections
            ->filter(
                fn (participant_section $ps): bool =>
                    (int)$ps->section_id === $section_id
            )
            ->map(
                fn (participant_section $ps): participant_section => $ps
                    ->set_attribute('availability', open::get_code())
                    ->save()
                    ->refresh()
            );

        return $pi
            ->set_attribute('progress', $progress_class::get_code())
            ->save()
            ->refresh();
    }
}
