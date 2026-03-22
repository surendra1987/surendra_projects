/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 */

const utils = require('eslint-plugin-vue/lib/utils');

module.exports = {
  create(context) {
    return context.parserServices.defineTemplateBodyVisitor({
      "VAttribute[directive=true][key.name.name='model'][key.argument=null]": node => {
        const element = node.parent.parent;

        if (utils.isCustomComponent(element)) {
          context.report({
            node,
            message: `v-model must have prop argument, e.g. v-model:value="..." (on component ${element.rawName})`,
          });
        }
      },
    });
  },
};
