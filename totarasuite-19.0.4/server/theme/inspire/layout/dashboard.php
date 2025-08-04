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

$PAGE->set_popup_notification_allowed(false);

$themerenderer = $PAGE->get_renderer('theme_inspire');
$full_header = $themerenderer->full_header();

if (!empty($PAGE->layout_options['nonavbar'])) {
    $full_header = '';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<?php require(__DIR__."/partials/head.php"); ?>

<body <?php echo $OUTPUT->body_attributes(['theme_inspire']); ?>>
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div class="page-layout">
<?php echo $themerenderer->menu(); ?>

<div class="page-body">

<!-- Header -->
<?php
$totara_core_renderer = $PAGE->get_renderer('totara_core');
$hasguestlangmenu = (!isset($PAGE->layout_options['langmenu']) || $PAGE->layout_options['langmenu'] );
$nocustommenu = !empty($PAGE->layout_options['nocustommenu']);
echo $totara_core_renderer->masthead($hasguestlangmenu, $nocustommenu);
?>

<?php if ($full_header !== '') { ?>
<!-- Breadcrumb and edit buttons -->
<div class="container-fluid breadcrumb-container">
    <div class="row">
        <div class="col-sm-12">
            <?php echo $full_header; ?>
        </div>
    </div>
</div>
<?php } ?>

    <!-- Content -->
    <div id="page" class="container-fluid">
        <div id="page-content">
            <?php echo $themerenderer->page_main_heading($PAGE->heading, sr_only: !get_config('totara_dashboard', 'show_title')); ?>

            <?php echo $themerenderer->blocks_top(); ?>
            <div class="row">
                <div id="region-main" class="<?php echo $themerenderer->main_content_classes(); ?>">
                    <?php echo $themerenderer->course_content_header(); ?>
                    <?php echo $themerenderer->blocks_main(); ?>
                    <?php echo $themerenderer->main_content(); ?>
                    <?php echo $themerenderer->course_content_footer(); ?>
                </div>
                <?php echo $themerenderer->blocks_pre(); ?>
                <?php echo $themerenderer->blocks_post(); ?>
            </div>
            <?php echo $themerenderer->blocks_bottom(); ?>

        </div>
    </div>

    <!-- Footer -->
    <?php require(__DIR__."/partials/footer.php"); ?>
</div>

</div>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
