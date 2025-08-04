<?php
/**
 * This file is part of Totara Learn
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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @package totara_notification
 */

namespace totara_notification\repository;

use core\orm\entity\repository;
use core\orm\query\builder;
use totara_core\extended_context;

class deletion_helper {

    /**
     * Delete records from notification tables based on the context
     *
     * @param repository $repository
     * @param extended_context $extended_context
     * @return void
     */
    public static function delete(repository $repository, extended_context $extended_context): void {
        // If it is not a natural context then it can have no descendents, so just delete in that context.
        if (!$extended_context->is_natural_context()) {
            $repository->get_builder()->where('context_id', $extended_context->get_context_id())
                ->where('component', $extended_context->get_component())
                ->where('area', $extended_context->get_area())
                ->where('item_id', $extended_context->get_item_id())
                ->delete();
            return;
        }

        $context = $extended_context->get_context();

        // The context might have been deleted.
        // In this case we cannot rely on the context table
        // to give us the ids to delete. To be on the safe side
        // we'll delete the records for the given context, at the very least.
        $repository->get_builder()
            ->where('context_id', $context->id)
            ->delete();

        // Find all contexts that are descendants of the given context.
        // Remove all records where they belong to one of the descendant contexts
        $sql = "
            DELETE FROM {{$repository->get_builder()->get_table()}} 
            WHERE context_id IN (
                SELECT id FROM {context} WHERE path LIKE '{$context->path}/%'
            )
        ";

        builder::get_db()->execute($sql);
    }

}