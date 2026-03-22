<?php

/**
 * This file is part of Totara Core
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package core_theme
 */

use core\entity\tenant;
use core\files\file_helper;
use core\theme\file\helper as theme_file_helper;
use core\theme\settings as theme_settings;

define('CLI_SCRIPT', true);

require_once __DIR__ . '/../../config.php';
global $CFG, $DB;

require_once $CFG->libdir . '/clilib.php';
require_once $CFG->libdir . '/adminlib.php';
require_once "{$CFG->dirroot}/lib/filelib.php";

$placeholder_filename = 'placeholder_logomark.png';
$placeholder_path = __DIR__ . '/' . $placeholder_filename;

cli_logo();
echo PHP_EOL;
cli_heading("Migrate Theme Customisation");

$help =
    "Migration of Theme Settings from Ventura to Inspire Theme.

This migration tool may benefit your site upgrades if you have, for example,
changed colour values and uploaded images. The script may be particularly
beneficial if you have used the ‘Tenant Theme’ feature within Theme Settings
and may have many Tenants. Running the script can optionally update those
Tenant settings as well, so you can avoid manually updating each Tenant.

There are limitations and assumptions built into the script to be aware of.
The scenarios where we anticipate this script to be useful are:

1. You have a simple switch from Ventura Theme to Inspire Theme, and have
   adjusted available Ventura settings for the main site. You don’t have
   Tenants, you haven’t customised the interface and available settings,
   and you haven’t inherited from the Inspire Theme.  You want to copy all
   Ventura settings over to Inspire for the main site Theme.

2. As above, except that you do have one or more Tenant Themes depending on
   the main site Ventura settings. You want to copy all Ventura settings
   over to Inspire for the main site and one or more Tenant Themes.

3. You want one of the above scenarios, but instead of the script inserting
   a blank image for the new ‘Site Logomark’ setting, you want to have the
   main site Theme and optionally one or more Tenant Themes use an image
   that you provide as your preference.

Please note:
 - For Tenant Themes, the Custom CSS field is not migrated from the main
   site Settings, because by default we don’t support the Custom CSS
   field for Tenant Themes.
 - The script has not been tested with child Themes that inherit from Inspire
   due to unknown naming conventions and potential customisations to a child
   Theme's settings. The script can be modified to accommodate your use case.

Copy this script with placeholder_logomark.png in the same folder to replace.
Please be aware this may result in distorted images. You can edit the
$placeholder_filename variable to use a different image filename or use --imagepath option.

We strongly recommend testing the script after a site backup, or in a
staging/testing environment, to ensure that it delivers the result you’re
expecting.

Options:
-f, --force       Make the changes to the theme(s) without prompting.
-t, --tenant=ID   Specify a tenant ID to migrate only that tenant’s images.
                 Use 'all' to migrate for all tenants.
-h, --help        Print this help message.
-i, --imagepath   Custom path to the placeholder logomark image.

Example:
$ php admin/cli/migrate_theme_customization.php --tenant=3 --force
$ php admin/cli/migrate_theme_customization.php --tenant=all --force
";

// Get CLI options
list($options, $unrecognized) = cli_get_params(
    [
        'help' => false,
        'force' => false,
        'tenant' => null,
        'imagepath' => null,
    ],
    [
        'h' => 'help',
        'f' => 'force',
        't' => 'tenant',
        'i' => 'imagepath',
    ]
);

if ($unrecognized) {
    cli_error(get_string('cliunknowoption', 'admin', implode("\n  ", $unrecognized)));
}

if (!empty($options['help'])) {
    echo $help;
    exit(0);
}

if (!empty($options['imagepath'])) {
    $placeholder_path = $options['imagepath'];
    $placeholder_filename = basename($placeholder_path);
}

$admin = get_admin();
\core\session\manager::set_user($admin);

// Load theme configurations
$ventura_theme = \theme_config::load('ventura');
$inspire_theme = \theme_config::load('inspire');

