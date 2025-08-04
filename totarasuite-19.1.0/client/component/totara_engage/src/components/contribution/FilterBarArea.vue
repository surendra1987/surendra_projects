<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module totara_engage
-->

<template>
  <section
    class="tui-totara_engage-filterBarArea"
    :class="{
      'tui-totara_engage-filterBarArea--hasSortBy': showSort,
    }"
  >
    <template v-if="!$apollo.loading">
      <FilterBarArea
        v-model:value="filters"
        :has-bottom-bar="true"
        :has-top-bar="true"
        :accessibility-title="$str('filterbartitle', 'totara_engage')"
        :reset-values="resetValues"
        @input="
          e => {
            $emit('update:value', e);
            $emit('input', e);
          }
        "
      >
        <template v-slot:bar-filters="{ filters, update }">
          <SearchFilter
            :value="filters.search"
            :drop-label="true"
            :label="$str('searchlibrary', 'totara_engage')"
            :aria-label="$str('searchlibrary', 'totara_engage')"
            :stacked="true"
            :placeholder="$str('searchlibrary', 'totara_engage')"
            @input="update('search', $event)"
          />
        </template>

        <template v-slot:extra-filters="{ filters, update }">
          <SelectFilter
            id="filter_access"
            :value="filters.access"
            :label="$str('filteraccess', 'totara_engage')"
            :show-label="true"
            :options="filter.access.options"
            :stacked="true"
            @input="update('access', $event)"
          />

          <SelectFilter
            v-if="showType"
            id="filter_type"
            :value="filters.type"
            :label="$str('filtertype', 'totara_engage')"
            :show-label="true"
            :options="filter.type.options"
            :stacked="true"
            @input="update('type', $event)"
          />

          <SelectFilter
            id="filter_topic"
            :value="filters.topic"
            :label="$str('filtertopic', 'totara_engage')"
            :show-label="true"
            :options="filter.topic.options"
            :stacked="true"
            @input="update('topic', $event)"
          />
        </template>
      </FilterBarArea>

      <div class="tui-totara_engage-filterBarArea__sort">
        <SelectFilter
          v-model:value="sort"
          :label="$str('sortby', 'core')"
          :show-label="true"
          :options="filter.sort.options"
        />
      </div>
    </template>
  </section>
</template>

<script>
import SelectFilter from 'tui/components/filters/SelectFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import FilterBarArea from 'tui/components/filters/FilterBarArea';

// GraphQL
import getFilterOptions from 'totara_engage/graphql/get_filter_options';

export default {
  components: {
    FilterBarArea,
    SelectFilter,
    SearchFilter,
  },

  props: {
    component: {
      type: String,
      required: true,
    },
    area: {
      type: String,
      required: true,
    },
    showType: {
      type: Boolean,
      default: true,
    },
    showSort: {
      type: Boolean,
      default: true,
    },
    searchPlaceholder: {
      type: String,
      required: false,
    },
    value: {
      type: [Array, Object],
      required: true,
    },
    sortValue: {
      type: [Number, String],
      required: true,
    },
  },

  emits: ['sort', 'input', 'update:value'],

  data() {
    return {
      filters: this.value,
      resetValues: {
        bar: {
          search: null,
        },
        extra: {
          access: null,
          type: null,
          topic: null,
        },
      },
    };
  },

  apollo: {
    filter: {
      query: getFilterOptions,
      variables() {
        return {
          component: this.component,
          area: this.area,
        };
      },
      update({ accesses, types, topics, sorts }) {
        topics = topics.map(({ id, value }) => {
          return {
            id: id,
            value: id,
            label: value,
          };
        });

        // Adding default selected topic value.
        topics.unshift({
          id: null,
          label: this.$str('all', 'core'),
        });

        return {
          access: {
            options: accesses.options,
            label: accesses.label,
          },

          type: {
            options: types.options,
            label: types.label,
          },

          topic: {
            options: topics,
            label: this.$str('filtertopic', 'totara_engage'),
          },

          sort: {
            options: sorts.options,
            sort: sorts.label,
          },
        };
      },
    },
  },

  computed: {
    sort: {
      get() {
        return this.sortValue;
      },
      set(value) {
        this.$emit('sort', {
          value: value,
        });
      },
    },
  },
};
</script>

<style lang="scss">
.tui-totara_engage-filterBarArea {
  &__sort {
    display: flex;
    justify-content: flex-end;
    width: 100%;
    margin-top: var(--gap-4);
    margin-right: 0;
  }
}
</style>
