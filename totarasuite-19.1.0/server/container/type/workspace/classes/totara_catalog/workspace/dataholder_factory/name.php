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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totara.com>
 * @package container_workspace
 * @category totara_catalog
 */

namespace container_workspace\totara_catalog\workspace\dataholder_factory;

defined('MOODLE_INTERNAL') || die();

use totara_catalog\dataformatter\formatter;
use totara_catalog\dataformatter\fts;
use totara_catalog\dataformatter\strip_tags;
use totara_catalog\dataformatter\text;
use totara_catalog\dataholder;
use totara_catalog\dataholder_factory;

class name extends dataholder_factory {

    public static function get_dataholders(): array {
        return [
            new dataholder(
                'name',
                new \lang_string('field:name', 'container_workspace'),
                [
                    formatter::TYPE_FTS => new fts(
                        'base.fullname'
                    ),
                    formatter::TYPE_PLACEHOLDER_TITLE => new text(
                        'base.fullname'
                    ),
                    formatter::TYPE_PLACEHOLDER_TEXT => new text(
                        'base.fullname'
                    ),
                    formatter::TYPE_SORT_TEXT => new strip_tags(
                        'base.fullname'
                    ),
                ],
            )
        ];
    }
}
