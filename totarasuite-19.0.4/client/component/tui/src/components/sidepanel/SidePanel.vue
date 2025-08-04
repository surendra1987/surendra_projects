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

  @author Dave Wallace <dave.wallace@totaralearning.com>
  @module tui
-->

<template>
  <div
    ref="sidePanel"
    class="tui-sidePanel"
    :class="{
      'tui-sidePanel--animated': animated,
      'tui-sidePanel--flush': flush,
      'tui-sidePanel--overflows': overflows,
      'tui-sidePanel--sticky': sticky,
      'tui-sidePanel--hasButtonControl': showButtonControl,
      'tui-sidePanel--open': isOpen && !closing,
      'tui-sidePanel--closed': !isOpen && !opening,
      'tui-sidePanel--opening': opening,
      'tui-sidePanel--closing': closing,
      'tui-sidePanel--ltr': direction === 'ltr',
      'tui-sidePanel--rtl': direction === 'rtl',
      'tui-sidePanel--displayChrome': !chromeless,
    }"
    :style="{
      minHeight: minHeight ? minHeight + 'px' : 'initial',
    }"
  >
    <div ref="sidePanel__inner" class="tui-sidePanel__inner">
      <div
        ref="sidePanel__content"
        class="tui-sidePanel__content"
        :style="{ width: contentWidth }"
      >
        <slot ref="removableContent" />
      </div>
    </div>
  </div>
</template>

<script>
import { waitForTransitionEnd } from 'tui/dom/transitions';

export default {
  props: {
    /**
     * Whether the SidePanel should is intended to be opened from the left or
     * right side of a page. Expected values are 'ltr' and 'rtl'
     *
     * @deprecated Since Totara 19.0
     **/
    direction: {
      type: String,
      default: 'ltr',
      validator: str => ['ltr', 'rtl'].includes(str),
    },

    /**
     * Whether the SidePanel's inner content should have a fixed width when its state is expanding or collapsing
     * When set to true, a fixed-width will be applied, preventing reflow of SidePanel contents during transitions
     **/
    fixContentWidth: {
      type: Boolean,
      default: true,
    },

    /**
     * Whether the SidePanel should be open when it is first rendered
     **/
    initiallyOpen: {
      type: Boolean,
      default: false,
    },

    /**
     * Whether transition lifecycles should be managed for CSS-based animations
     **/
    animated: {
      type: Boolean,
      default: true,
    },

    /**
     * Whether the SidePanel should remain wholly in the viewport when a long
     * page is scrolled
     **/
    sticky: {
      type: Boolean,
      default: true,
    },

    /**
     * Pixel based value that the SidePanel will respect with short viewports
     **/
    minHeight: {
      type: [Number, String],
      default: 'initial', // assumed a px based calculation
    },

    /**
     * Whether to assume the SidePanel is flush to the page header and footer
     **/
    flush: {
      type: Boolean,
      default: true,
    },

    /**
     * Whether the SidePanel inner container should invoke a scrollbar if its
     * contents exceed its available height
     **/
    overflows: {
      type: Boolean,
      default: true,
    },

    /**
     * Whether to render the expand/collapse SidePanel toggle control
     *
     * @deprecated since Totara 19.0
     **/
    showButtonControl: {
      type: Boolean,
      default: true,
    },

    /**
     * Variant to remove container styles
     **/
    chromeless: Boolean,

    /**
     * Way to pass in expanded state as a controlled component
     **/
    open: {
      default: undefined,
      type: Boolean,
    },
  },

  emits: [
    'sidepanel-expanding',
    'sidepanel-expanded',
    'sidepanel-collapsing',
    'sidepanel-collapsed',
  ],

  data() {
    return {
      /**
       * Toggle value indicating the SidePanel is completely open or closed.
       * Includes after an expand or collapse transition lifecycle has completed,
       * or immediately in the case where no transition is used
       **/
      isOpen: false,

      /**
       * Toggle value indicating the SidePanel is currently moving between a
       * completely open state to a completely closed state
       **/
      closing: false,

      /**
       * Toggle value indicating the SidePanel is currently moving between a
       * completely closed state to a completely open state
       **/
      opening: false,

      /**
       * Width of the content preventing reflow of SidePanel contents during transitions
       **/
      contentWidth: 'auto',
    };
  },

  computed: {
    shouldBeOpen() {
      if (this.open === undefined) {
        return this.initiallyOpen;
      }
      return this.open;
    },
  },

  watch: {
    shouldBeOpen(open) {
      if (open) {
        this.expand();
      } else {
        this.collapse();
      }
    },
  },

  mounted() {
    // expand as soon as the SidePanel mounts, if configured so
    if (this.shouldBeOpen) {
      this.expand();
    }
  },

  methods: {
    expand() {
      if (this.opening) {
        return;
      }
      this.opening = true;
      this.$emit('sidepanel-expanding');

      this.contentWidth = 'auto';

      this.$_animate().then(() => {
        this.opening = false;
        this.isOpen = true;

        this.$emit('sidepanel-expanded');
      });
    },

    collapse() {
      if (this.closing) {
        return;
      }

      this.closing = true;
      this.$emit('sidepanel-collapsing');

      this.contentWidth = this.getContentWidth();

      this.$_animate().then(() => {
        this.closing = false;
        this.isOpen = false;

        this.$emit('sidepanel-collapsed');
      });
    },

    getContentWidth() {
      if (this.fixContentWidth)
        return (
          this.$refs.sidePanel__content.getBoundingClientRect().width + 'px'
        );
    },

    async $_animate() {
      if (this.animated) {
        // only interested in tracking transitions on content holding container
        const transitionEls = [this.$refs.sidePanel__content].filter(Boolean);

        await waitForTransitionEnd(transitionEls);
      }
    },
  },
};
</script>

