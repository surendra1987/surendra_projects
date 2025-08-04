<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core_block
 */

namespace core_block\usagedata;

use core\orm\query\builder;
use tool_usagedata\export;

class type_per_contextlevel implements export {

    public function get_summary(): string {
        return get_string('type_per_contextlevel_summary', 'block');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_ARRAY;
    }

    /**
     * @throws \coding_exception
     */
    public function export(): array {
        $query = builder::table('block_instances', 'bi')
            ->select(['bi.blockname', 'COUNT(bi.id) AS blockcount'])
            ->join(['context', 'ctx'], 'parentcontextid', 'id')
            ->group_by('bi.blockname');

        $modifiers = [
            'system' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_SYSTEM),
            'tenant' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_TENANT),
            'user' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_USER),
            'coursecat' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_COURSECAT),
            'program' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_PROGRAM),
            'course' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_COURSE),
            'module' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_MODULE),
            'block' => fn(builder $builder) => $builder->where('ctx.contextlevel', CONTEXT_BLOCK),
            'other' => fn(builder $builder) => $builder->where_not_in('ctx.contextlevel', [
                CONTEXT_SYSTEM, CONTEXT_TENANT, CONTEXT_USER, CONTEXT_COURSECAT,
                CONTEXT_PROGRAM, CONTEXT_COURSE, CONTEXT_MODULE, CONTEXT_BLOCK
            ])
        ];

        $results = [];
        foreach ($modifiers as $name => $modifier) {
            $query->remove_where('ctx.contextlevel');
            $modifier($query);
            $blocks = $query->fetch();
            $results[] = [
                'context_level' => $name,
                'blocks' => array_values($blocks),
            ];
        }

        return $results;
    }
}
