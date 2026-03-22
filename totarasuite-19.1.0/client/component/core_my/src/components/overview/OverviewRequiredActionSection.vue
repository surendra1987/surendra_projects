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
  <div class="tui-myPerformOverviewRequiredActionSection">
    <h3 class="tui-myPerformOverviewRequiredActionSection__header">
      <span class="tui-myPerformOverviewRequiredActionSection__header-icon">
        <slot name="icon" />
      </span>

      <span>
        {{ title }}
      </span>
    </h3>

    <Responsive
      :breakpoints="[
        { name: 'vertical', boundaries: [0, 330] },
        { name: 'horizontal', boundaries: [328, 900] },
      ]"
      @responsive-resize="resize"
    >
      <div class="tui-myPerformOverviewRequiredActionSection__content">
        <slot name="content" :stacked="stacked" />
      </div>
    </Responsive>
  </div>
</template>

<script>
import Responsive from 'tui/components/responsive/Responsive';

export default {
  components: {
    Responsive,
  },

  props: {
    // Title of section
    title: {
      required: true,
      type: String,
    },
  },

  data() {
    return {
      currentBoundaryName: null,
    };
  },

  computed: {
    stacked() {
      return this.currentBoundaryName === 'vertical';
    },
  },

  methods: {
    /**
     * Handles responsive resizing
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
.tui-myPerformOverviewRequiredActionSection {
  hyphens: none;

  & > * + * {
    margin-top: var(--gap-4);
  }

  &__header {
    @include font(h5);
    display: flex;
    margin: 0;
    padding-bottom: var(--gap-2);
    border-bottom: var(--border-width-thin) solid var(--color-neutral-7);

    & > * + * {
      margin-left: var(--gap-1);
    }

    &-icon {
      display: flex;
      > * {
        flex-shrink: 0;
        margin: 0 2px 0 1px;
      }
    }
  }

  &__content {
    & > * + * {
      margin-top: var(--gap-4);
      padding-top: var(--gap-4);
      border-top: var(--border-width-thin) solid var(--color-neutral-5);
    }
  }
}
</style>
