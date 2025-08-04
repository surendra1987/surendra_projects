<?php
/**
 * This file is part of Totara Core
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core
 */

namespace core\model;

use cache;
use cache_helper;
use context_coursecat;
use context_helper;
use core\entity\course;
use core\orm\collection;
use core\orm\entity\model;
use core\entity\course_categories as entity;
use totara_program\entity\program;

/**
 * Course category model
 *
 * @property-read int $id
 * @property string $name
 * @property string $idnumber
 * @property string $description
 * @property int $descriptionformat
 * @property int $parent
 * @property int $sortorder
 * @property int $coursecount
 * @property int $visible
 * @property int $visibleold
 * @property int $timemodified
 * @property int $depth
 * @property string $path
 * @property string $theme
 * @property int $programcount
 * @property int $certifcount
 * @property int $issystem
 * @property int $iscontainer
 *
 * Relationships:
 * @property-read course[] $courses
 *
 * @package core\model
 */
class course_category extends model {

    /**
     * @inheritDoc
     */
    protected $entity_attribute_whitelist = [
        'id',
        'name',
        'idnumber',
        'description',
        'descriptionformat',
        'parent',
        'sortorder',
        'coursecount',
        'visible',
        'visibleold',
        'timemodified',
        'depth',
        'path',
        'theme',
        'programcount',
        'certifcount',
        'issystem',
        'iscontainer',
        'children',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * Ensure the sort order of courses within this category is sequential.
     */
    public function ensure_sequential_course_sortorder($update_course_count=false): void {
        $courses = course::repository()
            ->where('category', '=', $this->id)
            ->order_by('sortorder', 'ASC')
            ->order_by('id', 'ASC')
            ->get();
        $this->fix_course_sortorder($courses);
        if ($update_course_count) {
            $this->update_course_count();
            $this->purge_category_cache($this->id);
        }
    }

    public function get_course_count(): int {
        return course::repository()
            ->where('category', '=', $this->id)
            ->count();
    }

    /**
     * Update the course count for this category.
     * @param int $count
     */
    public function update_course_count(int $count=0): void {
        $count = empty($count) ? $this->get_course_count() : $count;

        if ($count != $this->coursecount) {
            $this->entity->coursecount = $count;
            $this->entity->save();
        }
    }

    /**
     * Update the program count for this category.
     */
    public function update_program_count(): void {
        $count = program::repository()
            ->where('category', '=', $this->id)
            ->count();

        if ($count != $this->programcount) {
            $this->entity->programcount = $count;
            $this->entity->save();
        }
    }

    /**
     * Update the certification count for this category.
     */
    public function update_certification_count(): void {
        $count = program::repository()
            ->where('category', '=', $this->id)
            ->where_not_null('certifid')
            ->count();

        if ($count != $this->certifcount) {
            $this->entity->certifcount = $count;
            $this->entity->save();
        }
    }

    /**
     * Fix the sort order of courses within a category.
     * Remove the sort order gap
     */
    private function fix_course_sortorder(collection $courses): void {
        $i = 0;
        /** @var course $course */
        foreach ($courses as $course) {
            $i++;
            $expected_sortorder = $this->sortorder + $i;
            if ($expected_sortorder == $course->sortorder) {
                continue;
            }
            // Update the course sort order if we found the sort order gap
            $course->sortorder = $expected_sortorder;
            $course->save();
        }
    }

    /**
     * Fix and update sort order and paths for categories.
     */
    public static function fix_category_sortorder(): void {
        $sortorder = 0;
        $fixcontexts = [];

        $topcats = self::get_top_categories()->all();
        if (self::fix_course_cats_recursive($topcats, $sortorder, 0, 0, '', $fixcontexts)) {
            if (empty($fixcontexts)) {
                // Trigger necessary cache events
                self::trigger_cache_updates();
                return;
            }
            static::fix_contexts_after_course_changes($fixcontexts);
        }
    }

    /**
     * Fix context paths and depths after changes in course categories.
     */
    public static function fix_contexts_after_course_changes(array $fixcontexts): void {
        if (empty($fixcontexts)) {
            return;
        }
        // Reset paths for all contexts that need fixing.
        foreach ($fixcontexts as $fixcontext) {
            $fixcontext->reset_paths(false);
        }
        // Rebuild all paths.
        context_helper::build_all_paths(false);

        // Clear the fixcontexts array to free up memory.
        unset($fixcontexts);

        // Trigger cache events to rebuild necessary caches.
        self::trigger_cache_updates();
    }

    /**
     * Fetch top-level categories.
     */
    private static function get_top_categories(): collection {
        return entity::repository()
            ->where('parent', '=', 0)
            ->order_by('sortorder', 'ASC')
            ->get();
    }

    /**
     * Trigger necessary cache updates after sort order fix.
     */
    private static function trigger_cache_updates(): void {
        // Trigger cache rebuilds for categories and courses
        cache_helper::purge_by_event('changesincourse');
        cache_helper::purge_by_event('changesincoursecat');
    }

    /**
     * Recursively fix categories' sort order and paths.
     */
    private static function fix_course_cats_recursive(array $children, &$sortorder, $parent, $depth, $path, &$fixcontexts): bool {
        $changesmade = false;
        $depth++;

        // Separate system categories from regular ones.
        $sorted_cats = self::sort_system_categories_last($children);
        foreach ($sorted_cats as $cat) {
            $sortorder += MAX_COURSES_IN_CATEGORY;
            if (self::update_category_if_needed($cat, $sortorder, $parent, $depth, $path, $fixcontexts)) {
                $changesmade = true;
            }

            $sub_categories = $cat->children;

            // Recursively fix subcategories
            if (!empty($sub_categories) && self::fix_course_cats_recursive($sub_categories, $sortorder, $cat->id, $cat->depth, $cat->path, $fixcontexts)) {
                $changesmade = true;
            }
        }
        return $changesmade;
    }

    /**
     * Sort system categories last.
     */
    private static function sort_system_categories_last(array $children): array {
        $cats = [];
        $syscats = [];
        foreach ($children as $cat) {
            if ($cat->issystem) {
                $syscats[] = $cat;
            } else {
                $cats[] = $cat;
            }
        }
        // Regular categories first, system categories last.
        return array_merge($cats, $syscats);
    }

    /**
     * Update category if changes are needed.
     */
    private static function update_category_if_needed($cat, $sortorder, $parent, $depth, $path, &$fixcontexts): bool {
        $update = false;
        /** @var entity $cat */
        if ($cat->parent != $parent || $cat->depth != $depth || $cat->path != $path.'/'.$cat->id) {
            $cat->parent = $parent;
            $cat->depth = $depth;
            $cat->path = $path.'/'.$cat->id;
            $update = true;
            $context = context_coursecat::instance($cat->id);
            $fixcontexts[$context->id] = $context;
        }
        if ($cat->sortorder != $sortorder) {
            $cat->sortorder = $sortorder;
            $update = true;
        }
        if ($update) {
            $cat->save();
        }
        return $update;
    }

    /**
     * Purge the cache value if the category is changed
     *
     * @param $id
     * @return void
     */
    private function purge_category_cache($id=null): void {
        $coursecatrecordcache = cache::make('core', 'coursecatrecords');
        if (empty($id)) {
            $coursecatrecordcache->set($id, null);
            return;
        }
        $coursecatrecordcache->purge();
    }
}
