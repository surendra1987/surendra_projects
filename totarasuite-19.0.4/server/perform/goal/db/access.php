<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'perform/goal:viewownpersonalgoals' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:manageownpersonalgoals' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:setownpersonalgoalprogress' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'user' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:viewpersonalgoals' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'staffmanager' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:managepersonalgoals' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'staffmanager' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:setpersonalgoalprogress' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => [
            'staffmanager' => CAP_ALLOW,
        ],
        'riskbitmask' => RISK_PERSONAL
    ],
    'perform/goal:manage_comment_notifications' => [
        'riskbitmask' => RISK_SPAM | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'tenantdomainmanager' => CAP_ALLOW,
        ]
    ],
];
