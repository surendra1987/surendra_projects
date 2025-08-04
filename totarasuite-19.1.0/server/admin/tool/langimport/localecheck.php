<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralearning.com>
 * @package tool_langimport
 */

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('localecheck');

echo $OUTPUT->header();

// NOTE: this page is not necessary to be localised

echo $OUTPUT->page_main_heading(get_string('localecheck', 'tool_langimport'));

$info = <<<EOT
Totara uses the PHP Internationalization library to format dates. Each language pack includes the locale to use for formatting
dates and other values.

Dates rely on the PHP version of the ICU library, and do not require the locale to be installed on your server, however we still recommend it.
If a server locale name is different to lang pack locale name, it might lead to errors when showing content in Totara.

Use this tool to check if your Totara lang packs locale names are compatible with your server.
EOT;

if ($CFG->ostype === 'WINDOWS') {
    $info .= <<<EOT


You can fix any errors by overriding lang pack locally. Go to "Language customisation",
select component "langconfig.php" and alter the value of "locale" (for dates) or "localewin" (for everything else).
EOT;
} else {
    $info .= <<<EOT


You can fix any errors by overriding lang pack locally. Go to "Language customisation",
select component "langconfig.php" and alter the value of "locale" string.
EOT;
}

echo $OUTPUT->notification(markdown_to_html($info), 'info');

$stringmanager = get_string_manager();

$langs = $stringmanager->get_list_of_translations(false);
ksort($langs);
$en = $langs['en'];
unset($langs['en']);
$langs = ['en' => $en] + $langs;

echo $OUTPUT->heading('Language pack server locale tests', 2);

$table = new html_table();
$table->head = ['Lang', 'Name', 'Server', 'Date Locale', 'Sample Date', 'Localisation'];
$table->attributes['class'] = 'admintable generaltable';
$table->id = 'langlocales';
$table->data = [];

$SESSION->lang = 'en';
if ($CFG->ostype === 'WINDOWS') {
    $enlocale = get_string('localewin', 'core_langconfig');
} else {
    $enlocale = get_string('locale', 'core_langconfig');
}

$time = 1592891100;

$months = [
    1704456212, // Jan
    1707134612, // Feb
    1709640212, // Mar
    1712318612, // Apr
    1714914212, // May
    1717592612, // Jun
    1720184612, // Jul
    1722863012, // Aug
    1725541412, // Sep
    1728129812, // Oct
    1730808212, // Nov
    1733400212, // Dec
];
$days_of_week = [
    1733130000, // M
    1733216400, // T
    1733302800, // W
    1733389200, // T
    1733475600, // F
    1733562000, // S
    1733648400, // S
];

foreach ($langs as $lang => $unused) {
    $SESSION->lang = $lang;
    $langname = get_string('thislanguage', 'core_langconfig');
    if ($CFG->ostype === 'WINDOWS') {
        $locale = get_string('localewin', 'core_langconfig');
        $date_locale = get_string('locale', 'core_langconfig');
    } else {
        $locale = get_string('locale', 'core_langconfig');
        $date_locale = $locale;
    }

    // Reset to known values.
    setlocale(LC_ALL, $enlocale);
    moodle_setlocale($enlocale);

    $result = setlocale(LC_TIME, $locale);
    if ($result === false) {
        // Fallback hack from moodle_setlocale();
        if (stripos($locale, '.UTF-8') !== false) {
            $newlocale = str_ireplace('.UTF-8', '.UTF8', $locale);
            $result = setlocale(LC_TIME, $newlocale);
        } else if (stripos($locale, '.UTF8') !== false) {
            $newlocale = str_ireplace('.UTF8', '.UTF-8', $locale);
            $result = setlocale(LC_TIME, $newlocale);
        }
        if ($result !== false) {
            $locale = $newlocale;
        }
    }

    // Check if the locale is available inside the intl function
    $intl_locale_test = locale_canonicalize($date_locale) !== $date_locale;
    $full_months = [];
    $short_months = [];
    foreach ($months as $month) {
        $full_months[] = userdate($month, '%B');
        $short_months[] = userdate($month, '%b');
    }
    $full_days = [];
    $short_days = [];
    foreach ($days_of_week as $day) {
        $full_days[] = userdate($day, '%A');
        $short_days[] = userdate($day, '%a');
    }

    $samples = [
        implode(' ', $full_months),
        implode(' ', $short_months),
        implode(' ', $full_days),
        implode(' ', $short_days),
    ];

    $locale_dates = implode('<br />', [
        userdate($time, '%c', 'UTC'),
        '',
        userdate($time, '%x', 'UTC'),
        userdate($time, '%X', 'UTC'),
    ]);

    $table->data[] = [
        $lang,
        $langname,
        $result ? $locale : '<span style="color:red">' . $locale . '<br />Unavailable</span>',
        $intl_locale_test ? $date_locale : '<span style="color:red">' . $date_locale . '<br />Unavailable</span>',
        '<div class="text-nowrap">' . $locale_dates . '</div>',
        implode('<br />', $samples),
    ];

    unset($SESSION->lang);
}

echo $OUTPUT->render($table);

echo $OUTPUT->heading('ICU Version', 2);
// Print out our ICU version information
try {
    $intl = new \ReflectionExtension('intl');
    ob_start();
    $intl->info();
    $icu_info = strip_tags(ob_get_clean());

    $icu_table = new html_table();
    $icu_table->head  = ['Name', 'Version'];
    $icu_table->attributes['class'] = 'admintable generaltable';
    $icu_table->id = 'langlocales_icu';
    $icu_table->data  = [];
    $rows = [];

    if (preg_match_all('/^(?P<name>ICU(?: [A-Za-z_]*)? version)(?:=>)?(?P<version>.*)$/m', $icu_info, $matches)) {
        foreach ($matches['name'] as $i => $name) {
            if (isset($matches['version'][$i])) {
                $rows[] = [$name, $matches['version'][$i]];
            }
        }
    }

    $icu_table->data = $rows;
    echo $OUTPUT->render($icu_table);

} catch (\ReflectionException $ex) {
    // Load the partial constant instead
    echo html_writer::tag('p', defined('INTL_ICU_VERSION') ? constant('INTL_ICU_VERSION') : 'Unknown');
}


if ($CFG->ostype !== 'WINDOWS') {
    echo $OUTPUT->heading('Available server locales', 2);
    exec('locale -a', $locales);
    foreach ($locales as $k => $locale) {
        if (stripos($locale, 'UTF') === false) {
            unset($locales[$k]);
        }
    }
    asort($locales);
    echo '<p>' . implode(', ', $locales) . '</p>';
}

echo $OUTPUT->footer();
