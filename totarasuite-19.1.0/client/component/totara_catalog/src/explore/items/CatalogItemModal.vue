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
import { useQuery } from 'tui/apollo/composable';
import Button from 'tui/components/buttons/Button';
import CloseIcon from 'tui/components/icons/Close';
import LoadingIcon from 'tui/components/icons/Loading';
import Modal from 'tui/components/modal/Modal';
import IconPlaceholders from 'totara_catalog/explore/items/placeholders/IconPlaceholders';
import TextPlaceholders from 'totara_catalog/explore/items/placeholders/TextPlaceholders';
import detailsQuery from 'totara_catalog/graphql/item_details';

const props = defineProps({
  item: { type: Object, required: true },
});

const emit = defineEmits(['request-close']);

const query = useQuery(detailsQuery, () => ({
  input: { itemid: props.item.itemid },
}));

const details = computed(
  () => query.result.value?.totara_catalog_item_details.details
);

const hasPlaceholders = computed(
  () =>
    details.value.text_placeholders?.length > 0 ||
    details.value.icon_placeholders?.length > 0
);
</script>

<template>
  <Modal :aria-labelledby="$id('title')" size="large">
    <div class="tui-totara_catalog-itemModal__container">
      <button
        type="button"
        class="tui-totara_catalog-itemModal__close"
        shape="pill"
        variant="stealth"
        :aria-label="$str('closebuttontitle', 'core')"
        @click="emit('request-close')"
      >
        <CloseIcon />
      </button>
      <img
        v-if="details"
        class="tui-totara_catalog-itemModal__image"
        :src="item.image?.url"
      />
      <div v-if="!details" class="tui-totara_catalog-itemModal__content">
        <div class="tui-totara_catalog-itemModal__loading">
          <div>
            <LoadingIcon aria-hidden="true" />
            {{ $str('loading', 'core') }}
          </div>
        </div>
      </div>
      <div v-else class="tui-totara_catalog-itemModal__content">
        <h1 :id="$id('title')" class="tui-totara_catalog-itemModal__title">
          {{ details.title }}
        </h1>
        <div v-if="details.manage_link">
          <a :href="details.manage_link.url">{{ details.manage_link.label }}</a>
        </div>
        <div
          v-if="details.details_link"
          class="tui-totara_catalog-itemModal__detailsLink"
        >
          <div
            v-if="details.details_link.description"
            class="tui-totara_catalog-itemModal__detailsLink-description"
          >
            {{ details.details_link.description }}
          </div>
          <Button
            v-if="details.details_link.button"
            class="tui-totara_catalog-itemModal__detailsLink-button"
            variant="primary"
            :text="details.details_link.button.label"
            :href="details.details_link.button.url"
          />
        </div>
        <div
          v-if="hasPlaceholders"
          class="tui-totara_catalog-itemModal__placeholders"
        >
          <TextPlaceholders
            v-if="details.text_placeholders?.length > 0"
            :items="details.text_placeholders"
          />
          <IconPlaceholders
            v-if="details.icon_placeholders?.length > 0"
            :items="details.icon_placeholders"
          />
        </div>
        <div
          v-if="details.rich_text"
          class="tui-totara_catalog-itemModal__richText"
        >
          <div v-html="details.rich_text" />
        </div>
        <div
          v-if="details.description"
          class="tui-totara_catalog-itemModal__description"
        >
          {{ details.description }}
        </div>
      </div>
    </div>
  </Modal>
</template>

<style lang="scss">
.tui-totara_catalog-itemModal {
  &__container {
    position: relative;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-height: 0;
    padding: gap(2);
    overflow-y: auto;
  }

  &__image {
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    background: var(--color-neutral-4);
    border-radius: calc(var(--modal-border-radius) - #{gap(2)});
  }

  &__close {
    position: absolute;
    top: gap(6);
    right: gap(6);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    color: var(--color-backdrop-contrast);
    background-color: var(--color-backdrop-standard);
    border: none;
    border-radius: 100%;

    &:hover {
      background-color: var(--color-backdrop-heavy);
    }
  }

  &__content {
    display: flex;
    flex-flow: column;
    gap: gap(6);
    margin-top: gap(2);
    padding: gap(4);
  }

  &__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: rem-px(583);
    color: var(--color-neutral-6);
  }

  &__title {
    margin: 0;
  }

  &__detailsLink {
    display: flex;
    gap: gap(2);
    align-items: center;
    padding: gap(4);
    background: var(--color-neutral-2);
    border: var(--border-width-thin) solid var(--color-neutral-5);
    border-radius: var(--border-radius-normal);

    &-description {
      flex: 1;
    }
  }

  &__placeholders {
    display: flex;
    flex-flow: column;
    gap: gap(4);
  }
}
</style>
