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
import Button from 'tui/components/buttons/Button';
import {
  Uniform,
  FormCheckbox,
  FormRow,
  FormSelect,
  FormText,
} from 'tui/components/uniform';
import InputSizedText from 'tui/components/form/InputSizedText';
import SettingsIcon from 'tui/components/icons/Settings';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';
import { ref } from 'vue';

const variantOptions = [
  { id: 'default', label: 'default' },
  { id: 'primary', label: 'primary' },
  { id: 'stealth', label: 'stealth' },
  { id: 'link', label: 'link' },
];

const sizeOptions = [
  { id: 'default', label: 'default' },
  { id: 'sm', label: 'sm' },
  { id: 'xs', label: 'xs' },
];

const colorOptions = [
  { id: 'default', label: 'default' },
  { id: 'danger', label: 'danger' },
];

const shapeOptions = [
  { id: 'default', label: 'default' },
  { id: 'pill', label: 'pill' },
  { id: 'circle', label: 'circle' },
];

const values = ref({
  text: 'Button',
  variant: 'default',
  size: 'default',
  color: 'default',
  shape: 'default',
});
</script>

<template>
  <div>
    Foo

    <SamplesExample>
      <Button
        :variant="values.variant"
        :size="values.size"
        :color="values.color"
        :shape="values.shape"
        :disabled="values.disabled"
        :loading="values.loading"
        :caret="values.caret"
        :text="values.text"
      >
        <template v-if="values.icon" v-slot:icon>
          <SettingsIcon :size="values.size === 'xs' ? null : 200" />
        </template>
      </Button>
    </SamplesExample>

    <SamplesPropCtl>
      <Uniform :initial-values="values" @change="v => (values = v)">
        <FormRow label="text">
          <FormText name="text" />
        </FormRow>
        <FormRow label="variant">
          <FormSelect name="variant" :options="variantOptions" />
        </FormRow>
        <FormRow label="size">
          <FormSelect name="size" :options="sizeOptions" />
        </FormRow>
        <FormRow label="color">
          <FormSelect name="color" :options="colorOptions" />
        </FormRow>
        <FormRow label="shape">
          <FormSelect name="shape" :options="shapeOptions" />
        </FormRow>
        <FormRow label="disabled">
          <InputSizedText>
            <FormCheckbox name="disabled" :options="colorOptions">
              Disabled
            </FormCheckbox>
          </InputSizedText>
        </FormRow>
        <FormRow label="loading">
          <InputSizedText>
            <FormCheckbox name="loading" :options="colorOptions">
              Loading
            </FormCheckbox>
          </InputSizedText>
        </FormRow>
        <FormRow label="caret">
          <InputSizedText>
            <FormCheckbox name="caret" :options="colorOptions">
              Caret
            </FormCheckbox>
          </InputSizedText>
        </FormRow>
        <FormRow label="icon">
          <InputSizedText>
            <FormCheckbox name="icon" :options="colorOptions">
              Icon
            </FormCheckbox>
          </InputSizedText>
        </FormRow>
      </Uniform>
    </SamplesPropCtl>
  </div>
</template>
