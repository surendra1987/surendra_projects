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

  @author Arshad Anwer <arshad.anwer@totara.com>
  @module tui
-->

<script setup>
import { computed } from 'vue';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import More from 'tui/components/icons/More';

/**
 * @typedef {object} Action
 * @property {string} label
 * @property {object} icon
 * @property {boolean} persistent
 * @property {boolean} inMenu
 * @property {function} onClick
 */

const props = defineProps({
  // Determines the minimum height
  size: {
    type: String,
    default: 'default',
    validator: x => ['default', 'sm'].includes(x),
  },
  variant: {
    type: String,
    default: 'hero',
    validator: x => ['hero', 'grey-body', 'background'].includes(x),
  },
  image: String,
  imageBackgroundColor: {
    type: String,
    validator: x => ['default', 'secondary'].includes(x),
  },
  href: String,
  heroLabel: String,
  /** @type {import('vue').PropType<Array<Action>>} */
  actions: Array,
  title: String,
  draggable: Boolean,
});

const emit = defineEmits(['click']);

// Forced variant to hero if image prop is null
const variantType = computed(() => {
  if (!props.image) {
    return 'hero';
  }
  return props.variant;
});

const filteredActions = computed(() => {
  let sorted = {
    dropdown: {
      persistent: false,
      items: [],
    },
    items: [],
  };

  if (!props.actions) return sorted;

  for (const item of props.actions) {
    if (item.inMenu) {
      sorted.dropdown.items.push(item);

      // Skip assigning the value again if sorted object persistent is already true
      if (item.persistent && !sorted.dropdown.persistent) {
        sorted.dropdown.persistent = item.persistent;
      }
    } else {
      sorted.items.push(item);
    }
  }
  return sorted;
});

const backgroundGradientImage = computed(() => {
  if (props.image && variantType.value === 'background') {
    return {
      backgroundImage: `var(--tui-learningCard-background-gradient), url(${props.image})`,
    };
  }
  return null;
});

const backgroundImage = computed(() => {
  if (props.image && variantType.value !== 'background') {
    return {
      backgroundImage: `url(${props.image})`,
    };
  }
  return null;
});

function handleClick(e) {
  if (!props.href) {
    e.preventDefault();
  }
  emit('click', e);
}
</script>

