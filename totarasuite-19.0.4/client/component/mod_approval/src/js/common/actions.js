/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Kunle Odusan <kunle.odusan@totaralearning.com>
 * @module mod_approval
 */
import pending from 'tui/pending';
import { totaraUrl, get, parseQueryString, formatParams } from 'tui/util';
import { getString } from 'tui/i18n';
import { assign } from 'tui_xstate/xstate';
import { notify } from 'tui/notifications';
import { MY, OTHERS } from 'mod_approval/constants';
import { getErrorMessage } from 'mod_approval/messages';

/**
 * Actions hold the side effects that happen on a page, e.g redirecting to another page, updating context, etc.
 */

export const navigateToDashboard = () => {
  const done = pending();
  setTimeout(() => {
    done();
    window.location.href = totaraUrl('/mod/approval/application/index.php', {
      notify_type: 'success',
      notify: 'delete_application',
    });
  }, 1000);
};

export const navigateToClone = (context, event) => {
  const editUrl = get(event, [
    'data',
    'data',
    'mod_approval_application_clone',
    'application',
    'page_urls',
    'edit',
  ]);
  window.location.href = totaraUrl(editUrl, {
    notify_type: 'success',
    notify: 'clone_application',
  });
};

export const errorNotify = assign({
  notification: (context, event) => {
    return {
      message: getErrorMessage(event),
      type: 'error',
    };
  },
});

export const withdrawnNotify = () => {
  notify({
    duration: 3000,
    message: getString('success:withdraw_application', 'mod_approval'),
    type: 'success',
  });
};

export const dismissNotification = assign({ notification: null });

const messages = {
  success: {
    delete_application: getString('success:delete_application', 'mod_approval'),
    submit_application: getString('success:submit_application', 'mod_approval'),
    create_draft_application: getString(
      'success:create_draft_application',
      'mod_approval'
    ),
    clone_application: getString('success:clone_application', 'mod_approval'),
    clone_workflow: getString('success:clone_workflow', 'mod_approval'),
    create_workflow: getString('success:create_workflow', 'mod_approval'),
  },
};

export const showNotify = context => {
  const message =
    messages[context.notifyType] &&
    messages[context.notifyType][context.notify];
  if (message) {
    notify({
      duration: 3000,
      message,
      type: context.notifyType,
    });
  }
};

export const unsetNotify = assign({
  notify: null,
  notifyType: null,
});

/**
 * Removes the params the ApplicationsTable uses for preserving state.
 * These are not needed here beyond page load.
 */
export const stripDashboardParams = () => {
  const params = parseQueryString(window.location.search);
  const updatedParams = {};
  Object.keys(params).forEach(key => {
    if (
      !key.includes(OTHERS) &&
      !key.includes(MY) &&
      key !== 'tab' &&
      key !== 'from_dashboard'
    ) {
      updatedParams[key] = params[key];
    }
  });

  const formattedParams = formatParams(updatedParams);
  const url = `${window.location.pathname}?${formattedParams}`;
  window.history.replaceState(null, null, url);
};