// Load image classes
$classes = theme_file_helper::get_classes();

// Determine tenants to process
$tenants = [];
if (!empty($options['tenant'])) {
    if ($options['tenant'] !== 'all' && !is_numeric($options['tenant'])) {
        cli_error("tenant value must be 'all' or tenant id");
        die;
    }
    if ($options['tenant'] === 'all') {
        $tenants = tenant::repository()->select(['id', 'idnumber', 'name'])->get()->to_array();
    } else {
        $tenant = tenant::repository()->select(['id', 'idnumber', 'name'])->where('id', '=', $options['tenant'])->order_by('id', 'ASC')->first();
        if (!$tenant) {
            cli_error("Invalid tenant ID: {$options['tenant']}");
        }
        $tenants = [$tenant->to_array()];
    }
} else {
    // Default: Only process site-wide settings (tenant_id = 0)
    $tenants = [['id' => 0, 'idnumber' => 'site', 'name' => 'Site-wide']];
}


$to_copy = [];
$messages = [];

$get_file_helper = function ($image) {
    $file_helper = new file_helper($image->get_component(), $image->get_area(), $image->get_context(false));
    $file_helper->set_item_id($image->get_item_id());
    return $file_helper;
};

$check_and_copy = function ($tenant_id) use ($classes, $get_file_helper, &$to_copy, &$messages) {
    foreach ($classes as $class) {
        $ventura_theme_config = theme_config::load('ventura');
        $inspire_theme_config = theme_config::load('inspire');

        $ventura_image = new $class($ventura_theme_config);
        $ventura_image->set_tenant_id($tenant_id);
        $ventura_fh = $get_file_helper($ventura_image);

        if (empty($ventura_fh->get_stored_files()) && empty($ventura_image->get_reference_url())) {
            $messages[] = ["{$class} (Tenant ID: {$tenant_id})", "No image found in Ventura, skipping..."];
            continue;
        }

        $inspire_image = new $class($inspire_theme_config);
        $inspire_image->set_tenant_id($tenant_id);

        $to_copy[] = [$tenant_id, $ventura_image, $inspire_image];
    }
};

// Process site-wide (tenant_id = 0) if no specific tenant is chosen
if (empty($options['tenant'])) {
    $check_and_copy(0);
}

// Process each tenant
foreach ($tenants as $tenant) {
    $check_and_copy($tenant['id']);
}

// Display messages
cli_writeln('');
$max_len = max(array_map(fn($msg) => strlen($msg[0]), $messages));
cli_separator();
foreach ($messages as $message) {
    cli_writeln(str_pad($message[0], $max_len) . ' - ' . $message[1]);
}
cli_separator();

if (empty($to_copy)) {
    cli_writeln('No updated images found to copy. Continuing with settings migration...');
}

if (empty($options['force'])) {
    $confirm = cli_input('Proceed with copying images and settings? (Y or N)');
    if (strtolower($confirm) !== 'y') {
        cli_error('Operation canceled.');
    }
    cli_writeln('Proceeding with image copying...');
}

cli_writeln('');
cli_writeln('Saving theme changes...');
cli_writeln('');

$fs = get_file_storage();

