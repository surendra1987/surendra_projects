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

namespace core\data_providers;

use core\entity\cohort;
use core\entity\user;
use core\orm\entity\filter\filter_factory;
use totara_core\data_provider\provider;

defined('MOODLE_INTERNAL') || die();

/**
 * "Model" for dealing with collections of cohort_static_cohorts.
 */
class cohort_static_cohorts extends provider {
    /**
     * Valid sort fields
     */
    public const SORT_FIELDS = [
        'id',
        'idnumber',
        'timecreated',
        'timemodified',
        'startdate',
        'enddate',
        'modifierid',
        'name'
    ];

    /**
     * @inheritDoc
     */
    public static function create(?filter_factory $filter_factory = null): provider {
        $current_user = user::logged_in();
        [$capsql, $params] = get_has_capability_sql('moodle/cohort:view', 'ctx.id', $current_user->id);
        return new static(
            cohort::repository()
                ->join(['context', 'ctx'], 'contextid', 'ctx.id')
                ->where_raw($capsql, $params),
            self::SORT_FIELDS,
            $filter_factory
        );
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'cohort';
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select() {
        return 'id';
    }
}