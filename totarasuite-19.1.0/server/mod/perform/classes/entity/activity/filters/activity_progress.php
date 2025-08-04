<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\entity\activity\filters;

use coding_exception;
use core\collection;
use core\orm\entity\filter\filter;
use core\orm\query\builder;
use mod_perform\models\activity_progress as activity_progress_enum;
use mod_perform\state\participant_instance\complete;

final class activity_progress extends filter {
    /**
     * @var string
     */
    private string $si_alias;

    /**
     * Default constructor.
     *
     * @param string $si_alias the subject instance table alias to use when
     *        filtering. Of course, this also means the repository this filter
     *        manipulates must also be joined to/created from this table.
     */
    public function __construct(string $si_alias = 'si') {
        parent::__construct();
        $this->si_alias = $si_alias;
    }

    /**
     * Adds a filter clause depending on the type of progress value passed in.
     *
     * @param builder $builder the ORM builder to populate.
     * @param activity_progress_enum $value the progress value to filter by.
     * @param string $where the specific filter method to invoke on the builder.
     *        This should be a normal "where" the first time the constraint is
     *        added whereas it should be 'where_or' subsequently.
     *
     * @return builder the updated builder.
     */
    private function add_constraint(
        builder $builder, activity_progress_enum $value, string $where
    ): builder {
        $si = $this->si_alias;
        $progress = "$si.progress";
        $due_date = "$si.due_date";

        $complete = complete::get_code();

        switch (true) {
            case activity_progress_enum::complete()->is_equal_to($value):
                return $builder->$where($progress, '=', $complete);

            case activity_progress_enum::incomplete()->is_equal_to($value):
                return $builder->$where($progress, '!=', $complete);

            case activity_progress_enum::overdue()->is_equal_to($value):
                // The code below is taken from the original overdue filter (see
                // class subject_instances_overdue) which only targets subject
                // instances whose progress is _incomplete_.
                //
                // When the subject instance is closed, its progress changes to
                // 'complete' (see subject_instance::manually_close()). Which
                // means a closed subject instance can never be overdue.

                // If the due date falls within today, it is technically not
                // overdue until 00:00:00 tommorrow - in the user's timezone.
                $beginning_of_today = usergetmidnight(time());

                return $builder->$where(
                    fn (builder $b1): builder => $b1
                        ->where_not_null($due_date)
                        ->where($progress, '!=', $complete)
                        ->where($due_date, '<', $beginning_of_today)
                );

            default:
                throw new coding_exception(
                    "unknown activity progress: {$value->name}"
                );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function apply() {
        $ex = new coding_exception(
            __METHOD__ . ' expects ' . activity_progress_enum::class . '[]'
        );

        if (!is_array($this->value)) {
            throw $ex;
        }

        $invalid = array_filter(
            $this->value,
            fn ($value): bool => !($value instanceof activity_progress_enum)
        );

        if ($invalid) {
            throw $ex;
        }

        $targets = array_unique($this->value, SORT_REGULAR);
        $initial = $targets[0];
        $remaining = collection::new(array_slice($targets, 1));

        // This produces this SQL snippet:
        // (
        //      WHERE(<1st progress constraint>)
        //      OR WHERE(<2nd progress constraint>)
        //      ...
        //      OR_WHERE(<nth progress constraint>)
        // )
        $this->builder->where(
            fn (builder $b): builder => $remaining->reduce(
                fn (builder $b1, activity_progress_enum $value): builder
                    => self::add_constraint($b1, $value, 'or_where'),
                self::add_constraint($b, $initial, 'where')
            )
        );
    }
}