// Copy images
foreach ($to_copy as [$tenant_id, $ventura_image, $inspire_image]) {
    cli_write("Copying {$ventura_image->get_component()} (Tenant ID: {$tenant_id})...");

    $fh = $get_file_helper($inspire_image);
    $draft_id = $fh->create_file_area($admin->id)->get_draft_id();

    // Clean up draft files before copying
    $draft_files = $fs->get_area_files(
        \context_user::instance($admin->id)->id,
        'user',
        'draft',
        $draft_id
    );

    foreach ($draft_files as $draft_file) {
        if (!$draft_file->is_directory()) {
            $draft_file->delete();
        }
    }

    if ($ventura_image->get_current_file()) {
        $fs->create_file_from_storedfile(
            [
                'contextid' => \context_user::instance($admin->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $draft_id,
            ],
            $ventura_image->get_current_file()
        );
    } elseif ($tenant_id > 0) {
        $fs->create_file_from_storedfile(
            [
                'contextid' => \context_user::instance($admin->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $draft_id,
            ],
            $ventura_image->get_current_file($ventura_image->get_item_id($tenant_id, 'ventura', true))
        );
    }

    cli_write('...');
    $inspire_image->save_files($draft_id, false);
    cli_writeln(" done");
}


/**
 * Creates a logomark image for the Inspire theme.
 *
 * @param int $tenant_id The tenant ID to associate with the logomark.
 * @param \file_storage $fs The Moodle file storage instance.
 */
function create_inspire_logomark_image($tenant_ids, stdClass $admin, $get_file_helper, \file_storage $fs, string $filename, string $filepath): void {
    if (!is_array($tenant_ids)) {
        $tenant_ids = [$tenant_ids];
    }

    foreach ($tenant_ids as $tenant_id) {
        cli_writeln('');
        cli_writeln("Creating logomark image for Tenant ID: {$tenant_id}...");
        cli_writeln('');

        $inspire_theme_config = theme_config::load('inspire');
        $inspire_image_logomark = new \core\theme\file\logo_mark($inspire_theme_config);
        $inspire_image_logomark->set_tenant_id($tenant_id);

        $fh = $get_file_helper($inspire_image_logomark);
        $draft_id = $fh->create_file_area($admin->id)->get_draft_id();

        // Clean up draft files before copying
        $draft_files = $fs->get_area_files(
            \context_user::instance($admin->id)->id,
            'user',
            'draft',
            $draft_id
        );

        foreach ($draft_files as $draft_file) {
            if (!$draft_file->is_directory()) {
                $draft_file->delete();
            }
        }

        $filerecord = [
            'contextid' => context_user::instance($admin->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draft_id,
            'filepath' => '/',
            'filename' => $filename,
        ];

        if (file_exists($filepath)) {
            $fs->create_file_from_pathname($filerecord, $filepath);
            $inspire_image_logomark->save_files($draft_id, false);
            cli_writeln("Logomark image created successfully for Tenant ID: {$tenant_id}.");
        } else {
            cli_writeln("File not found: {$filepath}");
        }

        cli_writeln('');
    }
}


/**
 * Copies specific properties from one category to another.
 *
 * @param array $ventura_categories The Ventura theme categories.
 * @param array $inspire_categories The Inspire theme categories.
 * @param string $source_category The category name in Ventura.
 * @param string $target_category The category name in Inspire.
 * @param array $properties_to_copy Mapping of Ventura properties to Inspire properties.
 * @return array The updated Inspire theme categories.
 */
function migrate_category_properties(
    array $ventura_categories,
    array $inspire_categories,
    string $source_category,
    string $target_category,
    array $properties_to_copy
): array {
    // Find the source category in Ventura
    $ventura_category = null;
    foreach ($ventura_categories as $vc) {
        if ($vc['name'] === $source_category) {
            $ventura_category = $vc;
            break;
        }
    }

    // If source category is missing, return unchanged Inspire categories
    if (!$ventura_category || empty($ventura_category['properties'])) {
        return $inspire_categories;
    }

    // Find or create the target category in Inspire
    $inspire_category = null;
    foreach ($inspire_categories as &$ic) {
        if ($ic['name'] === $target_category) {
            $inspire_category = &$ic;
            break;
        }
    }

    if (!$inspire_category) {
        // Create the target category if it doesn't exist
        $inspire_category = [
            'name' => $target_category,
            'properties' => [],
        ];
        $inspire_categories[] = &$inspire_category;
    }

    // Convert Inspire properties into a lookup table
    $inspire_properties_map = [];
    foreach ($inspire_category['properties'] as &$prop) {
        $inspire_properties_map[$prop['name']] = &$prop;
    }

    // Copy properties
    foreach ($properties_to_copy as $ventura_property => $inspire_property) {
        foreach ($ventura_category['properties'] as $ventura_prop) {
            if ($ventura_prop['name'] === $ventura_property) {
                if (isset($inspire_properties_map[$inspire_property])) {
                    // Update existing property
                    $inspire_properties_map[$inspire_property]['value'] = $ventura_prop['value'];
                } else {
                    // Add new property
                    $inspire_category['properties'][] = [
                        'name' => $inspire_property,
                        'type' => $ventura_prop['type'],
                        'value' => $ventura_prop['value'],
                    ];
                }
                break;
            }
        }
    }

    return $inspire_categories;
}

// Determine tenant ID (0 for site-wide or tenant ID if specific tenant chosen)
$tenant_ids = empty($options['tenant']) ? [0] : array_column($tenants, 'id');

create_inspire_logomark_image($tenant_ids, $admin, $get_file_helper, $fs, $placeholder_filename, $placeholder_path);

// Migrate theme settings
cli_writeln('');
cli_writeln('Migrating theme settings...');
cli_writeln('');


foreach ($tenant_ids as $tenant_id) {
    // Load theme settings for each tenant
    $ventura_settings = new theme_settings($ventura_theme, $tenant_id);
    $inspire_settings = new theme_settings($inspire_theme, $tenant_id);

    $ventura_categories = $ventura_settings->get_categories();
    $inspire_categories = $inspire_settings->get_categories();

    // If Inspire categories are empty, initialize them from Ventura
    if (empty($inspire_categories)) {
        $inspire_categories = $ventura_categories;
    }

    foreach ($ventura_categories as $ventura_category) {
        $category_found = false;

        foreach ($inspire_categories as &$inspire_category) {
            if ($ventura_category['name'] === $inspire_category['name']) {
                $category_found = true;

                foreach ($ventura_category['properties'] as $ventura_property) {
                    $property_found = false;

                    foreach ($inspire_category['properties'] as &$inspire_property) {
                        if ($ventura_property['name'] === $inspire_property['name']) {
                            $inspire_property['value'] = $ventura_property['value'];
                            $property_found = true;
                            break;
                        }
                    }

                    // If property doesn't exist in Inspire, add it
                    if (!$property_found) {
                        $inspire_category['properties'][] = $ventura_property;
                    }
                }
                break;
            }
        }

        // If category doesn't exist in Inspire, add it
        if (!$category_found) {
            $inspire_categories[] = $ventura_category;
        }
    }

    // Migrate properties from 'brand' to 'custom'
    $properties_to_copy = [
        'formbrand_field_notificationshtmlheader' => 'formcustom_field_notificationshtmlheader',
        'formbrand_field_notificationshtmlfooter' => 'formcustom_field_notificationshtmlfooter',
        'formbrand_field_notificationstextfooter' => 'formcustom_field_notificationstextfooter',
    ];
    $updated_inspire_categories_custom = migrate_category_properties($ventura_categories, $inspire_categories, 'brand', 'custom', $properties_to_copy);
    // Migrate properties from 'colors' to 'brand'
    $properties_to_copy = [
        'color-state' => 'nav-selected-color',
        'nav-bg-color' => 'nav-bg-color',
        'nav-text-color' => 'nav-text-color',
    ];
    $updated_inspire_categories_brand = migrate_category_properties($ventura_categories, $inspire_categories, 'colours', 'brand', $properties_to_copy);

    // Apply updated theme settings
    $inspire_settings->update_categories($inspire_categories);
    $inspire_settings->update_categories($updated_inspire_categories_custom);
    $inspire_settings->update_categories($updated_inspire_categories_brand);
}

set_config('theme', 'inspire');
theme_reset_all_caches();

cli_writeln('');
cli_writeln('Theme customization migration completed successfully.');
cli_writeln('Run again to verify updates.');
