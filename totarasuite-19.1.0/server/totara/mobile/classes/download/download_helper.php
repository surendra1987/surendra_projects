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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\download;

use cm_info;
use context;
use context_course;
use core\format;
use core\orm\query\builder;
use core_availability\info_module;
use core_plugin_manager;
use moodle_exception;
use stdClass;
use totara_mobile\completion\completion_helper;

class download_helper {
    /**
     * Get downloadable activity content mostly it's used in query resolver.
     *
     * @param string $component
     * @param stdClass $cm
     * @return downloadable_activity
     */
    public static function get_downloadable_activity_content(string $component, stdClass $cm): downloadable_activity {
        $class = "mod_{$component}\\download\\{$component}";
        if (!class_exists($class)) {
            throw new moodle_exception("{$class} does not exist");
        }

        if ($cm->modname !== $component) {
            throw new moodle_exception("{$component} not found");
        }

        $cm_info = cm_info::create($cm);
        if (!static::is_activity_downloadable($cm_info)) {
            throw new moodle_exception($cm_info->modname . ' does not support mobile download');
        }

        return (new $class($cm_info))->get_content();
    }

    /**
     * Get generic activity content information mostly it's called in mod_modtype/download/modtype.php.
     *
     * @param cm_info $cm_info
     * @param downloadable_activity $activity
     * @return downloadable_activity
     */
    public static function get_generic_activity_content(cm_info $cm_info, downloadable_activity $activity): downloadable_activity {
        $activity->id = $cm_info->id;
        $activity->instanceid = $cm_info->instance;
        $activity->name = $cm_info->get_formatted_name();
        $activity->viewurl = $cm_info->url;
        $activity->showdescription = $cm_info->showdescription;
        $activity->completionenabled = completion_helper::get_completionenabled($cm_info);
        $activity->completion = completion_helper::get_completion_value($cm_info);
        $activity->completionstatus = completion_helper::get_completionstatus_value($cm_info);
        $activity->downloadsize = $activity->get_total_download_size();

        if (plugin_supports('mod', $cm_info->modname, FEATURE_MOD_INTRO, true)) {
            $record = static::get_activity_instance($cm_info);

            $activity->intro = $record->intro;
            $activity->attachments = static::get_attachments_for_content($cm_info->context, 'mod_'.$cm_info->modname, 'intro', 0);
            $activity->introformat = format::from_moodle($record->introformat);
        }

        return $activity;
    }

    /**
     * Returns true if activity supports mobile download, false otherwise.
     * @param $cm
     * @return bool
     */
    public static function is_activity_downloadable($cm): bool {
        global $CFG, $USER;
        if (file_exists($CFG->dirroot . '/mod/' . $cm->modname . '/lib.php')) {
            include_once $CFG->dirroot . '/mod/' . $cm->modname . '/lib.php';

            $function_name = $cm->modname . '_support_mobile_download';
            return function_exists($function_name) && $function_name($cm) && info_module::is_user_visible($cm->id, $USER->id);
        }
        return false;
    }

    /**
     * Check the course contains downloadable activities or not.
     *
     * @param int $course_id
     * @return bool
     */
    public static function have_supported_activities_by_course_id(int $course_id): bool {
        global $USER;

        $course_context = context_course::instance($course_id);
        if (!is_enrolled($course_context, $USER->id)) {
            return false;
        }

        if ($cms = get_course_mods($course_id)) {
            foreach ($cms as $cm) {
                // As long as there is an activity supporting mobile download, return true.
                if (static::is_activity_downloadable($cm)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param int $course_id
     * @return array
     */
    public static function get_supported_offline_activities(int $course_id): array {
        global $USER;
        $offline_activities = [];
        $course_context = context_course::instance($course_id);
        if (!is_enrolled($course_context, $USER->id)) {
            return $offline_activities;
        }

        if ($cms = get_course_mods($course_id)) {
            foreach ($cms as $cm) {
                if (static::is_activity_downloadable($cm)) {
                    $instance = get_coursemodule_from_instance($cm->modname, $cm->instance);
                    $cm_info = cm_info::create($cm);
                    $plugin = core_plugin_manager::instance()->get_plugin_info('mod_' . $cm->modname);
                    $activity = new stdClass();
                    $activity->cmid = $cm->id;
                    $activity->type = $cm->modname;
                    $activity->name = self::override_activity_name($cm_info);
                    $activity->version = $plugin->versiondb;
                    $activity->enablecompletion = $instance->completion != COMPLETION_TRACKING_NONE;
                    $offline_activities[] = $activity;
                }
            }
        }
        return $offline_activities;
    }

    /**
     * @param cm_info $cm_info
     * @return string
     */
    private static function override_activity_name(cm_info $cm_info): string {
        if ($cm_info->modname === 'label') {
            return ($cm_info->get_course())->fullname . ':' . get_string('label', 'totara_mobile') . $cm_info->instance;
        }
        return $cm_info->get_formatted_name();
    }

    /**
     * @param context $context
     * @param string $component
     * @param string $filearea
     * @param int $item_id
     * @param cm_info|null $cm_info
     * @param string $sort
     *
     * @return array
    */
    public static function get_attachments_for_content(
        context $context,
        string $component,
        string $filearea,
        int $item_id = null,
        string $sort = "itemid, filepath, filename"
    ): array {
        global $CFG;

        $urlbase = $CFG->wwwroot . '/totara/mobile/pluginfile.php';
        $fs = get_file_storage();
        $attachments = [];
        foreach ($fs->get_area_files($context->id, $component, $filearea, $item_id, $sort) as $file) {
            if ($file->is_directory()) {
                continue;
            }

            if ($file->get_filename() == '.') {
                continue;
            }

            $filename = $file->get_filename();
            if ($item_id == 0 && in_array($filearea, ['overviewfiles', 'intro', 'summary'])) {
                $item_id = null;
            }

            if (!is_null($item_id)) {
                $attachments[] = new attachment_info(\moodle_url::make_file_url($urlbase, "/$context->id/$component/$filearea/$item_id/$filename")->out(false), $file->get_filesize());
            } else {
                $attachments[] = new attachment_info(\moodle_url::make_file_url($urlbase, "/$context->id/$component/$filearea/$filename")->out(false), $file->get_filesize());
            }
        }

        return $attachments;
    }

    /**
     * @param cm_info $cm_info
     * @return false|mixed|stdClass
     */
    public static function get_activity_instance(cm_info $cm_info) {
        return builder::get_db()->get_record($cm_info->modname, ['id' => $cm_info->instance]);
    }

    /**
     * Get each activity total download file size.
     *
     * @param cm_info $cminfo
     *
     * @return int
     */
    public static function get_activity_download_size(cm_info $cminfo): int {
        if (!static::is_activity_downloadable($cminfo)) {
            return 0;
        }

        $class = "mod_{$cminfo->modname}\\download\\{$cminfo->modname}";
        if (!class_exists($class)) {
            // This should not be reached
            throw new moodle_exception("{$class} does not exist");
        }
        return (new $class($cminfo))->get_total_download_size();
    }
}