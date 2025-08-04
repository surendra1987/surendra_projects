<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use core\orm\query\builder;
use totara_core\advanced_feature;
use perform_goal\settings_helper;
use hierarchy_goal\entity\personal_goal as personal_goal_entity;
use hierarchy_goal\entity\company_goal as company_goal_entity;
use totara_hierarchy\testing\generator as hierarchy_generator;

/** @var core_config $CFG */
global $CFG;
require_once($CFG->dirroot . '/perform/goal/db/upgradelib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/goal/lib.php');

class perform_goal_upgrade_admin_subsystems_perform_setting_test extends testcase {


    private const PERFORM_FLAVOURS = ['learn_perform', 'perform_engage', 'perform', 'learn_perform_engage'];
    private const NON_PERFORM_FLAVOURS = ['learn', 'learn_professional', 'engage', 'learn_engage'];

    /**
     * Test if there are no legacy records, set to totara goals enabled
     */
    public function test_goals_none_exist_and_totara_goals_enabled() {

        perform_goal_upgrade_admin_subsystems_perform_setting();
        $config = get_config('perform_goal');

        self::assertFalse(settings_helper::is_legacy_goals_enabled());
        self::assertTrue(settings_helper::is_perform_goals_enabled());
        self::assertSame(settings_helper::TOTARA_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test if there are legacy company records & legacy goals is enabled
     */
    public function test_company_goals_exist_and_legacy_goals_enabled() {
        self::setAdminUser();
        advanced_feature::enable('goals');

        $generator = self::getDataGenerator();
        /** @var hierarchy_generator $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
        $framework = $hierarchy_generator->create_goal_frame(['name' => 'frame1']);
        $hierarchy_generator->create_goal(
            ['fullname' => 'goal1', 'frameworkid' => $framework->id, 'targetdate' => time()]
        );

        self::assertTrue(builder::table(company_goal_entity::TABLE)->exists());

        perform_goal_upgrade_admin_subsystems_perform_setting();
        $config = get_config('perform_goal');

        self::assertTrue(settings_helper::is_legacy_goals_enabled());
        self::assertFalse(settings_helper::is_perform_goals_enabled());
        self::assertSame(settings_helper::LEGACY_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test if there are legacy personal records & legacy goals is enabled
     */
    public function test_personal_goals_exist_and_legacy_goals_enabled() {
        self::setAdminUser();
        advanced_feature::enable('goals');

        $generator = self::getDataGenerator();

        /** @var hierarchy_generator $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');

        $hierarchy_generator->create_personal_goal(
            $generator->create_user()->id, ['name'  => 'My goal']);

        self::assertTrue(builder::table(personal_goal_entity::TABLE)->exists());

        perform_goal_upgrade_admin_subsystems_perform_setting();
        $config = get_config('perform_goal');

        self::assertTrue(settings_helper::is_legacy_goals_enabled());
        self::assertFalse(settings_helper::is_perform_goals_enabled());
        self::assertSame(settings_helper::LEGACY_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test if there are legacy company and personal records & legacy goals is enabled
     */
    public function test_company_personal_goals_exist_and_legacy_goals_enabled() {
        self::setAdminUser();
        advanced_feature::enable('goals');

        $generator = self::getDataGenerator();

        /** @var hierarchy_generator $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');

        $hierarchy_generator->create_personal_goal(
            $generator->create_user()->id, ['name'  => 'My goal']);
        $framework = $hierarchy_generator->create_goal_frame(['name' => 'frame1']);
        $hierarchy_generator->create_goal(
            ['fullname' => 'goal1', 'frameworkid' => $framework->id, 'targetdate' => time()]
        );

        self::assertTrue(builder::table(company_goal_entity::TABLE)->exists());
        self::assertTrue(builder::table(personal_goal_entity::TABLE)->exists());

        perform_goal_upgrade_admin_subsystems_perform_setting();
        $config = get_config('perform_goal');

        self::assertTrue(settings_helper::is_legacy_goals_enabled());
        self::assertFalse(settings_helper::is_perform_goals_enabled());
        self::assertSame(settings_helper::LEGACY_GOALS, (int)$config->goals_choice);
    }

    /**
     * Test if there are legacy records & legacy goals is disabled
     */
    public function test_goals_exist_and_goals_disabled() {
        self::setAdminUser();
        advanced_feature::enable('goals');

        $generator = self::getDataGenerator();

        /** @var hierarchy_generator $hierarchy_generator */
        $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');

        $hierarchy_generator->create_personal_goal(
            $generator->create_user()->id, ['name'  => 'My goal']);
        $framework = $hierarchy_generator->create_goal_frame(['name' => 'frame1']);
        $hierarchy_generator->create_goal(
            ['fullname' => 'goal1', 'frameworkid' => $framework->id, 'targetdate' => time()]
        );

        self::assertTrue(builder::table(company_goal_entity::TABLE)->exists());
        self::assertTrue(builder::table(personal_goal_entity::TABLE)->exists());

        advanced_feature::disable('goals');

        perform_goal_upgrade_admin_subsystems_perform_setting();
        $config = get_config('perform_goal');

        self::assertFalse(settings_helper::is_legacy_goals_enabled());
        self::assertFalse(settings_helper::is_perform_goals_enabled());
        self::assertSame(settings_helper::NO_GOALS, (int)$config->goals_choice);
    }

    /**
     * @return array[]
     */
    public static function fix_upgrade_data_provider_for_perform_flavours(): array {
        $disabled = advanced_feature::DISABLED;
        $enabled = advanced_feature::ENABLED;
        $no_goals = settings_helper::NO_GOALS;
        $legacy_goals = settings_helper::LEGACY_GOALS;
        $perform_goals = settings_helper::TOTARA_GOALS;
        $transition_mode = settings_helper::TOTARA_GOALS_TRANSITION_MODE;

        return [
            // Make sure for perform sites the upgrade doesn't change a setting.
            [$no_goals, false, $no_goals, $disabled, $disabled],
            [$no_goals, true, $no_goals, $disabled, $disabled],
            [$legacy_goals, false, $legacy_goals, $enabled, $disabled],
            [$legacy_goals, true, $legacy_goals, $enabled, $disabled],
            [$perform_goals, false, $perform_goals, $disabled, $enabled],
            [$perform_goals, true, $perform_goals, $disabled, $enabled],
            [$transition_mode, false, $transition_mode, $enabled, $enabled],
            [$transition_mode, true, $transition_mode, $enabled, $enabled],
        ];
    }

    /**
     * @return array[]
     */
    public static function fix_upgrade_data_provider_for_non_perform_flavours(): array {
        $disabled = advanced_feature::DISABLED;
        $enabled = advanced_feature::ENABLED;
        $no_goals = settings_helper::NO_GOALS;
        $legacy_goals = settings_helper::LEGACY_GOALS;
        $perform_goals = settings_helper::TOTARA_GOALS;
        $transition_mode = settings_helper::TOTARA_GOALS_TRANSITION_MODE;

        return [
            // Make sure for non-perform sites the upgrade disables perform goals only.
            [$no_goals, false, $no_goals, $disabled, $disabled],
            [$no_goals, true, $no_goals, $disabled, $disabled],
            [$legacy_goals, false, $legacy_goals, $enabled, $disabled],
            [$legacy_goals, true, $legacy_goals, $enabled, $disabled],
            [$perform_goals, false, $no_goals, $disabled, $disabled],
            [$perform_goals, true, $no_goals, $disabled, $disabled],
            [$transition_mode, false, $no_goals, $disabled, $disabled],
            [$transition_mode, true, $legacy_goals, $enabled, $disabled],
        ];
    }

    /**
     * @dataProvider fix_upgrade_data_provider_for_perform_flavours
     * @param int $goals_choice
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @return void
     */
    public function test_fix_upgrade_for_perform_flavours(
        int $goals_choice,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting
    ): void {
        $this->assert_settings_for_flavours(
            $goals_choice,
            $create_legacy_goals,
            $expected_goals_choice,
            $expected_legacy_goals_setting,
            $expected_perform_goals_setting,
            static::PERFORM_FLAVOURS
        );
    }

    /**
     * @dataProvider fix_upgrade_data_provider_for_non_perform_flavours
     * @param int $goals_choice
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @return void
     */
    public function test_fix_upgrade_for_non_perform_flavours(
        int $goals_choice,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting
    ): void {
        $this->assert_settings_for_flavours(
            $goals_choice,
            $create_legacy_goals,
            $expected_goals_choice,
            $expected_legacy_goals_setting,
            $expected_perform_goals_setting,
            static::NON_PERFORM_FLAVOURS
        );
    }

    /**
     * @param int $goals_choice
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @param array $flavours
     * @return void
     */
    private function assert_settings_for_flavours(
        int $goals_choice,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting,
        array $flavours
    ): void {
        global $CFG;
        foreach ($flavours as $flavour) {
            set_config('goals_choice', $goals_choice, 'perform_goal');
            settings_helper::advanced_features_callback();
            builder::table(personal_goal_entity::TABLE)->delete();
            if ($create_legacy_goals) {
                $generator = static::getDataGenerator();
                /** @var hierarchy_generator $hierarchy_generator */
                $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
                $hierarchy_generator->create_personal_goal($generator->create_user()->id, ['name'  => 'My goal']);
            }
            $CFG->forceflavour = $flavour;

            perform_goal_fix_goal_settings_for_non_perform_sites();

            static::assertEquals($expected_goals_choice, get_config('perform_goal', 'goals_choice'));
            static::assertEquals($expected_legacy_goals_setting, get_config(null, 'enablegoals'));
            static::assertEquals($expected_perform_goals_setting, get_config(null, 'enableperform_goals'));

            // Repeating the upgrade code should not change anything.
            perform_goal_fix_goal_settings_for_non_perform_sites();

            static::assertEquals($expected_goals_choice, get_config('perform_goal', 'goals_choice'));
            static::assertEquals($expected_legacy_goals_setting, get_config(null, 'enablegoals'));
            static::assertEquals($expected_perform_goals_setting, get_config(null, 'enableperform_goals'));
        }
    }

    /**
     * @return array[]
     */
    public static function init_settings_data_provider_for_perform_flavours(): array {
        $disabled = advanced_feature::DISABLED;
        $enabled = advanced_feature::ENABLED;
        $legacy_goals = settings_helper::LEGACY_GOALS;
        $perform_goals = settings_helper::TOTARA_GOALS;

        return [
            // Make sure for perform sites the component installation on upgrade enables perform goals by default.
            // Only when legacy goals are enabled and exist we leave legacy goals enabled.
            [$disabled, false, $perform_goals, $disabled, $enabled],
            [$disabled, true, $perform_goals, $disabled, $enabled],
            [$enabled, false, $perform_goals, $disabled, $enabled],
            [$enabled, true, $legacy_goals, $enabled, $disabled],
        ];
    }

    /**
     * @return array[]
     */
    public static function init_settings_data_provider_for_non_perform_flavours(): array {
        $disabled = advanced_feature::DISABLED;
        $enabled = advanced_feature::ENABLED;
        $no_goals = settings_helper::NO_GOALS;
        $legacy_goals = settings_helper::LEGACY_GOALS;

        return [
            // Make sure for non-perform sites the component installation on upgrade disables all goals by default.
            // Only when legacy goals are enabled and exist we leave legacy goals enabled.
            [$disabled, false, $no_goals, $disabled, $disabled],
            [$disabled, true, $no_goals, $disabled, $disabled],
            [$enabled, false, $no_goals, $disabled, $disabled],
            [$enabled, true, $legacy_goals, $enabled, $disabled],
        ];
    }

    /**
     * @dataProvider init_settings_data_provider_for_perform_flavours
     * @param int $legacy_goals_setting
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @return void
     */
    public function test_component_install_goal_settings_for_perform_flavours(
        int $legacy_goals_setting,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting,
    ): void {
        $this->assert_component_install_settings_for_flavours(
            $legacy_goals_setting,
            $create_legacy_goals,
            $expected_goals_choice,
            $expected_legacy_goals_setting,
            $expected_perform_goals_setting,
            static::PERFORM_FLAVOURS
        );
    }

    /**
     * @dataProvider init_settings_data_provider_for_non_perform_flavours
     * @param int $legacy_goals_setting
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @return void
     */
    public function test_component_install_goal_settings_for_non_perform_flavours(
        int $legacy_goals_setting,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting,
    ): void {
        $this->assert_component_install_settings_for_flavours(
            $legacy_goals_setting,
            $create_legacy_goals,
            $expected_goals_choice,
            $expected_legacy_goals_setting,
            $expected_perform_goals_setting,
            static::NON_PERFORM_FLAVOURS
        );
    }

    /**
     * @param int $legacy_goals_setting
     * @param bool $create_legacy_goals
     * @param int $expected_goals_choice
     * @param int $expected_legacy_goals_setting
     * @param int $expected_perform_goals_setting
     * @param array $flavours
     * @return void
     */
    private function assert_component_install_settings_for_flavours(
        int $legacy_goals_setting,
        bool $create_legacy_goals,
        int $expected_goals_choice,
        int $expected_legacy_goals_setting,
        int $expected_perform_goals_setting,
        array $flavours
    ): void {
        global $CFG;
        foreach ($flavours as $flavour) {
            set_config('enablegoals', $legacy_goals_setting);
            builder::table(personal_goal_entity::TABLE)->delete();
            if ($create_legacy_goals) {
                $generator = static::getDataGenerator();
                /** @var hierarchy_generator $hierarchy_generator */
                $hierarchy_generator = $generator->get_plugin_generator('totara_hierarchy');
                $hierarchy_generator->create_personal_goal($generator->create_user()->id, ['name'  => 'My goal']);
            }
            $CFG->forceflavour = $flavour;

            perform_goal_initialise_goal_settings();

            static::assertEquals($expected_goals_choice, get_config('perform_goal', 'goals_choice'));
            static::assertEquals($expected_legacy_goals_setting, get_config(null, 'enablegoals'));
            static::assertEquals($expected_perform_goals_setting, get_config(null, 'enableperform_goals'));

            // Repeating the upgrade code should not change anything.
            perform_goal_initialise_goal_settings();

            static::assertEquals($expected_goals_choice, get_config('perform_goal', 'goals_choice'));
            static::assertEquals($expected_legacy_goals_setting, get_config(null, 'enablegoals'));
            static::assertEquals($expected_perform_goals_setting, get_config(null, 'enableperform_goals'));
        }
    }
}
