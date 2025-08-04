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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\data_provider;

use coding_exception;
use context;
use core\collection;
use core\orm\entity\repository;
use core\orm\pagination\cursor_paginator;
use core\orm\pagination\offset_cursor_paginator;
use core\pagination\base_paginator;
use core\pagination\cursor;
use core\pagination\offset_cursor;
use perform_goal\data_provider\filter\goal_context_filter;
use perform_goal\data_provider\filter\goal_name_filter;
use perform_goal\data_provider\filter\goal_status_filter;
use perform_goal\data_provider\filter\goal_user_filter;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_repository;
use perform_goal\interactor\goal_interactor;
use stdClass;

/**
 * A class providing most of the functionality for querying perform_goals (introduced 2023).
 */
class goal_data_provider_base implements goal_data_provider_interface {
    /**
     * Array of filters to apply when fetching the data
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Array of fields that the repository can be sorted by.
     * Note: the target_date field appears unsuitable for sorting, as it can contain duplicate values, which won't work
     * well with pagination.
     *
     * @var array
     */
    protected $sort_by_fields = ['id', 'name', 'target_date', 'created_at', 'completion_percent'];

    /**
     * Names of the fields to sort by.
     *
     * @var array
     */
    protected $sort_by;

    /**
     * Return whether data has been fetched
     *
     * @var bool
     */
    protected $fetched = false;

    /**
     * @var collection
     */
    protected $items;

    /**
     * @var goal_repository
     */
    protected $repository;

    public function __construct() {
        $this->repository = goal_entity::repository();
        $this->sort_by = [];
    }

    /**
     * @inheritDoc
     * @return $this
     */
    final public function add_filters(array $filters): self {
        $this->filters = array_merge(
            $this->filters,
            array_filter($filters, static function ($filter_value) {
                return isset($filter_value);
            })
        );

        return $this;
    }

    /**
     *  @inheritDoc
     * @return $this
     */
    final public function add_sort_by(array $sort_fields): self {
        // Skip if empty.
        if (empty($sort_fields)) {
            return $this;
        }

        foreach ($sort_fields as $sort) {
            // Enforce a structure on the sort_type array.
            if (empty($sort['column'])) {
                throw new coding_exception("Sort parameter must have a 'column' key");
            }
            if (empty($sort['direction'])) {
                $sort['direction'] = 'ASC';
            }
            if (!in_array($sort['direction'], ['ASC', 'DESC'])) {
                throw new coding_exception("Invalid sort direction");
            }

            // Check that column exists as a sort option.
            if (!in_array($sort['column'], $this->sort_by_fields)) {
                throw new coding_exception("Unknown sort column");
            }

            // Set property.
            $this->sort_by[] = $sort;
        }
        return $this;
    }

    /**
     * Run the ORM query and mark the data provider as already fetched.
     */
    protected function fetch(): self {
        $this->fetched = false;

        $this->build_query();
        $this->apply_query_filters();
        $this->apply_query_sorting();

        $this->items = $this->repository->get();
        $this->fetched = true;
        $this->process_fetched_items();

        return $this;
    }

    /**
     * @inheritDoc
     *
     * Note this doesn't take capabilities into account. Add permission filter before calling this or filter the results if appropriate.
     */
    public function get_results(): collection {
        if (!$this->fetched) {
            $this->fetch();
        }

        return $this->items;
    }

    /**
     * @inheritDoc
     *
     * Note this doesn't take capabilities into account. Add permission filter before calling this or filter the results if appropriate.
     */
    final public function get_offset_page(int $page_size = cursor_paginator::DEFAULT_ITEMS_PER_PAGE, int $page_requested = 1): stdClass {
        $cursor = offset_cursor::create()->set_limit($page_size)->set_page($page_requested);
        $paginator = $this->get_offset_paginator($cursor);
        $this->items = $paginator->get_items();

        $this->process_fetched_items();

        $next_cursor = $paginator->get_next_cursor();

        return (object)[
            'items' => $this->items,
            'total' => $paginator->get_total(),
            'next_cursor' => $next_cursor === null ? '' : $next_cursor->encode(),
        ];
    }

