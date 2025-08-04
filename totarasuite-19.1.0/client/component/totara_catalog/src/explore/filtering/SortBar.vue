<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module totara_catalog
-->

<script setup>
import { config } from 'tui/config';
import { copyText } from 'tui/dom/clipboard';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import Button from 'tui/components/buttons/Button';
import Popover from 'tui/components/popover/Popover';
import InputText from 'tui/components/form/InputText';
import LinkIcon from 'tui/components/icons/Link';
import FilterBarButton from 'totara_catalog/explore/ui/FilterBarButton';
import { computed } from 'vue';

const props = defineProps({
  totalCount: Number,
  options: Array,
  sortBy: String,
  canLoadMore: Boolean,
  suggestion: String,
});

defineEmits(['update:sortBy', 'acceptSuggestion']);

const numberFormat = new Intl.NumberFormat(config.locale.tag);

const selectedOption = computed(() =>
  props.options?.find(x => x.id == props.sortBy)
);

function getUrl() {
  return window.location.href;
}

function copyUrl() {
  copyText(window.location.href);
}
</script>

<template>
  <div
    class="tui-totara_catalog-sortBar"
    :sort-by="sortBy"
    :options="options"
    @update:sort-by="$emit('update:sortBy', $event)"
  >
    <div class="tui-totara_catalog-sortBar__start">
      <div v-if="totalCount != null">
        {{
          totalCount === 1
            ? $str(
                'count_to_item',
                'totara_catalog',
                numberFormat.format(totalCount)
              )
            : $str(
                canLoadMore ? 'count_up_to' : 'count_to_items',
                'totara_catalog',
                numberFormat.format(totalCount)
              )
        }}
      </div>
      <Popover
        :title="$str('link_sharing', 'totara_catalog')"
        :triggers="['click']"
      >
        <template v-slot:trigger>
          <Button
            shape="circle"
            variant="stealth"
            size="sm"
            :aria-label="$str('link_sharing', 'totara_catalog')"
          >
            <template v-slot:icon>
              <LinkIcon :size="200" />
            </template>
          </Button>
        </template>

        <div class="tui-totara_catalog-sortBar__content">
          <div>{{ $str('share_link_description', 'totara_catalog') }}</div>

          <InputText
            :value="getUrl()"
            readonly
            :aria-label="$str('url', 'core')"
          />
        </div>

        <template v-slot:buttons>
          <Button
            class="tui-totara_catalog-sortBar__copy"
            variant="primary"
            :text="$str('copy_link_to_clipboard', 'totara_catalog')"
            @click="copyUrl"
          />
        </template>
      </Popover>
      <span
        v-if="suggestion"
        class="tui-totara_catalog-sortBar__start-suggestion"
      >
        {{ $str('search_suggestion_pre', 'totara_catalog')
        }}<a
          tabindex="0"
          :aria-label="
            $str('replace_search_text', 'totara_catalog', suggestion)
          "
          aria-controls="searchFilter"
          @click="$emit('acceptSuggestion', suggestion)"
          @keydown.enter="$emit('acceptSuggestion', suggestion)"
          >{{ suggestion }}</a
        >{{ $str('search_suggestion_post', 'totara_catalog') }}
      </span>
    </div>

    <div class="tui-totara_catalog-sortBar__end">
      <Dropdown
        v-if="options && options.length"
        :separator="false"
        position="bottom-right"
      >
        <template v-slot:trigger="{ toggle, isOpen }">
          <FilterBarButton
            :aria-expanded="isOpen"
            :aria-label="
              $str(
                selectedOption ? 'sort_by_x' : 'sort_by',
                'totara_catalog',
                selectedOption?.label
              )
            "
            caret
            @click="toggle"
          >
            {{ selectedOption?.label }}
          </FilterBarButton>
        </template>
        <DropdownButton
          v-for="(option, i) in options"
          :key="i"
          @click="$emit('update:sortBy', option.id)"
        >
          {{ option.label }}
        </DropdownButton>
      </Dropdown>
    </div>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-sortBar {
  display: flex;
  flex-flow: row wrap;
  gap: var(--gap-3) var(--gap-6);
  align-items: center;
  justify-content: space-between;

  &__start {
    display: flex;
    gap: var(--gap-2);
    align-items: center;

    &-suggestion > a {
      cursor: pointer;
    }
  }

  &__copy {
    flex: 1;
  }

  &__content {
    display: flex;
    flex-flow: column;
    gap: var(--gap-2);
  }
}
</style>
