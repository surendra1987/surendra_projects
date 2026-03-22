/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @module mod_approval
 */

import { config } from 'tui/config';
import { getString } from 'tui/i18n';

const successMessageTable = {
  // application
  'done.invoke.approveApplication': getString(
    'success:approve_application',
    'mod_approval'
  ),
  'done.invoke.rejectApplication': getString(
    'success:reject_application',
    'mod_approval'
  ),
  // workflow
  'done.invoke.archiveWorkflow': getString(
    'success:archive_workflow',
    'mod_approval'
  ),
  'done.invoke.unarchiveWorkflow': getString(
    'success:unarchive_workflow',
    'mod_approval'
  ),
  'done.invoke.deleteWorkflow': getString(
    'success:delete_workflow',
    'mod_approval'
  ),
  'done.invoke.editWorkflow': getString(
    'success:edit_workflow',
    'mod_approval'
  ),
  'done.invoke.publishWorkflowVersion': getString(
    'success:publish_workflow_version',
    'mod_approval'
  ),
  'done.invoke.saveOverrides': getString(
    'success:save_overrides',
    'mod_approval'
  ),
  'done.invoke.archiveOverrides': getString(
    'success:archive_overrides',
    'mod_approval'
  ),
  'done.invoke.createOverrideAssignment': getString(
    'success:create_override',
    'mod_approval'
  ),
  'done.invoke.updateDefaultTransition': getString(
    'success:update_default_transition',
    'mod_approval'
  ),
};

const errorMessageTable = {
  // TODO remove unused?

  // application
  'error.platform.createComment': getString(
    'error:reject_application',
    'mod_approval'
  ),
  'error.platform.rejectApplication': getString(
    'error:reject_application',
    'mod_approval'
  ),
  'error.platform.approveApplication': getString(
    'error:approve_application',
    'mod_approval'
  ),
  'error.platform.saveApplication': getString(
    'error:save_application',
    'mod_approval'
  ),
  'error.platform.saveAsDraftApplication': getString(
    'error:save_application',
    'mod_approval'
  ),
  'error.platform.submitApplication': getString(
    'error:submit_application',
    'mod_approval'
  ),
  'error.platform.deleteApplication': getString(
    'error:delete_application',
    'mod_approval'
  ),
  'error.platform.withdrawApplication': getString(
    'error:withdraw_application',
    'mod_approval'
  ),
  'error.platform.cloneApplication': getString(
    'error:clone_application',
    'mod_approval'
  ),
  'error.platform.cloningApplication': getString(
    'error:clone_application',
    'mod_approval'
  ),
  'error.platform.creatingApplicationMutation': getString(
    'error:create_application',
    'mod_approval'
  ),
  // workflow
  'error.platform.editWorkflow': getString(
    'error:edit_workflow_details',
    'mod_approval'
  ),
  'error.platform.unarchiveWorkflow': getString(
    'error:unarchive_workflow',
    'mod_approval'
  ),
  'error.platform.archiveWorkflow': getString(
    'error:archive_workflow',
    'mod_approval'
  ),
  'error.platform.cloneWorkflow': getString(
    'error:clone_workflow',
    'mod_approval'
  ),
  'error.platform.deleteWorkflow': getString(
    'error:delete_workflow',
    'mod_approval'
  ),
  'error.platform.publishWorkflowVersion': getString(
    'error:publish_workflow_version',
    'mod_approval'
  ),
};

function getMessage(table, event) {
  return table[event.type] || undefinedError(event.type);
}

function suggestName(eventType) {
  const snakeCase = s => s.replace(/([A-Z])/g, '_$1').toLowerCase();
  if (/^done\.invoke\.[a-z]/.test(eventType)) {
    return 'success:' + snakeCase(eventType.substring(12));
  } else if (/^error\.platform\.[a-z]/.test(eventType)) {
    return 'error:' + snakeCase(eventType.substring(15));
  }
  return null;
}

function undefinedError(eventType) {
  // Display debug info in development.
  if (process.env.NODE_ENV === 'development') {
    let message = `message for ${eventType} is not defined.`;
    const suggestion = suggestName(eventType);
    if (suggestion) {
      message += ` (${suggestion})`;
    }
    console.error(message);
    return `Error: ${message}`;
  }
  // Display '?' in behat.
  if (config.behatSiteRunning) {
    return '?';
  }
  // Display 'an error occurred' in production.
  return getString('error:generic', 'mod_approval');
}

export function getSuccessMessage(event) {
  return getMessage(successMessageTable, event);
}

/** @deprecated since Totara 19.0 */
export function getSuccessMessageAsync(event) {
  return Promise.resolve(getMessage(successMessageTable, event));
}

export function getErrorMessage(event) {
  return getMessage(errorMessageTable, event);
}

/** @deprecated since Totara 19.0 */
export function getErrorMessageAsync(event) {
  return Promise.resolve(getMessage(errorMessageTable, event));
}
