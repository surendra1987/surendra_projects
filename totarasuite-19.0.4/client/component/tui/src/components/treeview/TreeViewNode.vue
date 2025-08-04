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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<script setup>
import { computed } from 'vue';
import { getString } from 'tui/i18n';
import Button from 'tui/components/buttons/Button';
import CollapseIcon from 'tui/components/icons/Collapse';
import ExpandIcon from 'tui/components/icons/Expand';

const props = defineProps({
  expandedItems: Array,
  focusableItem: [Number, String],
  item: { type: Object, required: true },
  multiSelect: Boolean,
  selectedItems: Array,
});

const emit = defineEmits(['select', 'toggle']);

const hasChildren = computed(() => {
  return props.item.children && props.item.children.length;
});

const tabIndex = computed(() => {
  return props.focusableItem == props.item.id ? '0' : '-1';
});

const selected = computed(() => {
  return props.selectedItems.includes(props.item.id);
});

/**
 * Emit a select event if the item is selectable, otherwise toggle
 *
 * @param item
 */
function handleSelect(item, event) {
  // If clicking on the expand/collapse button then ignore
  if (event?.target?.closest('button')) {
    return;
  }

  if (item.selectable) {
    emit('select', item);
  } else if (hasChildren.value) {
    handleToggle(item);
  }
}

const expanded = computed(() => {
  return props.expandedItems.includes(props.item.id);
});

/**
 * Emit a toggle event
 *
 * @param item
 */
function handleToggle(item) {
  emit('toggle', item);
}

/**
 * Aria label for the toggle button
 *
 * @return {String}
 */
const toggleAriaLabel = computed(() => {
  return expanded.value
    ? getString('a11y_treeview_collapse_item', 'totara_core', props.item.label)
    : getString('a11y_treeview_expand_item', 'totara_core', props.item.label);
});

/**
 * Aria label for the item
 *
 * @return {String}
 */
const itemAriaLabel = computed(() => {
  if (props.item.selectable) {
    return selected.value
      ? getString(
          'a11y_treeview_deselect_item',
          'totara_core',
          props.item.label
        )
      : getString('a11y_treeview_select_item', 'totara_core', props.item.label);
  } else {
    return toggleAriaLabel.value;
  }
});
</script>

<template>
  <li
    class="tui-treeViewNode"
    role="treeitem"
    :aria-expanded="hasChildren ? expanded : undefined"
    :aria-selected="selected"
    :data-id="JSON.stringify(item.id)"
    :tabindex="tabIndex"
  >
    <div
      class="tui-treeViewNode__item"
      :class="{
        'tui-treeViewNode__item--selected': selected,
        'tui-treeViewNode__item--selectable': item.selectable,
        'tui-treeViewNode__item--hasChildren': hasChildren,
      }"
      :aria-label="itemAriaLabel"
      @click="handleSelect(item, $event)"
    >
      <Button
        v-if="hasChildren"
        class="tui-treeViewNode__item-toggle"
        :aria-controls="expanded ? $id('children') : null"
        :aria-label="toggleAriaLabel"
        shape="pill"
        size="xs"
        tabindex="-1"
        variant="stealth"
        @click="handleToggle(item)"
      >
        <template v-slot:icon>
          <CollapseIcon
            v-if="expanded"
            size="100"
            class="tui-treeViewNode__item-icon"
          />
          <ExpandIcon v-else size="100" class="tui-treeViewNode__item-icon" />
        </template>
      </Button>

      {{ item.label }}
    </div>
    <ul
      v-if="hasChildren && expanded"
      :id="$id('children')"
      class="tui-treeViewNode__item-children"
      role="group"
    >
      <TreeViewNode
        v-for="child in item.children"
        :key="child.id"
        :expanded-items="expandedItems"
        :focusable-item="focusableItem"
        :item="child"
        :multi-select="multiSelect"
        :selected-items="selectedItems"
        @select="handleSelect"
        @toggle="handleToggle"
      />
    </ul>
  </li>
</template>

<style lang="scss">
.tui-treeViewNode {
  display: flex;
  flex-direction: column;
  gap: var(--border-width-normal);
  list-style: none;
  border-radius: var(--border-radius-small);

  &:focus-visible {
    @include tui-focus();
  }

  &__item {
    display: flex;
    gap: gap(1);
    align-items: center;
    min-height: rem-px(32);
    padding: gap(1) gap(2) gap(1) gap(9);
    overflow-wrap: anywhere;
    border-radius: var(--border-radius-small);
    user-select: none;

    &:hover {
      background-color: var(--color-neutral-3);
    }

    &--selectable {
      cursor: pointer;
    }

    &--hasChildren {
      padding: gap(1) gap(2) gap(1) gap(2);
      cursor: pointer;
    }

    &--selected {
      color: var(--color-neutral-1);
      background-color: var(--color-state);

      &:hover {
        background-color: var(--color-state);
      }
    }

    &-children {
      display: flex;
      flex-direction: column;
      gap: var(--border-width-normal);
      margin-left: gap(4);
    }

    &-icon {
      pointer-events: none;
    }

    &-toggle {
      align-self: start;
      min-width: rem-px(24);
      min-height: rem-px(24);
    }
  }

  &__item--selected &__item-toggle {
    color: var(--color-neutral-1);
  }
}
</style>
