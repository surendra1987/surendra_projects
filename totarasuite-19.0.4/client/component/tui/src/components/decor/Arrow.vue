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

<template>
  <div
    class="tui-arrow"
    :class="[
      'tui-arrow--' + computedSide,
      'tui-arrow--' + size,
      'tui-arrow__variant--' + variant,
    ]"
    :style="style"
  />
</template>

<script>
import { isRtl } from 'tui/i18n';

const relativeSide = {
  top: 'bottom',
  bottom: 'top',
  left: 'right',
  right: 'left',
};

export default {
  props: {
    // direction the arrow should point towards (top/bottom/left/right)
    side: String,
    // side relative to the reference - inverse of side. provide one or the other.
    relativeSide: String,
    // distance along side to place the arrow
    distance: [Number, String],
    size: {
      type: String,
      default: 'normal',
    },
    // color variation (default/inverse)
    variant: {
      type: String,
      default: 'default',
    },
  },

  computed: {
    style() {
      const side = this.computedSide;
      if (side == null || this.distance == null) {
        return {};
      }
      let posSide = 'top';
      if (side == 'top' || side == 'bottom') {
        posSide = isRtl() ? 'right' : 'left';
      }

      return {
        [posSide]: Math.round(this.distance) + 'px',
      };
    },

    computedSide() {
      return this.side || relativeSide[this.relativeSide];
    },
  },
};
</script>

<style lang="scss">
@mixin generate-arrow-base($sel, $size) {
  #{$sel}--top,
  #{$sel}--bottom {
    /*!rtl:ignore*/
    left: 50%;
    width: $size * 2;
    height: $size * 2;
    /*!rtl:ignore*/
    margin-left: -$size;
  }

  #{$sel}--top {
    top: -($size * 2);
  }

  #{$sel}--bottom {
    bottom: -($size * 2);
  }

  #{$sel}--left,
  #{$sel}--right {
    top: 50%;
    width: $size * 2;
    height: $size * 2;
    margin-top: -$size;
  }

  #{$sel}--left {
    left: -($size * 2);
  }

  #{$sel}--right {
    right: -($size * 2);
  }
}

@mixin generate-arrow-part($sel, $sel_2, $size, $offset) {
  #{$sel}#{$sel_2} {
    position: absolute;
    display: block;
    border: $size solid transparent;
    content: '';
  }

  #{$sel}--top#{$sel_2} {
    top: $offset * 2;
    left: $offset;
  }

  #{$sel}--bottom#{$sel_2} {
    bottom: $offset * 2;
    left: $offset;
  }

  #{$sel}--left#{$sel_2} {
    top: $offset;
    left: $offset * 2;
  }

  #{$sel}--right#{$sel_2} {
    top: $offset;
    right: $offset * 2;
  }
}

@mixin arrow-variant($sel, $variant_typ, $color) {
  .tui-arrow--normal,
  .tui-arrow--large {
    &.tui-arrow__variant--#{$variant_typ} {
      &.tui-arrow--top#{$sel} {
        border-bottom-color: $color;
      }
      &.tui-arrow--bottom#{$sel} {
        border-top-color: $color;
      }
      &.tui-arrow--left#{$sel} {
        border-right-color: $color;
      }
      &.tui-arrow--right#{$sel} {
        border-left-color: $color;
      }
    }
  }
}

.tui-arrow {
  position: absolute;
  pointer-events: none;
}

@include generate-arrow-base('.tui-arrow--normal.tui-arrow', 10px);
@include generate-arrow-part(
  '.tui-arrow--normal.tui-arrow',
  '::before',
  10px,
  0
);
@include generate-arrow-part(
  '.tui-arrow--normal.tui-arrow',
  '::after',
  8px,
  2px
);

@include generate-arrow-base('.tui-arrow--large.tui-arrow', 14px);
@include generate-arrow-part(
  '.tui-arrow--large.tui-arrow',
  '::before',
  14px,
  0
);
@include generate-arrow-part(
  '.tui-arrow--large.tui-arrow',
  '::after',
  12px,
  2px
);

@include arrow-variant('::before', 'default', var(--arrow-border-color));
@include arrow-variant('::after', 'default', var(--arrow-bg-color));

@include arrow-variant('::before', 'inverse', var(--arrow-bg-inverse-color));
@include arrow-variant('::after', 'inverse', var(--arrow-bg-inverse-color));
</style>
