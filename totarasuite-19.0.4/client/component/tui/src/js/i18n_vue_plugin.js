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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

/* eslint-disable tui/replaceable-string-references */

import { getString, hasString } from './i18n';

/**
 * Vue plugin to enable i18n support through language strings
 */
const i18nPlugin = {
  install(app) {
    const gp = app.config.globalProperties;

    gp.$str = getString;
    gp.$str.__r = getString.__r;
    gp.$hasStr = hasString;

    gp.$tryStr = function(key, totaraComponent, param) {
      return hasString(key, totaraComponent)
        ? getString(key, totaraComponent, param)
        : null;
    };
  },
};

export default i18nPlugin;

/**
 * Collect all language strings required by the provided component and its
 * dependencies.
 *
 * @deprecated since Totara 19.0
 * @param {object} component Component definition.
 * @return {array}
 */
export function collectStrings() {
  return [];
}
