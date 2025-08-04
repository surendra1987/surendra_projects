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
  <Card>
    <div class="tui-myPerformOverviewSection">
      <h2
        class="tui-myPerformOverviewSection__header"
        :class="{
          'tui-myPerformOverviewSection__header--stacked': stackedPage,
        }"
      >
        <a v-if="url" :href="url">
          {{ title }}
        </a>
        <template v-else>
          {{ title }}
        </template>
      </h2>

      <Loader :loading="loading">
        <slot v-if="$slots['no-content']" name="no-content" />

        <Responsive
          v-else
          :breakpoints="[
            { name: 'vertical', boundaries: [0, 645] },
            { name: 'horizontal', boundaries: [645, 645] },
          ]"
          @responsive-resize="resize"
        >
          <Grid
            :direction="direction"
            :max-units="16"
            :use-vertical-gap="false"
          >
            <!-- Left content -->
            <GridItem :units="gridUnitsLeft">
              <div
                class="tui-myPerformOverviewSection__chartArea"
                :class="{
                  'tui-myPerformOverviewSection__chartArea--stacked': stacked,
                }"
              >
                <div class="tui-myPerformOverviewSection__chartArea-content">
                  <SkeletonContent
                    v-if="loading"
                    :has-overlay="true"
                    :lines="8"
                  />

                  <slot v-else name="chart-area" />
                </div>
              </div>
            </GridItem>
            <!-- Right content -->
            <GridItem :units="gridUnitsRight">
              <HideShow
                v-if="stacked"
                :aria-region-label="
                  $str('overview_section_expanded_details', 'core_my')
                "
                :hide-content-text="
                  $str('overview_section_hide_details', 'core_my')
                "
                :narrow-trigger="true"
                :show-content-text="
                  $str('overview_section_show_details', 'core_my')
                "
              >
                <template v-slot:trigger="{ controls, text, toggleContent }">
                  <div class="tui-myPerformOverviewSection__toggle">
                    <Button
                      :aria-controls="controls"
                      :styleclass="{
                        small: true,
                        transparent: true,
                      }"
                      :text="text"
                      @click="toggleContent"
                    />
                  </div>
                </template>
                <template v-slot:content>
                  <slot name="content" />
                </template>
              </HideShow>
              <template v-else>
                <slot name="content" />
              </template>
            </GridItem>
          </Grid>
        </Responsive>
      </Loader>

      <ModalPresenter
        :open="openViewAll"
        @request-close="$emit('view-all-closed')"
      >
        <ViewAllModal
          :last-view-all-page="lastViewAllPage"
          :loading="loadingViewAll"
          :stacked-page="stackedPage"
          :title="viewAllTitle"
          @view-all-load-more="$emit('view-all-load-more')"
        >
          <template v-slot:view-all-content>
            <Loader :loading="loadingViewAll">
              <slot name="view-all-content" />
            </Loader>
          </template>
        </ViewAllModal>
      </ModalPresenter>
    </div>
  </Card>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Card from 'tui/components/card/Card';
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import HideShow from 'tui/components/collapsible/HideShow';
import Loader from 'tui/components/loading/Loader';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import Responsive from 'tui/components/responsive/Responsive';
import SkeletonContent from 'tui/components/loading/SkeletonContent';
import ViewAllModal from 'core_my/components/overview/OverviewViewAllModal';

export default {
  components: {
    Button,
    Card,
    Grid,
    GridItem,
    HideShow,
    Loader,
    ModalPresenter,
    Responsive,
    SkeletonContent,
    ViewAllModal,
  },

  props: {
    // Initial component load
    initialLoad: {
      type: Boolean,
    },
    // Displaying the last view all page
    lastViewAllPage: {
      type: Boolean,
    },
    // Loading content
    loading: {
      type: Boolean,
    },
    // Loading view all content
    loadingViewAll: {
      type: Boolean,
    },
    // Open view all modal
    openViewAll: {
      type: Boolean,
    },
    // Outer layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // Section title
    title: {
      required: true,
      type: String,
    },
    // Section URL
    url: {
      required: true,
      type: String,
    },
    // Title for the view all modal
    viewAllTitle: {
      type: String,
    },
  },

  emits: ['view-all-closed', 'view-all-load-more'],

  data() {
    return {
      boundaryDefaults: {
        vertical: {
          gridUnitsLeft: 16,
          gridUnitsRight: 16,
        },
        horizontal: {
          gridUnitsLeft: 6,
          gridUnitsRight: 10,
        },
      },
      currentBoundaryName: null,
    };
  },

  computed: {
    /**
     * Which direction should the grid flow
     *
     * @return {String} horizontal || vertical
     */
    direction() {
      if (this.initialLoad) {
        return 'horizontal';
      }

      return this.currentBoundaryName;
    },

    /**
     * Return the number of grid units for left column
     *
     * @return {Number}
     */
    gridUnitsLeft() {
      let boundaryValue =
        this.initialLoad || !this.currentBoundaryName
          ? 'horizontal'
          : this.currentBoundaryName;

      return this.boundaryDefaults[boundaryValue].gridUnitsLeft;
    },

    /**
     * Return the number of grid units for right column
     *
     * @return {Number}
     */
    gridUnitsRight() {
      let boundaryValue =
        this.initialLoad || !this.currentBoundaryName
          ? 'horizontal'
          : this.currentBoundaryName;

      return this.boundaryDefaults[boundaryValue].gridUnitsRight;
    },

    /**
     * Is this layout stacked
     *
     * @return {Boolean}
     */
    stacked() {
      return this.direction === 'vertical';
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
.tui-myPerformOverviewSection {
  width: 100%;
  padding: var(--gap-4);

  & > * + * {
    margin-top: var(--gap-4);
  }

  &__header {
    @include font(h3);
    margin: 0;

    &--stacked {
      @include font(h4);
    }
  }

  &__chartArea {
    min-height: 184px;
    padding-right: var(--gap-4);
    border-right: var(--border-width-thin) solid var(--color-neutral-5);

    &-content {
      max-width: 320px;
      margin: 0 auto;
    }

    &--stacked {
      padding-right: 0;
      border-right: none;
      border-bottom: var(--border-width-thin) solid var(--color-neutral-5);
    }
  }

  &__toggle {
    margin: 0 auto;
    padding-top: var(--gap-2);
  }
}
</style>
