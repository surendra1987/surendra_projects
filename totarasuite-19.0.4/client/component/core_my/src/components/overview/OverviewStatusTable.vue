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
  <Table
    :border-bottom-hidden="!viewAllMode"
    :border-separator-hidden="!viewAllMode"
    :border-top-hidden="viewAllMode"
    class="tui-overviewStatusTable"
    :data="data"
    :get-id="(row, index) => ('unique_id' in row ? row.unique_id : row.id)"
    :hover-off="true"
    :loading-overlay-active="true"
    :loading-preview="loading"
    :loading-preview-rows="numberOfLoadingRows"
    :stack-at="0"
    :thin-header="true"
  >
    <template v-if="!viewAllMode" v-slot:header-row>
      <HeaderCell size="12" valign="center">
        <template v-slot:custom-loader>
          <div class="tui-overviewStatusTable__contentLoader">
            <div
              class="tui-overviewStatusTable__header"
              :class="{
                'tui-overviewStatusTable__header--stacked': stackedPage,
              }"
            >
              <SkeletonContent :char-length="10" :has-overlay="true" />
            </div>
          </div>
        </template>

        <template v-slot:default>
          <!-- Heading -->
          <h3
            class="tui-overviewStatusTable__header"
            :class="{ 'tui-overviewStatusTable__header--stacked': stackedPage }"
          >
            {{ title }}
            <span class="tui-overviewStatusTable__header-count">
              <template v-if="total > 2">
                {{ $str('overview_status_table_count_left', 'core_my') }}
                <Button
                  class="tui-overviewStatusTable__header-countButton"
                  :styleclass="{
                    primary: true,
                    small: true,
                    transparent: true,
                  }"
                  :text="total.toString()"
                  @click="$emit('view-all')"
                />
                {{ $str('overview_status_table_count_right', 'core_my') }}
              </template>

              <template v-else>
                {{
                  $str('overview_status_table_count', 'core_my', {
                    left: $str('overview_status_table_count_left', 'core_my'),
                    right: $str('overview_status_table_count_right', 'core_my'),
                    total: total,
                  })
                }}
              </template>
            </span>
          </h3>
        </template>
      </HeaderCell>
    </template>

    <template v-slot:row="{ row }">
      <Cell size="12">
        <template v-slot:custom-loader>
          <div class="tui-overviewStatusTable__contentLoader">
            <div class="tui-overviewStatusTable__item">
              <div
                class="tui-overviewStatusTable__item-name"
                :class="{
                  'tui-overviewStatusTable__item-name--stacked': stackedPage,
                }"
              >
                <SkeletonContent :char-length="20" :has-overlay="true" />
              </div>
              <div class="tui-overviewStatusTable__item-content">
                <SkeletonContent :char-length="15" :has-overlay="true" />
              </div>
            </div>
          </div>
        </template>

        <template v-slot:default>
          <Responsive
            v-slot="{ currentBoundaryName }"
            :breakpoints="[
              { name: 'small', boundaries: [0, 409] },
              { name: null, boundaries: [408, 408] },
            ]"
          >
            <div class="tui-overviewStatusTable__item">
              <h4
                class="tui-overviewStatusTable__item-name"
                :class="{
                  'tui-overviewStatusTable__item-name--stacked': stackedPage,
                }"
              >
                <a v-if="row.url" :href="row.url" :title="row.name">
                  {{ row.name }}
                </a>
                <template v-else>
                  {{ row.name }}
                </template>
              </h4>

              <div
                class="tui-overviewStatusTable__item-content"
                :class="{
                  'tui-overviewStatusTable__item-content--separator': !currentBoundaryName,
                }"
              >
                <slot name="sub-content" :row="row" />
              </div>

              <div
                v-if="row.description && $slots['description']"
                class="tui-overviewStatusTable__item-description"
              >
                <HideShow :narrow-trigger="true">
                  <template
                    v-slot:trigger="{
                      controls,
                      expanded,
                      toggleContent,
                    }"
                  >
                    <ButtonIcon
                      :aria-controls="controls"
                      :aria-label="
                        expanded
                          ? $str('a11y_overview_description_hide', 'core_my')
                          : $str('a11y_overview_description_show', 'core_my')
                      "
                      :styleclass="{
                        transparentNoPadding: true,
                        xsmall: true,
                      }"
                      :text="$str('overview_description', 'core_my')"
                      @click="toggleContent"
                    >
                      <component
                        :is="expanded ? 'CollapseIcon' : 'ExpandIcon'"
                        aria-hidden="true"
                        size="100"
                      />
                    </ButtonIcon>
                  </template>
                  <template v-slot:content>
                    <div class="tui-overviewStatusTable__item-descriptionText">
                      <slot name="description" :row="row" />
                    </div>
                  </template>
                </HideShow>
              </div>
            </div>
          </Responsive>
        </template>
      </Cell>
    </template>
  </Table>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Cell from 'tui/components/datatable/Cell';
import CollapseIcon from 'tui/components/icons/Collapse';
import ExpandIcon from 'tui/components/icons/Expand';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import HideShow from 'tui/components/collapsible/HideShow';
import Responsive from 'tui/components/responsive/Responsive';
import SkeletonContent from 'tui/components/loading/SkeletonContent';
import Table from 'tui/components/datatable/Table';

export default {
  components: {
    Button,
    ButtonIcon,
    Cell,
    CollapseIcon,
    ExpandIcon,
    HeaderCell,
    HideShow,
    Responsive,
    SkeletonContent,
    Table,
  },

  props: {
    // Table data
    data: {
      type: Array,
    },
    // Loading content
    loading: {
      type: Boolean,
    },
    // Outer layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // Table title text
    title: {
      type: String,
    },
    // Total number of items in this state
    total: {
      type: Number,
    },
    // Displaying view all content
    viewAllMode: {
      type: Boolean,
    },
  },

  emits: ['view-all'],

  computed: {
    /**
     * The number of rows we want to display while loading
     *
     * @return {Boolean}
     */
    numberOfLoadingRows() {
      return this.viewAllMode ? 6 : 2;
    },
  },
};
</script>

<style lang="scss">
.tui-overviewStatusTable {
  &__header {
    @include font(h4);
    margin: 0;

    &-count {
      display: inline-flex;
    }

    &-countButton {
      font-size: inherit;
    }

    &--stacked {
      @include font(h5);
    }
  }

  &__item {
    & > * + * {
      margin-top: var(--gap-2);
    }

    &-name {
      @include font(h5);
      margin: 0;
      overflow: hidden;
      font-weight: var(--label-weight);
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &-content {
      @include font(body-sm);
      color: var(--color-neutral-6);

      & > * + * {
        margin-top: var(--gap-2);
      }

      &--separator {
        @include tui-separator-pipe();
        margin-top: var(--gap-1);

        & > * + * {
          margin-top: 0;

          &:before {
            color: var(--color-neutral-5);
          }
        }
      }
    }

    &-descriptionText {
      @include font(body-sm);
      margin-top: var(--gap-2);
    }
  }
}
</style>
