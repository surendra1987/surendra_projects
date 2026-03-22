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

namespace core\usagedata;

use tool_usagedata\export;

class settings_feature implements export {

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('settings_feature_summary');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    public function export(): array {
        global $CFG;

        return [
            // Experimental features
            'enablesafebrowserintegration' => $CFG->enablesafebrowserintegration ?? self::NOTSET,
            'dndallowtextandlinks' => $CFG->dndallowtextandlinks ?? self::NOTSET,
            'enableglobalsearch' => $CFG->enableglobalsearch ?? self::NOTSET,
            'tenantsisolated' => $CFG->tenantsisolated ?? self::NOTSET,

            // Shared service settings
            'usecomments' => $CFG->usecomments ?? self::NOTSET,
            'commentsperpage' => $CFG->commentsperpage ?? self::NOTSET,
            'usetags' => $CFG->usetags ?? self::NOTSET,
            'enablenotes' => $CFG->enablenotes ?? self::NOTSET,
            'messaging' => $CFG->messaging ?? self::NOTSET,
            'messaginghidereadnotifications' => $CFG->messaginghidereadnotifications ?? self::NOTSET,
            'messagingallowemailoverride' => $CFG->messagingallowemailoverride ?? self::NOTSET,
            'enablestats' => $CFG->enablestats ?? self::NOTSET,
            'enablerssfeeds' => $CFG->enablerssfeeds ?? self::NOTSET,
            'enablebadges' => $CFG->enablebadges ?? self::NOTSET,
            'enablereportcaching' => $CFG->enablereportcaching ?? self::NOTSET,
            'enableglobalrestrictions' => $CFG->enableglobalrestrictions ?? self::NOTSET,
            'enableuser_reports' => $CFG->enableuser_reports ?? self::NOTSET,
            'audiencevisibility' => $CFG->audiencevisibility ?? self::NOTSET,
            'enableconnectserver' => $CFG->enableconnectserver ?? self::NOTSET,
            'showhierarchyshortnames' => $CFG->showhierarchyshortnames ?? self::NOTSET,
            'enabletotaradashboard' => $CFG->enabletotaradashboard ?? self::NOTSET,
            'enablereportgraphs' => $CFG->enablereportgraphs ?? self::NOTSET,
            'enablepositions' => $CFG->enablepositions ?? self::NOTSET,
            'enableorganisations' => $CFG->enableorganisations ?? self::NOTSET,
            'totara_job_allowmultiplejobs' => $CFG->totara_job_allowmultiplejobs ?? self::NOTSET,
            'enablesitepolicies' => $CFG->enablesitepolicies ?? self::NOTSET,
            'catalogtype' => $CFG->catalogtype ?? self::NOTSET,
            'tenantsenabled' => $CFG->tenantsenabled ?? self::NOTSET,
            'enablecompetencies' => $CFG->enablecompetencies ?? self::NOTSET,
            'enablemyteam' => $CFG->enablemyteam ?? self::NOTSET,
            'enableevidence' => $CFG->enableevidence ?? self::NOTSET,
            'enablewebservices' => $CFG->enablewebservices ?? self::NOTSET,
            'enableblogs' => $CFG->enableblogs ?? self::NOTSET,

            // Learn settings
            'enableportfolios' => $CFG->enableportfolios ?? self::NOTSET,
            'enablecompletion' => $CFG->enablecompletion ?? self::NOTSET,
            'completiondefault' => $CFG->completiondefault ?? self::NOTSET,
            'enableavailability' => $CFG->enableavailability ?? self::NOTSET,
            'enablecourserpl' => $CFG->enablecourserpl ?? self::NOTSET,
            'enablemodulerpl' => $CFG->enablemodulerpl ?? self::NOTSET,
            'enableplagiarism' => $CFG->enableplagiarism ?? self::NOTSET,
            'enablecontentmarketplaces' => $CFG->enablecontentmarketplaces ?? self::NOTSET,
            'enableprogramextensionrequests' => $CFG->enableprogramextensionrequests ?? self::NOTSET,
            'enablelearningplans' => $CFG->enablelearningplans ?? self::NOTSET,
            'enableprograms' => $CFG->enableprograms ?? self::NOTSET,
            'enablecertifications' => $CFG->enablecertifications ?? self::NOTSET,
            'enablerecordoflearning' => $CFG->enablerecordoflearning ?? self::NOTSET,
            'enableprogramcompletioneditor' => $CFG->enableprogramcompletioneditor ?? self::NOTSET,
            'enableoutcomes' => $CFG->enableoutcomes ?? self::NOTSET,
            'enablelegacyprogramassignments' => $CFG->enablelegacyprogramassignments ?? self::NOTSET,
            'enablecompletionimport' => $CFG->enablecompletionimport ?? self::NOTSET,

            // Perform settings
            'enablegoals' => $CFG->enablegoals ?? self::NOTSET,
            'enableperformance_activities' => $CFG->enableperformance_activities ?? self::NOTSET,
            'perform_hide_suspended_users' => $CFG->perform_hide_suspended_users ?? self::NOTSET,
            'perform_close_suspended_user_instances' => $CFG->perform_close_suspended_user_instances ?? self::NOTSET,
            'perform_hide_incomplete_responses_closed_instances' => $CFG->perform_hide_incomplete_responses_closed_instances ?? self::NOTSET,
            'perform_sync_participant_instance_creation' => $CFG->perform_sync_participant_instance_creation ?? self::NOTSET,
            'perform_sync_participant_instance_closure' => $CFG->perform_sync_participant_instance_closure ?? self::NOTSET,
            'enablecompetency_assignment' => $CFG->enablecompetency_assignment ?? self::NOTSET,
            'dynamicappraisals' => $CFG->dynamicappraisals ?? self::NOTSET,
            'dynamicappraisalsautoprogress' => $CFG->dynamicappraisalsautoprogress ?? self::NOTSET,
            'enableappraisals' => $CFG->enableappraisals ?? self::NOTSET,
            'showhistoricactivities' => $CFG->showhistoricactivities ?? self::NOTSET,
            'enablefeedback360' => $CFG->enablefeedback360 ?? self::NOTSET,

            // Engage settings
            'enableengage_resources' => $CFG->enableengage_resources ?? self::NOTSET,
            'enablecontainer_workspace' => $CFG->enablecontainer_workspace ?? self::NOTSET,
            'enableml_recommender' => $CFG->enableml_recommender ?? self::NOTSET,
            'enabletotara_msteams' => $CFG->enabletotara_msteams ?? self::NOTSET,

            // Server; Cleanup
            'deleteunconfirmed' => $CFG->deleteunconfirmed ?? self::NOTSET,
            'deleteincompleteusers' => $CFG->deleteincompleteusers ?? self::NOTSET,
            'deletecompletionlogs' => $CFG->deletecompletionlogs ?? self::NOTSET,
            'disablegradehistory' => $CFG->disablegradehistory ?? self::NOTSET,
            'gradehistorylifetime' => $CFG->gradehistorylifetime ?? self::NOTSET,
            'tempdatafoldercleanup' => $CFG->tempdatafoldercleanup ?? self::NOTSET,

            // Server; Performance
            'menulifetime' => $CFG->menulifetime ?? self::NOTSET,

            // Security settings
            'forcelogin' => $CFG->forcelogin ?? self::NOTSET,
            'forceloginforprofiles' => $CFG->forceloginforprofiles ?? self::NOTSET,
            'forceloginforprofileimage' => $CFG->forceloginforprofileimage ?? self::NOTSET,
            'preventmultiplelogins' => $CFG->preventmultiplelogins ?? self::NOTSET,
            'opentogoogle' => $CFG->opentogoogle ?? self::NOTSET,
            'publishgridcatalogimage' => $CFG->publishgridcatalogimage ?? self::NOTSET,
            'maxbytes' => $CFG->maxbytes ?? self::NOTSET,
            'disableconsistentcleaning' => $CFG->disableconsistentcleaning ?? self::NOTSET,
            'extendedusernamechars' => $CFG->extendedusernamechars ?? self::NOTSET,
            'uses_sitepolicy' => !empty($CFG->sitepolicy),
            'uses_sitepolicyguest' => !empty($CFG->sitepolicyguest),
            'keeptagnamecase' => $CFG->keeptagnamecase ?? self::NOTSET,
            'profilesforenrolledusersonly' => $CFG->profilesforenrolledusersonly ?? self::NOTSET,
            'cronclionly' => $CFG->cronclionly ?? self::NOTSET,
            'uses_cronremotepassword' => !empty($CFG->cronremotepassword),
            'passwordpolicy' => $CFG->passwordpolicy ?? self::NOTSET,
            'disableuserimages' => $CFG->disableuserimages ?? self::NOTSET,
            'persistentloginenable' => $CFG->persistentloginenable ?? self::NOTSET,
            'rememberusername' => $CFG->rememberusername ?? self::NOTSET,
            'strictformsrequired' => $CFG->strictformsrequired ?? self::NOTSET,

            // HTTP security
            'stricttransportsecurity' => $CFG->stricttransportsecurity ?? self::NOTSET,
            'securereferrers' => $CFG->securereferrers ?? self::NOTSET,
            'allowframembedding' => $CFG->allowframembedding ?? self::NOTSET,
            'permittedcrossdomainpolicies' => $CFG->permittedcrossdomainpolicies ?? self::NOTSET,
            'uses_curlsecurityblockedhosts' => !empty($CFG->curlsecurityblockedhosts),
            'uses_curlsecurityallowedport' => !empty($CFG->curlsecurityallowedport),

            // Config.php only
            'uses_pcntl_phpclipath' => !empty($CFG->pcntl_phpclipath),
            'uses_urlrewriteclass' => !empty($CFG->urlrewriteclass),
            'disable_visibility_maps' => !empty($CFG->disable_visibility_maps),
            'uses_custom_page_manager_class' => !empty($CFG->moodlepageclass),
            'uses_custom_block_manager_class' => !empty($CFG->blockmanagerclass),
            'preventfilelocking' => isset($CFG->preventfilelocking) ? $CFG->preventfilelocking : self::NOTSET,
            'preventexecpath' => isset($CFG->preventexecpath) ? $CFG->preventexecpath : self::NOTSET,
            'allowlogincsrf' => isset($CFG->allowlogincsrf) ? $CFG->allowlogincsrf : self::NOTSET,
            'customscripts' => isset($CFG->customscripts) ? $CFG->customscripts : self::NOTSET,
        ];
    }
}