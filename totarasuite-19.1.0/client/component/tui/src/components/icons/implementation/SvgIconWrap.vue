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
import { h } from 'vue';

export default {
  props: {
    rootFill: {
      type: String,
      default: 'currentColor',
    },
    htmlContent: String,
    viewBox: String,
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
    flipRtl: Boolean,
  },

  render() {
    return [
      h('svg', {
        class: [
          'tui-svgIcon',
          this.size && 'tui-svgIcon--size-' + this.size,
          this.state && 'tui-svgIcon--state-' + this.state,
          this.flipRtl && 'tui-svgIcon--flipRtl',
          this.class,
          this.customClass,
        ],
        ...this.$attrs,
        xmlns: 'http://www.w3.org/2000/svg',
        'xmlns:xlink': 'http://www.w3.org/1999/xlink',
        width: '1em',
        height: '1em',
        viewBox: this.viewBox,
        role: 'presentation',
        focusable: 'false',
        fill: this.rootFill,
        innerHTML: this.htmlContent,
      }),
      this.alt && h('span', { class: 'sr-only' }, this.alt),
    ];
  },
};
</script>

<style lang="scss">
.tui-svgIcon {
  // same as the bootstrap icons default css
  // better alignment in most cases than vertical-align: middle
  vertical-align: text-bottom;

  &--size {
    &-100 {
      font-size: calc(var(--icon-size-base) * 0.875);
    }
    &-200 {
      font-size: var(--icon-size-base);
    }
    &-300 {
      font-size: calc(var(--icon-size-base) * 1.25);
    }
    &-400 {
      font-size: calc(var(--icon-size-base) * 1.5);
    }
    &-500 {
      font-size: calc(var(--icon-size-base) * 1.75);
    }
    &-600 {
      font-size: calc(var(--icon-size-base) * 2);
    }
    &-700 {
      font-size: calc(var(--icon-size-base) * 2.375);
    }
  }

  &--state {
    &-info {
      color: var(--color-prompt-info);
    }

    &-alert {
      color: var(--color-prompt-alert);
    }

    &-warning {
      color: var(--color-prompt-warning);
    }

    &-success {
      color: var(--color-prompt-success);
    }

    &-dimmed {
      color: var(--color-neutral-6);
    }
  }
}

.dir-rtl .tui-svgIcon--flipRtl {
  transform: scale(-1, 1);
}
</style>
