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
import { watch, ref, onMounted, onBeforeUnmount } from 'vue';
import { isRtl } from 'tui/i18n';
import TreeViewNode from 'tui/components/treeview/TreeViewNode';

const props = defineProps({
  expandedItems: { type: Array, required: true },
  items: { type: Array, required: true },
  multiSelect: Boolean,
  selectedItems: { type: Array, required: true },
});

const emit = defineEmits(['update:selectedItems', 'update:expandedItems']);

// The root tree dom element
const treeDomElement = ref(null);

// The ids of all the items without children (leafs)
const leafItems = ref([]);

// The ids of all the selectable items
const selectableItems = ref([]);

// The id of the item that is currently focusable
const focusableItem = ref(null);

// When the items change we need to re-initialise
watch(() => props.items, initialise, { deep: true });

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
  initialise();
});

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

/**
 * Initialise the treeview by finding:
 *  the leaf nodes and the selectable items and resetting the focus if needed
 *
 * Note: This is all done initially to avoid unnecessary iteration of the tree.
 * The only time we need to re-initialise is when the provided items change
 */
function initialise() {
  if (!props.items.length) {
    return;
  }

  const getNodes = items =>
    items.flatMap(item => [
      item,
      ...(item.children ? getNodes(item.children) : []),
    ]);
  const nodes = getNodes(props.items);

  const getVisibleNodes = itemList =>
    itemList.flatMap(item => [
      item.id,
      ...(props.expandedItems.includes(item.id) && item.children.length > 0
        ? getVisibleNodes(item.children)
        : []),
    ]);
  const visibleNodes = getVisibleNodes(props.items);

  leafItems.value = nodes.filter(x => x.children.length === 0).map(x => x.id);
  selectableItems.value = nodes.filter(x => x.selectable).map(x => x.id);

  if (!visibleNodes.includes(focusableItem.value)) {
    focusableItem.value = props.items[0].id;
  }
}

/**
 * Gets all the treeitem dom elements within the tree
 *
 * @return {Array}
 */
function getDomItems() {
  return [...treeDomElement.value.querySelectorAll('[role=treeitem]')];
}

/**
 * Gets a treeitem dom element by id
 *
 * @param id
 * @return {Object}
 */
function getDomItemById(id, domItems) {
  return domItems.find(item => JSON.parse(item.getAttribute('data-id')) === id);
}

/**
 * Handles the expanding/collapsing of a tree node
 *
 * @param id
 */
function handleToggle({ id }) {
  let expandedItems = props.expandedItems;

  if (expandedItems.includes(id)) {
    expandedItems = expandedItems.filter(itemId => itemId !== id);
  } else {
    expandedItems = [...props.expandedItems, id];
  }

  emit('update:expandedItems', expandedItems);

  const domItems = getDomItems();
  focusDomItem(getDomItemById(id, domItems), domItems);
}

/**
 * Handles the selection/deselection of a tree node
 *
 * @param id
 */
function handleSelect({ id }) {
  // If not selectable then do nothing
  if (!selectableItems.value.includes(id)) {
    return;
  }

  let selectedItems = props.selectedItems;

  if (selectedItems.includes(id)) {
    selectedItems = selectedItems.filter(itemId => itemId !== id);
  } else {
    if (props.multiSelect) {
      selectedItems = [...props.selectedItems, id];
    } else {
      selectedItems = [id];
    }
  }

  emit('update:selectedItems', selectedItems);

  const domItems = getDomItems();
  focusDomItem(getDomItemById(id, domItems), domItems);
}

/**
 * Handles the keyboard navigation of the tree
 *
 * @param event
 */
function handleKeyDown(event) {
  // Exit if the browser focus isn't within the tree
  if (!treeDomElement.value.contains(document.activeElement)) {
    return;
  }

  // Fetch all the dom elements
  const domItems = getDomItems();

  // If expanded then toggle, otherwise focus the parent
  const handleLeft = () => {
    if (props.expandedItems.includes(focusableItem.value)) {
      handleToggle({ id: focusableItem.value });
    } else {
      focusParentItem(domItems);
    }
  };

  // If expanded then focus the next item, otherwise toggle
  const handleRight = () => {
    // If a leaf node then do nothing
    if (leafItems.value.includes(focusableItem.value)) {
      return;
    }
    if (props.expandedItems.includes(focusableItem.value)) {
      focusNextItem(domItems);
    } else {
      handleToggle({ id: focusableItem.value });
    }
  };

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      focusNextItem(domItems);
      break;
    case 'ArrowUp':
      event.preventDefault();
      focusPreviousItem(domItems);
      break;
    case 'ArrowLeft':
      event.preventDefault();

      if (isRtl()) {
        handleRight();
      } else {
        handleLeft();
      }
      break;
    case 'ArrowRight':
      event.preventDefault();

      if (!isRtl()) {
        handleRight();
      } else {
        handleLeft();
      }
      break;
    case ' ':
    case 'Space':
    case 'Enter':
      event.preventDefault();
      handleSelect({ id: focusableItem.value });
      break;
    case 'Home':
      event.preventDefault();
      focusFirstItem(domItems);
      break;
    case 'End':
      event.preventDefault();
      focusLastItem(domItems);
      break;
  }
}

/**
 * Get the index of a tree item in the dom
 *
 * @param id
 * @param domItems
 */
function getItemDomIndex(id, domItems) {
  return domItems.findIndex(
    item => JSON.parse(item.getAttribute('data-id')) === id
  );
}

/**
 * Focus the provided dom treeitem
 *
 * @param {Object} item
 */
function focusDomItem(item) {
  focusableItem.value = JSON.parse(item.getAttribute('data-id'));

  item.focus();
}

/**
 * Move focus from the current focusable treeitem to it's nearest parent treeitem
 */
function focusParentItem(domItems) {
  const domIndex = getItemDomIndex(focusableItem.value, domItems);
  const item = domItems[domIndex];
  const parentItem = item.parentElement?.closest('[role=treeitem]');

  if (parentItem) {
    focusDomItem(parentItem, domItems);
  }
}

/**
 * Move focus from the current focusable treeitem to the next treeitem
 */
function focusNextItem(domItems) {
  const domIndex = getItemDomIndex(focusableItem.value, domItems);
  const nextIndex =
    domIndex + 1 < domItems.length ? domIndex + 1 : domItems.length - 1;

  focusDomItem(domItems[nextIndex], domItems);
}

/**
 * Move focus from the current focusable treeitem to the previous treeitem
 */
function focusPreviousItem(domItems) {
  const domIndex = getItemDomIndex(focusableItem.value, domItems);
  const previousIndex = domIndex - 1 > -1 ? domIndex - 1 : 0;

  focusDomItem(domItems[previousIndex], domItems);
}

/**
 * Focus the first visible item in the tree
 */
function focusFirstItem(domItems) {
  focusDomItem(domItems[0], domItems);
}

/**
 * Focus the last visible item in the tree
 */
function focusLastItem(domItems) {
  focusDomItem(domItems.at(-1), domItems);
}
</script>

<template>
  <ul ref="treeDomElement" class="tui-treeView" role="tree">
    <TreeViewNode
      v-for="item in items"
      :key="item.id"
      :expanded-items="expandedItems"
      :focusable-item="focusableItem"
      :item="item"
      :multi-select="multiSelect"
      :selected-items="selectedItems"
      @select="handleSelect"
      @toggle="handleToggle"
    />
  </ul>
</template>

<style lang="scss">
.tui-treeView {
  display: flex;
  flex-direction: column;
  gap: var(--border-width-normal);
  margin-left: 0;
}
</style>
