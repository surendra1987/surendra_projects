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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\reference;

use core\webapi\reference\base_record_reference;
use hierarchy_organisation\entity\organisation_framework;
use hierarchy_organisation\entity\organisation_type;

/**
 * Organisation record reference. Used to find one record by provided parameters
 */
class hierarchy_organisation_type_record_reference extends base_record_reference {
    /**
     * @inheritDoc
     */
    protected array $refine_columns = ['id', 'idnumber'];

    /**
     * @inheritDoc
     */
    protected function get_table_name(): string {
        return organisation_type::TABLE;
    }

    /**
     * @inheritDoc
     */
    protected function get_entity_name(): string {
        return 'Organisation Type';
    }
}
