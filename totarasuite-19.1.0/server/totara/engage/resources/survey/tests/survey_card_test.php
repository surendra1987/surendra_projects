<?php
/**
 * This file is part of Totara Engage
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package engage_survey
 */

use core_phpunit\testcase;
use core_user\totara_engage\share\recipient\user;
use engage_survey\totara_engage\card\survey_card;
use totara_engage\card\card_loader;
use totara_engage\query\query;

defined('MOODLE_INTERNAL') || die();

class engage_survey_survey_card_test extends testcase {

    public function test_get_url(): void {
        // First we have to create a survey_card object.
        $generator = self::getDataGenerator();
        /** @var \engage_survey\testing\generator $survey_generator */
        $survey_generator = $generator->get_plugin_generator('engage_survey');
        $user = $generator->create_user();
        self::setUser($user);

        $public_survey = $survey_generator->create_public_survey();
        $survey_generator->share_survey($public_survey, [new user($user->id)]);

        $query = new query();
        $query->set_component('totara_engage');
        $query->set_area('shared');

        $loader = new card_loader($query);
        $result = $loader->fetch();
        $cards = $result->get_items();

        /** @var survey_card $survey_card */
        $survey_card = $cards->first();
        self::assertInstanceOf(survey_card::class, $survey_card);

        self::assertSame(
            'https://www.example.com/moodle/totara/engage/resources/survey/redirect.php?id=' . $public_survey->get_id() . '&source=ws.17.1',
            $survey_card->get_url(['source' => 'ws.17.1'])
        );

        // Check that previous sources are prepended to the source parameter.
        $_GET['source'] = 'lb.0__lb.4';
        self::assertSame(
            'https://www.example.com/moodle/totara/engage/resources/survey/redirect.php?id=' . $public_survey->get_id() . '&source=lb.0__lb.4__ws.17.1',
            $survey_card->get_url(['source' => 'ws.17.1'])
        );
    }
}