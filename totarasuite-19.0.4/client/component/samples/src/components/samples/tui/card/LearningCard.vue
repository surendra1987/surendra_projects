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
import LearningCard from 'tui/components/card/LearningCard';
import { Uniform, FormRow, FormSelect, FormText } from 'tui/components/uniform';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';
import AddIcon from 'tui/components/icons/Add';
import ShareIcon from 'tui/components/icons/Share';
import BookmarkIcon from 'tui/components/icons/Bookmark';
import { ref } from 'vue';

const imageDisplayOptions = [
  { id: 'hero', label: 'hero' },
  { id: 'grey-body', label: 'grey-body' },
  { id: 'background', label: 'background' },
];

const sizeOptions = [
  { id: 'default', label: 'default' },
  { id: 'sm', label: 'sm' },
];

const actions = [
  {
    label: 'Add',
    icon: AddIcon,
    inMenu: true,
    persistent: false,
    onClick: () => alert('Add'),
  },
  {
    label: 'ShareIcon',
    icon: ShareIcon,
    persistent: false,
    onClick: () => alert('Share'),
  },
  {
    label: 'Bookmark',
    icon: BookmarkIcon,
    persistent: true,
    onClick: () => alert('Bookmark'),
  },
];

const values = ref({
  variant: 'hero',
  title: 'Lorem ipsum',
  size: 'default',
  href: '',
  heroLabel: 'Hero data',
  imageUrl:
    'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNkYPhfz0AEYBxVSF+FAP5FDvcfRYWgAAAAAElFTkSuQmCC',
});
</script>

<template>
  <div>
    <SamplesExample>
      <div class="tui-learningCard-example">
        <div
          :class="[
            'tui-learningCard-example__cardContainer',
            'tui-learningCard-example__cardContainer--size-' + values.size,
          ]"
        >
          <LearningCard
            :title="values.title"
            :size="values.size"
            :hero-label="values.heroLabel"
            :href="values.href"
            :variant="values.variant"
            :image="values.imageUrl"
            :actions="actions"
          >
            <template v-slot:body>
              <div>
                Lorem ipsum dolor sit amet, consectetur adipiscing.
              </div>
            </template>
            <template v-slot:footer>
              Lorem ipsum dolor sit amet
            </template>
          </LearningCard>
        </div>
      </div>
    </SamplesExample>
    <SamplesPropCtl>
      <Uniform :initial-values="values" @change="values = $event">
        <FormRow label="variant">
          <FormSelect name="variant" :options="imageDisplayOptions" />
        </FormRow>
        <FormRow label="title">
          <FormText name="title" />
        </FormRow>
        <FormRow label="size">
          <FormSelect name="size" :options="sizeOptions" />
        </FormRow>
        <FormRow label="href">
          <FormText name="href" />
        </FormRow>
        <FormRow label="hero label">
          <FormText name="heroLabel" />
        </FormRow>
        <FormRow
          label="image url"
          helpmsg="If image url is null, card variant will always be forced to hero."
        >
          <FormText name="imageUrl" />
        </FormRow>
      </Uniform>
    </SamplesPropCtl>
  </div>
</template>

<style lang="scss">
.tui-learningCard-example {
  &__cardContainer {
    &--size-default {
      width: var(--tui-card-default-width);
    }

    &--size-sm {
      width: var(--tui-card-sm-width);
    }
  }
}
</style>
