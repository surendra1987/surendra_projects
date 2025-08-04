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
use core\collection;
use core\orm\entity\model;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\event\goal_category_activated;
use perform_goal\event\goal_category_created;
use perform_goal\event\goal_category_deactivated;

/**
 * Perform goal_category model class, associates a semantic name with a goaltype plugin.
 *
 * Properties:
 * @property-read int $id
 * @property-read string $plugin_name Plugin name
 * @property-read string $name Category name
 * @property-read string|null $id_number Optional unique identifier
 * @property-read bool $active Whether this category is selectable for new goals
 * @property-read string $component Frankenstyle plugin name, like 'goaltype_basic'
 *
 * Relations:
 * @property-read collection|goal[] $goals
 *
 */
class goal_category extends model {

    /** @var int Maximum string length for the name property */
    private const NAME_MAX_LENGTH = 1024;

    /** @var string Hint used to prefix names of system categories, so that they can be translated at runtime */
    private const LANG_STRING_HINT = 'multilang:';

    /** @var goal_category_entity */
    protected $entity;

    /** @var string[] */
    protected $entity_attribute_whitelist = [
        'id',
        'plugin_name',
        'id_number',
        'active',
    ];

    /** @var string[] */
    protected $model_accessor_whitelist = [
        'name',
        'component',
        'goals',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return goal_category_entity::class;
    }

    /**
     * Returns name from lang string if prefixed with lang string hint, otherwise entity name.
     *
     * @return string
     */
    public function get_name(): string {
        if (substr($this->entity->name, 0, strlen(self::LANG_STRING_HINT)) == self::LANG_STRING_HINT) {
            $namepart = substr($this->entity->name, strlen(self::LANG_STRING_HINT));
            $lang_string = 'categoryname:' . $namepart;
            if (get_string_manager()->string_exists($lang_string, $this->component)) {
                return get_string($lang_string, $this->component);
            }
        }
        return $this->entity->name;
    }

    /**
     * Gets the frankenstyle component name for this category's goaltype plugin.
     *
     * @return string
     */
    public function get_component(): string {
        return 'goaltype_' . $this->plugin_name;
    }

    /**
     * Get the related goals.
     *
     * @return collection|goal[]
     */
    public function get_goals(): collection {
        return $this->entity->goals->sort('id')->map_to(goal::class);
    }

    /**
     * Given a plugin name, return the class name of the matching goal model.
     *
     * @param string $plugin_name
     * @return string
     */
    public static function get_goal_model_class(string $plugin_name): string {
        return "goaltype_{$plugin_name}\\model\\{$plugin_name}_goal";
    }

    /**
     * Create a new, not-yet-activated goal_category object.
     *
     * @param string $plugin_name
     * @param string $name
     * @param string|null $id_number
     * @return static
     */
    public static function create(string $plugin_name, string $name, ?string $id_number = null): self {
        $plugin_name = trim($plugin_name);
        self::validate_plugin_name($plugin_name);
        $name = trim($name);
        self::validate_name($name);
        self::validate_id_number($id_number);

        $entity = new goal_category_entity();
        $entity->plugin_name = $plugin_name;
        $entity->name = $name;
        $entity->id_number = $id_number;
        $entity->active = false;
        $entity->save();

        $event = goal_category_created::create_from_instance($entity);
        $event->trigger();

        return self::load_by_entity($entity);
    }

    /**
     * Validate the goaltype sub-plugin name.
     *
     * @param string $plugin_name
     * @return void
     * @throws coding_exception
     */
    private static function validate_plugin_name(string $plugin_name): void {
        if ($plugin_name === '') {
            throw new coding_exception('Goal category must have a plugin name.');
        }

        $class_name = self::get_goal_model_class($plugin_name);
        if (!class_exists($class_name)) {
            throw new coding_exception("Invalid goal_category plugin name, class {$plugin_name} does not exist.");
        }
    }

    /**
     * Validate the name of the goal_category.
     *
     * @param string $name
     * @return void
     * @throws coding_exception
     */
    private static function validate_name(string $name): void {
        if ($name === '') {
            throw new coding_exception('Goal category must have a name.');
        }

        if (strlen($name) > self::NAME_MAX_LENGTH) {
            throw new coding_exception('Goal category name must not be longer than ' . self::NAME_MAX_LENGTH . ' characters.');
        }
    }

    /**
     * Validate the id_number for a goal_category.
     *
     * @param string|null $id_number
     * @return void
     * @throws coding_exception
     */
    private static function validate_id_number(?string $id_number): void {
        if (is_null($id_number)) {
            return; // null is fine.
        }

        if (goal_category_entity::repository()->where('id_number', $id_number)->exists()) {
            throw new coding_exception('Goal category id_number already exists: ' . $id_number);
        }
    }

    /**
     * Activate this object.
     *
     * @return self
     */
    public function activate(): self {
        if (!$this->entity->active) {
            $this->entity->active = true;
            $this->entity->save();

            $event = goal_category_activated::create_from_instance($this->entity);
            $event->trigger();
        }
        return $this;
    }

    /**
     * Deactivate this object.
     *
     * @return self
     */
    public function deactivate(): self {
        if ($this->entity->active) {
            $this->entity->active = false;
            $this->entity->save();

            $event = goal_category_deactivated::create_from_instance($this->entity);
            $event->trigger();
        }
        return $this;
    }

    /**
     * Reload the properties on the model's entity.
     *
     * @return self
     */
    public function refresh(): self {
        $this->entity->refresh();

        return $this;
    }
}