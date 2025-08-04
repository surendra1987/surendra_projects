<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <div class="tui-performOverviewPageLayoutTwoColumn">
    <slot name="feedback-banner" />

    <slot name="user-overview" />

    <div class="tui-performOverviewPageLayoutTwoColumn__heading">
      <slot name="content-nav" />

      <PageHeading :small="stacked" :title="title">
        <template v-slot:buttons>
          <slot name="header-buttons" />
        </template>
      </PageHeading>
    </div>

    <slot name="pre-body" />

    <Loader
      :loading="loading"
      class="tui-performOverviewPageLayoutTwoColumn__body"
      :class="{
        'tui-performOverviewPageLayoutTwoColumn__body--flush': flushBody,
      }"
    >
      <Responsive
        :breakpoints="[
          { name: 'small', boundaries: [0, 963] },
          { name: 'medium', boundaries: [962, 1162] },
          { name: 'large', boundaries: [1162, 1366] },
          { name: 'xLarge', boundaries: [1366, 1680] },
        ]"
        @responsive-resize="resize"
      >
        <Grid :stack-at="stackAt" gutter-size="var(--gap-page-columns)">
          <!-- Left content -->
          <GridItem :units="gridUnitsLeft">
            <slot name="left-content" :stacked="stacked" />
          </GridItem>
          <!-- Right content -->
          <GridItem :units="gridUnitsRight">
            <slot name="right-content" :stacked="stacked" />
          </GridItem>
        </Grid>
      </Responsive>
    </Loader>

    <slot name="modals" />
  </div>
</template>

<script>
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import Loader from 'tui/components/loading/Loader';
import PageHeading from 'tui/components/layouts/PageHeading';
import Responsive from 'tui/components/responsive/Responsive';

export default {
  components: {
    Grid,
    GridItem,
    Loader,
    PageHeading,
    Responsive,
  },

  props: {
    // Reduced top margin for body content
    flushBody: { type: Boolean },
    // Display loader over all content
    loading: { type: Boolean },
    // Custom stack at value
    stackAt: { type: Number, default: 1162 },
    // Page title
    title: { type: String, required: true },
  },

  data() {
    return {
      boundaryDefaults: {
        small: {
          gridUnitsLeft: 12,
          gridUnitsRight: 12,
        },
        medium: {
          gridUnitsLeft: 12,
          gridUnitsRight: 12,
        },
        large: {
          gridUnitsLeft: 8,
          gridUnitsRight: 4,
        },
        xLarge: {
          gridUnitsLeft: 8,
          gridUnitsRight: 4,
        },
      },
      currentBoundaryName: null,
    };
  },

  computed: {
    /**
     * Return the number of grid units for left column
     *
     * @return {Number}
     */
    gridUnitsLeft() {
      if (!this.currentBoundaryName) {
        return;
      }

      return this.boundaryDefaults[this.currentBoundaryName].gridUnitsLeft;
    },

    /**
     * Return the number of grid units for right column
     *
     * @return {Number}
     */
    gridUnitsRight() {
      if (!this.currentBoundaryName) {
        return;
      }

      return this.boundaryDefaults[this.currentBoundaryName].gridUnitsRight;
    },

    /**
     * Check if using a stacked boundary
     *
     * @return {Boolean}
     */
    stacked() {
      return (
        this.currentBoundaryName === 'small' ||
        this.currentBoundaryName === 'medium'
      );
    },
  },

  methods: {
    /**
     * Handles responsive resizing for content columns
     *
     * @param {String} boundaryName
     */
    resize(boundaryName) {
      this.currentBoundaryName = boundaryName;
    },
  },
};
</script>

<style lang="scss">
.tui-performOverviewPageLayoutTwoColumn {
  @include font(body);

  & > * + * {
    margin-top: var(--gap-8);
  }

  &__heading {
    & > * + * {
      margin-top: var(--gap-2);
    }
  }

  &__body {
    &--flush {
      margin-top: var(--gap-4);
    }
  }
}
</style>
