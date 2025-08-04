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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\data_providers\activity;

use coding_exception;
use core\collection;
use core\orm\entity\repository;
use core\orm\query\builder;
use core\orm\query\order;
use core\pagination\offset_cursor;
use mod_perform\data_providers\provider;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\subject_instance;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\subject_instance_overview_item;
use mod_perform\state\participant_instance\complete as participant_instance_complete;
use mod_perform\state\participant_instance\in_progress as participant_instance_in_progress;
use mod_perform\state\subject_instance\closed as subject_instance_closed;
use mod_perform\state\subject_instance\complete as subject_instance_complete;
use mod_perform\state\subject_instance\in_progress as subject_instance_in_progress;
use mod_perform\state\subject_instance\not_started as subject_instance_not_started;

/**
 * Class subject_instance
 *
 * @package mod_perform\data_providers\activity
 */
class subject_instance_overview extends subject_instance_for_participant_provider {

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_PROGRESSED = 'progressed';
    public const STATUS_NOT_PROGRESSED = 'not_progressed';

    /**
     * @var offset_cursor
     */
    protected offset_cursor $cursor;

    /**
     * The total count. It's set when the fetch() method is executed.
     * It's different from the items count when paginating, so we store it separately.
     *
     * @var int|null
     */
    protected ?int $total;

    private string $status;

    /**
     * @param int $participant_id The id of the user we would like to get activities that they are participating in.
     * @param int $participant_source see participant_source model for constants
     */
    public function __construct(int $participant_id, int $participant_source) {
        $this->participant_id = $participant_id;
        $this->participant_source = $participant_source;
    }

