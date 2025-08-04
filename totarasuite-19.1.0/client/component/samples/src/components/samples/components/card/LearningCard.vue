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
import AddIcon from 'tui/components/icons/Add';
import BookmarkIcon from 'tui/components/icons/Bookmark';
import DragHandleIcon from 'tui/components/icons/DragHandle';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import LearningCard from 'tui/components/card/LearningCard';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import ShareIcon from 'tui/components/icons/Share';
import { Uniform, FormRow, FormSelect, FormText } from 'tui/components/uniform';
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

const imageBackgroundColorOptions = [
  { id: 'default', label: 'default' },
  { id: 'secondary', label: 'secondary' },
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
  draggable: false,
  heroLabel: 'Hero data',
  href: '#',
  imageBackgroundColor: 'secondary',
  imageUrl:
    'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAFUlEQVR42mNkYPhfz0AEYBxVSF+FAP5FDvcfRYWgAAAAAElFTkSuQmCC',
  size: 'default',
  title: 'Lorem ipsum',
  variant: 'hero',
});

const includeSlotBody = ref(true);
const includeSlotBottomOverlay = ref(false);
const includeSlotCentreOverlay = ref(false);
const includeSlotFooter = ref(true);
const includeSlotHero = ref(false);
</script>

<template>
  <SamplesExample>
    <div class="tui-samples-learningCard">
      <div
        :class="[
          'tui-samples-learningCard__cardContainer',
          'tui-samples-learningCard__cardContainer--size-' + values.size,
        ]"
      >
        <LearningCard
          :title="values.title"
          :size="values.size"
          :hero-label="values.heroLabel"
          :href="values.href"
          :variant="values.variant"
          :image="values.imageUrl"
          :image-background-color="values.imageBackgroundColor"
          :actions="actions"
          :draggable="values.draggable"
        >
          <template v-if="includeSlotHero" v-slot:hero="{ popFront }">
            <DragHandleIcon
              :class="['tui-engageArticleCard__drag', popFront]"
            />
          </template>
          <template v-if="includeSlotBody" v-slot:body>
            <div>
              Lorem ipsum dolor sit amet, consectetur adipiscing.
            </div>
          </template>
          <template v-if="includeSlotFooter" v-slot:footer>
            Lorem ipsum dolor sit amet
          </template>

          <template v-if="includeSlotCentreOverlay" v-slot:media-centre-overlay>
            Lorem ipsum
          </template>

          <template v-if="includeSlotBottomOverlay" v-slot:media-bottom-overlay>
            Lorem ipsum
          </template>
        </LearningCard>
      </div>
    </div>
  </SamplesExample>
  <SamplesCtl>
    <Uniform :initial-values="values" @change="values = $event">
      <FormRow v-slot="{ id }" label="Variant">
        <FormSelect name="variant" :options="imageDisplayOptions" />
        <FormRowDetails :id="id">
          image
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Title">
        <FormText name="title" />
        <FormRowDetails :id="id">
          title
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Size">
        <FormSelect name="size" :options="sizeOptions" />
        <FormRowDetails :id="id">
          size
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="href">
        <FormText name="href" disabled />
        <FormRowDetails :id="id">
          href
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Hero label">
        <FormText name="heroLabel" />
        <FormRowDetails :id="id">
          heroLabel
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Image url"
        helpmsg="If image url is null, card variant will always be forced to hero."
      >
        <FormText name="imageUrl" />
        <FormRowDetails :id="id">
          image
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Image background colour">
        <FormSelect
          name="imageBackgroundColor"
          :options="imageBackgroundColorOptions"
        />
        <FormRowDetails :id="id">
          imageBackgroundColor
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Draggable">
        <RadioGroup v-model:value="values.draggable" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          draggable
        </FormRowDetails>
      </FormRow>
    </Uniform>
  </SamplesCtl>

  <SamplesCtl label="Slot options">
    <FormRow v-slot="{ id }" label="Hero">
      <RadioGroup v-model:value="includeSlotHero" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        hero
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Body">
      <RadioGroup v-model:value="includeSlotBody" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        body
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Footer">
      <RadioGroup v-model:value="includeSlotFooter" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        footer
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Media overlay centre">
      <RadioGroup v-model:value="includeSlotCentreOverlay" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        media-centre-overlay
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Media overlay bottom">
      <RadioGroup v-model:value="includeSlotBottomOverlay" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        media-bottom-overlay
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<style lang="scss">
.tui-samples-learningCard {
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
