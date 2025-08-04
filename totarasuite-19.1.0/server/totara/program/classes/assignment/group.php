<?php
/**
 * This file is part of Totara Learn
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

namespace totara_program\assignment;

use core\format;
use core\collection;
use core\entity\user;
use core\orm\entity\repository;
use core\orm\query\builder;
use core\webapi\formatter\field\string_field_formatter;
use totara_program\entity\prog_group;
use totara_program\entity\prog_group_user;
use totara_program\entity\program_assignment;

class group extends base {

    const ASSIGNTYPE_GROUP = 8;

    private ?prog_group $prog_group = null;

    /**
     * Loads in the group given its id.
     *
     * Note: the base class has a similar method: create_from_instance_id().
     * However that needs the caller to pass in a program id which makes things
     * difficult when you are trying to traverse through stuff in the opposite
     * direction to program -> assignment -> group.
     *
     * Note also the assumption here that groups are not shared across programs.
     *
     * @param int $id id of the group to look up.
     *
     * @return ?self the group if it was found.
     */
    public static function create_from_group_id(int $id): ?self {
        $assignment_id = program_assignment::repository()
            ->where('assignmenttypeid', $id)
            ->where('assignmenttype', self::ASSIGNTYPE_GROUP)
            ->one(false)
            ?->id;

         return $assignment_id ? static::create_from_id($assignment_id) : null;
    }

    /**
     * Get type for this assignment
     */
    public function get_type(): int {
        return self::ASSIGNTYPE_GROUP;
    }

    private function entity(): prog_group {
        if (!$this->prog_group) {
            $this->load_prog_group();
        }

        return $this->prog_group;
    }

    private function load_prog_group(): void {
        if (empty($this->instanceid)) {
            throw new \coding_exception('Tried to load prog group before id has been specified');
        }
        $this->prog_group = new prog_group($this->instanceid);
    }

    /**
     * Get user-friendly name for this assignment
     *
     * @return String
     */
    public function get_name(): string {
        $ctx = $this->get_program()->get_context();
        return (new string_field_formatter(format::FORMAT_PLAIN, $ctx))
            ->format($this->entity()->name);
    }

    /**
     * Returns the description.
     *
     * @return string the description.
     */
    public function get_description(): string {
        $ctx = $this->get_program()->get_context();
        return (new string_field_formatter(format::FORMAT_PLAIN, $ctx))
            ->format($this->entity()->description);
    }

    /**
     * Indicates whether a user can self enrol into this group.
     *
     * @return bool true if self enrolment is allowed.
     */
    public function can_self_enrol(): bool {
        return $this->entity()->can_self_enrol;
    }

    /**
     * Indicates whether a group member can unenrol from this group.
     *
     * @return bool true if self unenrolment is allowed.
     */
    public function can_self_unenrol(): bool {
        return $this->entity()->can_self_unenrol;
    }

    /**
     * Return learner count
     *
     * @return int
     */
    public function get_user_count(): int {
        return $this->entity()->group_users()->count();
    }

    /**
     * Return the set of members in this group.
     *
     * @param int[] $filter_user_ids Optional list of user ids to check - if provided, only
     *                               return users if they are in the given array.
     *
     * @return collection<user> the list of users.
     */
    public function get_users(array $filter_user_ids = []): collection {
        return $this->entity()
            ->group_users()
            ->when(
                !empty($filter_user_ids),
                function (repository $builder) use ($filter_user_ids) {
                    $builder->where_in('user_id', $filter_user_ids)
                        ->get()
                        ->map(fn(prog_group_user $it): user => $it->user);
                }
            )
            ->get()
            ->map(fn(prog_group_user $it): user => $it->user);
    }

    /**
     * @inheritDoc
     */
    public function remove(): bool {
        if ($this->prog_group === null) {
            $this->load_prog_group();
        }

        return builder::get_db()->transaction(function () {
            if (!parent::remove()) {
                return false;
            }

            $this->prog_group->delete();

            return true;
        });
    }

    /**
     * Removes users from a group and unassign them
     * @param array $user_ids
     * @throws \coding_exception
     * @throws \core\orm\query\exceptions\multiple_records_found_exception
     * @throws \core\orm\query\exceptions\record_not_found_exception
     */
    public function remove_users(array $user_ids) {
        global $DB;

        foreach ($user_ids as $user_id) {
            /** @var prog_group_user $group_user */
            $group_user = \core\orm\query\builder::table(prog_group_user::TABLE, 'u')
                ->select('u.*')
                ->join([program_assignment::TABLE, 'a'], 'u.prog_group_id', '=', 'a.assignmenttypeid')
                ->where('a.assignmenttype', '=', group::ASSIGNTYPE_GROUP)
                ->where('a.id', '=', $this->id)
                ->where('u.user_id', '=', $user_id)
                ->map_to(prog_group_user::class)
                ->one(true);

            $group_user->delete();
        }

        // Get array of users that are still assigned
        $sql = "SELECT id, userid FROM {prog_user_assignment}
                 WHERE programid = :programid
                 AND assignmentid != :assignmentid";
        $otherassignmentusers = $DB->get_records_sql_menu($sql, ['programid' => $this->programid, 'assignmentid' => $this->id]);

        foreach ($user_ids as $id => $userid) {
            if (in_array($userid, $otherassignmentusers)) {
                // Remove prog_user_assignment record
                $DB->delete_records('prog_user_assignment', ['programid' => $this->programid, 'userid' => $userid, 'assignmentid' => $this->id]);

                // Remove user from removed from the program
                unset($user_ids[$id]);
            }
        }

        $this->ensure_program_loaded();
        // Remove all learners from the assignment
        $this->program->unassign_learners($user_ids);
    }

    /**
     * Adds the specified users to the group. Note this assumes the caller has
     * done all the permission checks.
     *
     * Also creates program completion data and sets due date.
     *
     * @param user|iterable<user> users to add.
     *
     * @return self the updated group.
     */
    public function add_users(user|iterable $users): self {
        $group_id = $this->get_instanceid();

        // Convert the input into a collection.
        if ($users instanceof user) {
            $users = collection::new([$users]);
        } elseif (is_array($users)) {
            $users = collection::new($users);
        } elseif (!($users instanceof collection)) {
            $users = collection::new(iterator_to_array($users));
        }

        $existing_user_ids = $this->get_users($users->pluck('id'))->pluck('id');

        builder::get_db()->transaction(function () use ($users, $existing_user_ids, $group_id): void {
            $updated_users = [];
            foreach ($users as $user) {
                if (in_array($user->id, $existing_user_ids)) {
                    continue;
                }

                $group_user = new prog_group_user([
                    'user_id' => $user->id,
                    'prog_group_id' => $group_id,
                ]);
                $group_user->save();

                $updated_users[] = (object)['id' => $user->id];
            }

            if (!empty($updated_users)) {
                $this->update_assignments_for_users($updated_users);
            }
        });

        return $this;
    }
}
