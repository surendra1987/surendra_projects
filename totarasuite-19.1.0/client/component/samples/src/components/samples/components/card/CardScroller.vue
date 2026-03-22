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
import Button from 'tui/components/buttons/Button';
import CardScroller from 'tui/components/card/CardScroller';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import SelectFilter from 'tui/components/filters/SelectFilter';
import InputText from 'tui/components/form/InputText';
import LearningCard from 'tui/components/card/LearningCard';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

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

const includeSlotEmptyState = ref(false);
const includeSlotHeaderButtons = ref(false);
const title = ref('Lorem ipsum');
const titleHref = ref('#');
const titleTooltip = ref('');
const variant = ref('boxed');

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
  <SamplesExample>
    <div class="tui-samples-cardScroller">
      <CardScroller
        :can-load-more="canLoadMore"
        :items="visibleCards"
        :loading="loading"
        :title-href="titleHref"
        :title-tooltip="titleTooltip"
        :title="title"
        :variant="variant"
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

        <template v-if="includeSlotHeaderButtons" v-slot:header-buttons>
          <Button text="Header button" />
        </template>

        <template v-if="includeSlotEmptyState" v-slot:empty-state>
          No cards
        </template>
      </CardScroller>
    </div>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Variant">
      <SelectFilter
        v-model:value="variant"
        :options="variantOptions"
        label=""
      />
      <FormRowDetails :id="id">
        variant
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Title">
      <InputText v-model:value="title" />
      <FormRowDetails :id="id">
        title
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Title href">
      <InputText v-model:value="titleHref" disabled />
      <FormRowDetails :id="id">
        titleHref
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Title tooltip">
      <InputText v-model:value="titleTooltip" />
      <FormRowDetails :id="id">
        titleTooltip
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <SamplesCtl label="Slot options">
    <FormRow v-slot="{ id }" label="Header buttons">
      <RadioGroup
        :id="id"
        v-model:value="includeSlotHeaderButtons"
        :horizontal="true"
      >
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        header-buttons
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      helpmsg="The content of this slot is displayed within the cards container when there are no items"
      label="Empty state"
    >
      <RadioGroup
        :id="id"
        v-model:value="includeSlotEmptyState"
        :horizontal="true"
      >
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        empty-state
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
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
