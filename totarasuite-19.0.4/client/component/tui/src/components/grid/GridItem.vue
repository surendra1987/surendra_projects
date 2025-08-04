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
  <component
    :is="gridItemTag"
    class="tui-grid-item"
    :class="[
      grows ? 'tui-grid-item--grow' : '',
      shrinks ? '' : 'tui-grid-item--no-shrink',
      overflows ? 'tui-grid-item--overflow' : '',
      gridItemTag === 'li' ? 'tui-grid-item--list' : '',
    ]"
    :style="getStyles"
  >
    <slot />
  </component>
</template>

<script>
import { isRtl } from 'tui/i18n';

export default {
  name: 'GridItem',

  props: {
    /**
     * The gridItemTag for the GridItem. Should make semantic sense as a child of the Grid.
     **/
    gridItemTag: {
      type: String,
      validator: x => ['div', 'li', 'aside', 'section'].includes(x),
      default: 'div',
    },
    /**
     * The number of units a GridItem should use in the Grid
     **/
    units: {
      type: [String, Number],
      default: 1,
      validator: function(value) {
        return value >= 0;
      },
    },
    /**
     * The flex order of the GridItem within the Grid
     **/
    order: {
      type: Number,
    },
    /**
     * Whether the GridItem should use flexbox growth behaviours. If it should,
     * the `flex-grow` behaviour is restricted to a value of `1` via an applied
     * CSS className.
     **/
    grows: {
      type: Boolean,
      default: function() {
        return false;
      },
    },
    /**
     * Whether the GridItem should use flexbox shrink behaviours. If it should,
     * the `flex-shrink` behaviour is restricted to a value of `1` via an applied
     * CSS className.
     **/
    shrinks: {
      type: Boolean,
      default: function() {
        return true;
      },
    },
    /**
     * Whether the contents of a GridItem should trigger the default browser
     * scroll control if they exceed the GridItems dimensions.
     **/
    overflows: {
      type: Boolean,
      default: function() {
        return false;
      },
    },
    /**
     * Unused
     * @deprecated since Totara 19
     **/
    hyphens: {
      type: Boolean,
    },
    /**
     * Props data received from the intentionally coupled Grid component. This
     * data is used to calculate the dimensions of each GridItem and apply gutters.
     **/
    sizeData: {
      type: Object,
      default: function() {
        return {
          gridDirection: undefined,
          maxGridUnits: undefined,
          useVerticalGap: undefined,
          defaultGutterSizeVertical: undefined,
          gutterSizeVertical: undefined,
          useHorizontalGap: undefined,
          defaultGutterSizeHorizontal: undefined,
          gutterSizeHorizontal: undefined,
        };
      },
    },
  },

  computed: {
    getStyles() {
      let styles = {
        flexBasis: this.getSize(),
        order: this.order,
        borderTopWidth: this.sizeData.gutterSizeVertical,
      };
      if (isRtl()) {
        styles.borderRightWidth = this.sizeData.gutterSizeHorizontal;
      } else {
        styles.borderLeftWidth = this.sizeData.gutterSizeHorizontal;
      }

      return styles;
    },
  },

  methods: {
    // Calculate the `flex-basis` value for the current GridItem, which takes
    // into consideration the number of units this GridItem currently is, how
    // many units are allowed in a single "row" in the Grid, gutters, and any
    // gutter cancellation.
    getSize: function() {
      // firstly, if this GridItem has zero units, it should use zero for size
      if (this.units === 0) {
        return 0;
      }

      // determine the directional gutter value to use in calculation, or don't
      // use one if gutters are cancelled for a given direction
      let defaultGutterSize =
        this.sizeData.gridDirection === 'horizontal'
          ? this.sizeData.defaultGutterSizeHorizontal
          : this.sizeData.defaultGutterSizeVertical;

      if (
        this.sizeData.gridDirection === 'horizontal' &&
        !this.sizeData.useHorizontalGap
      ) {
        defaultGutterSize = '0px';
      }
      if (
        this.sizeData.gridDirection === 'vertical' &&
        !this.sizeData.useVerticalGap
      ) {
        defaultGutterSize = '0px';
      }

      // one item taking up one unit is calculated by:
      //   - 100% of container minus all required gutters
      //   - divide by max number of units 'allowed' in the Grid
      let defaultItemSize = `(100% - (${defaultGutterSize} * ${this.sizeData
        .maxGridUnits - 1})) / ${this.sizeData.maxGridUnits}`;

      if (this.units === 1) {
        return `calc( ${defaultItemSize} )`;
      }

      // one item taking up multiple units is calculated by:
      //   - the size of one item taking up one unit (defaultItemSize)
      //   - multiply by the number of units the item is taking up
      //   - add back the gutter widths between combined units
      let multiUnitItemSize = `(${defaultItemSize} * ${
        this.units
      }) + (${defaultGutterSize} * ${this.units - 1})`;

      return `calc( ${multiUnitItemSize} )`;
    },
  },
};
</script>
