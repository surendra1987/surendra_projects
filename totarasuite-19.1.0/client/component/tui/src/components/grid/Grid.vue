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

<!-- No <template /> element, we will use this component's `render()` method to
     assemble GridItem contents, as we need to use some logic -->

<script>
import { h } from 'vue';
import { addProps, eachChild } from 'tui/vue/vnode';
import theme from 'tui/theme';

const isGridItem = vnode =>
  typeof vnode.type === 'object' && vnode.type.name === 'GridItem';

export default {
  props: {
    /**
     * The tag for the Grid.
     **/
    gridTag: {
      type: String,
      validator: x => ['div', 'ul', 'ol'].includes(x),
      default: 'div',
    },
    /**
     * `horizontal` and `vertical` are the two expected values, the Grid works
     * in both situations. Vertical grids will need more attention paid to their
     * individual implementations because of limitations when an explicit CSS
     * `height` property is not applied.
     **/
    direction: {
      type: String,
      default: function() {
        return 'horizontal';
      },
    },
    /**
     * Applies, or excludes, a visual gap between grid items along the main flex
     * axis.
     **/
    useHorizontalGap: {
      type: Boolean,
      default: function() {
        return true;
      },
    },
    /**
     * Applies, or excludes, a visual gap between grid items along the cross
     * flex axis.
     **/
    useVerticalGap: {
      type: Boolean,
      default: function() {
        return true;
      },
    },
    /**
     * This property is used to calculate GridItem widths. It can be exceeded
     * both by the number of GridItem children and also by the total number of
     * units that each GridItem takes up. While not ideal, if the value is
     * exceeded, excess GridItems will wrap.
     **/
    maxUnits: {
      type: [String, Number], // theme.getVar() always returns a String
      default: function() {
        return theme.getVar('grid-maxunits');
      },
    },
    /**
     * When a value is supplied, a className is appended to the Grid which
     * provides a basic switch from a flex layout to a stack of Block elements.
     * Note that this does not use a viewport breakpoint, it uses the width of
     * the Grid itself - this is 'container query'-like behaviour.
     **/
    stackAt: {
      type: Number,
    },
    /**
     * Value for the vertical gutters between wrapped GridItems, or GridItems
     * when the Grid direction is vertical.
     **/
    gutterSizeVertical: {
      type: String,
    },
    /**
     * Value for the horizontal gutters between GridItems.
     **/
    gutterSizeHorizontal: {
      type: String,
    },
    /**
     * Value for both the horizontal and vertical gutters, if either is not specified.
     */
    gutterSize: {
      type: String,
    },
  },

  emits: [],

  data: function() {
    return {
      isStacked: false,
      resizeObserverRef: null,
    };
  },

  computed: {
    gutterSizeVerticalComputed() {
      return (
        this.gutterSizeVertical ||
        this.gutterSize ||
        'var(--grid-gutter-vertical)'
      );
    },

    gutterSizeHorizontalComputed() {
      return (
        this.gutterSizeHorizontal ||
        this.gutterSize ||
        'var(--grid-gutter-horizontal)'
      );
    },
  },

  mounted() {
    // when mounted, create a resize observer to detect changes in dimensions,
    // this will facilitate responsiveness to a finer level than relying solely
    // on viewport width. this technique is referred to as a 'container query'
    // and is useful when you want to switch between layouts inside a narrow
    // column, for example
    if (Number.isInteger(this.stackAt) && this.$el instanceof Element) {
      this.resizeObserverRef = new ResizeObserver(this.handleResize);
      this.resizeObserverRef.observe(this.$el);
    }
  },

  unmounted() {
    // clean up ahead of garbage collection as there may be multiple Grids on a
    // page observing
    if (this.resizeObserverRef && this.$el instanceof Element) {
      this.resizeObserverRef.unobserve(this.$el);
    }
  },

  methods: {
    /**
     * Callback for ResizeObserver, toggles stack/grid modes
     *
     * @param {Array} entries
     **/
    handleResize: function(entries) {
      this.isStacked = entries[0].contentRect.width <= this.stackAt;
    },

    /**
     * Returns default Grid classNames, combined with supplied additional
     * classNames
     *
     * @param {Array} additionalClasses
     * @return {Array}
     */
    gridClasses: function(additionalClasses) {
      let classes = ['tui-grid', 'tui-grid--' + this.direction];

      if (
        this.direction === 'horizontal' &&
        this.useHorizontalGap &&
        !this.isStacked
      ) {
        classes.push('tui-grid--horizontal-gap');
      }

      if (
        this.direction === 'vertical' &&
        this.useVerticalGap &&
        !this.isStacked
      ) {
        classes.push('tui-grid--vertical-gap');
      }

      if (this.isStacked && this.useVerticalGap) {
        classes.push('tui-grid--stacked-gap');
      }

      if (this.gridTag === 'ul' || this.gridTag === 'ol') {
        classes.push('tui-grid--list');
      }

      if (additionalClasses && additionalClasses.length) {
        classes = classes.concat(additionalClasses);
      }

      return classes;
    },
  },

  render() {
    let children = this.$slots.default();

    // first pass: count supplied units and figure out the first item order-wise
    let totalSuppliedUnits = 0;
    let firstGridItem = null;
    eachChild(children, vnode => {
      if (!isGridItem(vnode)) {
        return;
      }
      totalSuppliedUnits += vnode.props.units ?? 1;
      if (firstGridItem === null || vnode.props.order === 1) {
        firstGridItem = vnode;
      }
    });

    // second pass: add props to our child griditems
    let currentUnwrappedUnits = 0;
    let itemHasWrapped = false;

    children = addProps(children, vnode => {
      if (!isGridItem(vnode)) {
        return;
      }

      const addedProps = {
        class: [],
      };

      // default gutters
      let gutterSizeHorizontal = this.gutterSizeHorizontalComputed;
      let gutterSizeVertical = this.gutterSizeVerticalComputed;

      // how many units does this GridItem add
      const itemUnits = vnode.props.units ?? 1;

      // a zero-unit GridItem should not have any visible gutters
      if (itemUnits === 0) {
        addedProps.class.push('tui-grid-item--no-units');
        gutterSizeHorizontal = '0px';
        gutterSizeVertical = '0px';
      }

      // the very first visual GridItem should have no left gutter
      if (vnode === firstGridItem) {
        addedProps.class.push('tui-grid-item--first');
        gutterSizeHorizontal = '0px';
        gutterSizeVertical = '0px';
      }

      // handle wrapping of too-large grid items
      if (this.direction !== 'vertical' && totalSuppliedUnits > this.maxUnits) {
        // we're going to wrap, at least once, on this item
        if (currentUnwrappedUnits + itemUnits > this.maxUnits) {
          addedProps.class.push('tui-grid-item--first');
          currentUnwrappedUnits = itemUnits; // restart the count
          itemHasWrapped = true;

          // the first wrapped GridItem on each row shouldn't have a left gutter
          gutterSizeHorizontal = '0px';
        } else {
          currentUnwrappedUnits += itemUnits; // continue the count
        }

        // add to all GridItems that wrap, when we first wrap
        if (itemHasWrapped) {
          addedProps.class.push('tui-grid-item--wrapped');
        }
      }

      // for a horizontal Grid, if we haven't wrapped there should be no top
      // gutter
      if (this.direction !== 'vertical' && !itemHasWrapped) {
        gutterSizeVertical = '0px';
      }

      // determine the gutter size for grid items, which could be zero if props
      // have been supplied to disable visual gaps
      if (this.direction === 'horizontal' && !this.useHorizontalGap) {
        gutterSizeHorizontal = '0px';
      }
      if (this.direction === 'vertical' && !this.useVerticalGap) {
        gutterSizeVertical = '0px';
      }

      // pass props to child GridItems so they can calculate their size
      addedProps.sizeData = {
        defaultGutterSizeHorizontal: this.gutterSizeHorizontalComputed,
        defaultGutterSizeVertical: this.gutterSizeVerticalComputed,
        gutterSizeHorizontal: gutterSizeHorizontal,
        gutterSizeVertical: gutterSizeVertical,
        maxGridUnits: this.maxUnits,
        gridDirection: this.direction,
        useHorizontalGap: this.useHorizontalGap,
        useVerticalGap: this.useVerticalGap,
      };

      return addedProps;
    });

    // apply default classNames to Grid node, plus any conditionally required
    // ones
    let additionalClasses = [];
    if (this.direction !== 'vertical' && totalSuppliedUnits > this.maxUnits) {
      if (this.useVerticalGap) {
        additionalClasses.push('tui-grid--wrapped', 'tui-grid--wrapped-gap');
      } else {
        additionalClasses.push('tui-grid--wrapped');
      }
    }

    // if switching from flex layout to a stack is required, conditionally apply
    // a stacking className if the Grid is supplied a numeric value to switch at,
    // instead of based on viewport
    if (this.isStacked) {
      additionalClasses.push('tui-grid--stacked');
    }

    return h(
      this.gridTag,
      {
        class: this.gridClasses(additionalClasses),
      },
      [children]
    );
  },
};
</script>

