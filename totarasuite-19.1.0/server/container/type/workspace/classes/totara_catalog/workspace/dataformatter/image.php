<?php
/*
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */

namespace container_workspace\totara_catalog\workspace\dataformatter;

defined('MOODLE_INTERNAL') || die();

use container_workspace\theme\file\workspace_image;
use container_workspace\workspace;
use core\files\file_helper;
use core_container\factory;
use totara_catalog\dataformatter\formatter;

class image extends formatter {

    /**
     * @param string $workspace_id_field the database field containing the workspace id
     * @param string $alt_field the database field containing the image alt text
     */
    public function __construct(string $workspace_id_field, string $alt_field) {
        $this->add_required_field('workspace_id', $workspace_id_field);
        $this->add_required_field('alt', $alt_field);
    }

    public function get_suitable_types(): array {
        return [
            formatter::TYPE_PLACEHOLDER_IMAGE,
        ];
    }

    /**
     * Given a workspace id (actually a course ID), gets the image.
     *
     * @param array $data
     * @param \context $context
     * @return \stdClass
     */
    public function get_formatted_value(array $data, \context $context): \stdClass {
        global $PAGE, $USER;

        if (!array_key_exists('workspace_id', $data)) {
            throw new \coding_exception("workspace image data formatter expects 'workspace_id'");
        }

        if (!array_key_exists('alt', $data)) {
            throw new \coding_exception("workspace image data formatter expects 'alt'");
        }

        $result = new \stdClass();

        $workspace = factory::from_id($data['workspace_id']);

        if (!$workspace->is_typeof(workspace::get_type())) {
            throw new \coding_exception("Cannot fetch image of a container that is not a workspace");
        }

        $file_helper = new file_helper(
            workspace::get_type(),
            workspace::IMAGE_AREA,
            $workspace->get_context()
        );

        $url = $file_helper->get_file_url();

        if ($url) {
            $result->url = $url->out();
        } else {
            // If there is no image for the workspace then use the default for the user's tenant.
            $workspace_image = new workspace_image($PAGE->theme);
            $workspace_image->set_tenant_id(!empty($USER->tenantid) ? $USER->tenantid : 0);

            $result->url = $workspace_image->get_current_or_default_url()->out();
        }

        $result->alt = format_string($data['alt'], true, ['context' => $context]);

        return $result;
    }
}
