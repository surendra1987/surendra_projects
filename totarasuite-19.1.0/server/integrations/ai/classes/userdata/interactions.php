<?php
/*
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package core_ai
 */

namespace core_ai\userdata;

use context;
use core_ai\entity\interaction_log;
use totara_userdata\userdata\export;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

/**
 * Does GDPR userdata processing for the user/ai interactions.
 */
class interactions extends item {
    /**
     * Transforms an ai interaction into a set of property value pairs. This can
     * then be used for exporting for example.
     *
     * @param interaction_log $record the interaction to format.
     *
     * @return array<string,mixed> the key value pairs.
     */
    public static function as_key_value_pairs(interaction_log $record): array {
        return [
            'interaction' => $record->interaction,
            'request' => $record->request,
            'response' => $record->response,
            'plugin' => $record->plugin,
            'feature' => $record->feature
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function get_fullname_string() {
        return ['userdataiteminteractions', 'ai'];
    }

    /**
     * {@inheritDoc}
     */
    public static function get_main_component() {
        return 'core_ai';
    }

    /**
     * {@inheritDoc}
     */
    public static function is_countable() {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected static function count(target_user $user, context $context) {
        return interaction_log::repository()
            ->where('user_id', $user->get_user_record()->id)
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public static function is_exportable() {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * NB: the context is ignored.
     */
    protected static function export(target_user $user, context $context) {
        return interaction_log::repository()
            ->where('user_id', $user->get_user_record()->id)
            ->get()
            ->reduce(
                function(export $acc, interaction_log $it): export {
                    $acc->data[] = self::as_key_value_pairs($it);
                    return $acc;
                },
                new export()
            );
    }

    /**
     * {@inheritDoc}
     */
    public static function is_purgeable(int $userstatus) {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * NB: the context is ignored.
     */
    protected static function purge(target_user $user, context $context) {
        interaction_log::repository()
            ->where('user_id', $user->get_user_record()->id)
            ->delete();

        return self::RESULT_STATUS_SUCCESS;
    }
}