<style lang="scss">
@mixin grid-item-generate-gutters($_borderType: left) {
  > .tui-grid-item {
    // because we use transparent borders for gutters but don't want that
    // counting as visible item width
    box-sizing: content-box;
    background-clip: padding-box;
    border-#{$_borderType}-color: transparent;
    border-#{$_borderType}-style: solid;
    // `border-#{$_borderType}-width` is set using inline styles via GridItem
  }
}

// Grid styles
.tui-grid {
  display: flex;
  flex-grow: 1; // in case nested inside a parent grid cell
  max-width: 100%;

  // main Grid modifiers applied based on supplied prop values
  &--wrapped {
    flex-wrap: wrap;
  }

  // content-containing elements
  &-item {
    flex-grow: 0; // by default we want item size to respect unit-based calculations
    flex-shrink: 1; // by default we want to auto-adjust for gutters
    min-width: 0; // allows flex items to shrink below their minimum content size
    margin: 0;
    padding: 0;

    // Grid item modifiers based on supplied prop values
    &--grow {
      flex-grow: 1;
    }
    &--no-shrink {
      flex-shrink: 0;
    }
    &--overflow {
      overflow: auto;
    }
  }

  // horizontal grid
  &--horizontal {
    flex-direction: row;

    &-gap {
      @include grid-item-generate-gutters(left);
    }
  }

  // vertical grid
  &--vertical {
    flex-direction: column;

    &-gap {
      @include grid-item-generate-gutters(top);
    }
  }

  &--wrapped-gap .tui-grid-item--wrapped {
    border-top-color: transparent;
    border-top-style: solid;
  }

  // all zero unit GridItems should not show any gutters or content
  &--vertical,
  &--horizontal {
    .tui-grid-item--no-units {
      overflow: hidden;
    }
  }

  // switch to stacked display at an container-based pixel width breakpoint
  // value (class is conditionally applied during Grid render())
  &--stacked {
    display: block;

    > .tui-grid-item {
      flex-basis: auto;
    }

    &-gap {
      @include grid-item-generate-gutters(top);
    }
  }

  &--list {
    margin: 0;
    padding: 0;
    list-style-type: none;
  }
}
</style>
