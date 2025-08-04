<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package cohort
 */

namespace core\webapi\resolver\query;

use core\entity\cohort_static_cohorts_filters;
use core\orm\query\order;
use core\pagination\cursor;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use core\data_providers\cohort_static_cohorts as data_provider;
use core_tag\entity\tag_instance;
use totara_cohort\cohort_interactor;
use totara_cohort\exception\cohort_static_cohorts_exception;
use totara_core\data_provider\provider;

/**
 * Handles the "core_cohort_static_cohorts" GraphQL query.
 */
class cohort_static_cohorts extends query_resolver {
    /**
     * @var String
     */
    public const TYPE_STATIC = 1;

    /**
     * @param array $args
     * @param execution_context $ec
     * @return mixed|\totara_core\data_provider\provider
     */
    public static function resolve(array $args, execution_context $ec) {
        $query = $args['query'] ?? [];
        $sort = $query['sort'] ?? [];
        $filter = $query['filter'] ?? [];

        $order_by = $order_dir = null;
        if (!empty($sort)) {
            if (count($sort) > 1) {
                throw new cohort_static_cohorts_exception("Sorting by more than one column is not currently supported.");
            }

            if (empty($sort[0]['column'])) {
                throw new cohort_static_cohorts_exception("Required parameter 'column' not being passed.");
            }

            $sort = reset($sort);
            $order_by = $sort['column'];
            $order_dir = $sort['direction'] ?? order::DIRECTION_ASC;
        }

        $interactor = cohort_interactor::for_user();
        if (!$interactor->can_view_cohort()) {
            throw new cohort_static_cohorts_exception('You do not have capabilities to view cohorts.');
        }

        $filters = self::get_default_filters($filter);
        if ($interactor->tenant_enabled()) {
            $context_ids[] = $interactor->get_context()->id;
            foreach ($interactor->get_context()->get_child_contexts() as $context) {
                if ($interactor->get_context()->tenantid == $context->tenantid && $context->contextlevel == CONTEXT_COURSECAT) {
                    $context_ids[] =  $context->id;
                }
            }
            $filters['contextids'] = $context_ids;
        }

        $enc_cursor = $query['pagination']['cursor'] ?? null;
        return data_provider::create(new cohort_static_cohorts_filters())
            ->set_filters($filters)
            ->set_order($order_by, $order_dir)
            ->set_page_size($query['pagination']['limit'] ?? provider::DEFAULT_PAGE_SIZE)
            ->fetch_paginated($enc_cursor ? cursor::decode($enc_cursor) : null);
    }

    /**
     * @param array $filter
     * @return array
     */
    protected static function get_default_filters(array $filter = []): array {
        global $CFG;

        // By default, only fetch static and visible, exclude 'totara_tenant'
        $filters = ['type' => self::TYPE_STATIC, 'visible' => true, 'not_component' => 'totara_tenant'];

        if (!empty($filter)) {
            if (isset($filter['tag_filter'])) {
                if (empty($CFG->usetags)) {
                    throw new cohort_static_cohorts_exception('Tag feature is disabled.');
                }
                $item_ids = tag_instance::repository()->get_item_ids_by_tag_rawname($filter['tag_filter'], 'cohort', 'core');
                $filters['tag'] = $item_ids;
            }

            if (isset($filter['since_timecreated'])) {
                $filters['since_timecreated'] = $filter['since_timecreated'];
            }

            if (isset($filter['since_timemodified'])) {
                $filters['since_timemodified'] = $filter['since_timemodified'];
            }
        }

        return $filters;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            require_authenticated_user::class
        ];
    }
}