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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core
 */

use core\usagedata\settings_feature;
use core_phpunit\testcase;
use tool_usagedata\export;

class core_usagedata_settings_feature_test extends testcase {

    public function test_export() {
        // Static configurations
        set_config('enablesafebrowserintegration', 'test_value_for_enablesafebrowserintegration');
        set_config('dndallowtextandlinks', 'test_value_for_dndallowtextandlinks');
        set_config('enableglobalsearch', 'test_value_for_enableglobalsearch');
        set_config('tenantsisolated', 'test_value_for_tenantsisolated');
        set_config('usecomments', 'test_value_for_usecomments');
        set_config('commentsperpage', 'test_value_for_commentsperpage');
        set_config('usetags', 'test_value_for_usetags');
        set_config('enablenotes', 'test_value_for_enablenotes');
        set_config('messaging', 'test_value_for_messaging');
        set_config('messaginghidereadnotifications', 'test_value_for_messaginghidereadnotifications');
        set_config('messagingallowemailoverride', 'test_value_for_messagingallowemailoverride');
        set_config('enablestats', 'test_value_for_enablestats');
        set_config('enablerssfeeds', 'test_value_for_enablerssfeeds');
        set_config('enablebadges', 'test_value_for_enablebadges');
        set_config('enablereportcaching', 'test_value_for_enablereportcaching');
        set_config('enableglobalrestrictions', 'test_value_for_enableglobalrestrictions');
        set_config('enableuser_reports', 'test_value_for_enableuser_reports');
        set_config('audiencevisibility', 'test_value_for_audiencevisibility');
        set_config('enableconnectserver', 'test_value_for_enableconnectserver');
        set_config('showhierarchyshortnames', 'test_value_for_showhierarchyshortnames');
        set_config('enabletotaradashboard', 'test_value_for_enabletotaradashboard');
        set_config('enablereportgraphs', 'test_value_for_enablereportgraphs');
        set_config('enablepositions', 'test_value_for_enablepositions');
        set_config('enableorganisations', 'test_value_for_enableorganisations');
        set_config('totara_job_allowmultiplejobs', 'test_value_for_totara_job_allowmultiplejobs');
        set_config('enablesitepolicies', 'test_value_for_enablesitepolicies');
        set_config('catalogtype', 'test_value_for_catalogtype');
        set_config('tenantsenabled', 'test_value_for_tenantsenabled');
        set_config('enablecompetencies', 'test_value_for_enablecompetencies');
        set_config('enablemyteam', 'test_value_for_enablemyteam');
        set_config('enableevidence', 'test_value_for_enableevidence');
        set_config('enablewebservices', 'test_value_for_enablewebservices');
        set_config('enableblogs', 'test_value_for_enableblogs');
        set_config('enableportfolios', 'test_value_for_enableportfolios');
        set_config('enablecompletion', 'test_value_for_enablecompletion');
        set_config('completiondefault', 'test_value_for_completiondefault');
        set_config('enableavailability', 'test_value_for_enableavailability');
        set_config('enablecourserpl', 'test_value_for_enablecourserpl');
        set_config('enablemodulerpl', 'test_value_for_enablemodulerpl');
        set_config('enableplagiarism', 'test_value_for_enableplagiarism');
        set_config('enablecontentmarketplaces', 'test_value_for_enablecontentmarketplaces');
        set_config('enableprogramextensionrequests', 'test_value_for_enableprogramextensionrequests');
        set_config('enablelearningplans', 'test_value_for_enablelearningplans');
        set_config('enableprograms', 'test_value_for_enableprograms');
        set_config('enablecertifications', 'test_value_for_enablecertifications');
        set_config('enablerecordoflearning', 'test_value_for_enablerecordoflearning');
        set_config('enableprogramcompletioneditor', 'test_value_for_enableprogramcompletioneditor');
        set_config('enableoutcomes', 'test_value_for_enableoutcomes');
        set_config('enablelegacyprogramassignments', 'test_value_for_enablelegacyprogramassignments');
        set_config('enablecompletionimport', 'test_value_for_enablecompletionimport');
        set_config('enablegoals', 'test_value_for_enablegoals');
        set_config('enableperformance_activities', 'test_value_for_enableperformance_activities');
        set_config('perform_hide_suspended_users', 'test_value_for_perform_hide_suspended_users');
        set_config('perform_close_suspended_user_instances', 'test_value_for_perform_close_suspended_user_instances');
        set_config('perform_hide_incomplete_responses_closed_instances', 'test_value_for_perform_hide_incomplete_responses_closed_instances');
        set_config('perform_sync_participant_instance_creation', 'test_value_for_perform_sync_participant_instance_creation');
        set_config('perform_sync_participant_instance_closure', 'test_value_for_perform_sync_participant_instance_closure');
        set_config('enablecompetency_assignment', 'test_value_for_enablecompetency_assignment');
        set_config('dynamicappraisals', 'test_value_for_dynamicappraisals');
        set_config('dynamicappraisalsautoprogress', 'test_value_for_dynamicappraisalsautoprogress');
        set_config('enableappraisals', 'test_value_for_enableappraisals');
        set_config('showhistoricactivities', 'test_value_for_showhistoricactivities');
        set_config('enablefeedback360', 'test_value_for_enablefeedback360');
        set_config('enableengage_resources', 'test_value_for_enableengage_resources');
        set_config('enablecontainer_workspace', 'test_value_for_enablecontainer_workspace');
        set_config('enableml_recommender', 'test_value_for_enableml_recommender');
        set_config('enabletotara_msteams', 'test_value_for_enabletotara_msteams');
        set_config('deleteunconfirmed', 'test_value_for_deleteunconfirmed');
        set_config('deleteincompleteusers', 'test_value_for_deleteincompleteusers');
        set_config('deletecompletionlogs', 'test_value_for_deletecompletionlogs');
        set_config('disablegradehistory', 'test_value_for_disablegradehistory');
        set_config('gradehistorylifetime', 'test_value_for_gradehistorylifetime');
        set_config('tempdatafoldercleanup', 'test_value_for_tempdatafoldercleanup');
        set_config('menulifetime', 'test_value_for_menulifetime');
        set_config('forcelogin', 'test_value_for_forcelogin');
        set_config('forceloginforprofiles', 'test_value_for_forceloginforprofiles');
        set_config('forceloginforprofileimage', 'test_value_for_forceloginforprofileimage');
        set_config('preventmultiplelogins', 'test_value_for_preventmultiplelogins');
        set_config('opentogoogle', 'test_value_for_opentogoogle');
        set_config('publishgridcatalogimage', 'test_value_for_publishgridcatalogimage');
        set_config('maxbytes', 'test_value_for_maxbytes');
        set_config('disableconsistentcleaning', 'test_value_for_disableconsistentcleaning');
        set_config('extendedusernamechars', 'test_value_for_extendedusernamechars');
        set_config('keeptagnamecase', 'test_value_for_keeptagnamecase');
        set_config('profilesforenrolledusersonly', 'test_value_for_profilesforenrolledusersonly');
        set_config('cronclionly', 'test_value_for_cronclionly');
        set_config('passwordpolicy', 'test_value_for_passwordpolicy');
        set_config('disableuserimages', 'test_value_for_disableuserimages');
        set_config('persistentloginenable', 'test_value_for_persistentloginenable');
        set_config('rememberusername', 'test_value_for_rememberusername');
        set_config('strictformsrequired', 'test_value_for_strictformsrequired');
        set_config('stricttransportsecurity', 'test_value_for_stricttransportsecurity');
        set_config('securereferrers', 'test_value_for_securereferrers');
        set_config('allowframembedding', 'test_value_for_allowframembedding');
        set_config('permittedcrossdomainpolicies', 'test_value_for_permittedcrossdomainpolicies');

        // Conditionals on basis of empty state
        set_config('sitepolicy', 'not_empty');
        set_config('sitepolicyguest', 'not_empty');
        set_config('cronremotepassword', 'not_empty');
        set_config('curlsecurityblockedhosts', 'not_empty');
        set_config('curlsecurityallowedport', 'not_empty');
        set_config('pcntl_phpclipath', 'not_empty');
        set_config('urlrewriteclass', 'not_empty');
        set_config('disable_visibility_maps', 'not_empty');
        set_config('moodlepageclass', 'not_empty');
        set_config('blockmanagerclass', 'not_empty');

        // Conditionals on basis of isset state
        set_config('preventfilelocking', 'test_value_for_preventfilelocking');
        set_config('preventexecpath', 'test_value_for_preventexecpath');
        set_config('allowlogincsrf', 'test_value_for_allowlogincsrf');
        set_config('customscripts', 'test_value_for_customscripts');

        $export_result = (new settings_feature())->export();

        // Static configurations
        $this->assertEquals('test_value_for_enablesafebrowserintegration', $export_result['enablesafebrowserintegration']);
        $this->assertEquals('test_value_for_dndallowtextandlinks', $export_result['dndallowtextandlinks']);
        $this->assertEquals('test_value_for_enableglobalsearch', $export_result['enableglobalsearch']);
        $this->assertEquals('test_value_for_tenantsisolated', $export_result['tenantsisolated']);
        $this->assertEquals('test_value_for_usecomments', $export_result['usecomments']);
        $this->assertEquals('test_value_for_commentsperpage', $export_result['commentsperpage']);
        $this->assertEquals('test_value_for_usetags', $export_result['usetags']);
        $this->assertEquals('test_value_for_enablenotes', $export_result['enablenotes']);
        $this->assertEquals('test_value_for_messaging', $export_result['messaging']);
        $this->assertEquals('test_value_for_messaginghidereadnotifications', $export_result['messaginghidereadnotifications']);
        $this->assertEquals('test_value_for_messagingallowemailoverride', $export_result['messagingallowemailoverride']);
        $this->assertEquals('test_value_for_enablestats', $export_result['enablestats']);
        $this->assertEquals('test_value_for_enablerssfeeds', $export_result['enablerssfeeds']);
        $this->assertEquals('test_value_for_enablebadges', $export_result['enablebadges']);
        $this->assertEquals('test_value_for_enablereportcaching', $export_result['enablereportcaching']);
        $this->assertEquals('test_value_for_enableglobalrestrictions', $export_result['enableglobalrestrictions']);
        $this->assertEquals('test_value_for_enableuser_reports', $export_result['enableuser_reports']);
        $this->assertEquals('test_value_for_audiencevisibility', $export_result['audiencevisibility']);
        $this->assertEquals('test_value_for_enableconnectserver', $export_result['enableconnectserver']);
        $this->assertEquals('test_value_for_showhierarchyshortnames', $export_result['showhierarchyshortnames']);
        $this->assertEquals('test_value_for_enabletotaradashboard', $export_result['enabletotaradashboard']);
        $this->assertEquals('test_value_for_enablereportgraphs', $export_result['enablereportgraphs']);
        $this->assertEquals('test_value_for_enablepositions', $export_result['enablepositions']);
        $this->assertEquals('test_value_for_enableorganisations', $export_result['enableorganisations']);
        $this->assertEquals('test_value_for_enablecourserpl', $export_result['enablecourserpl']);
        $this->assertEquals('test_value_for_totara_job_allowmultiplejobs', $export_result['totara_job_allowmultiplejobs']);
        $this->assertEquals('test_value_for_enablesitepolicies', $export_result['enablesitepolicies']);
        $this->assertEquals('test_value_for_catalogtype', $export_result['catalogtype']);
        $this->assertEquals('test_value_for_tenantsenabled', $export_result['tenantsenabled']);
        $this->assertEquals('test_value_for_enablecompetencies', $export_result['enablecompetencies']);
        $this->assertEquals('test_value_for_enablemyteam', $export_result['enablemyteam']);
        $this->assertEquals('test_value_for_enableevidence', $export_result['enableevidence']);
        $this->assertEquals('test_value_for_enablewebservices', $export_result['enablewebservices']);
        $this->assertEquals('test_value_for_enableblogs', $export_result['enableblogs']);
        $this->assertEquals('test_value_for_enableportfolios', $export_result['enableportfolios']);
        $this->assertEquals('test_value_for_enablecompletion', $export_result['enablecompletion']);
        $this->assertEquals('test_value_for_completiondefault', $export_result['completiondefault']);
        $this->assertEquals('test_value_for_enableavailability', $export_result['enableavailability']);
        $this->assertEquals('test_value_for_enablemodulerpl', $export_result['enablemodulerpl']);
        $this->assertEquals('test_value_for_enableplagiarism', $export_result['enableplagiarism']);
        $this->assertEquals('test_value_for_enablecontentmarketplaces', $export_result['enablecontentmarketplaces']);
        $this->assertEquals('test_value_for_enableprogramextensionrequests', $export_result['enableprogramextensionrequests']);
        $this->assertEquals('test_value_for_enablelearningplans', $export_result['enablelearningplans']);
        $this->assertEquals('test_value_for_enableprograms', $export_result['enableprograms']);
        $this->assertEquals('test_value_for_enablecertifications', $export_result['enablecertifications']);
        $this->assertEquals('test_value_for_enablerecordoflearning', $export_result['enablerecordoflearning']);
        $this->assertEquals('test_value_for_enableprogramcompletioneditor', $export_result['enableprogramcompletioneditor']);
        $this->assertEquals('test_value_for_enableoutcomes', $export_result['enableoutcomes']);
        $this->assertEquals('test_value_for_enablelegacyprogramassignments', $export_result['enablelegacyprogramassignments']);
        $this->assertEquals('test_value_for_enablecompletionimport', $export_result['enablecompletionimport']);
        $this->assertEquals('test_value_for_enablegoals', $export_result['enablegoals']);
        $this->assertEquals('test_value_for_enableperformance_activities', $export_result['enableperformance_activities']);
        $this->assertEquals('test_value_for_perform_hide_suspended_users', $export_result['perform_hide_suspended_users']);
        $this->assertEquals('test_value_for_perform_close_suspended_user_instances', $export_result['perform_close_suspended_user_instances']);
        $this->assertEquals('test_value_for_perform_hide_incomplete_responses_closed_instances', $export_result['perform_hide_incomplete_responses_closed_instances']);
        $this->assertEquals('test_value_for_perform_sync_participant_instance_creation', $export_result['perform_sync_participant_instance_creation']);
        $this->assertEquals('test_value_for_perform_sync_participant_instance_closure', $export_result['perform_sync_participant_instance_closure']);
        $this->assertEquals('test_value_for_enablecompetency_assignment', $export_result['enablecompetency_assignment']);
        $this->assertEquals('test_value_for_dynamicappraisals', $export_result['dynamicappraisals']);
        $this->assertEquals('test_value_for_dynamicappraisalsautoprogress', $export_result['dynamicappraisalsautoprogress']);
        $this->assertEquals('test_value_for_enableappraisals', $export_result['enableappraisals']);
        $this->assertEquals('test_value_for_showhistoricactivities', $export_result['showhistoricactivities']);
        $this->assertEquals('test_value_for_enablefeedback360', $export_result['enablefeedback360']);
        $this->assertEquals('test_value_for_enableengage_resources', $export_result['enableengage_resources']);
        $this->assertEquals('test_value_for_enablecontainer_workspace', $export_result['enablecontainer_workspace']);
        $this->assertEquals('test_value_for_enableml_recommender', $export_result['enableml_recommender']);
        $this->assertEquals('test_value_for_enabletotara_msteams', $export_result['enabletotara_msteams']);
        $this->assertEquals('test_value_for_deleteunconfirmed', $export_result['deleteunconfirmed']);
        $this->assertEquals('test_value_for_deleteincompleteusers', $export_result['deleteincompleteusers']);
        $this->assertEquals('test_value_for_deletecompletionlogs', $export_result['deletecompletionlogs']);
        $this->assertEquals('test_value_for_disablegradehistory', $export_result['disablegradehistory']);
        $this->assertEquals('test_value_for_gradehistorylifetime', $export_result['gradehistorylifetime']);
        $this->assertEquals('test_value_for_tempdatafoldercleanup', $export_result['tempdatafoldercleanup']);
        $this->assertEquals('test_value_for_menulifetime', $export_result['menulifetime']);
        $this->assertEquals('test_value_for_forcelogin', $export_result['forcelogin']);
        $this->assertEquals('test_value_for_forceloginforprofiles', $export_result['forceloginforprofiles']);
        $this->assertEquals('test_value_for_forceloginforprofileimage', $export_result['forceloginforprofileimage']);
        $this->assertEquals('test_value_for_preventmultiplelogins', $export_result['preventmultiplelogins']);
        $this->assertEquals('test_value_for_opentogoogle', $export_result['opentogoogle']);
        $this->assertEquals('test_value_for_publishgridcatalogimage', $export_result['publishgridcatalogimage']);
        $this->assertEquals('test_value_for_maxbytes', $export_result['maxbytes']);
        $this->assertEquals('test_value_for_disableconsistentcleaning', $export_result['disableconsistentcleaning']);
        $this->assertEquals('test_value_for_extendedusernamechars', $export_result['extendedusernamechars']);
        $this->assertEquals('test_value_for_keeptagnamecase', $export_result['keeptagnamecase']);
        $this->assertEquals('test_value_for_profilesforenrolledusersonly', $export_result['profilesforenrolledusersonly']);
        $this->assertEquals('test_value_for_cronclionly', $export_result['cronclionly']);
        $this->assertEquals('test_value_for_passwordpolicy', $export_result['passwordpolicy']);
        $this->assertEquals('test_value_for_disableuserimages', $export_result['disableuserimages']);
        $this->assertEquals('test_value_for_persistentloginenable', $export_result['persistentloginenable']);
        $this->assertEquals('test_value_for_rememberusername', $export_result['rememberusername']);
        $this->assertEquals('test_value_for_strictformsrequired', $export_result['strictformsrequired']);
        $this->assertEquals('test_value_for_stricttransportsecurity', $export_result['stricttransportsecurity']);
        $this->assertEquals('test_value_for_securereferrers', $export_result['securereferrers']);
        $this->assertEquals('test_value_for_allowframembedding', $export_result['allowframembedding']);
        $this->assertEquals('test_value_for_permittedcrossdomainpolicies', $export_result['permittedcrossdomainpolicies']);

        // Conditionals on basis of empty state
        $this->assertTrue($export_result['uses_sitepolicy']);
        $this->assertTrue($export_result['uses_sitepolicyguest']);
        $this->assertTrue($export_result['uses_cronremotepassword']);
        $this->assertTrue($export_result['uses_curlsecurityblockedhosts']);
        $this->assertTrue($export_result['uses_curlsecurityallowedport']);
        $this->assertTrue($export_result['uses_pcntl_phpclipath']);
        $this->assertTrue($export_result['uses_urlrewriteclass']);
        $this->assertTrue($export_result['disable_visibility_maps']);
        $this->assertTrue($export_result['uses_custom_page_manager_class']);
        $this->assertTrue($export_result['uses_custom_block_manager_class']);

        // Conditionals on basis of isset state
        $this->assertEquals('test_value_for_preventfilelocking', $export_result['preventfilelocking']);
        $this->assertEquals('test_value_for_preventexecpath', $export_result['preventexecpath']);
        $this->assertEquals('test_value_for_allowlogincsrf', $export_result['allowlogincsrf']);
        $this->assertEquals('test_value_for_customscripts', $export_result['customscripts']);

        // Now, let's unset all the static configurations and flip the conditionals

        unset_config('enablesafebrowserintegration');
        unset_config('dndallowtextandlinks');
        unset_config('enableglobalsearch');
        unset_config('tenantsisolated');
        unset_config('usecomments');
        unset_config('commentsperpage');
        unset_config('usetags');
        unset_config('enablenotes');
        unset_config('messaging');
        unset_config('messaginghidereadnotifications');
        unset_config('messagingallowemailoverride');
        unset_config('enablestats');
        unset_config('enablerssfeeds');
        unset_config('enablebadges');
        unset_config('enablereportcaching');
        unset_config('enableglobalrestrictions');
        unset_config('enableuser_reports');
        unset_config('audiencevisibility');
        unset_config('enableconnectserver');
        unset_config('showhierarchyshortnames');
        unset_config('enabletotaradashboard');
        unset_config('enablereportgraphs');
        unset_config('enablepositions');
        unset_config('enableorganisations');
        unset_config('totara_job_allowmultiplejobs');
        unset_config('enablesitepolicies');
        unset_config('catalogtype');
        unset_config('tenantsenabled');
        unset_config('enablecompetencies');
        unset_config('enablemyteam');
        unset_config('enableevidence');
        unset_config('enablewebservices');
        unset_config('enableblogs');
        unset_config('enableportfolios');
        unset_config('enablecompletion');
        unset_config('completiondefault');
        unset_config('enableavailability');
        unset_config('enablecourserpl');
        unset_config('enablemodulerpl');
        unset_config('enableplagiarism');
        unset_config('enablecontentmarketplaces');
        unset_config('enableprogramextensionrequests');
        unset_config('enablelearningplans');
        unset_config('enableprograms');
        unset_config('enablecertifications');
        unset_config('enablerecordoflearning');
        unset_config('enableprogramcompletioneditor');
        unset_config('enableoutcomes');
        unset_config('enablelegacyprogramassignments');
        unset_config('enablecompletionimport');
        unset_config('enablegoals');
        unset_config('enableperformance_activities');
        unset_config('perform_hide_suspended_users');
        unset_config('perform_close_suspended_user_instances');
        unset_config('perform_hide_incomplete_responses_closed_instances');
        unset_config('perform_sync_participant_instance_creation');
        unset_config('perform_sync_participant_instance_closure');
        unset_config('enablecompetency_assignment');
        unset_config('dynamicappraisals');
        unset_config('dynamicappraisalsautoprogress');
        unset_config('enableappraisals');
        unset_config('showhistoricactivities');
        unset_config('enablefeedback360');
        unset_config('enableengage_resources');
        unset_config('enablecontainer_workspace');
        unset_config('enableml_recommender');
        unset_config('enabletotara_msteams');
        unset_config('deleteunconfirmed');
        unset_config('deleteincompleteusers');
        unset_config('deletecompletionlogs');
        unset_config('disablegradehistory');
        unset_config('gradehistorylifetime');
        unset_config('tempdatafoldercleanup');
        unset_config('menulifetime');
        unset_config('forcelogin');
        unset_config('forceloginforprofiles');
        unset_config('forceloginforprofileimage');
        unset_config('preventmultiplelogins');
        unset_config('opentogoogle');
        unset_config('publishgridcatalogimage');
        unset_config('maxbytes');
        unset_config('disableconsistentcleaning');
        unset_config('extendedusernamechars');
        unset_config('keeptagnamecase');
        unset_config('profilesforenrolledusersonly');
        unset_config('cronclionly');
        unset_config('passwordpolicy');
        unset_config('disableuserimages');
        unset_config('persistentloginenable');
        unset_config('rememberusername');
        unset_config('strictformsrequired');
        unset_config('stricttransportsecurity');
        unset_config('securereferrers');
        unset_config('allowframembedding');
        unset_config('permittedcrossdomainpolicies');


        // Conditionals on basis of empty state
        set_config('sitepolicy', '');
        set_config('sitepolicyguest', '');
        set_config('cronremotepassword', '');
        set_config('curlsecurityblockedhosts', '');
        set_config('curlsecurityallowedport', '');
        set_config('pcntl_phpclipath', '');
        set_config('urlrewriteclass', '');
        set_config('disable_visibility_maps', '');
        set_config('moodlepageclass', '');
        set_config('blockmanagerclass', '');

        // Conditionals on basis of isset state
        unset_config('preventfilelocking');
        unset_config('preventexecpath');
        unset_config('allowlogincsrf');
        unset_config('customscripts');

        $export_result = (new settings_feature())->export();

        // Conditionals on basis on empty state
        $this->assertFalse($export_result['uses_sitepolicy']);
        $this->assertFalse($export_result['uses_sitepolicyguest']);
        $this->assertFalse($export_result['uses_cronremotepassword']);
        $this->assertFalse($export_result['uses_curlsecurityblockedhosts']);
        $this->assertFalse($export_result['uses_curlsecurityallowedport']);
        $this->assertFalse($export_result['uses_pcntl_phpclipath']);
        $this->assertFalse($export_result['uses_urlrewriteclass']);
        $this->assertFalse($export_result['disable_visibility_maps']);
        $this->assertFalse($export_result['uses_custom_page_manager_class']);
        $this->assertFalse($export_result['uses_custom_block_manager_class']);

        // Conditionals on basis of isset state
        $this->assertEquals(export::NOTSET, $export_result['preventfilelocking']);
        $this->assertEquals(export::NOTSET, $export_result['preventexecpath']);
        $this->assertEquals(export::NOTSET, $export_result['allowlogincsrf']);
        $this->assertEquals(export::NOTSET, $export_result['customscripts']);
    }
}