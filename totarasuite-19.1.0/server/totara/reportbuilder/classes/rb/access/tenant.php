<?php
/*
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
 * @package totara_reportbuilder
 */

namespace totara_reportbuilder\rb\access;

use context_system;
use core\orm\query\builder;
use totara_tenant\local\util;
use html_writer;
use reportbuilder;

/**
 * Role based access restriction for tenant
 *
 * Limit access to reports by user roles in tenant context or tenant category context
 */
class tenant extends base {
    public function get_accessible_reports($userid) {
        // Not logged in
        if ($userid <= 0) {
            return [];
        }

        // User can manage site level reports.
        if (has_capability('totara/reportbuilder:managereports', context_system::instance(), $userid)) {
            return builder::table('report_builder')
                ->where_not_null('tenantid')
                ->select('id')
                ->get()
                ->pluck('id');
        }

        $tenantids = util::get_user_participation($userid);
        $allowed_reports = [];

        // Return reports for this tenant.
        $reports = builder::table('report_builder', 'rb')
            ->join(['report_builder_settings', 'rbs'], 'rbs.reportid', 'rb.id')
            ->where_in('rb.tenantid', $tenantids)
            ->where('rb.embedded', 0)
            ->where('rbs.type', $this->get_type())
            ->where('rbs.name', 'activeroles')
            ->select_raw('rb.id, rb.accessmode, rbs.value as activeroles, rb.tenantid')
            ->get();

        $tenant_context_ids = builder::table('context', 'c')
            ->select_raw('c.id')
            ->join(['tenant', 't'], 'c.tenantid', 't.id')
            ->where_in('c.contextlevel', [CONTEXT_TENANT, CONTEXT_COURSECAT])
            ->where('c.parentid', \context_system::instance()->id)
            ->where_in('t.id', $reports->pluck('tenantid'))
            ->get()
            ->pluck('id');

        $tenant_roles = builder::table('role_assignments', 'ra')
            ->join(['context', 'c'], 'c.id', 'ra.contextid')
            ->where('ra.userid', $userid)
            ->where_in('ra.contextid', $tenant_context_ids) // can be used as a subquery
            ->select(['ra.id', 'ra.roleid', 'c.tenantid'])
            ->get()
            ->all(true);

        $roles_by_tenant_id = [];

        // Key roles by tenant
        foreach ($tenant_roles as $tenantrole) {
            $roles_by_tenant_id[$tenantrole->tenantid][] = $tenantrole->roleid;
        }

        foreach ($reports as $rpt) {
            // Grant access as the user is a tenant user
            if ((int) $rpt->accessmode === REPORT_BUILDER_ACCESS_MODE_NONE) {
                $allowed_reports[] = $rpt->id;
                continue;
            }

            // Checking if the user has the allowed role within the tenant.
            $allowed_roles = explode('|', $rpt->activeroles ?? '');

            if (!empty($roles_by_tenant_id[$rpt->tenantid])) {
                $roles_in_tenant = array_intersect($roles_by_tenant_id[$rpt->tenantid], $allowed_roles);
            }

            if (!empty($roles_in_tenant)) {
                $allowed_reports[] = $rpt->id;
            }
        }

        return $allowed_reports;
    }

    /**
     * Adds form elements required for this access restriction's settings page
     *
     * @param \MoodleQuickForm $mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     */
    public function form_template($mform, $reportid): void {
        $report = builder::table('report_builder')
            ->find($reportid);
        if (!$report->tenantid) {
            return;
        }

        $type = $this->get_type();
        $mform->addElement('header', 'accessbytenant', get_string('accessbytenant', 'totara_reportbuilder'));
        $activeroles = explode('|', reportbuilder::get_setting($reportid, $type, 'activeroles'));

        $mform->addElement('hidden', 'role_enable', 1);
        $mform->setType('role_enable', PARAM_INT);

        $roles = $this->get_tenant_roles();

        if (empty($roles)) {
            $mform->addElement('html', html_writer::tag('p', get_string('error:norolesfound', 'totara_reportbuilder')));
            return;
        }
        // set context for role-based access
        $rolesgroup = array();
        foreach ($roles as $role) {
            $rolesgroup[] = $mform->createElement(
                'advcheckbox', "role_activeroles[{$role->id}]", '', $role->localname, null, array(0, 1)
            );
            if (in_array($role->id, $activeroles)) {
                $mform->setDefault("role_activeroles[{$role->id}]", 1);
            }
        }

        $mform->addGroup(
            $rolesgroup,
            'roles',
            get_string('roleswithaccess', 'totara_reportbuilder'),
            html_writer::empty_tag('br'),
            false
        );

        $mform->disabledIf('roles', 'accessenabled', 'eq', 0);
        $mform->addHelpButton('roles', 'reportbuilderrolesaccess', 'totara_reportbuilder');
    }

    /**
     * Set report settings for a new tenant report.
     *
     * @param int $reportid
     * @return void
     * @throws \coding_exception
     */
    public function initialize_tenant_access(int $reportid): void {
        reportbuilder::update_setting($reportid, $this->get_type(), 'enable', 1);

        // Add default access to Manger and Tenant domain manager
        $default_roles = builder::table('role')
            ->where_in('shortname', ['manager', 'tenantdomainmanager'])
            ->select('id')
            ->get()
            ->pluck('id');

        if (!empty($default_roles)) {
            reportbuilder::update_setting($reportid, $this->get_type(), 'activeroles', implode('|', $default_roles));
        }
    }

    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param \MoodleQuickForm $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform): bool {
        // save the results of submitting the access form to
        // report_builder_settings
        $type = $this->get_type();
        // enable checkbox option
        $enable = (isset($fromform->role_enable) && $fromform->role_enable) ? 1 : 0;
        reportbuilder::update_setting($reportid, $type, 'enable', $enable);

        $activeroles = array();
        if (isset($fromform->role_activeroles)) {
            foreach ($fromform->role_activeroles as $roleid => $setting) {
                if ($setting == 1) {
                    $activeroles[] = $roleid;
                }
            }
            // implode into string and update setting
            reportbuilder::update_setting($reportid, $type, 'activeroles', implode('|', $activeroles));
        }
        return true;
    }

    /**
     * Get roles for the tenant.
     *
     * @return array Returns roles found in both tenant context & tenant category context.
     */
    protected function get_tenant_roles(): array {
        $fields = ['r.id', 'r.name', 'r.shortname', 'r.sortorder', 'r.archetype'];
        $roles = builder::table('role', 'r')
            ->select($fields)
            ->join(['role_context_levels', 'rcl'], 'r.id', '=', 'rcl.roleid')
            ->where('rcl.contextlevel', [CONTEXT_TENANT, CONTEXT_COURSECAT])
            ->group_by($fields)
            ->order_by_raw('r.sortorder ASC')
            ->fetch(true);

        return role_fix_names($roles);
    }
}