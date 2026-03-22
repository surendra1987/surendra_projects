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
 * @package block_totara_recommendations
 */

use block_totara_recommendations\entity\totara_recommendations_trending;
use core\orm\collection;
use core_phpunit\testcase;
use totara_core\advanced_feature;
use totara_engage\access\access;
use totara_engage\answer\answer_type;

defined('MOODLE_INTERNAL') || die();

/**
 * @group block_totara_recommendations
 */
class block_totara_recommendations_totara_recommendations_trending_repository_test extends testcase {
    /**
     * Assert the general storage and truncation functions work.
     *
     * @return void
     */
    public function test_store_and_truncate(): void {
        global $DB;
        $DB->delete_records(totara_recommendations_trending::TABLE);
        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(0, $count);

        $trending = new totara_recommendations_trending();
        $trending->item_id = 1;
        $trending->component = 'mock_component';
        $trending->area = null;
        $trending->counter = 5;
        $trending->time_created = time();
        $trending->unique_id = 'abc';
        $trending2 = clone $trending;

        $repo = totara_recommendations_trending::repository();
        $repo->store_trending_items(collection::new([$trending]));

        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(1, $count);

        // Add again
        $trending2->unique_id = 'def';
        $trending2->component = 'mock_component_2';

        $repo->store_trending_items(collection::new([$trending2]));

        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(2, $count);

        $repo->delete_for_component('mock_component', 1);

        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(1, $count);

        // Truncate
        $repo->truncate_totara_recommendations_trending();
        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(0, $count);
    }

