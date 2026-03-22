<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_course
 */

namespace core_course\rb\traits;

use coding_exception;

trait tenant_aware {

    /**
     * Adds conditions to the context join to limit records returned to the tenant.
     * @param int $tenant_id
     * @param bool $tenantsisolated
     * @return void
     * @throws coding_exception
     */
    protected function apply_multitenancy(int $tenant_id, bool $tenantsisolated): void {
        // find and modify the existing ctx join in joinlist
        $ctx_join = $this->get_join('ctx');

        if (empty($ctx_join)) {
            throw new coding_exception("Missing context join(ctx), it must be defined in the joinlist");
        }
        $tenant_conditions = $tenantsisolated
            ? " AND ctx.tenantid = $tenant_id"
            : " AND (ctx.tenantid = $tenant_id OR ctx.tenantid IS NULL)";

        // Add tenant conditions when it has not yet been applied.
        // This is because the build_query method is called multiple times through:
        // result_count_info() and report_html()
        if (strpos($ctx_join->conditions, $tenant_conditions) === false) {
            $ctx_join->conditions .= $tenant_conditions;
        }
    }
}