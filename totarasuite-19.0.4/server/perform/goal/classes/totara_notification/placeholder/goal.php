<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara_notification\placeholder;

use moodle_url;
use html_writer;
use coding_exception;
use perform_goal\controllers\goals_overview;
use perform_goal\model\goal as goal_model;
use totara_notification\placeholder\abstraction\placeholder_instance_cache;
use totara_notification\placeholder\abstraction\single_emptiable_placeholder;
use totara_notification\placeholder\option;

final class goal extends single_emptiable_placeholder {
    use placeholder_instance_cache;

    // Allowed placeholder options.
    public const OPTION_NAME = 'name';
    public const OPTION_NAME_LINKED = 'name_linked';

    /**
     * Create a goal placeholder given a goal id.
     *
     * @param int $id the goal id.
     *
     * @return self the placeholder instance.
     */
    public static function from_id(int $goal_id): self {
        $placeholder = self::get_cached_instance($goal_id);
        if ($placeholder) {
            return $placeholder;
        }

        $goal = goal_model::load_by_id($goal_id);
        $goal_name = $goal->name;
        $url = new moodle_url(goals_overview::BASE_URL, ['goal' => $goal->id]);

        $values = [
            self::OPTION_NAME => $goal_name,
            self::OPTION_NAME_LINKED => html_writer::link(
                $url, format_string($goal_name)
            )
        ];

        $placeholder = new self($values);
        self::add_instance_to_cache($goal_id, $placeholder);

        return $placeholder;
    }

    /**
     * Default constructor.
     *
     * @param array<string,mixed> values a mapping of placeholder option names
     *        to their values.
     */
    private function __construct(private array $values) {
        // EMPTY BLOCK.
    }

    /**
     * @inheritDoc
     *
     * @see totara_notification\placeholder\abstraction\placeholder
     */
    public static function get_options(): array {
        return [
            option::create(
                self::OPTION_NAME,
                get_string('notification_placeholder_goal_name', 'perform_goal')
            ),
            option::create(
                self::OPTION_NAME_LINKED,
                get_string('notification_placeholder_goal_name_linked', 'perform_goal')
            )
        ];
    }

    /**
     * @inheritDoc
     *
     * @see single_emptiable_placeholder
     */
    protected function is_available(string $key): bool {
        $allowed_keys = array_map(
            fn (option $option): string => $option->get_key(),
            self::get_options()
        );

        return in_array($key, $allowed_keys);
    }

    /**
     * @inheritDoc
     *
     * @see single_emptiable_placeholder
     */
    public function do_get(string $key): string {
        return array_key_exists($key, $this->values)
            ? $this->values[$key]
            : throw new coding_exception("Unknown placeholder key '$key'");
    }

    /**
     * @inheritDoc
     *
     * @see single_emptiable_placeholder
     */
    public static function is_safe_html(string $key): bool {
        return $key === self::OPTION_NAME_LINKED || parent::is_safe_html($key);
    }
}