    /**
     * @inheritDoc
     *
     * Note this doesn't take capabilities into account. Add permission filter before calling this or filter the results if appropriate.
     */
    final public function get_page_results(string $opaque_cursor = null, int $page_size = base_paginator::DEFAULT_ITEMS_PER_PAGE): stdClass {
        // Refresh the repository here.
        $this->repository = goal_entity::repository();
        if (is_null($opaque_cursor) || $opaque_cursor === '') {
            $local_cursor = cursor::create()->set_limit($page_size);
        } else {
            $local_cursor = cursor::decode($opaque_cursor);
        }
        $paginator = $this->get_paginator($local_cursor);
        $this->items = $paginator->get_items();

        $this->process_fetched_items();

        $next_cursor = $paginator->get_next_cursor();

        return (object)[
            'items' => $this->items,
            'total' => $paginator->get_total(),
            'next_cursor' => $next_cursor === null ? '' : $next_cursor->encode(),
        ];
    }

    /**
     * Take the current filter settings, extract the context ids in the result and
     * add a filter for only the context ids that the logged-in user has permission to view.
     *
     * @return void
     */
    public function add_permission_filter(): void {
        // First get only the context ids from the results.
        $repository = goal_entity::repository();
        $repository->select_raw('DISTINCT context_id');
        $this->apply_query_filters($repository);

        $context_ids_in_unfiltered_result = $repository->get();

        // Find the contexts that the currently logged-in user has permission to view.
        $context_ids_with_view_permission = $context_ids_in_unfiltered_result
            ->filter(
                fn (goal_entity $goal)
                => (new goal_interactor(context::instance_by_id($goal->context_id)))->can_view_personal_goals()
            )
            ->pluck('context_id');

        // Filter for those contexts.
        if (count($context_ids_with_view_permission) < $context_ids_in_unfiltered_result->count()) {
            $this->add_filters(['context_ids' => $context_ids_with_view_permission]);
        }
    }

    /**
     * Build the base ORM query using the relevant repository. This can be overridden.
     */
    protected function build_query(): void {
        $this->repository
            ->select(['*'])
            ->add_select_completion_percent();
    }

    /**
     * Apply filters to a given repository before it is fetched from the database.
     *
     * To add a query filter, define a method like:
     * ```
     *     protected function filter_query_by_FILTERNAME(mixed $filter_value): void { ... }
     * ```
     *
     * @param $repository
     * @return $this
     */
    protected function apply_query_filters($repository = null): self {
        foreach ($this->filters as $key => $value) {
            if ($this->fetched) {
                throw new coding_exception('Must call apply_query_filters() before fetching.');
            }

            if (!method_exists($this, 'filter_query_by_' . $key)) {
                throw new coding_exception("Filtering by '{$key}' is not supported");
            }

            $this->{'filter_query_by_' . $key}($value, $repository);
        }

        return $this;
    }

    /**
     * Apply sorting to a given repository before it is fetched from the database.
     *
     * To add a query filter, define a method like:
     * ```
     *     protected function sort_query_by_SORTNAME(): void { ... }
     * ```
     *
     * @return $this
     */
    protected function apply_query_sorting(): self {
        if ($this->fetched) {
            throw new coding_exception('Must call apply_query_sorting() before fetching.');
        }

        if (!empty($this->sort_by)) {
            foreach ($this->sort_by as $sort_field) {
                $column = $sort_field['column'];
                $direction = $sort_field['direction'] ?? 'ASC';

                if (method_exists($this, 'sort_query_by_' . $column)) {
                    $this->{'sort_query_by_' . $column}($direction);
                } else {
                    $this->repository->order_by($column, $direction);
                }
            }
        }

        if (!$this->repository->has_order_by()) {
            // If no order is set, then fallback to id to prevent random unit test failures (due to unpredictable sorting)
            $this->repository->order_by('id');
        }

        return $this;
    }

