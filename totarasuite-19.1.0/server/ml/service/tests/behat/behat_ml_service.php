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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package ml_recommender
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat helper for ml recommender calls
 */
class behat_ml_service extends behat_base {
    /**
     * Enable the recommendation system.
     *
     * @Then /^(?:|I )enable ml_service/
     */
    public function enable_service() {
        \behat_hooks::set_step_readonly(false);

        \totara_core\advanced_feature::enable('ml_recommender');
        set_config('ml_service_key', 'BEHAT_ONLY_KEY');
        set_config('ml_service_url', 'https://www.example.com');
        \core_ml\settings_helper::enable_ml_plugin('recommender');
    }

    /**
     * Disable the recommendation system.
     *
     * @Then /^(?:|I )disable ml_service/
     */
    public function disable_service() {
        \behat_hooks::set_step_readonly(false);

        \core_ml\settings_helper::disable_ml_plugin('recommender');
        set_config('ml_service_url', 'http://localhost:5000');
        set_config('ml_service_key', '');
        \totara_core\advanced_feature::disable('ml_recommender');
    }

    /**
     * Cleanup the leftover recommender storage files whenever any @ml_service process finishes.
     *
     * @AfterScenario @ml_service
     */
    public function cleanup_temp_files(): void
    {
        \ml_service\testing\mock_client::reset();
    }
}