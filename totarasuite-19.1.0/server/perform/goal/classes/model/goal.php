<?php
/**
 * This file is part of Totara Perform
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
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\model;

use coding_exception;
use context;
use core\collection;
use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\model;
use core\orm\query\builder;
use perform_goal\entity\goal as goal_entity;
use perform_goal\event\personal_goal_created;
use perform_goal\event\personal_goal_deleted;
use perform_goal\event\personal_goal_details_updated;
use perform_goal\event\personal_goal_status_updated;
use perform_goal\model\status\status;
use perform_goal\model\status\status_helper;
use perform_goal\settings_helper;
use perform_goal\totara_comment\comment_resolver;
use totara_comment\comment_helper;

/**
 * Perform goal model class
 *
 * Properties:
 * @property-read int $id
 * @property-read int $context_id
 * @property-read int $category_id
 * @property-read int $owner_id
 * @property-read int|null $user_id
 * @property-read string $name
 * @property-read string|null $id_number
 * @property-read string|null $description
 * @property-read int $start_date
 * @property-read string $target_type
 * @property-read int $target_date
 * @property-read float $target_value
 * @property-read float $current_value
 * @property-read int $current_value_updated_at
 * @property-read int $status_updated_at
 * @property-read int|null $closed_at
 * @property-read int $created_at
 * @property-read int $updated_at
 * @property-read int $comment_count
 * @property-read goal_tasks_metadata $goal_tasks_metadata
 *
 * Relations:
 * @property-read collection|goal_task[] $tasks
 * @property-read user $owner
 * @property-read user $user
 * @property-read context $context
 * @property-read string $assignment_type
 * @property-read status $status
 * @property-read string $plugin_name
 * @property-read collection|goal_activity[] $activities
 */
abstract class goal extends model {

    /** @var int Maximum string length for the name property */
    private const NAME_MAX_LENGTH = 1024;

    /** @var int Max value for current value and target value properties */
    private const CURRENT_TARGET_MAX_VALUE = 99999;

    /**
     * Statuses available to the model.
     *
     * @var string[]
     */
    protected static $status_options = [
        'not_started',
        'in_progress',
        'completed',
        'cancelled'
    ];

    /**
     * @var string[]
     */
    private static $target_type_options = ['date'];

    /** @var goal_entity */
    protected $entity;

    /** @var string[] */
    protected $entity_attribute_whitelist = [
        'id',
        'context_id',
        'category_id',
        'owner_id',
        'user_id',
        'name',
        'id_number',
        'description',
        'start_date',
        'target_type',
        'target_date',
        'target_value',
        'current_value',
        'current_value_updated_at',
        'status_updated_at',
        'closed_at',
        'created_at',
        'updated_at',
    ];

    /** @var string[] */
    protected $model_accessor_whitelist = [
        'activities',
        'user',
        'owner',
        'category',
        'context',
        'status',
        'assignment_type',
        'plugin_name',
        'tasks',
        'comment_count',
        'goal_tasks_metadata',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return goal_entity::class;
    }

    /**
     * Creates a new goal record and returns a model instance for it.
     *
     * @param context $context
     * @param goal_category $goal_category
     * @param string $name
     * @param int $start_date
     * @param string $target_type
     * @param int $target_date
     * @param float $target_value
     * @param float $current_value
     * @param string $status
     * @param int|null $owner_id
     * @param int|null $user_id
     * @param string|null $id_number
     * @param string|null $description
     * @return goal
     */
    public static function create(
        context $context,
        goal_category $goal_category,
        string $name,
        int $start_date,
        string $target_type,
        int $target_date,
        float $target_value,
        float $current_value,
        string $status,
        ?int $owner_id = null,
        ?int $user_id = null,
        ?string $id_number = null,
        ?string $description = null
    ): goal {
        self::validate_category($goal_category);
        $name = trim($name);
        self::validate_name($name);
        self::validate_owner_id($owner_id);
        self::validate_user_id($user_id);
        self::validate_id_number($id_number);
        self::validate_status_code($status);
        self::validate_target_type($target_type);
        self::validate_start_and_target_dates($start_date, $target_date);
        self::validate_value($current_value, 'current');
        self::validate_value($target_value, 'target');

        // Ok, everything looks valid. But are we using the correct model?
        $model_class = goal_category::get_goal_model_class($goal_category->plugin_name);
        if ($model_class != static::class) {
            return call_user_func(
                $model_class . '::create',
                $context,
                $goal_category,
                $name,
                $start_date,
                $target_type,
                $target_date,
                $target_value,
                $current_value,
                $status,
                $owner_id,
                $user_id,
                $id_number,
                $description,
            );
        }

        // We must be in the correct model, ok create the entity.
        $entity = self::create_goal_entity(
            $context,
            $goal_category,
            $name,
            $start_date,
            $target_type,
            $target_date,
            $target_value,
            $current_value,
            $status,
            $owner_id,
            $user_id,
            $id_number,
            $description,
        );

        // Once the details for the activity_type models have been fleshed out,
        // this part will have to be changed to update the goal_activity table
        // via the new activity_type/goal_activity models.
        if ($user_id) {
            // Only personal goals have the user id set.
            personal_goal_created::create_from_instance($entity)->trigger();
        }

        return static::load_by_entity($entity);
    }