<style lang="scss">
:root {
  --tui-sidepanel-button-width: 30px;
  --tui-sidepanel-button-height: 66px;
  --tui-sidepanel-border-width: 1px;
}

.tui-sidePanel {
  display: flex;
  flex-direction: column;

  &--displayChrome {
    display: flex;
    align-items: flex-start;
    height: 100%;
  }

  // inner content alignment
  &--rtl,
  .dir-rtl .tui-sidePanel--ltr & {
    justify-content: flex-end;
  }
  &--ltr,
  .dir-rtl .tui-sidePanel--rtl & {
    justify-content: flex-start;
  }

  &--sticky {
    position: sticky;
    top: 0;
    max-height: 100vh;
    overflow: hidden;
  }

  &--sticky&--closed {
    overflow: visible;
  }

  /**
   * A wrapper for content container, which helps with transitions on width
   * while overflowing content is still visible, and providing whitespace
   * between content and the edges of the SidePanel
   **/
  &__inner {
    .tui-sidePanel--displayChrome & {
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      flex-shrink: 1;
      width: 100%;
      background-color: var(--color-neutral-3);
      border-radius: var(--border-radius-curved);
    }

    .tui-sidePanel--flush & {
      border-top: none;
      border-bottom: none;
    }

    .tui-sidePanel--open.tui-sidePanel--overflows & {
      overflow-y: auto;
    }

    .tui-sidePanel--closed & {
      max-width: 1px;
      padding-right: 0;
      padding-left: 0;
      border-left: 0;
    }

    // we have to cut off overflow during these states otherwise we'll bump
    // page scrollbars, or a containing element scrollbars
    .tui-sidePanel--closed &,
    .tui-sidePanel--closing &,
    .tui-sidePanel--opening & {
      overflow: hidden;
    }
  }

  /**
   * Transitioned container for arbitrary SidePanel content
   **/
  &__content {
    .tui-sidePanel--displayChrome & {
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      overflow: hidden;
    }

    .tui-sidePanel--closed &,
    .tui-sidePanel--closing & {
      opacity: 0;
    }

    .tui-sidePanel--closed & {
      height: 0;
      visibility: hidden;
    }

    .tui-sidePanel--open &,
    .tui-sidePanel--opening & {
      opacity: 1;
    }

    .tui-sidePanel--animated & {
      transition: opacity var(--transition-sidepanel-content-duration)
        var(--transition-sidepanel-content-function);
    }

    .tui-sidePanel--open.tui-sidePanel--overflows & {
      overflow-y: auto;
    }
  }
}
</style>
