<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @package theme_inspire
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This file is included in server/admin/settings/appearance.php and defines user access
 * required for the Inspire Theme customisation page for a specific tenancy.
 */

/** @var core_config $CFG */
/** @var core\record\tenant $tenant */
/** @var context_coursecat $categorycontext */

$settings = new admin_externalpage(
    'inspire_editor',
    new lang_string('pluginname','theme_inspire'),
    "$CFG->wwwroot/totara/tui/theme_settings.php?theme_name=inspire&tenant_id=$tenant->id",
    'totara/tui:themesettings',
    false,
    $categorycontext
);