    /**
     * Create the underlying goal entity record for this model.
     *
     * @param context $context
     * @param goal_category $goal_category
     * @param string $name
     * @param int $start_date
     * @param string $target_type
     * @param int $target_date
     * @param float $target_value
     * @param float $current_value
     * @param string $status
     * @param int|null $owner_id
     * @param int|null $user_id
     * @param string|null $id_number
     * @param string|null $description
     * @return goal_entity
     */
    protected static function create_goal_entity(
        context $context,
        goal_category $goal_category,
        string $name,
        int $start_date,
        string $target_type,
        int $target_date,
        float $target_value,
        float $current_value,
        string $status,
        ?int $owner_id,
        ?int $user_id,
        ?string $id_number,
        ?string $description
    ) {
        $entity = new goal_entity();
        $entity->context_id = $context->id;
        $entity->category_id = $goal_category->id;
        $entity->owner_id = $owner_id ?? user::logged_in()->id;
        $entity->user_id = $user_id;
        $entity->name = $name;
        $entity->id_number = $id_number;
        $entity->description = $description;
        $entity->start_date = $start_date;
        $entity->target_type = $target_type;
        $entity->target_date = $target_date;
        $entity->target_value = $target_value;
        $entity->current_value = $current_value;
        $entity->current_value_updated_at = is_null($current_value) ? null : time();
        $entity->status = $status ?? 'not_started';
        $entity->status_updated_at = time();
        $entity->save();

        return $entity;
    }

    /**
     * Update status and current value of current goal
     *
     * @param string $status_code
     * @param float $current_value
     * @return $this
     */
    public function update_progress(string $status_code, float $current_value): self {
        builder::get_db()->transaction(function () use ($status_code, $current_value) {
            $this
                ->update_status($status_code)
                ->update_current_value($current_value);
            personal_goal_status_updated::create_from_instance($this->entity)->trigger();
        });
        return $this->refresh(true);
    }

    /**
     * Validate and set status of current goal
     *
     * @param string $status_code
     * @return $this
     */
    protected function update_status(string $status_code): self {
        self::validate_status_code($status_code);
        if ($this->entity->status !== $status_code) {
            $new_status = static::status_from_code($status_code);
            if ($new_status::is_closed() && is_null($this->entity->closed_at)) {
                // Switching to a closed status and closed_at property is null, so set it to the current time.
                $this->entity->closed_at = time();
            }
            $this->entity->status = $status_code;
            $this->entity->status_updated_at = time();
            $this->entity->save();
        }
        // No trigger event at this point, use this::update_progress(...)
        return $this->refresh();
    }

    /**
     * Validate and set current value of current goal
     *
     * @param float $current_value
     * @return $this
     */
    protected function update_current_value(float $current_value): self {
        self::validate_value($current_value, 'current');
        $original_value = $this->entity->current_value;
        // Converts a locale specific floating point/comma number back to a standard PHP float value.
        // Do NOT try to do any math operations before this conversion on any user submitted floats!
        // Requires to save to DB as the standard PHP float value.
        $this->entity->current_value = unformat_float($current_value);
        if (abs($original_value - $this->entity->current_value) > PHP_FLOAT_EPSILON) {
            $this->entity->current_value_updated_at = time();
            $this->entity->save();
        }
        // No trigger event at this point, use this::update_progress(...)
        return $this->refresh();
    }

    /**
     * Updates goal details (excluding non status and progress values).
     *
     * @param string|null $name
     * @param string|null $id_number
     * @param string|null $description
     * @param int|null $start_date
     * @param string|null $target_type
     * @param int|null $target_date
     * @param float|null $target_value
     * @return goal
     * @throws coding_exception
     */
    public function update(
        ?string $name = null,
        ?string $id_number = null,
        ?string $description = null,
        ?int $start_date = null,
        ?string $target_type = null,
        ?int $target_date = null,
        ?float $target_value = null
    ): goal {
        if (!is_null($name)) {
            $name = trim($name);
            self::validate_name($name);
        }
        self::validate_id_number($id_number);
        self::validate_target_type($target_type);
        self::validate_start_and_target_dates($start_date ?? $this->start_date, $target_date ?? $this->target_date);

        if (!is_null($target_value)) {
            self::validate_value($target_value, 'target');
        }

        $fields_for_update_available = ['name', 'id_number', 'description', 'start_date', 'target_type', 'target_date', 'target_value'];
        foreach ($fields_for_update_available as $field) {
            if (!is_null(${$field})) {
                $this->entity->{$field} = ${$field};
            }
        }
        $this->entity->save();

        if ($this->entity->user_id) {
            // Only personal goals have the user id set.
            personal_goal_details_updated::create_from_instance($this->entity)->trigger();
        }

        return self::load_by_entity($this->entity->refresh());
    }

