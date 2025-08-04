<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

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
import { cloneVNode, mergeProps } from 'vue';
import { extractSingleChild } from 'tui/vue/vnode';

const KEY_ENTER = 13;
const KEY_SPACE = 32;

export default {
  inheritAttrs: false,

  methods: {
    getDisabled() {
      return this.$el.getAttribute('aria-disabled');
    },
    handleClick(event) {
      if (this.getDisabled()) {
        event.preventDefault();
        // Replicate native behavior: no click events on disabled buttons
        // (including bubbling)
        event.stopPropagation();
      }
    },
    handleKeyDown(event) {
      if (event.keyCode === KEY_SPACE) {
        event.preventDefault();
      } else if (event.keyCode === KEY_ENTER) {
        event.preventDefault();
        this.$el.click();
      }
    },
    handleKeyUp(event) {
      if (event.keyCode === KEY_SPACE) {
        event.preventDefault();
        this.$el.click();
      }
    },
    wrapClickListener(fn) {
      return event => {
        if (!this.getDisabled()) {
          fn(event);
        }
      };
    },
  },

  render() {
    const content = this.$slots.default && this.$slots.default();
    if (!content || content.length == 0) {
      return null;
    }
    let vnode = extractSingleChild(content);

    vnode = cloneVNode(vnode);

    const isDisabled = vnode.props && Boolean(vnode.props.disabled);

    const originalProps = { ...vnode.props };
    if (originalProps.onClick) {
      if (Array.isArray(originalProps.onClick)) {
        originalProps.onClick = originalProps.onClick.map(
          this.wrapClickListener
        );
      } else {
        originalProps.onClick = this.wrapClickListener(originalProps.onClick);
      }
    }

    vnode.props = mergeProps(
      {
        role: 'button',
        tabindex: isDisabled ? null : '0',
        'aria-disabled': isDisabled ? 'true' : null,
        onKeydown: this.handleKeyDown,
        onKeyup: this.handleKeyUp,
        onClick: this.handleClick,
      },
      originalProps,
      // pass along attrs, but not event handlers
      Object.fromEntries(
        Object.entries(this.$attrs).filter(([key]) => !key.startsWith('on'))
      )
    );

    return vnode;
  },
};
</script>