    /**
     * @param bool $include_relations
     * @return repository
     */
    protected function build_query(bool $include_relations = true): repository {
        $repository = parent::build_query($include_relations);

        // We are only interested in subject instances where we are participating AND the subject.
        $repository->where('si.subject_user_id', $this->participant_id);

        if (!empty($this->status)) {
            $this->filter_query_by_status($repository, $this->status);
        }

        return $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(): provider {
        $this->fetched = false;

        if (empty($this->cursor)) {
            $query = $this->build_query();
            $this->apply_query_filters($query);
            $this->apply_query_sorting($query);
            $this->items = $query->get();
            $this->total = $this->items->count();
        } else {
            $paginator = $this->get_offset($this->cursor);
            $this->items = $paginator->get_items();
            $this->total = $paginator->get_total();
        }

        $this->fetched = true;
        $this->items = $this->process_fetched_items();

        return $this;
    }

    /**
     * @param offset_cursor $cursor
     * @return void
     */
    public function set_pagination(offset_cursor $cursor): void {
        $this->cursor = $cursor;
    }

    /**
     * Get completed subject instances
     *
     * @return collection
     */
    public function get_completed_subject_instances(): collection {
        $this->status = self::STATUS_COMPLETED;
        return $this->get();
    }

    /**
     * Get subject instances that were progressed after the start of the observation period.
     *
     * Subject instances where:
     * - the overall progress isn't set to closed
     * - the users instance is not set to not started
     * - the participant has made an update in the observation period
     *
     * @return collection
     */
    public function get_progressed_subject_instances(): collection {
        $this->status = self::STATUS_PROGRESSED;
        return $this->get();
    }

    /**
     * Get subject instances that were last progressed before the observation period.
     *
     * @return collection
     */
    public function get_not_progressed_subject_instances(): collection {
        $this->status = self::STATUS_NOT_PROGRESSED;
        return $this->get();
    }

    /**
     * Get subject instances that were not started at all (observation period doesn't matter).
     *
     * @return collection
     */
    public function get_not_started_subject_instances(): collection {
        $this->status = self::STATUS_NOT_STARTED;
        return $this->get();
    }

    /**
     * @return collection
     */
    public function get_completed_overview_items(): collection {
        return $this->get_completed_subject_instances()->map_to(
            fn (subject_instance $subject_instance) => new subject_instance_overview_item($this->participant_id, $subject_instance)
        );
    }

    /**
     * @return collection
     */
    public function get_progressed_overview_items(): collection {
        return $this->get_progressed_subject_instances()->map_to(
            fn (subject_instance $subject_instance) => new subject_instance_overview_item($this->participant_id, $subject_instance)
        );
    }

    /**
     * @return collection
     */
    public function get_not_progressed_overview_items(): collection {
        return $this->get_not_progressed_subject_instances()->map_to(
            fn (subject_instance $subject_instance) => new subject_instance_overview_item($this->participant_id, $subject_instance)
        );
    }

    /**
     * @return collection
     */
    public function get_not_started_overview_items(): collection {
        return $this->get_not_started_subject_instances()->map_to(
            fn (subject_instance $subject_instance) => new subject_instance_overview_item($this->participant_id, $subject_instance)
        );
    }

    /**
     * Get timestamp for the start of the observation period.
     *
     * @return int
     * @throws coding_exception
     */
    private function get_observation_period_start(): int {
        if (empty($this->filters['period']) || (int)$this->filters['period'] < 1) {
            throw new coding_exception('Period filter must be set to a positive integer.');
        }
        return time() - ((int)$this->filters['period'] * DAYSECS);
    }

    /**
     * Anything older than a certain time, we don't want to show.
     *
     * @return int timestamp
     */
    private function get_history_cut_off_time(): int {
        return strtotime('-2 years');
    }

    /**
     * @param repository $repository
     * @param bool $exclude_complete
     * @return void
     */
    protected function filter_query_by_period(repository $repository, bool $exclude_complete): void {
        // Does not need to be applied to the query.
    }

    /**
     * @param repository $repository
     */
    protected function sort_query_by_last_updated(repository $repository): void {
        // Does not need to be applied to the query.
    }

    /**
     * Filter query by status
     *
     * @param repository $repository
     * @param string $status
     * @return void
     */
    private function filter_query_by_status(repository $repository, string $status) {
        switch ($status) {
            case self::STATUS_COMPLETED:
                $repository->where('completed_at', '>', $this->get_observation_period_start())
                    ->where('completed_at', '>', $this->get_history_cut_off_time())
                    ->order_by('completed_at', order::DIRECTION_DESC);
                break;
            case self::STATUS_PROGRESSED:
                $this->filter_query_by_inside_observation_period($repository);
                break;
            case self::STATUS_NOT_PROGRESSED:
                $this->filter_query_by_inside_observation_period($repository, false);
                break;
            case self::STATUS_NOT_STARTED:
                $this->filter_query_by_not_started($repository);
                break;
        }
    }

    /**
     * Get subject instances where the subject hasn't progressed or completed their participant instance.
     * It doesn't matter what other participants have done.
     *
     * @param repository $repository
     * @return void
     */
    private function filter_query_by_not_started(repository $repository): void {
        $repository
            ->where_in('progress', [
                subject_instance_not_started::get_code(),
                subject_instance_in_progress::get_code()
            ])
            ->left_join([participant_instance::TABLE, 'ppi'], function (builder $builder) {
                $builder->where_field('si.id', 'subject_instance_id')
                    ->where('ppi.participant_source', '=', participant_source::INTERNAL)
                    ->where('ppi.participant_id', '=', $this->participant_id)
                    ->where_in('ppi.progress', [
                        participant_instance_in_progress::get_code(),
                        participant_instance_complete::get_code(),
                    ]);
            })
            ->where_null('ppi.id')
            ->where('created_at', '>', $this->get_history_cut_off_time())
            ->order_by('created_at', 'DESC');
    }

    /**
     * Implement the filter query for progressed instances and not progressed instances
     *
     * @param repository $repository
     * @param bool $inside_observation_period
     * @return void
     * @throws coding_exception
     */
    private function filter_query_by_inside_observation_period(repository $repository, bool $inside_observation_period = true): void {
        $update_at_column_name = 'pps.progress_updated_at';

        $repository->select(['id', 'completed_at', 'subject_user_id', 'progress'])
            ->add_select_raw("MAX({$update_at_column_name}) as max_updated_at")
            ->where('availability', '<>', subject_instance_closed::get_code())
            ->where('progress', '<>', subject_instance_complete::get_code())
            // Make sure we only get instances which have been started
            ->join([participant_instance::TABLE, 'ppi'], 'id', 'subject_instance_id')
            ->where('ppi.participant_source', '=', participant_source::INTERNAL)
            ->where('ppi.participant_id', '=', $this->participant_id)
            ->where_in('ppi.progress', [
                participant_instance_in_progress::get_code(),
                participant_instance_complete::get_code(),
            ])
            ->join([participant_section::TABLE, 'pps'], 'ppi.id', 'participant_instance_id')
            ->group_by(['id', 'completed_at', 'subject_user_id', 'progress'])
            ->order_by_raw('max_updated_at DESC');

        if ($inside_observation_period) {
            $repository->where($update_at_column_name, '>=', $this->get_observation_period_start());
        } else {
            $repository->where($update_at_column_name, '<', $this->get_observation_period_start());
        }

        // Dismiss changes that were made a very long time ago.
        $repository->where($update_at_column_name, '>', $this->get_history_cut_off_time());
    }

    /**
     * @return int
     * @throws coding_exception
     */
    public function get_total(): int {
        if ($this->total === null) {
            throw new coding_exception('Total has not been set. Please use this only after fetch() has been called.');
        }
        return $this->total;
    }
}