    /**
     * Override for model::load_by_entity(), uses the goal_category relation to determine exactly which model
     * class to instantiate.
     *
     * @param goal_entity $entity
     * @return mixed|model
     */
    public static function load_by_entity(entity $entity) {
        $entity_class = static::get_entity_class();

        if (!$entity instanceof $entity_class) {
            throw new \coding_exception('Expected entity class to match model class');
        }

        if (!$entity->exists()) {
            throw new \coding_exception('Can load only existing entities');
        }

        // Use category relation to discover which goaltype sub-plugin is in use.
        /** @var goal_entity $entity */
        $model_class = goal_category::get_goal_model_class($entity->category->plugin_name);

        return new $model_class($entity);
    }

    /**
     * Delete goal
     *
     * @param goal $goal
     * @return bool
     */
    public function delete(): bool {
        global $DB;

        $DB->transaction(
            function () {
                // Delete comments. This will also delete the reaction records for the comments.
                comment_helper::purge_area_comments(
                    settings_helper::get_component(),
                    comment_resolver::AREA,
                    $this->id
                );

                $this->delete_files();
                $event = personal_goal_deleted::create_from_instance($this->entity);
                $this->entity->delete();
                $event->trigger();
            }
        );
        return true;
    }

    /**
     * Get the parent context object for this goal.
     *
     * @return context
     */
    public function get_context(): context {
        return context::instance_by_id($this->context_id);
    }

    /**
     * Get the related user entity.
     * Returns null when there is no (or an invalid) user_id for this goal.
     *
     * @return user|entity|null
     */
    public function get_user(): ?user {
        return is_null($this->user_id)
            ? null
            : user::repository()->find($this->user_id);
    }

    /**
     * Get the related user entity for the owner of the goal.
     * Returns null when there is no (or an invalid) owner_id for this goal.
     *
     * @return user|entity|null
     */
    public function get_owner(): ?user {
        return is_null($this->owner_id)
            ? null
            : user::repository()->find($this->owner_id);
    }

    /**
     * Get the related goal activities.
     *
     * @return collection
     */
    public function get_activities(): collection {
        return $this->entity->activities->sort('id')->map_to(goal_activity::class);
    }

    /**
     * Get the related goal tasks ordered by task id.
     *
     * @return collection|goal_task[]
     */
    public function get_tasks(): collection {
        return $this->entity->tasks->sort('id')->map_to(goal_task::class);
    }

    /**
     * Get a tasks metadata object for this goal.
     *
     * @return goal_tasks_metadata
     */
    public function get_goal_tasks_metadata(): goal_tasks_metadata {
        return new goal_tasks_metadata($this);
    }

    /**
     * Get the associated goal_category.
     *
     * @return goal_category
     */
    public function get_category(): goal_category {
        return goal_category::load_by_id($this->category_id);
    }

    /**
     * Get the current status (as a status instance) of this goal.
     *
     * @return status
     */
    public function get_status(): status {
        return static::status_from_code($this->entity->status);
    }

    /**
     * Get the number of comments that exist for this goal.
     *
     * @return int
     */
    public function get_comment_count(): int {
        return $this->entity->comments()->count();
    }

    /**
     * Reload the properties on the model's entity.
     *
     * @param bool $reload
     * @return self
     */
    public function refresh(bool $reload = false): self {
        if ($reload) {
            $class = static::get_entity_class();
            $this->entity = new $class($this->id);
        } else {
            $this->entity->refresh();
        }
        return $this;
    }

    /**
     * Gets an instance of the status class corresponding to a status code.
     *
     * Will check for matching status implementation in goaltype subplugin first, then in core.
     *
     * @param string $status_code
     * @return status
     */
    public static function status_from_code(string $status_code): status {
        return status_helper::status_from_code($status_code);
    }

    /**
     * Gets an array of code => label pairs, one for each status available to the model.
     *
     * @return array
     */
    public static function get_status_choices(): array {
        $choices = [];
        foreach (static::$status_options as $status_option) {
            $status = static::status_from_code($status_option);
            $choices[$status_option] = $status::get_label();
        }

        return $choices;
    }

    /**
     * Get the goal's assignment type.
     *
     * @return string the type
     */
    public function get_assignment_type(): string {
        // Hardcoded for now.
        return 'Self';
    }

