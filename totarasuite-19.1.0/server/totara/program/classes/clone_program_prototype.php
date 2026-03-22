<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_program
 */

namespace totara_program;

use core\orm\query\builder;
use totara_certification\model\certification;
use totara_core\extended_context;
use totara_notification\manager\notification_preference_manager;
use totara_program\event\program_cloned;

class  clone_program_prototype {
    /**
     * CLONE_SECTION_XXX refers to the enum values of the endpoint
     * see clone_program.graphqls for the enum declaration
     */
    protected const CLONE_SECTION_DETAILS = 'DETAILS';
    protected const CLONE_SECTION_NOTIFICATION_PREFERENCES = 'NOTIFICATION_PREFERENCES';
    protected const CLONE_SECTION_CONTENT = 'CONTENT';

    /**
     * @var array
     */
    public const POSSIBLE_SECTIONS = [
        self::CLONE_SECTION_DETAILS,
        self::CLONE_SECTION_CONTENT,
        self::CLONE_SECTION_NOTIFICATION_PREFERENCES
    ];

    /**
     * @var program
     */
    protected $origin_program;

    /**
     * @var program
     */
    protected $cloned_program;

    /**
     * @var array
     */
    protected $clone_sections;

    public function __construct(program $origin_program, array $clone_sections) {
        $this->origin_program = $origin_program;
        $this->clone_sections = $clone_sections;
        $this->cloned_program = null;
    }

    /**
     * Do clone.
     * @return $this
     */
    public function clone(): self {
        builder::get_db()->transaction(function () {
            $this->clone_details();

            // clone the program contents
            $this->clone_content();

            // clone the program notification preferences
            $this->clone_notification();

        });

        return $this;
    }

    /**
     * @return $this
     */
    public function post_clone(): self {
        // trigger the program cloned event
        program_cloned::create_for_operation($this->origin_program, $this->cloned_program)->trigger();


        // Call prog_fix_program_sortorder to ensure new program is displayed properly and the counts are updated.
        prog_fix_program_sortorder($this->cloned_program->category);

        return $this;
    }

    /**
     * @return void
     */
    protected function clone_details(): void {
        if (in_array(self::CLONE_SECTION_DETAILS, $this->clone_sections)) {
            $cloned_program = $this->origin_program->clone_program_details();
            $this->origin_program->clone_metadata($cloned_program)
                ->clone_tags($cloned_program)
                ->clone_custom_fields($cloned_program)
                ->clone_image($cloned_program)
                ->clone_overviewfiles($cloned_program);
            $shallow_clone = false;
        } else {
            // we must do a shallow clone to clone the categor (as notification preferences can't be cloned across parent context)
            // and clone fullname/shortname so the UI can render an anchor tag
            $cloned_base = $this->origin_program->shallow_clone_program_details();
            $cloned_program = program::create($cloned_base);
            $shallow_clone = true;
        }

        // Clone certification tab.
        if ($this->origin_program->is_certif()) {
            $original_certification = certification::load_by_id($this->origin_program->certifid);
            $original_certification->clone($cloned_program, $shallow_clone);
        }

        $this->cloned_program = $cloned_program;
    }

    /**
     * @return void
     */
    protected function clone_content(): void {
        if (in_array(self::CLONE_SECTION_CONTENT, $this->clone_sections)) {
            $this->origin_program->get_content()->clone($this->cloned_program);
        }
    }

    /**
     * @return void
     */
    protected function clone_notification(): void {
        if (in_array(self::CLONE_SECTION_NOTIFICATION_PREFERENCES, $this->clone_sections)) {
            $component = $this->origin_program->is_certif() ? 'totara_certification' : 'totara_program';
            $notification_pref_manager = notification_preference_manager::get_instance();
            $cloned_extended_context = extended_context::make_with_context(
                $this->cloned_program->get_context(),
                $component ,
                'program',
                $this->cloned_program->id
            );
            $origin_extended_context = extended_context::make_with_context(
                $this->origin_program->get_context(),
                $component ,
                'program',
                $this->origin_program->id
            );

            $notification_pref_manager->clone_context_notifications(
                $cloned_extended_context,
                $origin_extended_context
            );
        }
    }

    /**
     * @return program
     */
    public function get_cloned_program(): program {
        if (!$this->cloned_program) {
            // Normally this will be not called, just in case, initialize the cloned_program.
            $this->clone()->post_clone();
        }
        return $this->cloned_program;
    }
}