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

  @author Simon Chester <simon.chester@totara.com>
  @module totara_reportbuilder
-->

<template>
  <a
    class="tui-totara_reportbuilder-listingReportItem"
    :class="'tui-totara_reportbuilder-listingReportItem--' + view"
    :href="report.url"
  >
    <div
      v-if="tenantIndicatorVisible"
      class="tui-totara_reportbuilder-listingReportItem__indicator"
    >
      <Lozenge
        type="info"
        :text="$str('tenant_report', 'totara_reportbuilder')"
      />
    </div>
    <div class="tui-totara_reportbuilder-listingReportItem__imageWrap">
      <img
        class="tui-totara_reportbuilder-listingReportItem__image"
        :src="report.graph_image_url"
      />
    </div>
    <div class="tui-totara_reportbuilder-listingReportItem__info">
      <div class="tui-totara_reportbuilder-listingReportItem__title">
        {{ report.name }}
      </div>
      <div
        v-if="showDescription"
        class="tui-totara_reportbuilder-listingReportItem__description"
      >
        {{ report.description }}
      </div>
    </div>
  </a>
</template>

<script>
import Lozenge from 'tui/components/lozenge/Lozenge';

export default {
  components: {
    Lozenge,
  },

  props: {
    view: { type: String, default: 'grid' },
    report: Object,
    showDescription: Boolean,
    showTenantIndicator: Boolean,
  },

  computed: {
    tenantIndicatorVisible() {
      return this.showTenantIndicator && this.report.tenant_id != null;
    },
  },
};
</script>

<style lang="scss">
:root {
  --my-reports-image-bg-color: #f0f0f0;
}

.tui-totara_reportbuilder-listingReportItem {
  $block: #{&};
  position: relative;
  display: flex;
  color: var(--color-text);
  border: var(--border-width-thin) solid var(--color-border);

  // Hover animation
  transition-timing-function: ease-in-out;
  transition-duration: 200ms;
  transition-property: box-shadow;

  &:hover,
  &:active,
  &:focus {
    z-index: 1;
    color: var(--color-text);
    text-decoration: none;
  }

  &:hover {
    box-shadow: var(--shadow-2);
  }

  &:focus-visible {
    @include tui-focus;
  }

  &__indicator {
    position: absolute;
    top: 0;
    left: 0;
    padding: var(--gap-4);
  }

  &__imageWrap {
    background-color: var(--my-reports-image-bg-color);
  }

  &__title {
    @include font(h5);
    flex-shrink: 0;
    overflow: hidden;
    font-weight: 600;
  }

  &__description {
    @include font(body-sm);
    overflow: hidden;
  }

  &--grid {
    flex-direction: column;

    #{$block}__image {
      display: block;
      width: 100%;
      height: rem-px(120);
      margin: 0 auto;
    }

    #{$block}__info {
      display: flex;
      flex-direction: column;
      gap: var(--gap-2);
      min-height: rem-px(62);
      max-height: rem-px(130);
      padding: var(--gap-2) var(--gap-2) var(--gap-3) var(--gap-2);
    }
  }

  &--list {
    height: rem-px(70);
    border-width: 0 0 var(--border-width-thin) 0;

    #{$block}__imageWrap {
      flex: 0 0 auto;
      width: auto;
      height: 100%;
      max-height: 100%;
    }

    #{$block}__image {
      width: auto;
      height: 100%;
      max-height: 100%;
      margin: 0;
      padding: 0;
    }

    #{$block}__info {
      display: grid;
      flex-grow: 1;
      grid-template-columns: 1fr 1fr;
      gap: var(--gap-2);
      padding: var(--gap-3);
    }
  }
}
</style>
