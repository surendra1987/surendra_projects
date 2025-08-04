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
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import Button from 'tui/components/buttons/Button';
import { Uniform, FormRow, FormText } from 'tui/components/uniform';
import InputText from 'tui/components/form/InputText';
import SettingsIcon from 'tui/components/icons/Settings';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
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
  href: '#',
  autoFocus: false,
  ariaLabel: '',
  ariaDisabled: false,
});
</script>

<template>
  <h2>Button</h2>
  A configurable Totara button

  <SamplesExample>
    <Button v-bind="values">
      <template v-if="values.icon" v-slot:icon>
        <SettingsIcon :size="values.size === 'xs' ? null : 200" />
      </template>
    </Button>
  </SamplesExample>

  <SamplesCtl>
    <Uniform :initial-values="values" @change="v => (values = v)">
      <FormRow v-slot="{ id }" label="Text">
        <FormText name="text" />
        <FormRowDetails :id="id">
          <code>text: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Variant">
        <RadioGroup v-model:value="values.variant" horizontal>
          <Radio v-for="opt in variantOptions" :key="opt.id" :value="opt.id">{{
            opt.label
          }}</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>variant: 'default'|'primary'|'stealth'|'link'</code>
        </FormRowDetails>
        <FormRowDefaults>default</FormRowDefaults>
      </FormRow>
      <FormRow v-slot="{ id }" label="Size">
        <RadioGroup v-model:value="values.size" horizontal>
          <Radio v-for="opt in sizeOptions" :key="opt.id" :value="opt.id">{{
            opt.label
          }}</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>size: 'default'|'sm'|'xs'</code>
        </FormRowDetails>
        <FormRowDefaults>default</FormRowDefaults>
      </FormRow>
      <FormRow v-slot="{ id }" label="Color">
        <RadioGroup v-model:value="values.color" horizontal>
          <Radio v-for="opt in colorOptions" :key="opt.id" :value="opt.id">{{
            opt.label
          }}</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>color: 'default'|'danger'</code>
        </FormRowDetails>
        <FormRowDefaults>default</FormRowDefaults>
      </FormRow>
      <FormRow v-slot="{ id }" label="Shape">
        <RadioGroup v-model:value="values.shape" horizontal>
          <Radio v-for="opt in shapeOptions" :key="opt.id" :value="opt.id">{{
            opt.label
          }}</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>shape: 'default'|'pill'|'circle'</code>
        </FormRowDetails>
        <FormRowDefaults>default</FormRowDefaults>
      </FormRow>
      <FormRow v-slot="{ id }" label="Disabled">
        <ToggleSwitch
          v-model:value="values.disabled"
          aria-label="set disabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>disabled: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Loading">
        <ToggleSwitch
          v-model:value="values.loading"
          aria-label="set loading toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>loading: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Caret">
        <ToggleSwitch
          v-model:value="values.caret"
          aria-label="set caret toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>caret</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id, label }" label="Link">
        <InputText
          :id="id"
          v-model:value="values.href"
          :placeholder="label"
          disabled
        />
        <FormRowDetails :id="id">
          <code>href: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Auto focus">
        <ToggleSwitch
          v-model:value="values.autoFocus"
          aria-label="set autoFocus toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>autoFocus: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id, label }" label="Aria label">
        <InputText
          :id="id"
          v-model:value="values.ariaLabel"
          :placeholder="label"
        />
        <FormRowDetails :id="id">
          <code>ariaLabel: stirng</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Aria disabled">
        <ToggleSwitch
          v-model:value="values.ariaDisabled"
          aria-label="set ariaDisabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>ariaDisabled: boolean</code>
        </FormRowDetails>
      </FormRow>
    </Uniform>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The text content area for a button.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="icon">
      The icon area before a button's text.
      <FormRowDetails>
        <code>icon</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="icon-after">
      The icon area after a button's text where the caret icon sits in default.
      <FormRowDetails>
        <code>icon-after</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="click">
      Triggered when a button is clicked.
      <FormRowDetails>
        <code>click: (event) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>