    /**
     * Performs validation checks on each goal result, e.g. check that the currently logged in user has permission
     * to view the goal.
     * @return void
     */
    protected function validate_items(): void {
        // Do nothing here, override this in a child class.
    }

    /**
     * (Optionally) augment the fetched items before returning them with get().
     *
     * @return void
     */
    protected function process_fetched_items(): void {
        $this->validate_items();
    }

    /**
     * Move the paginator to the next set of results and return it.
     * NOTE: The caller is expected to call the applicable paginator methods to obtain the items, next_cursor, etc.
     *
     * @param offset_cursor $offset_cursor Caller should initialize
     * @return offset_cursor_paginator
     */
    final protected function get_offset_paginator(offset_cursor $offset_cursor): offset_cursor_paginator {
        $this->build_query();
        $this->apply_query_filters();
        $this->apply_query_sorting();

        $paginator = new offset_cursor_paginator($this->repository, $offset_cursor);
        $paginator->get();

        return $paginator;
    }

    /**
     * Move the paginator to the next set of results and return it
     * NOTE: The caller is expected to call the applicable paginator methods to obtain the items, next_cursor, etc.
     *
     * @param cursor $cursor Caller should initialize
     * @return cursor_paginator
     */
    final protected function get_paginator(cursor $cursor): cursor_paginator {
        $this->build_query();
        $this->apply_query_filters();
        $this->apply_query_sorting();

        $paginator = new cursor_paginator($this->repository, $cursor, true);
        $paginator->get();

        return $paginator;
    }

    /**
     * Sets a filter for the user_id field, i.e. where user_id = {user_id}.
     *
     * @param int $user_id
     * @param repository|null $repository
     * @return void
     */
    protected function filter_query_by_user_id(int $user_id, ?repository $repository = null): void {
        $filter = new goal_user_filter('perform_goal');
        $filter->set_value($user_id);
        $repository = $repository ?? $this->repository;
        $repository->set_filter($filter);
    }

    /**
     * Sets a filter for the context_id field.
     *
     * @param array $context_ids array of context ids
     * @param repository|null $repository
     * @return void
     */
    protected function filter_query_by_context_ids(array $context_ids, ?repository $repository = null): void {
        $filter = new goal_context_filter('perform_goal');
        $filter->set_value($context_ids);
        $repository = $repository ?? $this->repository;
        $repository->set_filter($filter);
    }

    /**
     * Filter goals by a search term.
     *
     * @param string $search
     * @param repository|null $repository
     * @return void
     */
    protected function filter_query_by_search(string $search, ?repository $repository = null): void {
        // For now, we can re-use the name filter. Later this may include other text such as goal tasks.
        $this->filter_query_by_name($search, $repository);
    }

    /**
     * Filter goals by a search term.
     *
     * @param array $status_codes
     * @param repository|null $repository
     * @return void
     */
    protected function filter_query_by_status(array $status_codes, ?repository $repository = null): void {
        $filter = new goal_status_filter('perform_goal');
        $filter->set_value($status_codes);
        $repository = $repository ?? $this->repository;
        $repository->set_filter($filter);
    }

    /**
     * Sets a filter on the perform_goal repository for the name field.
     *
     * @param string $name
     * @param repository|null $repository
     * @return void
     */
    protected function filter_query_by_name(string $name, ?repository $repository = null): void {
        $filter = new goal_name_filter('perform_goal');
        $filter->set_value($name);
        $repository = $repository ?? $this->repository;
        $repository->set_filter($filter);
    }

    /**
     * @param string $direction
     * @return void
     */
    protected function sort_query_by_completion_percent(string $direction): void {
        // Add validation for the direction as we're using raw sql here.
        $dir = strtoupper($direction);
        if ($dir !== 'ASC' && $dir !== 'DESC') {
            throw new coding_exception("Unknown sort direction");
        }

        $this->repository->order_by_raw("completion_percent $dir");
    }
}
