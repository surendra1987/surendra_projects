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
  @module tui
-->

<script setup>
import { computed, nextTick, ref, toRef, watch } from 'vue';
import { throttle } from 'tui/util';
import { useResizeObserver } from 'tui/dom/composables';
import { useCardFitter } from './internal/card_fitter';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import EntryPreviousIcon from 'tui/components/icons/EntryPrevious';
import EntryNextIcon from 'tui/components/icons/EntryNext';
import ForwardArrowIcon from 'tui/components/icons/ForwardArrow';
import LoadingIcon from 'tui/components/icons/Loading';

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  title: String,
  titleHref: String,
  titleTooltip: String,
  headingLevel: {
    type: Number,
    default: 3,
  },
  variant: {
    type: String,
    default: 'open',
    validator: x => x == 'open' || x == 'boxed',
  },
  canLoadMore: Boolean,
  loading: Boolean,
});

const emit = defineEmits(['load-more', 'onscreen-items-changed']);

const measuringEl = ref();
const cardsEl = ref();
const startIndex = ref(0);
const dir = ref('forward');
const lastRealHeight = ref(null);
const largestRealHeight = ref(1);

const { fittingCards, measuringItems, remeasure } = useCardFitter({
  loading: toRef(props, 'loading'),
  measuringEl,
  items: toRef(props, 'items'),
});

const renderedItems = computed(() => {
  const start = startIndex.value;
  let items = props.items.slice(start, start + fittingCards.value);
  if (fittingCards.value > items.length) {
    items = items.concat(Array(fittingCards.value - items.length).fill(null));
  }
  return items;
});

const expandToFill = computed(
  () => renderedItems.value.length === fittingCards.value
);

const morePages = computed(
  () =>
    props.canLoadMore ||
    props.items.length > startIndex.value + fittingCards.value
);
const canGoBack = computed(() => !props.loading && startIndex.value !== 0);
const canGoForward = computed(() => !props.loading && morePages.value);

function navigatePage(by) {
  largestRealHeight.value = Math.max(
    largestRealHeight.value,
    cardsEl.value.offsetHeight
  );

  dir.value = by > 0 ? 'forward' : 'back';
  startIndex.value = Math.max(0, startIndex.value + fittingCards.value * by);
  const pageIsFull = renderedItems.value.length === fittingCards.value;
  if (by == 1 && props.canLoadMore && !pageIsFull) {
    emit('load-more');
  }

  nextTick(() => {
    lastRealHeight.value = cardsEl.value.offsetHeight;
  });
}

const handleResize = throttle(() => {
  remeasure();
  largestRealHeight.value = 1;
}, 200);

// if we want more cards than we have, ask for more
watch(
  () =>
    props.canLoadMore &&
    !props.loading &&
    !measuringItems.value &&
    fittingCards.value > props.items.length - startIndex.value,
  needMoreCards => {
    if (needMoreCards) {
      emit('load-more');
    }
  }
);

useResizeObserver(cardsEl, (size, old) => {
  if (!old || size.width != old.width) {
    handleResize();
  }
});

watch(
  () => props.canLoadMore,
  value => {
    // handle canLoadMore being set to false after trying to load new page
    // (i.e. there's no data to show on this page)
    if (!value && renderedItems.value.length === 0) {
      // reset to last page
      dir.value = 'back';
      const pages = Math.ceil(props.items.length / fittingCards.value);
      startIndex.value = (pages - 1) * fittingCards.value;
    }
  }
);

watch(
  () => props.items.length,
  (length, oldLength) => {
    // reset to beginning if props.items length gets smaller
    if (length < oldLength) {
      startIndex.value = 0;
      largestRealHeight.value = 1;
    }
    if (oldLength === 0) {
      dir.value = 'forward';
    }
  }
);

watch(renderedItems, value => {
  emit('onscreen-items-changed', value);
});
</script>

