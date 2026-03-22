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
 * @author Dave Wallace <dave.wallace@totara.com>
 * @package theme_inspire
 */

defined('MOODLE_INTERNAL' || die());

$THEME->doctype = 'html5';
$THEME->name = 'inspire';
$THEME->parents = ['legacy', 'base'];
$THEME->enable_dock = false;
$THEME->enable_hide = false;
$THEME->minify_css = false;
$THEME->sheets = ['totara'];
$THEME->parents_exclude_sheets = [
    'base' => ['flexible-icons'],
    'legacy' => ['totara'],
];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
