<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module samples
-->

<template>
  <div>
    Standard tabs, usually used for admin screens.

    <SamplesExample>
      <p>
        <Button text="Add tab" @click="addTab" />
        <Button text="Remove tab" @click="removeTab" />
      </p>
      <Tabs :direction="direction" :small-tabs="smallTabs">
        <Tab
          v-for="tab in tabs"
          :id="tab.id"
          :key="tab.id"
          :name="tab.name"
          :disabled="disabledId === tab.id"
          :label-extra-text="
            tab.id === 2 ? ' (' + $str('error', 'core') + ')' : null
          "
        >
          <template v-if="tab.id === 2" v-slot:label-extra>
            <InvalidIcon state="alert" :alt="$str('error', 'core')" />
          </template>
          {{ tab.content }}
        </Tab>
      </Tabs>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Direction">
        <RadioGroup v-model:value="direction" :horizontal="true">
          <Radio value="vertical">Vertical</Radio>
          <Radio value="horizontal">Horizontal</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          direction
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Disabled">
        <RadioGroup v-model:value="disabledId" :horizontal="true">
          <Radio :value="0">None</Radio>
          <Radio :value="1">1</Radio>
          <Radio :value="2">2</Radio>
          <Radio :value="3">3</Radio>
          <Radio :value="4">4</Radio>
          <Radio :value="5">5</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          disabled
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Small tabs">
        <RadioGroup v-model:value="smallTabs" :horizontal="true">
          <Radio :value="false">Normal</Radio>
          <Radio :value="true">Small</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          smallTabs
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import InvalidIcon from 'tui/components/icons/Invalid';

export default {
  components: {
    Button,
    FormRow,
    FormRowDetails,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
    Tab,
    Tabs,
    InvalidIcon,
  },

  data() {
    const content = x => 'tab content ' + x;
    return {
      tabs: [
        { id: 1, name: 'Attendees', content: content(1) },
        { id: 2, name: 'Wait-list', content: content(2) },
        { id: 3, name: 'Cancellations', content: content(3) },
        { id: 4, name: 'Take attendance', content: content(4) },
        { id: 5, name: 'Message users', content: content(5) },
      ],
      selectedId: 1,
      disabledId: 0,
      direction: 'horizontal',
      smallTabs: false,
    };
  },

  methods: {
    addTab() {
      this.tabs.push({
        id: 'x' + Math.random(),
        name: 'New tab',
        content: 'Hello from new tab',
      });
    },

    removeTab() {
      this.tabs.pop();
    },
  },
};
</script>
