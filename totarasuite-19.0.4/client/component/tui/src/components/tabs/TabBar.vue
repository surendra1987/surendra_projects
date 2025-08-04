c<!--
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

<script setup>
import { computed, onUpdated, ref } from 'vue';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import Select from 'tui/components/form/Select';
import OverflowDetector from 'tui/components/util/OverflowDetector';

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
  },
  small: Boolean,
  direction: {
    type: String,
    default: 'horizontal',
    validator: x => ['horizontal', 'vertical'].includes(x),
  },
  value: [String, Number],
});

const emit = defineEmits(['update:value']);

const overflowing = ref(false);
const tabEls = ref([]);

const events = {
  horizontal: {
    prev: ['Left', 'ArrowLeft'],
    next: ['Right', 'ArrowRight'],
  },
  vertical: {
    prev: ['Left', 'ArrowLeft', 'Up', 'ArrowUp'],
    next: ['Right', 'ArrowRight', 'Down', 'ArrowDown'],
  },
};

const selectListTabs = computed(() => {
  return props.tabs.map(tab => {
    return {
      id: tab.id,
      label: tab.name + (tab.labelExtraText || ''),
      disabled: tab.disabled,
    };
  });
});

// enforce correct focus
onUpdated(() => {
  // check if focus is on a tab that is not selected, if so change
  // use role[tab] and aria-selected to determine
  if (tabEls.value.includes(document.activeElement)) {
    const selected = tabEls.value.find(x => x.getAttribute('aria-selected'));
    if (selected && selected != document.activeElement) {
      selected.focus();
    }
  }
});

function handleTabClick(tab) {
  if (!tab.disabled) {
    emit('update:value', tab.id);
  }
}

function handleTabClickId(tabId) {
  const tab = props.tabs.find(x => x.id == tabId);
  if (tab) {
    handleTabClick(tab);
  }
}

function navigateTabBy(direction) {
  direction = direction < 0 ? -1 : 1;

  let index = props.tabs.findIndex(x => x.id == props.value);

  if (index == -1) {
    // ensure index is within props.tabs otherwise we will get an infinite loop
    index = direction < 0 ? 0 : props.tabs.length - 1;
  }

  // find next non-disabled link
  let newIndex = index;
  do {
    newIndex += direction;
    if (newIndex < 0) {
      newIndex = props.tabs.length - 1;
    }
    if (newIndex >= props.tabs.length) {
      newIndex = 0;
    }
  } while (props.tabs[newIndex].disabled && newIndex != index);

  emit('update:value', props.tabs[newIndex].id);
}

function handleTabKeydown(e) {
  const currentEvents = events[props.direction] || events.horizontal;
  if (currentEvents.prev.includes(e.key)) {
    e.preventDefault();
    navigateTabBy(-1);
  }
  if (currentEvents.next.includes(e.key)) {
    e.preventDefault();
    navigateTabBy(1);
  }
  if (e.key === 'Home' || e.key === 'End') {
    e.preventDefault();
    const possibleTabs = props.tabs.filter(x => !x.disabled);
    if (possibleTabs.length > 0) {
      const tab = possibleTabs[e.key === 'End' ? possibleTabs.length - 1 : 0];
      emit('update:value', tab.id);
    }
  }
}

function overflowChanged(info) {
  if (props.direction === 'vertical') {
    return;
  }
  overflowing.value = info.overflowing;
}

function isActive(tab) {
  return tab.id == props.value;
}
</script>

<template>
  <OverflowDetector v-slot="{ measuring }" @change="overflowChanged">
    <ul
      class="tui-tabBar"
      :class="['tui-tabBar--' + direction]"
      role="tablist"
      :aria-orientation="direction"
    >
      <li
        v-for="(tab, i) in tabs"
        :key="i"
        class="tui-tabBar__tab"
        :class="{
          'tui-tabBar__tab--active': isActive(tab),
          'tui-tabBar__tab--disabled': tab.disabled,
          'tui-tabBar__tab--small': small,
          'tui-tabBar__tab--hidden': tab.hidden || (overflowing && !measuring),
        }"
        role="presentation"
      >
        <a
          :id="tab.htmlId"
          ref="tabEls"
          :aria-selected="isActive(tab) ? 'true' : null"
          :aria-disabled="tab.disabled ? 'true' : null"
          :aria-controls="isActive(tab) ? tab.htmlId + '-tabpanel' : null"
          href="#"
          class="tui-tabBar__link"
          role="tab"
          :tabindex="isActive(tab) ? null : -1"
          @click.prevent="handleTabClick(tab)"
          @keydown="handleTabKeydown"
        >
          <span class="tui-tabBar__tabLabel">{{ tab.name }}</span>
          <div v-if="tab.renderLabelExtra" class="tui-tabBar__tabLabelExtra">
            <render :vnode="tab.renderLabelExtra()" />
          </div>
        </a>
      </li>

      <!-- Fallback select list when there isn't enough space -->
      <li v-if="overflowing && !measuring" class="tui-tabBar__selector">
        <Form>
          <FormRow v-slot="{ id }" :label="$str('select_a_tab', 'totara_core')">
            <Select
              :id="id"
              :value="value"
              :options="selectListTabs"
              @input="handleTabClickId"
            />
          </FormRow>
        </Form>
      </li>
    </ul>
  </OverflowDetector>
</template>

