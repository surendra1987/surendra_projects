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
  @module samples
-->

<script setup>
import { computed, ref } from 'vue';
import CardScroller from 'tui/components/card/CardScroller';
import LearningCard from 'tui/components/card/LearningCard';
import Select from 'tui/components/form/Select';
import InputNumber from 'tui/components/form/InputNumber';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';

const img =
  'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNkYPhfz0AEYBxVSF+FAP5FDvcfRYWgAAAAAElFTkSuQmCC';

let a = 4019895631;
const mulberry32 = () => {
  let t = (a += 0x6d2b79f5);
  t = Math.imul(t ^ (t >>> 15), t | 1);
  t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
  return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
};

const variantOptions = [
  { id: 'open', label: 'open' },
  { id: 'boxed', label: 'boxed' },
];

const loading = ref(false);
const variant = ref('boxed');
const max = ref(30);
const canLoadMore = computed(() => cardData.value.length < max.value);

const cardData = ref(
  [...Array(10).keys()].map(i => {
    const times = ((mulberry32() * 100) | 0) + 1;
    return {
      id: i,
      content: `card #${i + 1} ` + 'hello '.repeat(times),
    };
  })
);

const visibleCards = computed(() => cardData.value.slice(0, max.value));

async function loadMore() {
  loading.value = true;
  await new Promise(x => setTimeout(x, 1000));
  loading.value = false;

  if (!canLoadMore.value) {
    return;
  }

  cardData.value.push(
    ...[...Array(10).keys()].map(iA => {
      const i = iA + cardData.value.length;
      const times = ((mulberry32() * 100) | 0) + 1;
      return {
        id: i,
        content: `card #${i + 1} ` + 'hello '.repeat(times),
      };
    })
  );
}
</script>

<template>
  <div class="tui-samples-cardScroller">
    <CardScroller
      :items="visibleCards"
      title="Cards"
      title-tooltip="500 items"
      :title-href="$url('/')"
      :variant="variant"
      :can-load-more="canLoadMore"
      :loading="loading"
      @load-more="loadMore"
    >
      <template v-slot="{ item }">
        <LearningCard
          title="Card"
          :image="img"
          class="tui-samples-cardScroller__item"
        >
          <template v-slot:body>
            <div>
              {{ item.content }}
            </div>
          </template>
        </LearningCard>
      </template>

      <template v-slot:empty-state>
        <div>No cards.</div>
      </template>
    </CardScroller>
    <Form>
      <FormRow v-slot="{ id }" label="Variant">
        <Select :id="id" v-model:value="variant" :options="variantOptions" />
      </FormRow>
      <FormRow v-slot="{ id }" label="Max items">
        <InputNumber :id="id" v-model:value="max" min="0" />
      </FormRow>
    </Form>
  </div>
</template>

<style lang="scss">
.tui-samples-cardScroller {
  display: flex;
  flex-flow: column;
  gap: var(--gap-4);

  &__item {
    min-width: var(--tui-card-default-width);
  }
}
</style>
