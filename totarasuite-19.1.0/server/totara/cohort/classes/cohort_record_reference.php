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
 * @package
 */

namespace totara_cohort;

use core\entity\cohort;
use core\entity\user;
use core\exception\unresolved_record_reference;
use core\webapi\reference\base_record_reference;
use stdClass;

class cohort_record_reference extends base_record_reference {

    /**
     * @inheritDoc
     */
    protected array $refine_columns = ['id', 'idnumber'];

    /**
     * @inheritDoc
     */
    protected function get_table_name(): string {
        return cohort::TABLE;
    }

    /**
     * @inheritDoc
     */
    protected function get_entity_name(): string {
        return 'Cohort';
    }

    /**
     * @inheritDoc
     */
    protected function convert_ref_columns_to_conditions(array $ref_columns = []): array {
        $conditions = parent::convert_ref_columns_to_conditions($ref_columns);
        $conditions['component'] = "";

        return $conditions;
    }

    /**
     * @inheritdoc
     */
    public static function load_for_viewer(array $ref_columns = [], user $actor = null): stdClass {
        $cohort_record_reference = new cohort_record_reference();
        $record = $cohort_record_reference->get_record($ref_columns);

        $cohort_interactor = cohort_interactor::for_user($actor);
        if ($cohort_interactor->tenant_enabled()) {
            if (!$cohort_interactor->is_under_same_tenancy_for_target_cohort($record->contextid)) {
                throw new unresolved_record_reference('You do not have capabilities to view a target audience.');
            }
        }

        return $record;
    }
}