<style lang="scss">
:root {
  --tab-border-width: 1px;
  // Tab inner horizontal padding
  --tab-h-padding: var(--gap-4);
  // Tab inner vertical padding
  --tab-v-padding: var(--gap-2);
  // Size of Highlight
  --tab-highlight-height: var(--gap-1);
  // Add extra spacing for drop shadow to be displayed
  --tab-shadow-offset: var(--gap-3);
  // Tab small version inner horizontal padding
  --tab-small-h-padding: var(--gap-4);
  // Tab small version inner vertical padding
  --tab-small-v-padding: var(--gap-2);
}

.tui-tabBar {
  $mod-horizontal: #{&}--horizontal;
  $mod-vertical: #{&}--vertical;

  display: flex;
  align-items: flex-end;
  margin: 0;
  padding: 0;

  &--horizontal {
    border-bottom: var(--tab-border-width) solid;
    border-bottom-color: var(--tabs-border-color);
  }

  &--vertical {
    flex-direction: column;
    align-items: stretch;
    border-right: var(--tab-border-width) solid;
    border-right-color: var(--tabs-border-color);
  }

  &__tab {
    display: block;
    overflow: hidden;
    pointer-events: none;

    #{$mod-horizontal} & {
      margin: calc(var(--tab-shadow-offset) * -1);
      margin-bottom: calc(var(--tab-border-width) * -1);
      padding: var(--tab-shadow-offset);
      padding-bottom: var(--tab-border-width);
    }

    #{$mod-vertical} & {
      max-width: 220px;
      margin-right: calc(var(--tab-border-width) * -1);
      margin-bottom: calc(var(--tab-shadow-offset) * -1);
      padding-right: var(--tab-border-width);
      padding-bottom: var(--tab-shadow-offset);
    }

    &--hidden {
      display: none;
    }
  }

  a&__link {
    display: flex;
    padding: var(--tab-v-padding) var(--tab-h-padding);
    color: var(--tabs-text-color);
    text-decoration: none;

    border: var(--tab-border-width) solid;
    border-color: transparent;

    pointer-events: auto;

    &:hover {
      color: var(--tabs-text-color-focus);
      background: var(--tabs-bg-color-focus);
    }

    &:focus-visible {
      color: var(--tabs-text-color-focus);
      background: var(--tabs-bg-color-focus);
      outline: 2px solid var(--color-state-focus);
      outline-offset: calc((var(--tab-border-width) + 4px) * -1);
    }

    &:active,
    &:active:focus,
    &:active:hover {
      color: var(--tabs-text-color-active);
      outline: none;
    }

    #{$mod-horizontal} & {
      margin-top: var(--tab-highlight-height);
      // overlap edges to avoid double border
      margin-right: calc(var(--tab-border-width) * -1);
      border-bottom: none;
    }

    #{$mod-vertical} & {
      // overlap edges to avoid double border
      margin-bottom: calc(var(--tab-border-width) * -1);
      margin-left: var(--tab-highlight-height);
      border-right: none;
    }
  }

  &__tab--disabled a&__link {
    color: var(--tabs-text-color-disabled);
    cursor: default;
    pointer-events: none;
  }

  &__tab--active a&__link {
    position: relative;
    color: var(--tabs-text-color-selected);
    background: var(--tabs-bg-color-selected);

    #{$mod-horizontal} & {
      top: var(--tab-border-width);
      padding-top: calc(var(--tab-v-padding) - var(--tab-border-width));
      padding-bottom: calc(var(--tab-v-padding) + var(--tab-border-width));
      border-color: var(--tabs-border-color);
      box-shadow: var(--shadow-3);
    }

    #{$mod-vertical} & {
      left: var(--tab-border-width);
      padding-right: calc(var(--tab-v-padding) + var(--tab-border-width));
      padding-left: calc(var(--tab-h-padding) - var(--tab-border-width));
      border-color: var(--tabs-border-color);
      box-shadow: var(--shadow-2);
    }

    &::after {
      position: absolute;
      background: var(--tabs-selected-bar-color);
      content: '';

      #{$mod-horizontal} & {
        top: calc(var(--tab-highlight-height) * -1);
        right: 0;
        left: calc(var(--tab-border-width) * -1);
        width: calc(100% + (var(--tab-border-width) * 2));
        height: var(--tab-highlight-height);
        border-radius: var(--border-radius-small) var(--border-radius-small) 0 0;
      }

      #{$mod-vertical} & {
        top: calc(var(--tab-border-width) * -1);
        bottom: 0;
        left: calc(var(--tab-highlight-height) * -1);
        width: var(--tab-highlight-height);
        height: calc(100% + (var(--tab-border-width) * 2));
        border-radius: var(--border-radius-small) 0 0 var(--border-radius-small);
      }
    }
  }

  &__tabLabel {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  &__tabLabelExtra {
    display: flex;
    align-items: center;
    margin-left: var(--gap-2);
  }

  &__selector {
    display: block;
  }
}

.tui-tabBar {
  $mod-horizontal: #{&}--horizontal;
  $block: #{&};

  // Small tab
  &__tab--small {
    #{$mod-horizontal} & {
      #{$block}__link {
        @include font(body-sm);
        padding: var(--tab-small-v-padding) var(--tab-small-h-padding);
      }
    }
  }

  // Active small tab
  &__tab--active&__tab--small {
    #{$mod-horizontal} & {
      #{$block}__link {
        padding-top: calc(var(--tab-small-v-padding) - var(--tab-border-width));
        // prettier-ignore
        padding-bottom: calc(var(--tab-small-v-padding) + var(--tab-border-width));
        color: var(--tabs-text-color-selected);
      }
    }
  }

  // Disabled small tab
  &__tab--disabled&__tab--small {
    #{$mod-horizontal} & {
      #{$block}__link {
        color: var(--tabs-text-color-disabled);
        cursor: default;
        pointer-events: none;
      }
    }
  }
}
</style>
