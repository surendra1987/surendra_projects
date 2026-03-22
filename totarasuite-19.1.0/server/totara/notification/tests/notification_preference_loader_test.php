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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package totara_notification
 */

use container_course\course;
use core\orm\query\builder;
use core_phpunit\testcase;
use mod_facetoface\testing\generator as seminar_generator;
use totara_core\extended_context;
use totara_notification\builder\notification_preference_builder;
use totara_notification\loader\notification_preference_loader;
use totara_notification\model\notification_preference;
use totara_notification\testing\generator as notification_generator;
use totara_notification_mock_built_in_notification as mock_built_in;
use totara_notification_mock_notifiable_event_resolver as mock_resolver;

class totara_notification_notification_preference_loader_test extends testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        $generator = notification_generator::instance();

        $generator->include_mock_notifiable_event_resolver();
        $generator->include_mock_complex_resolver();
        $generator->include_mock_built_in_notification();
    }

    public static function data_provider_notification_preferences_scenarios(): array {
        return [
            ['built_in', []],
            ['built_in', ['system']],
            ['built_in', ['system', 'course']],
            ['built_in', ['system', 'course', 'activity']],
            ['built_in', ['system', 'course', 'extended']],
            ['built_in', ['system', 'course', 'activity', 'extended']],
            ['built_in', ['system', 'activity']],
            ['built_in', ['system', 'activity', 'extended']],
            ['built_in', ['system', 'extended']],
            ['built_in', ['course']],
            ['built_in', ['course', 'activity']],
            ['built_in', ['course', 'extended']],
            ['built_in', ['course', 'activity', 'extended']],
            ['built_in', ['activity']],
            ['built_in', ['activity', 'extended']],
            ['built_in', ['extended']],
            ['system', []],
            ['system', ['course']],
            ['system', ['course', 'activity']],
            ['system', ['course', 'extended']],
            ['system', ['course', 'activity', 'extended']],
            ['system', ['activity']],
            ['system', ['activity', 'extended']],
            ['system', ['extended']],
            ['course', []],
            ['course', ['activity']],
            ['course', ['extended']],
            ['course', ['activity', 'extended']],
            ['activity', []],
            ['activity', ['extended']],
            ['extended', []],
        ];
    }

    private function create_control_preference(string $name, extended_context $extended_context, int $ancestor_id = null): notification_preference {
        $data = [
            'title' => 'title:' . $name,
            'additional_criteria' => json_encode(['criteria:' . $name, 'valid' => true]),
            'schedule_offset' => crc32('offset:' . $name),
            'subject' => 'subject:' . $name,
            'subject_format' => crc32('subject_format:' . $name) % 100,
            'body' => 'body:' . $name,
            'body_format' => crc32('body_format:' . $name) % 100,
            'notification_class_name' => null,
            'ancestor_id' => $ancestor_id,
        ];
        return notification_generator::instance()->create_notification_preference(
            totara_notification_mock_complex_resolver::class,
            $extended_context,
            $data
        );
    }

    /**
     * This test makes sure that get_notifiction_preferences returns the correct records. When $at_context_only is
     * true then it should only return records that exist in the given context. When $$at_context_only is false then
     * it should return one record for each "custom" or "built-in system" notification preference, but the actual
     * preference returned should be the override preference in the lowest-context if such an override exists, otherwise
     * it should just return the "custom" or "built-in system" record.
     *
     * This function also checks that all the get_xxxx properties of notification preferences return the correct
     * values, taking into account any overrides or non-overridden values, including getting the values from the
     * built-in notification where applicable.
     *
     * @dataProvider data_provider_notification_preferences_scenarios
     *
     * @param string $source_context_level
     * @param array $override_context_levels
     * @return void
     * @throws coding_exception
     */
    public function test_get_notification_preferences_and_preference_property_inheritance(string $source_context_level, array $override_context_levels): void {
        $system_extended_context = extended_context::make_system();
        $course = self::getDataGenerator()->create_course();
        $course_extended_context = extended_context::make_with_context(context_course::instance($course->id));
        $activity = seminar_generator::instance()->create_instance(['course' => $course->id]);
        $activity_extended_context = extended_context::make_with_context(context_module::instance($activity->cmid));
        $extended_extended_context = extended_context::make_with_context($activity_extended_context->get_context(), 'test_component', 'test_area', 123);

        //////////////////////////////////////////////////////////////////
        // Create the control data.

        // Standalone control preferences, one in each context level.
        $preference_control_standalone_built_in = notification_generator::instance()->create_notification_preference(
            totara_notification_mock_complex_resolver::class,
            $system_extended_context,
            [
                'notification_class_name' => 'control_standalone_notification_class_name',
            ]
        );

        $preference_control_standalone_system = $this->create_control_preference('control_standalone_system', $system_extended_context);
        $preference_control_standalone_course = $this->create_control_preference('control_standalone_course', $course_extended_context);
        $preference_control_standalone_activity = $this->create_control_preference('control_standalone_activity', $activity_extended_context);
        $preference_control_standalone_extended = $this->create_control_preference('control_standalone_extended', $extended_extended_context);

        // Standalone control preferences in off-path extended contexts.
        $this->create_control_preference(
            'control_standalone_system_extended',
            extended_context::make_with_context(context_system::instance(), 'test_component', 'test_area', 234)
        );
        $this->create_control_preference(
            'control_standalone_course_extended',
            extended_context::make_with_context(context_course::instance($course->id), 'test_component', 'test_area', 345)
        );
        $this->create_control_preference(
            'control_standalone_activity_extended',
            extended_context::make_with_context(context_module::instance($activity->cmid), 'test_component', 'test_area', 456)
        );

        // Override control preferences, one in each context level, all in one stack.
        $preference_control_override_built_in = notification_generator::instance()->create_notification_preference(
            totara_notification_mock_complex_resolver::class,
            $system_extended_context,
            [
                'notification_class_name' => 'control_override_notification_class_name',
            ]
        );

        $builder = notification_preference_builder::from_exist_model($preference_control_override_built_in);
        $builder->set_title('title:control_override_system');
        $builder->set_additional_criteria(json_encode(['criteria:control_override_system', 'valid' => true]));
        $builder->set_schedule_offset(crc32('offset:control_override_system'));
        $builder->set_subject('subject:control_override_system');
        $builder->set_subject_format(crc32('subject_format:control_override_system') % 100);
        $builder->set_body('body:control_override_system');
        $builder->set_body_format(crc32('body_format:control_override_system') % 100);
        $preference_control_override_system = $builder->save();

        $preference_control_override_course = $this->create_control_preference('control_override_course', $course_extended_context, $preference_control_override_built_in->get_id());
        $preference_control_override_activity = $this->create_control_preference('control_override_activity', $activity_extended_context, $preference_control_override_built_in->get_id());
        $preference_control_override_extended = $this->create_control_preference('control_override_extended', $extended_extended_context, $preference_control_override_built_in->get_id());

        // Override control preferences in off-path extended contexts.
        $this->create_control_preference(
            'control_override_system_extended',
            extended_context::make_with_context(context_system::instance(), 'test_component', 'test_area', 567),
            $preference_control_override_built_in->get_id()
        );
        $this->create_control_preference(
            'control_override_course_extended',
            extended_context::make_with_context(context_course::instance($course->id), 'test_component', 'test_area', 678),
            $preference_control_override_built_in->get_id()
        );
        $this->create_control_preference(
            'control_override_activity_extended',
            extended_context::make_with_context(context_module::instance($activity->cmid), 'test_component', 'test_area', 789),
            $preference_control_override_built_in->get_id()
        );

        //////////////////////////////////////////////////////////////////
        // Create the target preferences.

        // Create the target source preference.
        $preference_built_in = null;
        $preference_system = null;
        $preference_course = null;
        $preference_activity = null;
        $preference_extended = null;

        if ($source_context_level == 'built_in') {
            totara_notification_mock_built_in_notification::set_resolver_class_name(totara_notification_mock_complex_resolver::class);
            totara_notification_mock_built_in_notification::set_default_title('title:built_in');
            totara_notification_mock_built_in_notification::set_default_additional_criteria(json_encode(['criteria:built_in', 'valid' => true]));
            totara_notification_mock_built_in_notification::set_default_schedule_offset(crc32('offset:built_in'));
            totara_notification_mock_built_in_notification::set_default_subject(notification_generator::instance()->give_my_mock_lang_string('subject:built_in'));
            totara_notification_mock_built_in_notification::set_default_subject_format(crc32('subject_format:built_in') % 100);
            totara_notification_mock_built_in_notification::set_default_body(notification_generator::instance()->give_my_mock_lang_string('body:built_in'));
            totara_notification_mock_built_in_notification::set_default_body_format(crc32('body_format:built_in') % 100);
            $preference_built_in = $source_preference = notification_generator::instance()->create_notification_preference(
                totara_notification_mock_complex_resolver::class,
                $system_extended_context,
                [
                    'notification_class_name' => totara_notification_mock_built_in_notification::class,
                ]
            );
        } else {
            switch ($source_context_level) {
                case 'system':
                    $source_extended_context = $system_extended_context;
                    break;
                case 'course':
                    $source_extended_context = $course_extended_context;
                    break;
                case 'activity':
                    $source_extended_context = $activity_extended_context;
                    break;
                case 'extended':
                    $source_extended_context = $extended_extended_context;
                    break;
                default:
                    self::fail();
            }
            $data = [
                'title' => 'title:' . $source_context_level,
                'additional_criteria' => json_encode(['criteria:' . $source_context_level, 'valid' => true]),
                'schedule_offset' => crc32('offset:' . $source_context_level),
                'subject' => 'subject:' . $source_context_level,
                'subject_format' => crc32('subject_format:' . $source_context_level) % 100,
                'body' => 'body:' . $source_context_level,
                'body_format' => crc32('body_format:' . $source_context_level) % 100,
                'notification_class_name' => null,
            ];
            $new_preference = $source_preference = notification_generator::instance()->create_notification_preference(
                totara_notification_mock_complex_resolver::class,
                $source_extended_context,
                $data
            );
            switch ($source_context_level) {
                case 'system':
                    $preference_system = $new_preference;
                    break;
                case 'course':
                    $preference_course = $new_preference;
                    break;
                case 'activity':
                    $preference_activity = $new_preference;
                    break;
                case 'extended':
                    $preference_extended = $new_preference;
                    break;
                default:
                    self::fail();
            }
        }

        // Create the target override preferences (if any).
        foreach ($override_context_levels as $override_context_level) {
            switch ($override_context_level) {
                case 'system':
                    // An "override" in the system context must be modifying a built-in notification, and should be
                    // written into the existing preference record, rather than added as a new override.
                    $builder = notification_preference_builder::from_exist_model($preference_built_in);
                    $builder->set_title('title:system');
                    $builder->set_additional_criteria(json_encode(['criteria:system', 'valid' => true]));
                    $builder->set_schedule_offset(crc32('offset:system'));
                    $builder->set_subject('subject:system');
                    $builder->set_subject_format(crc32('subject_format:system') % 100);
                    $builder->set_body('body:system');
                    $builder->set_body_format(crc32('body_format:system') % 100);
                    $preference_system = $builder->save();
                    continue 2;
                case 'course':
                    $override_extended_context = $course_extended_context;
                    break;
                case 'activity':
                    $override_extended_context = $activity_extended_context;
                    break;
                case 'extended':
                    $override_extended_context = $extended_extended_context;
                    break;
                default:
                    self::fail();
            }
            $data = [
                'title' => 'title:' . $override_context_level,
                'additional_criteria' => json_encode(['criteria:' . $override_context_level, 'valid' => true]),
                'schedule_offset' => crc32('offset:' . $override_context_level),
                'subject' => 'subject:' . $override_context_level,
                'subject_format' => crc32('subject_format:' . $override_context_level) % 100,
                'body' => 'body:' . $override_context_level,
                'body_format' => crc32('body_format:' . $override_context_level) % 100,
                'notification_class_name' => $source_context_level == 'built_in' ? totara_notification_mock_built_in_notification::class : null,
                'ancestor_id' => $source_preference->get_id(),
            ];
            $new_preference = notification_generator::instance()->create_notification_preference(
                totara_notification_mock_complex_resolver::class,
                $override_extended_context,
                $data
            );
            switch ($override_context_level) {
                case 'course':
                    $preference_course = $new_preference;
                    break;
                case 'activity':
                    $preference_activity = $new_preference;
                    break;
                case 'extended':
                    $preference_extended = $new_preference;
                    break;
                default:
                    self::fail();
            }
        }

        //////////////////////////////////////////////////////////////////
        /// Extended context

        // Run the function with $at_context_only true.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $extended_extended_context,
            totara_notification_mock_complex_resolver::class,
            true
        );

        // Only records that exist in the context should be returned.
        $expected_preference_ids = [
            $preference_control_standalone_extended->get_id(),
            $preference_control_override_extended->get_id(),
        ];
        if ($preference_extended) {
            $expected_preference_ids[] = $preference_extended->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing($expected_preference_ids, $actual_preference_ids);

        // Run the function with $at_context_only false.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $extended_extended_context,
            totara_notification_mock_complex_resolver::class
        );

        // Inherited records should be included.
        $expected_preference_ids = [
            $preference_control_standalone_built_in->get_id(),
            $preference_control_standalone_system->get_id(),
            $preference_control_standalone_course->get_id(),
            $preference_control_standalone_activity->get_id(),
            $preference_control_standalone_extended->get_id(),
            $preference_control_override_extended->get_id(),
        ];
        // There will be a target preference if one was created in extended context or above.
        $expected_target_preference = $preference_extended ?? $preference_activity ?? $preference_course ?? $preference_system ?? $preference_built_in;
        $expected_preference_ids[] = $expected_target_preference->get_id(); // There is always a preference in this case.
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing(
            $expected_preference_ids,
            $actual_preference_ids
        );

        // Check the properties of the target preference.
        $actual_target_preferences = array_filter($actual_preferences, function ($preference) use ($expected_target_preference) {
            return $preference->get_id() == $expected_target_preference->get_id();
        });
        self::assertCount(1, $actual_target_preferences);
        $actual_target_preference = reset($actual_target_preferences);
        $expected_context_level = $preference_extended ? 'extended' : (
            $preference_activity ? 'activity' : (
                $preference_course ? 'course' : (
                    $preference_system ? 'system' : 'built_in'
                )
            )
        );
        self::assertEquals('title:' . $expected_context_level, $actual_target_preference->get_title()); // Title always comes from top.
        self::assertEquals(json_encode(['criteria:' . $expected_context_level, 'valid' => true]), $actual_target_preference->get_additional_criteria());
        self::assertEquals(crc32('offset:' . $expected_context_level), $actual_target_preference->get_schedule_offset());
        self::assertEquals('subject:' . $expected_context_level, $actual_target_preference->get_subject());
        self::assertEquals(crc32('subject_format:' . $expected_context_level) % 100, $actual_target_preference->get_subject_format());
        self::assertEquals('body:' . $expected_context_level, $actual_target_preference->get_body());
        self::assertEquals(crc32('body_format:' . $expected_context_level) % 100, $actual_target_preference->get_body_format());

        //////////////////////////////////////////////////////////////////
        /// Activity context

        // Run the function with $at_context_only true.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $activity_extended_context,
            totara_notification_mock_complex_resolver::class,
            true
        );

        // Only records that exist in the context should be returned.
        $expected_preference_ids = [
            $preference_control_standalone_activity->get_id(),
            $preference_control_override_activity->get_id(),
        ];
        if ($preference_activity) {
            $expected_preference_ids[] = $preference_activity->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing($expected_preference_ids, $actual_preference_ids);

        // Run the function with $at_context_only false.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $activity_extended_context,
            totara_notification_mock_complex_resolver::class
        );

        // Inherited records should be included.
        $expected_preference_ids = [
            $preference_control_standalone_built_in->get_id(),
            $preference_control_standalone_system->get_id(),
            $preference_control_standalone_course->get_id(),
            $preference_control_standalone_activity->get_id(),
            $preference_control_override_activity->get_id(),
        ];
        // There will be a target preference if one was created in activity context or above.
        $expected_target_preference = $preference_activity ?? $preference_course ?? $preference_system ?? $preference_built_in;
        if ($expected_target_preference) {
            $expected_preference_ids[] = $expected_target_preference->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing(
            $expected_preference_ids,
            $actual_preference_ids
        );

        // Check the properties of the target preference.
        if ($expected_target_preference) {
            $actual_target_preferences = array_filter($actual_preferences, function ($preference) use ($expected_target_preference) {
                return $preference->get_id() == $expected_target_preference->get_id();
            });
            self::assertCount(1, $actual_target_preferences);
            $actual_target_preference = reset($actual_target_preferences);
            $expected_context_level = $preference_activity ? 'activity' : (
                $preference_course ? 'course' : (
                    $preference_system ? 'system' : 'built_in'
                )
            );
            self::assertEquals('title:' . $expected_context_level, $actual_target_preference->get_title()); // Title always comes from top.
            self::assertEquals(json_encode(['criteria:' . $expected_context_level, 'valid' => true]), $actual_target_preference->get_additional_criteria());
            self::assertEquals(crc32('offset:' . $expected_context_level), $actual_target_preference->get_schedule_offset());
            self::assertEquals('subject:' . $expected_context_level, $actual_target_preference->get_subject());
            self::assertEquals(crc32('subject_format:' . $expected_context_level) % 100, $actual_target_preference->get_subject_format());
            self::assertEquals('body:' . $expected_context_level, $actual_target_preference->get_body());
            self::assertEquals(crc32('body_format:' . $expected_context_level) % 100, $actual_target_preference->get_body_format());
        }

        //////////////////////////////////////////////////////////////////
        /// Course context

        // Run the function with $at_context_only true.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $course_extended_context,
            totara_notification_mock_complex_resolver::class,
            true
        );

        // Only records that exist in the context should be returned.
        $expected_preference_ids = [
            $preference_control_standalone_course->get_id(),
            $preference_control_override_course->get_id(),
        ];
        if ($preference_course) {
            $expected_preference_ids[] = $preference_course->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing($expected_preference_ids, $actual_preference_ids);

        // Run the function with $at_context_only false.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $course_extended_context,
            totara_notification_mock_complex_resolver::class
        );

        // Inherited records should be included.
        $expected_preference_ids = [
            $preference_control_standalone_built_in->get_id(),
            $preference_control_standalone_system->get_id(),
            $preference_control_standalone_course->get_id(),
            $preference_control_override_course->get_id(),
        ];
        // There will be a target preference if one was created in course context or above.
        $expected_target_preference = $preference_course ?? $preference_system ?? $preference_built_in;
        if ($expected_target_preference) {
            $expected_preference_ids[] = $expected_target_preference->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing(
            $expected_preference_ids,
            $actual_preference_ids
        );

        // Check the properties of the target preference.
        if ($expected_target_preference) {
            $actual_target_preferences = array_filter($actual_preferences, function ($preference) use ($expected_target_preference) {
                return $preference->get_id() == $expected_target_preference->get_id();
            });
            self::assertCount(1, $actual_target_preferences);
            $actual_target_preference = reset($actual_target_preferences);
            $expected_context_level = $preference_course ? 'course' : (
                $preference_system ? 'system' : 'built_in'
            );
            self::assertEquals('title:' . $expected_context_level, $actual_target_preference->get_title()); // Title always comes from top.
            self::assertEquals(json_encode(['criteria:' . $expected_context_level, 'valid' => true]), $actual_target_preference->get_additional_criteria());
            self::assertEquals(crc32('offset:' . $expected_context_level), $actual_target_preference->get_schedule_offset());
            self::assertEquals('subject:' . $expected_context_level, $actual_target_preference->get_subject());
            self::assertEquals(crc32('subject_format:' . $expected_context_level) % 100, $actual_target_preference->get_subject_format());
            self::assertEquals('body:' . $expected_context_level, $actual_target_preference->get_body());
            self::assertEquals(crc32('body_format:' . $expected_context_level) % 100, $actual_target_preference->get_body_format());
        }

        //////////////////////////////////////////////////////////////////
        /// System context

        // Run the function with $at_context_only true.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $system_extended_context,
            totara_notification_mock_complex_resolver::class,
            true
        );

        // Only records that exist in the context should be returned.
        $expected_preference_ids = [
            $preference_control_standalone_built_in->get_id(), // Note both built_in and system context standalone preferences are here.
            $preference_control_standalone_system->get_id(),
            $preference_control_override_system->get_id(),
        ];
        if ($preference_system) {
            $expected_preference_ids[] = $preference_system->get_id();
        } else if ($preference_built_in) {
            $expected_preference_ids[] = $preference_built_in->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing($expected_preference_ids, $actual_preference_ids);

        // Run the function with $at_context_only false.
        $actual_preferences = notification_preference_loader::get_notification_preferences(
            $system_extended_context,
            totara_notification_mock_complex_resolver::class
        );

        // Inherited records should be included.
        $expected_preference_ids = [
            $preference_control_standalone_built_in->get_id(),
            $preference_control_standalone_system->get_id(),
            $preference_control_override_system->get_id(),
        ];
        // There will be a target preference if one was created in system context or above.
        $expected_target_preference = $preference_system ?? $preference_built_in;
        if ($expected_target_preference) {
            $expected_preference_ids[] = $expected_target_preference->get_id();
        }
        $actual_preference_ids = array_map(function ($preference) {
            return $preference->get_id();
        }, $actual_preferences);
        self::assertEqualsCanonicalizing(
            $expected_preference_ids,
            $actual_preference_ids
        );

        // Check the properties of the target preference.
        if ($expected_target_preference) {
            $actual_target_preferences = array_filter($actual_preferences, function ($preference) use ($expected_target_preference) {
                return $preference->get_id() == $expected_target_preference->get_id();
            });
            self::assertCount(1, $actual_target_preferences);
            $actual_target_preference = reset($actual_target_preferences);
            $expected_context_level = $preference_system ? 'system' : 'built_in';
            self::assertEquals('title:' . $expected_context_level, $actual_target_preference->get_title()); // Title always comes from top.
            self::assertEquals(json_encode(['criteria:' . $expected_context_level, 'valid' => true]), $actual_target_preference->get_additional_criteria());
            self::assertEquals(crc32('offset:' . $expected_context_level), $actual_target_preference->get_schedule_offset());
            self::assertEquals('subject:' . $expected_context_level, $actual_target_preference->get_subject());
            self::assertEquals(crc32('subject_format:' . $expected_context_level) % 100, $actual_target_preference->get_subject_format());
            self::assertEquals('body:' . $expected_context_level, $actual_target_preference->get_body());
            self::assertEquals(crc32('body_format:' . $expected_context_level) % 100, $actual_target_preference->get_body_format());
        }
    }

    /**
     * @return void
     */
    public function test_fetch_all_notifications_should_exclude_those_middle_overriddens(): void {
        $generator = self::getDataGenerator();
        $course = $generator->create_course();

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_built_in_notification_for_component();
        $mock_preference = notification_preference_loader::get_built_in(mock_built_in::class);

        // Create at course category level.
        $context_category = context_coursecat::instance($course->category);
        $category_preference = $notification_generator->create_overridden_notification_preference(
            $mock_preference,
            extended_context::make_with_context($context_category),
            [
                'title' => 'Kaboom',
                'subject' => 'dada-di-da',
            ]
        );

        // Create at course level.
        $context_course = context_course::instance($course->id);
        $course_preference = $notification_generator->create_overridden_notification_preference(
            $mock_preference,
            extended_context::make_with_context($context_course),
        );

        // Mock one custom record at system context for an event that we are going to fetch notifications for.
        $custom_preference = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(context_system::instance()),
            [
                'title' => 'kaboom',
                'subject' => 'my name',
                'body' => 'body',
                'body_format' => FORMAT_MOODLE,
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Now loading the list of notification preferences that react to the specific notifiable event at the context course
        // level and we should be able to see two preferences, one is the mock that we had overridden at the course level
        // and one that is a custom one that we created at this course level.
        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($context_course),
            mock_resolver::class
        );

        self::assertCount(2, $preferences);
        foreach ($preferences as $preference) {
            self::assertContainsEquals(
                $preference->get_id(),
                [
                    $custom_preference->get_id(),
                    $course_preference->get_id(),
                ]
            );

            self::assertNotEquals($preference->get_id(), $mock_preference->get_id());
            self::assertNotEquals($preference->get_id(), $category_preference->get_id());
        }
    }

    /**
     * @return void
     */
    public function test_fetch_custom_preferences_that_should_not_include_ancestor(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        $course = course::from_record($course_record);

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_built_in_notification_for_component();

        // Create a custom notification at the top level.
        $system_custom = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(context_system::instance()),
            [
                'body' => 'data',
                'subject' => 'body',
                'title' => 'title',
                'body_format' => FORMAT_JSON_EDITOR,
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Create a custom notification at the category level, but different from  the system  one.
        $category_custom = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(context_coursecat::instance($course->category)),
            [
                'body' => 'daa',
                'subject' => 'ioko',
                'title' => 'category title',
                'body_format' => FORMAT_HTML,
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Create an override at the course level, which override from the system one.
        $override_course = $notification_generator->create_overridden_notification_preference(
            $system_custom,
            extended_context::make_with_context($course->get_context()),
            ['body' => 'override body kjo!']
        );

        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($course->get_context()),
            mock_resolver::class
        );

        // There should have 3 preferences:
        // + One override at the course context.
        // + One custom at category context
        // + And one mock at the system context.
        self::assertCount(3, $preferences);
        $mock_preference = notification_preference_loader::get_built_in(mock_built_in::class);

        foreach ($preferences as $preference) {
            self::assertContainsEquals(
                $preference->get_id(),
                [
                    $mock_preference->get_id(),
                    $category_custom->get_id(),
                    $override_course->get_id(),
                ]
            );

            self::assertNotEquals(
                $preference->get_id(),
                $system_custom->get_id()
            );
        }
    }

    /**
     * @return void
     */
    public function test_fetch_middle_overridden_preferences(): void {
        $generator = self::getDataGenerator();
        $course_record = $generator->create_course();

        $course = course::from_record($course_record);

        $context_course = $course->get_context();
        $context_category = $context_course->get_parent_context();

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_built_in_notification_for_component();
        $system_built_in = notification_preference_loader::get_built_in(mock_built_in::class);

        // Override this system built in at the category level.
        $category_built_in = $notification_generator->create_overridden_notification_preference(
            $system_built_in,
            extended_context::make_with_context($context_category),
            ['body' => 'Category body']
        );

        // Fetch the notification at the course context level.
        // Start loading the preferences.
        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($context_course),
            mock_resolver::class
        );

        // There should only have one preference, as the course context should fall back to the category level.
        self::assertCount(1, $preferences);
        $preference = reset($preferences);

        self::assertEquals($category_built_in->get_id(), $preference->get_id());
    }

    /**
     * @return void
     */
    public function test_find_built_in(): void {
        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_built_in_notification_for_component();

        $result = notification_preference_loader::get_built_in('this_is_random');
        self::assertNull($result);

        $exist_preference = notification_preference_loader::get_built_in(mock_built_in::class);
        self::assertNotNull($exist_preference);
    }

    /**
     * @return void
     */
    public function test_find_only_overridden_at_lower_context(): void {
        // Create a custom notification at the system context.
        // Then overriding the system built in notification preference at this course
        // context, and check the loader if it is loading the course context only.
        $generator = self::getDataGenerator();
        $course = $generator->create_course();

        $notification_generator = notification_generator::instance();
        $notification_generator->add_mock_built_in_notification_for_component();
        $system_custom = $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(context_system::instance()),
            [
                'body' => 'Custom body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Create overridden of system built in at course context.
        $system_built_in = notification_preference_loader::get_built_in(mock_built_in::class);

        $context_course = context_course::instance($course->id);
        $system_overridden = $notification_generator->create_overridden_notification_preference(
            $system_built_in,
            extended_context::make_with_context($context_course)
        );

        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($context_course),
            null,
            true
        );

        self::assertCount(1, $preferences);
        $preference = reset($preferences);

        self::assertInstanceOf(notification_preference::class, $preference);
        self::assertNotEquals($system_custom->get_id(), $preference->get_id());
        self::assertNotEquals($system_built_in->get_id(), $preference->get_id());
        self::assertEquals($system_overridden->get_id(), $preference->get_id());
    }

    public function test_get_notification_preferences_includes_natural_parent_of_extended_contexts(): void {
        // Delete built-in notifications.
        builder::table('notification_preference')->delete();
        $notification_generator = notification_generator::instance();

        // System context preference.
        $system_context = context_system::instance();
        $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(
                $system_context
            ),
            [
                'body' => 'System body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Extended system context preference.
        $system_extended_context = extended_context::make_with_context(
            context_system::instance(),
            'system_component',
            'system_area',
            234
        );
        $notification_generator->create_notification_preference(
            mock_resolver::class,
            $system_extended_context,
            [
                'body' => 'System extended body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Course context preference.
        $course = self::getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);
        $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(
                $course_context
            ),
            [
                'body' => 'Course body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Extended couse context preference.
        $course_extended_context = extended_context::make_with_context(
            $course_context,
            'test_component',
            'test_area',
            123
        );
        $notification_generator->create_notification_preference(
            mock_resolver::class,
            $course_extended_context,
            [
                'body' => 'Extended body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // Extended course context control preference.
        $notification_generator->create_notification_preference(
            mock_resolver::class,
            extended_context::make_with_context(
                context_system::instance(),
                'course_control_component',
                'course_control_area',
                345
            ),
            [
                'body' => 'System extended control body',
                'recipient' => totara_notification_mock_recipient::class,
                'recipients' => [totara_notification_mock_recipient::class],
            ]
        );

        // The system preference is visible in the system context.
        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($system_context),
            mock_resolver::class
        );
        self::assertCount(1, $preferences);

        // The system and system-control preferences are visible in the extended system context.
        $preferences = notification_preference_loader::get_notification_preferences(
            $system_extended_context,
            mock_resolver::class
        );
        self::assertCount(2, $preferences);

        // The system and course context preferences are visible in the course context.
        $preferences = notification_preference_loader::get_notification_preferences(
            extended_context::make_with_context($course_context),
            mock_resolver::class
        );
        self::assertCount(2, $preferences);

        // All three preferences are visible in the extended context.
        $preferences = notification_preference_loader::get_notification_preferences(
            $course_extended_context,
            mock_resolver::class
        );
        self::assertCount(3, $preferences);
    }
}