<template>
  <div
    :class="[
      'tui-learningCard',
      `tui-learningCard--size-${size}`,
      `tui-learningCard--variant-${variantType}`,
    ]"
  >
    <div
      :class="[
        'tui-learningCard__background',
        !image && 'tui-learningCard__background--default',
      ]"
      :style="backgroundGradientImage"
    >
      <a
        :draggable="draggable"
        :href="href ? href : '#'"
        class="tui-learningCard__link"
        :aria-label="title"
        @click="handleClick"
      />
      <div
        :class="[
          'tui-learningCard__mediaContainer',
          image && 'tui-learningCard__mediaContainer--aspect-ratio',
        ]"
      >
        <div
          :class="[
            'tui-learningCard__image',
            imageBackgroundColor &&
              `tui-learningCard__image--background-${imageBackgroundColor}`,
          ]"
          :style="backgroundImage"
        >
          <div class="tui-learningCard__overlayWrapper">
            <div class="tui-learningCard__mediaOverlayTop">
              <div
                v-if="$slots.hero || heroLabel"
                class="tui-learningCard__heroContainer"
              >
                <div
                  v-if="$slots.hero"
                  class="tui-learningCard__heroIconWrapper"
                >
                  <slot name="hero" pop-front="tui-learningCard__popFront" />
                </div>
                <div
                  v-if="heroLabel"
                  class="tui-learningCard__heroLabelWrapper"
                >
                  <div class="tui-learningCard__hero-label">
                    {{ heroLabel }}
                  </div>
                </div>
              </div>
              <div class="tui-learningCard__actionContainer">
                <Dropdown
                  v-if="filteredActions.dropdown.items.length > 0"
                  :separator="false"
                  context-mode="uncontained"
                >
                  <template v-slot:trigger="{ toggle, isOpen }">
                    <div
                      :class="[
                        'tui-learningCard__actionWrapper',
                        filteredActions.dropdown.persistent &&
                          'tui-learningCard__actionWrapper--persistent',
                      ]"
                    >
                      <button
                        type="button"
                        :title="$str('triggermenu', 'totara_comment')"
                        :aria-expanded="isOpen"
                        :aria-label="$str('triggermenu', 'totara_comment')"
                        class="tui-learningCard__action-trigger"
                        @click="toggle"
                      >
                        <More />
                      </button>
                    </div>
                  </template>
                  <DropdownItem
                    v-for="(action, index) in filteredActions.dropdown.items"
                    :key="index"
                    @click="action.onClick"
                  >
                    {{ action.label }}
                  </DropdownItem>
                </Dropdown>
                <div
                  v-for="(action, index) in filteredActions.items"
                  :key="index"
                  :class="[
                    'tui-learningCard__actionWrapper',
                    action.persistent &&
                      'tui-learningCard__actionWrapper--persistent',
                  ]"
                >
                  <button
                    type="button"
                    :title="action.label"
                    :aria-label="action.label"
                    class="tui-learningCard__action-item"
                    @click="action.onClick"
                  >
                    <component :is="action.icon" />
                  </button>
                </div>
              </div>
            </div>
            <div
              v-if="$slots['media-centre-overlay']"
              class="tui-learningCard__mediaOverlayCentre"
            >
              <slot
                name="media-centre-overlay"
                pop-front="tui-learningCard__popFront"
              />
            </div>
            <div
              v-if="$slots['media-bottom-overlay']"
              class="tui-learningCard__mediaOverlayBottom"
            >
              <slot
                name="media-bottom-overlay"
                pop-front="tui-learningCard__popFront"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="tui-learningCard__body">
        <div v-if="title" class="tui-learningCard__title">{{ title }}</div>
        <slot name="body" pop-front="tui-learningCard__popFront" />
      </div>
      <div v-if="$slots.footer" class="tui-learningCard__footer">
        <slot name="footer" pop-front="tui-learningCard__popFront" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.tui-learningCard {
  $block: #{&};
  $hero-pill-h: rem-px(20);
  $action-item-dimension: rem-px(20);
  $hero-label-min-w: rem-px(58);
  $hover-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.15);
  --tui-learningCard-background-gradient: linear-gradient(
    180deg,
    rgba(0, 0, 0, 0) 10%,
    rgba(0, 0, 0, 0.4) 40%,
    var(--color-neutral-7) 80%
  );

  @mixin action-persistent {
    display: flex;
    padding-bottom: var(--gap-1);
    padding-left: var(--gap-1);
  }

  @mixin card-top-radius {
    border-top-left-radius: var(--card-border-radius);
    border-top-right-radius: var(--card-border-radius);
  }

  @mixin card-bottom-radius {
    border-bottom-right-radius: var(--card-border-radius);
    border-bottom-left-radius: var(--card-border-radius);
  }

  position: relative;
  display: flex;
  flex-direction: column;
  margin: calc((var(--gap-1) * 1.5) * -1);
  padding: calc(var(--gap-1) * 1.5);
  overflow-wrap: break-word;
  border-radius: calc(var(--card-border-radius) * 2);
  outline: none;
  isolation: isolate;
  transition: box-shadow 0.3s ease-in-out;

  @media (hover: none) {
    .tui-learningCard__actionWrapper {
      @include action-persistent;
    }
  }

  &:hover {
    z-index: 1;
    box-shadow: $hover-shadow;
  }

  &:hover,
  &:focus-within {
    .tui-learningCard__actionWrapper {
      @include action-persistent;
    }
  }

  @mixin background-image {
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
  }

  &--variant-hero {
    #{$block}__image {
      border-radius: var(--card-border-radius);
    }
  }

  &--variant-grey-body {
    #{$block}__image {
      @include card-top-radius;
    }

    #{$block}__body {
      @include card-bottom-radius;
      background: var(--color-neutral-2);
    }
  }

  &--variant-background {
    #{$block}__image {
      border-radius: var(--card-border-radius);
    }

    #{$block}__body {
      color: var(--color-neutral-1);
    }

    #{$block}__footer {
      @include card-bottom-radius;
      color: var(--color-neutral-1);
      background: var(--color-neutral-7);
    }
  }

  &__popFront {
    position: relative;
    z-index: 2;
  }

  &__link {
    position: absolute;
    inset: 0;
    z-index: 1;
    border-radius: var(--card-border-radius);

    &:focus-visible {
      @include tui-focus;
      outline-offset: var(--gap-2);
    }
  }

  &--size-sm {
    min-height: rem-px(200);
  }

  &--size-default {
    min-height: rem-px(312);
  }

  &__background {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    width: 100%;
    height: 100%;
    color: inherit;
    border-radius: var(--card-border-radius);
    @include background-image;

    &--default {
      background-color: var(--color-neutral-2);
    }
  }

  &__mediaContainer {
    position: relative;

    &--aspect-ratio {
      aspect-ratio: 16 / 9;
    }
  }

  &__image {
    display: flex;
    width: 100%;
    height: 100%;
    @include background-image;

    &--background-default {
      background-color: var(--color-primary);
    }
  }

  &__overlayWrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
  }

  &__mediaOverlayTop {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    min-height: rem-px(56);
    padding: var(--gap-3);
  }

  &__heroContainer {
    display: flex;
    min-width: $hero-label-min-w;
    margin-right: var(--gap-4);
  }

  &__heroIconWrapper {
    margin-right: var(--gap-1);
  }

  &__heroLabelWrapper {
    display: flex;
    align-items: center;
    height: $hero-pill-h;
    padding: var(--gap-2);
    overflow: hidden;
    color: var(--color-neutral-1);
    background-color: var(--color-neutral-7);
    border-radius: $hero-pill-h;
    @include font(body-sm);
  }

  &__hero-label {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    @include font(body-sm);
  }

  &__actionContainer {
    display: flex;
    margin-left: auto;
  }

  &__actionWrapper {
    display: none;

    &--persistent {
      @include action-persistent;
    }
  }

  &__action-trigger,
  &__action-item {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: calc(var(--gap-1) * 1.5);
    color: var(--color-neutral-7);
    background: var(--color-neutral-2);
    border: 0;
    border-radius: $action-item-dimension;

    &:hover {
      color: var(--color-state-hover);
      background: var(--color-neutral-1);
      box-shadow: var(--btn-shadow-hover);
    }

    &--persistent {
      display: flex;
    }
  }

  &__mediaOverlayCentre {
    display: flex;
    flex-basis: 100%;
    padding: var(--gap-3);
  }

  &__mediaOverlayBottom {
    margin-top: auto;
    padding: var(--gap-3);
  }

  &__body,
  &__footer {
    padding: var(--gap-3);
  }

  &__title {
    display: -webkit-box;
    margin: 0 0 var(--gap-1) 0;
    overflow: hidden;
    font-weight: 501;
    font-size: font-size-px(16);
    line-height: line-height-px(24);
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
  }

  &__body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
  }
}
</style>
