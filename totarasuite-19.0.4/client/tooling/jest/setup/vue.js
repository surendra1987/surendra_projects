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

/* eslint-disable vue/component-definition-name-casing */

import * as i18n from 'tui/i18n';
import { config } from '@vue/test-utils';
import { totaraUrl } from 'tui/util';

let counter = 0;

config.global.mocks.$str = (key, comp, a) => {
  if (i18n.hasString(key, comp)) {
    return i18n.getString(key, comp, a);
  }
  return a != null
    ? `[[${key}, ${comp}, ${JSON.stringify(a)}]]`
    : `[[${key}, ${comp}]]`;
};

config.global.mocks.uid = 'id';
config.global.mocks.$id = function(x) {
  const uid =
    '$_tui_uid' in this
      ? this.$_tui_uid
      : (this.$_tui_uid = 'uid-' + ++counter);
  return x ? uid + '-' + x : uid;
};
config.global.mocks.$idRef = function(id) {
  return '#' + this.$id(id);
};

config.global.mocks.$url = totaraUrl;

config.global.directives['trap-focus'] = {};
config.global.directives['focus-within'] = {};

config.global.components.passthrough = function(props, { slots }) {
  return slots.default && slots.default({});
};

config.global.components.render = function(props) {
  return props.vnode;
};

jest.mock('tui/components/icons/implementation/SvgIconWrap', () => {
  const { h } = require('vue');
  return {
    render: () => h('span'),
  };
});

beforeEach(() => {
  counter = 0;
});
