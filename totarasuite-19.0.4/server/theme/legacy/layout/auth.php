<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package theme_legacy
 */

use totara_core\output\masthead_login;

global $PAGE;
$themerenderer = $PAGE->get_renderer('theme_legacy');
echo $OUTPUT->doctype(); ?>

<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>"/>
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>
<body <?php echo $OUTPUT->body_attributes(['legacy']); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<!-- Main navigation -->
<?php echo $PAGE->get_renderer('totara_core')->render(masthead_login::create());
?>

<div id="page" class="container-fluid page-container-login">
    <div id="page-content">
        <div class="row">
            <section id="region-main" class="<?php echo $themerenderer->main_content_classes(); ?>">
                <?php echo $OUTPUT->main_content(); ?>
            </section>
        </div>
    </div>
</div>

<!-- Footer -->
<footer id="page-footer" class="page-footer">
    <div class="container-fluid">
        <div class="page-footer-main-content">
            <?php echo $OUTPUT->custom_footer_content(); ?>
            <?php echo $OUTPUT->standard_footer_html(); ?>
            <div class="page-footer-poweredby"><?php echo $OUTPUT->powered_by_totara(); ?></div>
        </div>
    </div>
</footer>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
