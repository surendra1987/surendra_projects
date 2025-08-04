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
namespace totara_notification\loader;

use context_system;
use core\entity\context;
use core\orm\query\builder;
use totara_core\extended_context;
use totara_notification\entity\notification_preference as entity;
use totara_notification\model\notification_preference;
use totara_notification\model\notification_preference as model;

class notification_preference_loader {

    /**
     * @var array Cache of notification preferences, specific to context and "at context only".
     */
    static array $cache = [];

    /**
     * notification_preference_loader constructor.
     * Preventing this class from construction.
     */
    private function __construct() {
    }

    /**
     * Returns notification preferences relating to the given context.
     *
     * If $at_context_only is true then it returns only those custom preferences or overrides that have been created in
     * the given context.
     *
     * If $at_context_only is false then it returns all preferences which exist or are inherited in the given context.
     * For each "custom" or built-in preference existing in or above the given context, one preference will be returned.
     * The preferences returned will be the lowest-context override which exists in or above the given context, or the
     * custom or built-in preference if there is no override at or above the given context. Custom preferences and
     * overrides in lower contexts are ignored.
     *
     * @param extended_context $extended_context
     * @param string|null      $resolver_class_name If the parameter is not provided, then we are assuming to load all
     *                                              of notification preferences.
     * @param bool             $at_context_only     The parameter will narrow down the list of notification preferences
     *                                              overridden or created at specific given context only.
     *
     * @return model[]
     */
    public static function get_notification_preferences(extended_context $extended_context,
                                                        ?string $resolver_class_name = null,
                                                        bool $at_context_only = false): array {
        $resolver_class_name = ltrim($resolver_class_name ?? '', '\\');

        $cache_key = $resolver_class_name . ':' .
            $extended_context->get_context_id() . ':' .
            $extended_context->get_area() . ':' .
            $extended_context->get_component() . ':' .
            $extended_context->get_item_id() . ':' .
            (int)$at_context_only;
        if (isset(static::$cache[$cache_key])) {
            return static::$cache[$cache_key];
        }

        $ancestor_context_ids = $extended_context->get_parent_context_ids();

        if (empty($ancestor_context_ids) || $at_context_only) {
            // Either we are fetching the notification preferences at the given context ONLY.
            // Or we are at the top context level (in which case we don't need to check ascendant contexts).
            $current_context_builder = builder::table(entity::TABLE, 'np')
                ->select('*')
                ->where('context_id', $extended_context->get_context_id())
                ->where('component', $extended_context->get_component())
                ->where('area', $extended_context->get_area())
                ->where('item_id', $extended_context->get_item_id())
                ->when(
                    !empty($resolver_class_name),
                    function (builder $inner_builder) use ($resolver_class_name): void {
                        $inner_builder->where('resolver_class_name', $resolver_class_name);
                    }
                )
                ->results_as_arrays()
                ->map_to([static::class, 'create_preference']);

            static::$cache[$cache_key] = $current_context_builder->fetch();
            return static::$cache[$cache_key];
        }

        // Get all custom and built-in notifictions in the context path (including the event context).
        $top_preference_builder = builder::table(entity::TABLE, 'np')
            ->where_null('ancestor_id')
            ->where(function (builder $context_builder) use ($extended_context, $ancestor_context_ids): void {
                $context_builder
                    ->where(function (builder $at_context_builder) use ($extended_context): void {
                        // Exact match on the given context.
                        $at_context_builder
                            ->where('context_id', '=', $extended_context->get_context_id())
                            ->where('component', '=', $extended_context->get_component())
                            ->where('area', '=', $extended_context->get_area())
                            ->where('item_id', '=', $extended_context->get_item_id());
                    })
                    ->or_where(function (builder $ancestor_context_builder) use ($ancestor_context_ids): void {
                        // Or match on any ancestor context (which must all be natural contexts);
                        $ancestor_context_builder
                            ->where_in('context_id', $ancestor_context_ids)
                            ->where('component', '=', extended_context::NATURAL_CONTEXT_COMPONENT)
                            ->where('area', '=', extended_context::NATURAL_CONTEXT_AREA)
                            ->where('item_id', '=', extended_context::NATURAL_CONTEXT_ITEM_ID);
                    });
            })
            ->when(
                !empty($resolver_class_name),
                function (builder $inner_builder) use ($resolver_class_name) {
                    $inner_builder->where('resolver_class_name', $resolver_class_name);
                }
            )
            ->results_as_arrays()
            ->map_to([static::class, 'create_preference']);

        $top_preferences = $top_preference_builder->fetch();

        // For each "top" preference, we find the matching "bottom" (lowest context) preference that is no lower than the
        // given context. If no override exists then we return the top preference.
        $bottom_preferences = [];
        /** @var notification_preference[] $top_preferences */
        foreach ($top_preferences as $top_preference) {
            $bottom_descedant = builder::table(entity::TABLE, 'np')
                ->join([context::TABLE, 'ctx'], 'context_id', '=', 'ctx.id')
                ->where('ancestor_id', '=', $top_preference->get_id()) // Descendants, excludes current preference.
                ->where(function (builder $context_builder) use ($extended_context, $ancestor_context_ids) {
                    $context_builder
                        ->where(function (builder $at_context_builder) use ($extended_context): void {
                            // Exact match on the given context.
                            $at_context_builder
                                ->where('context_id', '=', $extended_context->get_context_id())
                                ->where('component', '=', $extended_context->get_component())
                                ->where('area', '=', $extended_context->get_area())
                                ->where('item_id', '=', $extended_context->get_item_id());
                        })
                        ->or_where(function (builder $ancestor_context_builder) use ($ancestor_context_ids): void {
                            // Or match on any ancestor context (which must all be natural contexts);
                            $ancestor_context_builder
                                ->where_in('context_id', $ancestor_context_ids)
                                ->where('component', '=', extended_context::NATURAL_CONTEXT_COMPONENT)
                                ->where('area', '=', extended_context::NATURAL_CONTEXT_AREA)
                                ->where('item_id', '=', extended_context::NATURAL_CONTEXT_ITEM_ID);
                        });
                })
                ->order_by('ctx.depth', 'DESC')
                ->order_by('item_id', 'DESC') // If not zero then it's an extended context.
                ->results_as_arrays()
                ->map_to([static::class, 'create_preference'])
                ->limit(1)
                ->fetch();

            $bottom_preferences[] = empty($bottom_descedant) ? $top_preference : reset($bottom_descedant);
        }

        static::$cache[$cache_key] = $bottom_preferences;
        return static::$cache[$cache_key];
    }

    /**
     * Note: Please do not call this function outside of this class. It is only
     * public because we want the query builder to access it.
     *
     * @param array $row
     * @return model
     *
     * @internal
     */
    public static function create_preference(array $row): model {
        $entity = new entity($row);
        return model::from_entity($entity);
    }

    /**
     * Find built in notification preference at given context. Default to context system
     * if it is not provided.
     *
     * @param string                $notification_class_name
     * @param extended_context|null $extended_context
     *
     * @return model|null
     */
    public static function get_built_in(string $notification_class_name, ?extended_context $extended_context = null): ?model {
        $extended_context = $extended_context ?? extended_context::make_with_context(context_system::instance());

        $repository = entity::repository();
        $entity = $repository->find_built_in($notification_class_name, $extended_context);

        if (null !== $entity) {
            return model::from_entity($entity);
        }

        return null;
    }

    /**
     * Clears the static cache - needed for testing.
     *
     * @return void
     */
    public static function clear_cache(): void {
        static::$cache = [];
    }
}