    /**
     * Assert that trending content obeys the tenant rules.
     *
     * @return void
     */
    public function test_trending_content_with_tenants(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        /** @var totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $feature = advanced_feature::is_enabled('engage_resources');
        advanced_feature::enable('engage_resources');

        [$tenant_a, $tenant_b, $system] = array_values($this->generate_tenant_data());

        // Confirm we have 9 trending items record
        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(9, $count);

        // Scenario: Isolation is disabled. Tenants can see T + System, but not each other.
        set_config('tenantsisolated', 0);

        // Tenant A member can see System + TA and not TB
        $trending = $this->with_user($tenant_a->member, 6);
        $this->assert_trending([$tenant_a, $system], [$tenant_b], $trending);

        // Tenant A participant can see System + TA + TB
        $trending = $this->with_user($tenant_a->participant, 9);
        $this->assert_trending([$tenant_a, $system, $tenant_b], [], $trending);

        // Tenant B member can see System + TB and not TA
        $trending = $this->with_user($tenant_b->member, 6);
        $this->assert_trending([$tenant_b, $system], [$tenant_a], $trending);

        // Tenant B participant can see System + TB + TA
        $trending = $this->with_user($tenant_b->participant, 9);
        $this->assert_trending([$tenant_b, $system, $tenant_a], [], $trending);

        // System participant in both can see A + B + System
        $trending = $this->with_user($system->both, 9);
        $this->assert_trending([$tenant_a, $tenant_b, $system], [], $trending);

        // System non-participant can see A + B + System
        $trending = $this->with_user($system->none, 9);
        $this->assert_trending([$tenant_a, $tenant_b, $system], [], $trending);

        // Scenario: Isolation is on. Tenant members can see themselves only, System cannot see tenants unless participating.
        set_config('tenantsisolated', 1);

        // Tenant A member can see TA and not TB or system
        $trending = $this->with_user($tenant_a->member, 3);
        $this->assert_trending([$tenant_a], [$tenant_b, $system], $trending);

        // Tenant A participant can see System + TA and not TB
        $trending = $this->with_user($tenant_a->participant, 6);
        $this->assert_trending([$tenant_a, $system], [$tenant_b], $trending);

        // Tenant B member can see TB and not TA or system
        $trending = $this->with_user($tenant_b->member, 3);
        $this->assert_trending([$tenant_b], [$tenant_a, $system], $trending);

        // Tenant B participant can see System + TB but not TA
        $trending = $this->with_user($tenant_b->participant, 6);
        $this->assert_trending([$tenant_b, $system], [$tenant_a], $trending);

        // System participant in both can see A + B + System
        $trending = $this->with_user($system->both, 9);
        $this->assert_trending([$tenant_a, $tenant_b, $system], [], $trending);

        // System non-participant can see System but no A or B
        $trending = $this->with_user($system->none, 3);
        $this->assert_trending([$system], [$tenant_a, $tenant_b], $trending);

        // Engage is off, nobody sees anything
        advanced_feature::disable('engage_resources');
        $this->with_user($system->none, 0);
        $this->with_user($system->both, 0);
        $this->with_user($tenant_a->member, 0);
        $this->with_user($tenant_a->participant, 0);
        $this->with_user($tenant_b->member, 0);
        $this->with_user($tenant_b->participant, 0);

        // Reset flags back to normal
        set_config('tenantsisolated', 0);
        if ($feature) {
            advanced_feature::enable('engage_resources');
        } else {
            advanced_feature::disable('engage_resources');
        }
    }

    /**
     * Confirm trending returns what is expected without tenants.
     *
     * @return void
     */
    public function test_trending_content_with_no_tenants(): void {
        $generator = $this->getDataGenerator();
        /** @var totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        /** @var totara_playlist\testing\generator $playlist_generator */
        $playlist_generator = $generator->get_plugin_generator('totara_playlist');
        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $generator->get_plugin_generator('engage_article');
        /** @var engage_survey\testing\generator $survey_generator */
        $survey_generator = $generator->get_plugin_generator('engage_survey');

        $tenant_generator->disable_tenants();

        /** @var ml_recommender\testing\generator $recommender_generator */
        $recommender_generator = $generator->get_plugin_generator('ml_recommender');
        $recommender_generator->clear_trending();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        // Create each item and mark it as trending
        $playlist = $playlist_generator->create_public_playlist(['userid' => $user1->id]);
        $article = $article_generator->create_public_article(['userid' => $user1->id]);
        $survey = $survey_generator->create_survey(null, [], answer_type::MULTI_CHOICE, ['userid' => $user1->id, 'access' => access::PUBLIC]);

        $recommender_generator->create_trending_recommendation($playlist->get_id(), $playlist::get_resource_type(), null, 1);
        $recommender_generator->create_trending_recommendation($article->get_id(), $article::get_resource_type(), null, 1);
        $recommender_generator->create_trending_recommendation($survey->get_id(), $survey::get_resource_type(), null, 1);

        $trending = $this->with_user($user2, 3);
        $this->assertArrayHasKey($playlist::get_resource_type() . $playlist->get_id(), $trending);
        $this->assertArrayHasKey($article::get_resource_type() . $article->get_id(), $trending);
        $this->assertArrayHasKey($survey::get_resource_type() . $survey->get_id(), $trending);
    }

    /**
     * Helper function to generate the data for tests
     *
     * @return array
     */
    private function generate_tenant_data(): array {
        $generator = $this->getDataGenerator();
        /** @var totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        /** @var totara_playlist\testing\generator $playlist_generator */
        $playlist_generator = $generator->get_plugin_generator('totara_playlist');
        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $generator->get_plugin_generator('engage_article');
        /** @var engage_survey\testing\generator $survey_generator */
        $survey_generator = $generator->get_plugin_generator('engage_survey');

        // Create tenants A & B + System users
        $data = [];
        foreach (['a', 'b', 's'] as $key) {
            $record = new stdClass();

            if ($key === 's') {
                $record->owner = $generator->create_user();
                $record->both = $generator->create_user(['tenantparticipant' => join(',', [$data['a']->tenant->idnumber, $data['b']->tenant->idnumber])]);
                $record->none = $generator->create_user();
            } else {
                $tenant = $tenant_generator->create_tenant();
                $record->tenant = $tenant;
                $record->member = $generator->create_user(['tenantmember' => $tenant->idnumber]);
                $record->owner = $generator->create_user(['tenantmember' => $tenant->idnumber]);
                $record->participant = $generator->create_user(['tenantparticipant' => $tenant->idnumber]);
            }

            $data[$key] = $record;
        }

        /** @var ml_recommender\testing\generator $recommender_generator */
        $recommender_generator = $generator->get_plugin_generator('ml_recommender');
        $recommender_generator->clear_trending();

        // Create each item and mark it as trending
        foreach ($data as $record) {
            $record->playlist = $playlist_generator->create_public_playlist(['userid' => $record->owner->id]);
            $record->article = $article_generator->create_public_article(['userid' => $record->owner->id]);
            $record->survey = $survey_generator->create_survey(null, [], answer_type::MULTI_CHOICE, ['userid' => $record->owner->id, 'access' => access::PUBLIC]);

            $recommender_generator->create_trending_recommendation($record->playlist->get_id(), $record->playlist::get_resource_type(), null, 1);
            $recommender_generator->create_trending_recommendation($record->article->get_id(), $record->article::get_resource_type(), null, 1);
            $recommender_generator->create_trending_recommendation($record->survey->get_id(), $record->survey::get_resource_type(), null, 1);
        }

        return $data;
    }

    /**
     * Assert that each of the $expected items are visible inside $haystack
     * and none of the $not_expected are in the haystack.
     *
     * @param array $expected
     * @param array $not_expected
     * @param array $haystack
     * @return void
     */
    private function assert_trending(array $expected, array $not_expected, array $haystack): void {
        foreach ($expected as $collection) {
            $this->assertArrayHasKey($collection->playlist::get_resource_type() . $collection->playlist->get_id(), $haystack);
            $this->assertArrayHasKey($collection->article::get_resource_type() . $collection->article->get_id(), $haystack);
            $this->assertArrayHasKey($collection->survey::get_resource_type() . $collection->survey->get_id(), $haystack);
        }
        foreach ($not_expected as $collection) {
            $this->assertArrayNotHasKey($collection->playlist::get_resource_type() . $collection->playlist->get_id(), $haystack);
            $this->assertArrayNotHasKey($collection->article::get_resource_type() . $collection->article->get_id(), $haystack);
            $this->assertArrayNotHasKey($collection->survey::get_resource_type() . $collection->survey->get_id(), $haystack);
        }
    }

    /**
     * Helper function, will call trending and assert the number of results are what are expected.
     *
     * @param stdClass $user
     * @param int $expected_trending
     * @return array
     */
    private function with_user(stdClass $user, int $expected_trending): array {
        $this->setUser($user);
        $repo = totara_recommendations_trending::repository();
        $trending = $repo->get_cached_trending_content(10);
        $this->assertCount($expected_trending, $trending);

        return $trending;
    }
}
