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

import SvgIconWrap from 'tui/components/icons/implementation/SvgIconWrap';
import { h, mergeProps } from 'vue';

const propDefs = {
  title: String,
  alt: String,
  size: {
    type: [Number, String],
    default: 200,
    validator(prop) {
      if (prop == null) {
        return true;
      }
      const num = Number(prop);
      return [100, 200, 300, 400, 500, 600, 700].includes(num);
    },
  },
  state: String,
  class: [String, Object, Array],
  customClass: [String, Object, Array],
};

export function createIconComponent([type, svgAttrs, content], opts = {}) {
  // svgc (content string) is the only supported type
  if (type != 'svgc') throw new Error('Unsupported icon type');
  const comp = function Icon(props, ctx) {
    return h(
      SvgIconWrap,
      mergeProps(ctx.attrs, props, {
        htmlContent: content,
        viewBox: svgAttrs.viewBox,
        rootFill: svgAttrs.fill,
        state: props.state || opts.state,
        flipRtl: opts.flipRtl,
        class: opts.class,
      })
    );
  };
  comp.props = propDefs;
  if (opts.deprecated) {
    comp.deprecated = true;
  }
  return comp;
}
