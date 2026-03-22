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
import { computed } from 'vue';
import LearningCard from 'tui/components/card/LearningCard';
import Progress from 'tui/components/progress/Progress';
import FeaturedIcon from 'tui/components/icons/Featured';
import Tooltip from 'tui/components/popover/Tooltip';
import IconPlaceholders from 'totara_catalog/explore/items/placeholders/IconPlaceholders';
import TextPlaceholders from 'totara_catalog/explore/items/placeholders/TextPlaceholders';

const props = defineProps({
  item: Object,
  navigate: Boolean,
});

const emit = defineEmits(['click']);

const cardVariant = computed(() => {
  if (props.item.objecttype === 'engage_article') {
    return 'grey-body';
  }
  return 'hero';
});

const heroIcon = computed(() =>
  props.item.hero_data_type === 'icon' &&
  (props.item.hero_data_icon?.icon || props.item.hero_data_icon?.url)
    ? props.item.hero_data_icon
    : null
);
</script>

<template>
  <LearningCard
    class="tui-totara_catalog-itemCard"
    :title="item.title"
    :image="item.image?.url"
    :image-background-color="item.objecttype === 'playlist' ? 'default' : null"
    :href="item.redirecturl"
    :hero-label="item.hero_data_type === 'text' ? item.hero_data_text : null"
    draggable
    :variant="cardVariant"
    @click="emit('click', $event)"
  >
    <template v-if="heroIcon" v-slot:hero>
      <div
        v-if="heroIcon.icon"
        class="tui-totara_catalog-itemCard__hero-icon"
        v-html="item.hero_data_icon.icon"
      />
      <div
        v-else-if="heroIcon.url"
        class="tui-totara_catalog-itemCard__hero-icon"
      >
        <img :src="heroIcon.url" :alt="heroIcon.alt" :title="heroIcon.alt" />
      </div>
    </template>

    <template v-slot:media-bottom-overlay>
      <div class="tui-totara_catalog-itemCard__mediaBottomOverlay">
        <div v-if="item.logo?.url" class="tui-totara_catalog-itemCard__logo">
          <img :src="item.logo?.url" :alt="item.logo?.alt" />
        </div>
        <Progress
          v-if="item.progress_bar_enabled && item.progress_bar"
          :value="item.progress_bar.progress"
        />
      </div>
    </template>

    <template v-slot:body="{ popFront }">
      <div class="tui-totara_catalog-itemCard__body">
        <div
          v-if="item.type_label || item.featured"
          class="tui-totara_catalog-itemCard__subtitleRow"
        >
          <div>
            {{ item.type_label }}
          </div>
          <div>
            <div v-if="item.featured">
              <div class="tui-sr-only">
                {{ $str('featured', 'totara_catalog') }}
              </div>
              <Tooltip position="bottom">
                <template v-slot:trigger>
                  <!-- even though we're above the link now, clicking on the featured icon should still trigger the link -->
                  <a
                    :href="item.redirecturl"
                    tabindex="-1"
                    :class="popFront"
                    aria-hidden="true"
                    @click="emit('click', $event)"
                  >
                    <FeaturedIcon />
                  </a>
                </template>
                <template v-slot:content>
                  {{ $str('featured', 'totara_catalog') }}
                </template>
              </Tooltip>
            </div>
          </div>
        </div>
        <div v-if="item.description_enabled && item.description">
          {{ item.description }}
        </div>
        <TextPlaceholders
          v-if="item.text_placeholders?.length > 0"
          :items="item.text_placeholders"
        />
      </div>
    </template>

    <template v-if="item.icon_placeholders?.length > 0" v-slot:footer>
      <div class="tui-totara_catalog-itemCard__iconPlaceholders">
        <IconPlaceholders :items="item.icon_placeholders" />
      </div>
    </template>
  </LearningCard>
</template>

<style lang="scss">
.tui-totara_catalog-itemCard {
  &__hero-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    aspect-ratio: 1;
    padding: gap(1);
    background: var(--color-neutral-1);
    border-radius: 100%;

    > img {
      width: 1.5rem;
      height: 1.5rem;
      object-fit: contain;
    }
  }

  &__mediaBottomOverlay {
    display: flex;
    flex-flow: column;
    gap: gap(2);
  }

  &__logo {
    align-self: end;
    padding: gap(2);
    background: var(--color-neutral-1);

    & > img {
      max-height: 1rem;
    }
  }

  &__body {
    display: flex;
    flex-flow: column;
    gap: gap(2);
    @include font(body-sm);
  }

  &__subtitleRow {
    display: flex;
    flex-flow: row wrap;
    gap: gap(2);
    justify-content: space-between;
  }

  &__iconPlaceholders {
    display: flex;
    justify-content: center;
  }

  // Hide the placeholder icons until hover if hover is available
  @media (hover: hover) {
    &__iconPlaceholders {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    &:focus-within &__iconPlaceholders,
    &:hover &__iconPlaceholders {
      opacity: 1;
    }
  }
}
</style>
