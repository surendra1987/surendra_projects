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
 * @author Cody Finegan <cody.finegan@totaralearning.com>
 * @package block_totara_recommendations
 */

use block_totara_recommendations\entity\totara_recommendations_trending;
use block_totara_recommendations\task\refresh_totara_trending_data_task;
use core_phpunit\testcase;
use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();

/**
 * @group block_totara_recommendations
 */
class block_totara_recommendations_trending_test extends testcase {
    /**
     * Assert that the refresh job correctly generates records.
     *
     * @return void
     */
    public function test_refresh_totara_trending_data_task(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        /** @var ml_recommender\testing\generator $ml_generator */
        $ml_generator = $this->getDataGenerator()->get_plugin_generator('ml_recommender');

        // Remove any interaction or trending records
        $ml_generator->clear_trending();
        $ml_generator->clear_recommender_interactions();

        // Confirm we see nothing
        $count = $DB->count_records(totara_recommendations_trending::TABLE);
        $this->assertSame(0, $count);

        // Disable resources and run the task
        advanced_feature::disable('engage_resources');
        $count = $this->execute_trending_task();
        $this->assertSame(0, $count);

        // Now enable resources again and run the task
        advanced_feature::enable('engage_resources');
        $count = $this->execute_trending_task();
        $this->assertSame(0, $count);

        /** @var engage_article\testing\generator $article_generator */
        $article_generator = $this->getDataGenerator()->get_plugin_generator('engage_article');
        $article1 = $article_generator->create_public_article();
        $article2 = $article_generator->create_public_article();

        $ml_generator->create_trending_recommendation($article1->get_id(), $article1::get_resource_type(), null, 100);

        // We still should see nothing
        $count = $this->execute_trending_task();
        $this->assertSame(0, $count);

        // Now add an interaction
        $ml_generator->create_recommender_interaction($user->id, $article1->get_id(), $article1::get_resource_type());

        // We should now see 1 record in trending
        $count = $this->execute_trending_task();
        $this->assertSame(1, $count);

        // Add a new interaction for another article
        $ml_generator->create_recommender_interaction($user->id, $article2->get_id(), $article2::get_resource_type());

        // We should now see 2 records in trending
        $count = $this->execute_trending_task();
        $this->assertSame(2, $count);

        // Confirm we have a name
        $task = new refresh_totara_trending_data_task();
        $name = $task->get_name();
        $this->assertNotEmpty($name);
    }

    /**
     * Executes the trending task and returns the number of records in the trending table.
     *
     * @return int
     */
    private function execute_trending_task(): int {
        global $DB;

        $task = new refresh_totara_trending_data_task();
        $task->execute();

        // Confirm we see nothing
        return $DB->count_records(totara_recommendations_trending::TABLE);
    }
}
