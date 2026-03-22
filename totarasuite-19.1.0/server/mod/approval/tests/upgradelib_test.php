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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package mod_approval
 */

use core\testing\generator;
use core_phpunit\testcase;
use mod_approval\model\application\action\submit;
use mod_approval\model\assignment\assignment as assignment_model;
use mod_approval\model\assignment\assignment_type\cohort as aud_assignment;
use mod_approval\model\assignment\assignment_type\organisation as org_assignment;
use mod_approval\model\assignment\assignment_type\position as pos_assignment;
use mod_approval\model\form\form as form;
use mod_approval\model\workflow\workflow;
use mod_approval\model\workflow\workflow_type;
use mod_approval\entity\application\application_submission as application_submission_entity;
use mod_approval\model\application\application_submission;
use mod_approval\model\form\form_data;
use mod_approval\plugininfo\approvalform;
use mod_approval\testing\approval_workflow_test_setup;
use totara_core\advanced_feature;

global $CFG;
require_once(__DIR__ . '/testcase.php');
require_once(__DIR__ . '/../db/upgradelib.php');
require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/organisation/lib.php');
require_once($CFG->dirroot . '/totara/hierarchy/prefix/position/lib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

/**
 * @group approval_workflow
 */
class mod_approval_upgradelib_test extends mod_approval_testcase {
    use approval_workflow_test_setup;

    public function test_sync_assignment_id_numbers() {
        $this->setAdminUser();

        $hierarchy_generator = generator::instance()->get_plugin_generator('totara_hierarchy');
        $org_framework = $hierarchy_generator->create_framework('organisation');
        $pos_framework = $hierarchy_generator->create_framework('position');
        $default_org = $hierarchy_generator->create_org(
            [
                'frameworkid' => $org_framework->id,
                'fullname' => 'Agency',
                'idnumber' => '001',
                'shortname' => 'org'
            ]
        );

        // enable approval workflows
        advanced_feature::enable('approval_workflows');
        $workflow_type = workflow_type::create('test type');
        $plugin = approvalform::from_plugin_name('simple');
        if (!$plugin->is_enabled()) {
            approvalform::enable_plugin('simple');
        }
        $form = form::create('simple', 'org_form');

        // create workflow
        $workflow = workflow::create(
            $workflow_type,
            $form,
            "Generated workflow",
            '',
            org_assignment::get_code(),
            $default_org->id
        );

        // create organisation assignment overrides
        $orgs = $this->create_organisations($org_framework->id);
        $assignments = [
            'org' => [],
            'pos' => [],
            'aud' => [],
        ];
        foreach ($orgs as $org) {
            $assignments['org'][] = assignment_model::create(
                $workflow->course_id,
                org_assignment::get_code(),
                $org
            );
        }

        // create position assignment overrides
        $poss = $this->create_positions($pos_framework->id);
        foreach ($poss as $pos) {
            $assignments['pos'][] = assignment_model::create(
                $workflow->course_id,
                pos_assignment::get_code(),
                $pos
            );
        }

        // create audience assignment overrides
        $auds = $this->create_audiences();
        foreach ($auds as $aud) {
            $assignments['aud'][] = assignment_model::create(
                $workflow->course_id,
                aud_assignment::get_code(),
                $aud
            );
        }

        // change idnumber of first org|pos|aud
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[0])
            ->update([
                'shortname' => 'new-org-1'
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[0])
            ->update([
                'shortname' => 'new-pos-1'
            ]);

        \core\entity\cohort::repository()
            ->where('id', $auds[0])
            ->update([
                'idnumber' => 'new-aud-1'
            ]);

        // empty idnumber of second org|pos|aud
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[1])
            ->update([
                'shortname' => ''
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[1])
            ->update([
                'shortname' => ''
            ]);

        \core\entity\cohort::repository()
            ->where('id', $auds[1])
            ->update([
                'idnumber' => ''
            ]);

        // null idnumber of third org|pos|aud
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[2])
            ->update([
                'shortname' => null
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[2])
            ->update([
                'shortname' => null
            ]);

        \core\entity\cohort::repository()
            ->where('id', $auds[2])
            ->update([
                'idnumber' => null
            ]);

        mod_approval_sync_assignment_id_numbers();
        $this->assertTrue(true);

        // Check for changed idnumber
        $assignments['org'][0]->refresh();
        $this->assertEquals('new-org-1', $assignments['org'][0]->id_number);
        $assignments['pos'][0]->refresh();
        $this->assertEquals('new-pos-1', $assignments['pos'][0]->id_number);
        $assignments['aud'][0]->refresh();
        $this->assertEquals('new-aud-1', $assignments['aud'][0]->id_number);
        
        // check for empty idnumber
        $assignments['org'][1]->refresh();
        $this->assertEquals("ORGANISATION_{$orgs[1]}", $assignments['org'][1]->id_number);
        $assignments['pos'][1]->refresh();
        $this->assertEquals("POSITION_{$poss[1]}", $assignments['pos'][1]->id_number);
        $assignments['aud'][1]->refresh();
        $this->assertEquals("COHORT_{$auds[1]}", $assignments['aud'][1]->id_number);
        
        // check for null idnumber
        $assignments['org'][2]->refresh();
        $this->assertEquals("ORGANISATION_{$orgs[2]}", $assignments['org'][2]->id_number);
        $assignments['pos'][2]->refresh();
        $this->assertEquals("POSITION_{$poss[2]}", $assignments['pos'][2]->id_number);
        $assignments['aud'][2]->refresh();
        $this->assertEquals("COHORT_{$auds[2]}", $assignments['aud'][2]->id_number);

        // check for unchanged idnumber
        $assignments['org'][3]->refresh();
        $this->assertEquals("id-org-4", $assignments['org'][3]->id_number);
        $assignments['pos'][3]->refresh();
        $this->assertEquals("id-pos-4", $assignments['pos'][3]->id_number);
        $assignments['aud'][3]->refresh();
        $this->assertEquals("aud-4", $assignments['aud'][3]->id_number);
    }

    public function test_sync_org_pos_assignment_id_numbers() {
        $this->setAdminUser();

        $hierarchy_generator = generator::instance()->get_plugin_generator('totara_hierarchy');
        $org_framework = $hierarchy_generator->create_framework('organisation');
        $pos_framework = $hierarchy_generator->create_framework('position');
        $default_org = $hierarchy_generator->create_org(
            [
                'frameworkid' => $org_framework->id,
                'fullname' => 'Agency',
                'idnumber' => '001',
                'shortname' => 'org'
            ]
        );

        // enable approval workflows
        advanced_feature::enable('approval_workflows');
        $workflow_type = workflow_type::create('test type');
        $plugin = approvalform::from_plugin_name('simple');
        if (!$plugin->is_enabled()) {
            approvalform::enable_plugin('simple');
        }
        $form = form::create('simple', 'org_form');

        // create workflow
        $workflow = workflow::create(
            $workflow_type,
            $form,
            "Generated workflow",
            '',
            org_assignment::get_code(),
            $default_org->id
        );

        // create organisation assignment overrides
        $orgs = $this->create_organisations($org_framework->id);
        $assignments = [
            'org' => [],
            'pos' => [],
        ];
        foreach ($orgs as $org) {
            $assignments['org'][] = assignment_model::create(
                $workflow->course_id,
                org_assignment::get_code(),
                $org
            );
        }

        // create position assignment overrides
        $poss = $this->create_positions($pos_framework->id);
        foreach ($poss as $pos) {
            $assignments['pos'][] = assignment_model::create(
                $workflow->course_id,
                pos_assignment::get_code(),
                $pos
            );
        }

        // change idnumber of first org|pos
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[0])
            ->update([
                'idnumber' => 'new-org-1'
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[0])
            ->update([
                'idnumber' => 'new-pos-1'
            ]);

        // empty idnumber of second org|pos
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[1])
            ->update([
                'idnumber' => ''
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[1])
            ->update([
                'idnumber' => ''
            ]);

        // null idnumber of third org|pos
        \hierarchy_organisation\entity\organisation::repository()
            ->where('id', $orgs[2])
            ->update([
                'idnumber' => null
            ]);
        \hierarchy_position\entity\position::repository()
            ->where('id', $poss[2])
            ->update([
                'idnumber' => null
            ]);

        // Test running the upgrade function twice to avoid rerun problems during upgrades
        mod_approval_sync_org_and_pos_assignment_id_numbers();
        mod_approval_sync_org_and_pos_assignment_id_numbers();

        // Check for changed idnumber
        $assignments['org'][0]->refresh();
        $this->assertEquals('new-org-1', $assignments['org'][0]->id_number);
        $assignments['pos'][0]->refresh();
        $this->assertEquals('new-pos-1', $assignments['pos'][0]->id_number);

        // check for empty idnumber
        $assignments['org'][1]->refresh();
        $this->assertEquals("ORGANISATION_{$orgs[1]}", $assignments['org'][1]->id_number);
        $assignments['pos'][1]->refresh();
        $this->assertEquals("POSITION_{$poss[1]}", $assignments['pos'][1]->id_number);

        // check for null idnumber
        $assignments['org'][2]->refresh();
        $this->assertEquals("ORGANISATION_{$orgs[2]}", $assignments['org'][2]->id_number);
        $assignments['pos'][2]->refresh();
        $this->assertEquals("POSITION_{$poss[2]}", $assignments['pos'][2]->id_number);

        // check for unchanged idnumber
        $assignments['org'][3]->refresh();
        $this->assertEquals("id-asg-4", $assignments['org'][3]->id_number);
        $assignments['pos'][3]->refresh();
        $this->assertEquals("id-apg-4", $assignments['pos'][3]->id_number);
    }

    private function create_positions($framework_id): array {

        return (new position())->add_multiple_hierarchy_items(
            0,
            [
                (object) [
                    'fullname' => 'ID Pos 1',
                    'typeid' => 0,
                    'shortname' => 'id-pos-1',
                    'idnumber' => 'id-apg-1',
                ],
                (object) [
                    'fullname' => 'ID Pos 2',
                    'typeid' => 0,
                    'shortname' => 'id-pos-2',
                    'idnumber' => 'id-apg-2',
                ],
                (object) [
                    'fullname' => 'ID Pos 3',
                    'typeid' => 0,
                    'shortname' => 'id-pos-3',
                    'idnumber' => 'id-apg-3',
                ],
                (object) [
                    'fullname' => 'ID Pos 4',
                    'typeid' => 0,
                    'shortname' => 'id-pos-4',
                    'idnumber' => 'id-apg-4',
                ],
            ],
            $framework_id,
            false
        );
    }

    private function create_audiences(): array {
        return [
            cohort_add_cohort((object)[
                'idnumber' => "aud-1",
                'name' => "ID Audience 1",
                'contextid' => context_system::instance()->id,
            ]),
            cohort_add_cohort((object)[
                'idnumber' => "aud-2",
                'name' => "ID Audience 2",
                'contextid' => context_system::instance()->id,
            ]),
            cohort_add_cohort((object)[
                'idnumber' => "aud-3",
                'name' => "ID Audience 3",
                'contextid' => context_system::instance()->id,
            ]),
            cohort_add_cohort((object)[
                'idnumber' => "aud-4",
                'name' => "ID Audience 4",
                'contextid' => context_system::instance()->id,
            ]),
        ];
    }

    private function create_organisations($framework_id): array {
        return (new organisation())->add_multiple_hierarchy_items(
            0,
            [
                (object) [
                    'fullname' => 'ID Org 1',
                    'typeid' => 0,
                    'shortname' => 'id-org-1',
                    'idnumber' => 'id-asg-1',
                ],
                (object) [
                    'fullname' => 'ID Org 2',
                    'typeid' => 0,
                    'shortname' => 'id-org-2',
                    'idnumber' => 'id-asg-2',
                ],
                (object) [
                    'fullname' => 'ID Org 3',
                    'typeid' => 0,
                    'shortname' => 'id-org-3',
                    'idnumber' => 'id-asg-3',
                ],
                (object) [
                    'fullname' => 'ID Org 4',
                    'typeid' => 0,
                    'shortname' => 'id-org-4',
                    'idnumber' => 'id-asg-4',
                ],
            ],
            $framework_id,
            false
        );
    }

    public function test_fix_submission_date_format() {
        $submitter = self::getDataGenerator()->create_user();

        // Create an application.
        $this->setAdminUser();
        $application = $this->create_application_for_user();

        $submission = application_submission::create_or_update(
            $application,
            $submitter->id,
            form_data::from_json('{"kia":"kaha"}')
        );
        $submission->publish($submitter->id);

        /** @var \mod_approval\entity\application\application_submission */
        $submission = application_submission_entity::repository()->order_by('id', 'desc')->first();
        $submission->form_data = '{"foo":"2024-01-01","baz":{"iso":"2024-01-01"}}';
        $submission->save();

        // Run upgrade step
        mod_approval_fix_submission_date_format();

        $submission->refresh();
        $this->assertEquals('{"foo":"2024-01-01","baz":"2024-01-01"}', $submission->form_data);

        // Run it again to ensure it's a NOP if there's nothing to migrate
        mod_approval_fix_submission_date_format();

        $submission->refresh();
        $this->assertEquals('{"foo":"2024-01-01","baz":"2024-01-01"}', $submission->form_data);
    }
}

