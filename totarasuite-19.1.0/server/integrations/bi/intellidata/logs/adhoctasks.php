<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    bi
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use bi_intellidata\output\tables\adhoctasks_table;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\helpers\ParamsHelper;
use bi_intellidata\helpers\TasksHelper;

require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);

require_login();

$context = context_system::instance();
require_capability('bi/intellidata:viewadhoctasks', $context);

$pageurl = new \moodle_url('/integrations/bi/intellidata/logs/adhoctasks.php');
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout(SettingsHelper::get_page_layout());

if ($id && $action == 'delete' &&
    has_capability('bi/intellidata:deleteadhoctasks', $context)) {
    require_sesskey();
    TasksHelper::delete_adhoc_task($id);

    redirect($pageurl, get_string('taskdeleted', ParamsHelper::PLUGIN));
}

$title = get_string('exportadhoctasks', ParamsHelper::PLUGIN);

$PAGE->navbar->add($title);
$PAGE->set_title($title);
$PAGE->set_heading($title);

admin_externalpage_setup('bi_intellidataadhoctasks');

$table = new adhoctasks_table('adhoctasks_table');

echo $OUTPUT->header();
echo $OUTPUT->page_main_heading($title);

$table->out(20, true);

echo $OUTPUT->footer();
