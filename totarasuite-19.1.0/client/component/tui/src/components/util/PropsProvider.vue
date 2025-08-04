<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totaralearning.com>
  @module tui
-->

<script>
import { mergeProps, toHandlers } from 'vue';
import { addProps } from 'tui/vue/vnode';
/**
 * Component that modifies vnodes of its children, adding props and event
 * listeners.
 *
 * Use cases:
 *   * Tightly coupled parent->child component relationships, for example
 *     <Radio>s in a <RadioGroup>.
 */
export default {
  props: {
    provide: {
      type: [Function, Object],
      required: true,
    },
  },

  render() {
    return addProps(this.$slots.default(), vnode => {
      if (typeof vnode.type === 'string') {
        return {};
      }
      const info = { props: vnode.props };
      const provided =
        typeof this.provide === 'function' ? this.provide(info) : this.provide;
      const newProps = mergeProps(
        provided.props,
        vnode.props,
        provided.listeners && toHandlers(provided.listeners),
        provided.nativeListeners && toHandlers(provided.nativeListeners)
      );
      return newProps;
    });
  },
};
</script>