<template>
  <div
    class="tui-cardScroller"
    :class="[
      'tui-cardScroller--variant-' + variant,
      'tui-cardScroller--dir-' + dir,
      expandToFill && 'tui-cardScroller--expandToFill',
    ]"
  >
    <div class="tui-cardScroller__titleRow">
      <component
        :is="titleHref ? 'a' : 'div'"
        class="tui-cardScroller__title"
        :href="titleHref"
        :title="titleTooltip"
      >
        <h2 class="tui-cardScroller__title-header">
          {{ title }}
        </h2>
        <ForwardArrowIcon
          v-if="titleHref"
          class="tui-cardScroller__titleIcon"
          :size="null"
        />
      </component>
      <div class="tui-cardScroller__titleControls">
        <ButtonIcon
          variant="stealth"
          :aria-label="$str('previouspage', 'totara_core')"
          :disabled="!canGoBack"
          @click="navigatePage(-1)"
        >
          <EntryPreviousIcon
            class="tui-cardScroller__navButton-icon"
            :class="{
              'tui-cardScroller__navButton-icon--disabled': !canGoBack,
            }"
          />
        </ButtonIcon>
        <ButtonIcon
          variant="stealth"
          :aria-label="$str('nextpage', 'totara_core')"
          :disabled="!canGoForward"
          @click="navigatePage(1)"
        >
          <EntryNextIcon
            class="tui-cardScroller__navButton-icon"
            :class="{
              'tui-cardScroller__navButton-icon--disabled': !canGoForward,
            }"
          />
        </ButtonIcon>
        <slot name="header-buttons" />
      </div>
    </div>
    <div class="tui-cardScroller__cardsBox">
      <div class="tui-cardScroller__cardsContainer">
        <!-- element used to take layout measurements -->
        <div
          v-show="measuringItems !== null"
          ref="measuringEl"
          class="tui-cardScroller__cards tui-cardScroller__cards--measurer"
        >
          <template v-for="(item, i) in measuringItems" :key="i">
            <slot :item="item" />
          </template>
        </div>

        <!-- actual card rendering -->
        <TransitionGroup name="tui-cardScroller__cards--transition">
          <div
            v-show="items.length > 0"
            ref="cardsEl"
            :key="startIndex"
            class="tui-cardScroller__cards tui-cardScroller__cards--display"
            :style="{ minHeight: largestRealHeight + 'px' }"
          >
            <div
              v-if="loading"
              class="tui-cardScroller__cards-loading"
              :style="{ minHeight: lastRealHeight + 'px' }"
            >
              <div>
                <LoadingIcon aria-hidden="true" />
                {{ $str('loading', 'core') }}
              </div>
            </div>
            <template v-else>
              <template
                v-for="(item, i) in renderedItems"
                :key="item ? item.id : '__' + i"
              >
                <!-- render actual card -->
                <slot v-if="item" :item="item" />
                <!-- render placeholder to keep correct sizing -->
                <div v-else class="tui-cardScroller__placeholderCard" />
              </template>
            </template>
          </div>
        </TransitionGroup>

        <div
          v-if="items.length === 0"
          class="tui-cardScroller_emptyStateContainer"
        >
          <slot name="empty-state" />
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.tui-cardScroller {
  display: flex;
  flex-direction: column;

  &__titleRow {
    display: flex;
    gap: var(--gap-2);
    align-items: flex-end;
    justify-content: space-between;
    padding-bottom: var(--gap-3);
    border-bottom: var(--border-width-normal) solid var(--color-neutral-4);
  }

  &__title {
    @include font(h3);
    display: flex;
    gap: rem-px(6);
    align-items: center;
    padding-bottom: var(--gap-1);
    color: inherit;
    font-weight: 500;
    overflow-wrap: anywhere;
    &:hover,
    &:focus {
      color: inherit;
    }
    &:focus-visible {
      @include tui-focus;
    }

    // Dity trick to override the block header styling
    //
    // TODO: A better solution is to remove the ".block .content h2" styles from blocks.scss assuming they're not needed
    // This would require a thorough test of all the blocks to make sure it's safe to remove those
    &-header.tui-cardScroller__title-header.tui-cardScroller__title-header {
      margin: 0;
      font-size: var(--font-h3-size);
    }
  }

  &__titleIcon {
    flex-shrink: 0;
    margin-top: 0.2em;
    font-size: font-size-px(12);
    transition: tui-transition('button', transform);
  }

  &__title:hover &__titleIcon {
    transform: translateX(rem-px(3));
  }

  &__titleControls {
    flex-shrink: 0;
  }

  &__navButton-icon {
    color: var(--color-text);
    &--disabled {
      color: var(--color-state-disabled);
    }
  }

  &__cardsBox {
    padding-top: var(--gap-4);
  }

  &--variant-boxed &__cardsBox {
    padding: calc(var(--gap-1) * 5.5);
    background: var(--color-neutral-3);
    border-bottom-right-radius: rem-px(8);
    border-bottom-left-radius: rem-px(8);
  }

  &__cardsContainer:has(&__cards--transition-enter-active, &__cards--transition-leave-active) {
    position: relative;
    // temporarily expand bounds while we're applying hidden overflow to avoid cutting off shadow
    margin: calc(var(--gap-4) * -1);
    padding: var(--gap-4);
    overflow: hidden;
  }

  &__cards {
    position: relative;
    display: flex;
    flex-direction: row;
    gap: var(--gap-4);

    &--transition {
      &-enter-active,
      &-leave-active {
        transition: all 0.15s cubic-bezier(0, 0.1, 0, 1);
        @media (prefers-reduced-motion) {
          transition: none;
        }
      }

      &-enter-from {
        transform: translateX(calc(100% + var(--gap-6)));
      }
      &-leave-to {
        transform: translateX(calc(-100% - var(--gap-6)));
      }

      &-leave-active {
        position: absolute;
      }
    }

    & > * {
      flex-basis: 0;
    }
  }

  &--dir-back &__cards--transition {
    &-enter-from {
      transform: translateX(-100%);
    }
    &-leave-to {
      transform: translateX(100%);
    }
  }

  &__cards--measurer {
    overflow-x: hidden;
  }

  &--expandToFill &__cards--display > * {
    flex-grow: 1;
  }

  &__cards-loading {
    display: flex;
    flex: 1;
    align-items: center;
    justify-content: center;
    min-height: rem-px(100);
  }
}
</style>