    /**
     * Get this goal's goaltype plugin_name, which is the part after 'goaltype_' in the component name.
     *
     * @return string
     */
    public function get_plugin_name(): string {
        return $this->get_category()->plugin_name;
    }

    /**
     * Validate a goal_category for creating a goal
     *
     * @param goal_category $goal_category
     * @throws coding_exception
     */
    private static function validate_category(goal_category $goal_category): void {
        if (!$goal_category->active) {
            throw new coding_exception('Goals can only be created on an active goal_category');
        }
        // Ensure that plugin still exists.
        $class = goal_category::get_goal_model_class($goal_category->plugin_name);
        if (!class_exists($class)) {
            throw new coding_exception("The goaltype plugin {$goal_category->plugin_name} for goal_category {$goal_category->name} does not exist.");
        }
    }

    /**
     * Validate the name of the goal.
     *
     * @param string $name
     * @return void
     * @throws coding_exception
     */
    private static function validate_name(string $name): void {
        if ($name === '') {
            throw new coding_exception('Goal must have a name.');
        }

        if (strlen($name) > self::NAME_MAX_LENGTH) {
            throw new coding_exception('Goal name must not be longer than ' . self::NAME_MAX_LENGTH . ' characters.');
        }
    }

    /**
     * Validate the status of the goal.
     *
     * @param string|null $status_code
     * @return void
     * @throws coding_exception
     */
    private static function validate_status_code(?string $status_code): void {
        if (is_null($status_code)) {
            return; // null is fine.
        }

        if (!in_array($status_code, static::$status_options)) {
            throw new coding_exception('Invalid status: ' . $status_code);
        }
    }

    /**
     * Validate the user_id for a goal.
     *
     * @param int|null $user_id
     * @return void
     * @throws coding_exception
     */
    private static function validate_user_id(?int $user_id, $field_name = 'user_id'): void {
        if (is_null($user_id)) {
            return; // null is fine.
        }

        if (!user::repository()->where('id', $user_id)->exists()) {
            throw new coding_exception("Invalid {$field_name}: " . $user_id);
        }
    }

    /**
     * Validate the owner_id for a goal.
     *
     * @param int|null $owner_id
     * @return void
     * @throws coding_exception
     */
    private static function validate_owner_id(?int $owner_id): void {
        self::validate_user_id($owner_id, 'owner_id');
    }

    /**
     * Validate the id_number for a goal.
     *
     * @param string|null $id_number
     * @return void
     * @throws coding_exception
     */
    private static function validate_id_number(?string $id_number): void {
        if (is_null($id_number)) {
            return; // null is fine.
        }

        if (goal_entity::repository()->where('id_number', $id_number)->exists()) {
            throw new coding_exception('id_number already exists: ' . $id_number);
        }
    }

    /**
     * Validate the current_value/target_value as float value for a goal.
     *
     * @param float|null $value value to validate.
     * @return void
     * @throws coding_exception
     */
    private static function validate_value(?float $value, string $tag): void {
        $error = "Invalid $tag value: " . $value;
        if (is_null($value)) {
            throw new coding_exception($error);
        }

        $float_value = (float) $value;
        if (strval($float_value) != $value) {
            throw new coding_exception($error);
        }

        if ($float_value < 0 || $float_value > self::CURRENT_TARGET_MAX_VALUE) {
            throw new coding_exception($error);
        }
    }

    /**
     * @param string|null $target_type
     * @return void
     * @throws coding_exception
     */
    private static function validate_target_type(?string $target_type = null): void {
        if (is_null($target_type)) {
            return; // null is fine.
        }
        if (!in_array($target_type, self::$target_type_options)) {
            throw new coding_exception('Invalid target_type value: ' . $target_type . '.');
        }
    }

    /**
     * @param int|null $start_date
     * @param int|null $target_date
     * @return void
     * @throws coding_exception
     */
    private static function validate_start_and_target_dates(?int $start_date = null, ?int $target_date = null): void {
        if (is_null($start_date) || is_null($target_date)) {
            return; // We can't perform a comparison.
        }
        if ($start_date > $target_date) {
            throw new coding_exception('The goal start date must before the target_date.');
        }
    }

    /**
     * Purge the goal files for the given user.
     *
     * @return void
     */
    private function delete_files(): void {
        $fs = get_file_storage();
        builder::table('files')
            ->join([goal_entity::TABLE, 'goal'], 'itemid', 'goal.id')
            ->where('goal.context_id', $this->context_id)
            ->where('itemid', $this->id)
            ->where('component', settings_helper::get_component())
            ->where('filearea', settings_helper::get_filearea())
            ->get()
            ->map(function (object $file) use ($fs) {
                $fs->get_file_instance($file)->delete();
            });
    }
}