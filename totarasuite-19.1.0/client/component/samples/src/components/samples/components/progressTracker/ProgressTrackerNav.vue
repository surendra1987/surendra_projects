<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module samples
-->

<script setup>
import { ref } from 'vue';
import Checkbox from 'tui/components/form/Checkbox';
import CheckboxGroup from 'tui/components/form/CheckboxGroup';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import ProgressTrackerNav from 'tui/components/progresstracker/ProgressTrackerNav';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import Range from 'tui/components/form/Range';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SuccessIcon from 'tui/components/icons/Success';

// hack to ensure popover re-renders if the v-model:value data for `popoverTriggerType` changes
import { uniqueId } from 'tui/util';

const showCustomIcon = ref(false);
const showCustomPopoverContent = ref(false);
const gap = ref('medium');
const size = ref('medium');
const forceVertical = ref(true);
const labelOpensPopover = ref(false);
const popoverTriggerType = ref(['click']);
const workflowItemsToShow = ref(16);
const itemContentFullWidth = ref(false);
const itemContentOverflowHidden = ref(false);
const uncontainedPopover = ref(false);
const itemsWorkflow = ref([
  {
    id: 1,
    description: 'Workflow Step 1',
    states: ['ready'],
  },
  {
    id: 2,
    description: 'Workflow Step 2',
    states: ['locked'],
  },
  {
    id: 3,
    description: 'Workflow Step 3',
    states: ['done'],
  },
  {
    id: 4,
    description: 'Workflow Step 4',
    states: ['optional'],
  },
  {
    id: 5,
    description: 'Workflow Step 5',
    states: ['invalid'],
  },
  {
    id: 6,
    description: 'Workflow Step 6',
    states: ['ready', 'selected'],
  },
  {
    id: 7,
    description: 'Workflow Step 7',
    states: ['locked', 'selected'],
  },
  {
    id: 8,
    description: 'Workflow Step 8',
    states: ['done', 'selected'],
  },
  {
    id: 9,
    description: 'Workflow Step 9',
    states: ['optional', 'selected'],
  },
  {
    id: 10,
    description: 'Workflow Step 10',
    states: ['invalid', 'selected'],
  },
  {
    id: 11,
    description: 'Workflow Step 11',
    states: ['locked', 'optional'],
  },
  {
    id: 12,
    description: 'Workflow Step 12',
    states: ['locked', 'optional', 'selected'],
  },
  {
    id: 13,
    description: 'Workflow Step 13',
    states: ['selected'],
  },
  {
    id: 14,
    description: 'Workflow Step 14',
    states: ['view_only'],
  },
  {
    id: 15,
    description: 'Workflow Step 15',
    states: ['hidden'],
  },
  {
    id: 16,
    description: 'Workflow Step 16',
    states: ['hidden', 'selected'],
  },
]);
const achievementItemsToShow = ref(5);
const itemsAchievement = ref([
  {
    id: 1,
    description: 'Achievement Step 1',
    label: 'Achievement Step 1',
    states: ['pending'],
  },
  {
    id: 2,
    description: 'Achievement Step 2',
    label: 'Achievement Step 2',
    states: ['pending', 'target'],
  },
  {
    id: 3,
    description: 'Achievement Step 3',
    label: 'Achievement Step 3',
    states: ['complete'],
  },
  {
    id: 4,
    description: 'Achievement Step 3',
    label: 'Achievement Step 3',
    states: ['complete', 'current'],
  },
  {
    id: 5,
    description: 'Achievement Step 4',
    label: 'Achievement Step 4',
    states: ['achieved'],
  },
  {
    id: 6,
    description: 'Achievement Step 4',
    label: 'Achievement Step 4',
    states: ['achieved', 'current'],
  },
  {
    id: 7,
    description: 'Achievement Step 4',
    label: 'Achievement Step 4',
    states: ['achieved', 'target'],
  },
  {
    id: 8,
    description: 'Achievement Step 4',
    label: 'Achievement Step 4',
    states: ['achieved', 'target', 'current'],
  },
]);

function getUniqueId() {
  return uniqueId();
}
</script>

<template>
  <SamplesExample>
    <div
      class="tui-samples__progressTrackerNav"
      :class="
        forceVertical
          ? 'tui-samples__progressTrackerNav--vertical'
          : 'tui-samples__progressTrackerNav--horizontal'
      "
    >
      <div class="tui-samples__progressTrackerNav__content">
        <Range
          v-model:value="workflowItemsToShow"
          :value="workflowItemsToShow"
          :min="1"
          :max="itemsWorkflow.length"
        />
        <br />
        <ProgressTrackerNav
          :key="getUniqueId()"
          :force-vertical="forceVertical"
          :gap="gap"
          :item-content-full-width="itemContentFullWidth"
          :item-content-overflow-hidden="itemContentOverflowHidden"
          :items="itemsWorkflow.slice(0, workflowItemsToShow)"
          :label-opens-popover="labelOpensPopover"
          :popover-trigger-type="popoverTriggerType"
          :size="size"
          :uncontained-popover="uncontainedPopover"
          marker-mode="workflow"
        >
          <template v-if="showCustomIcon" v-slot:icon>
            <SuccessIcon
              :alt="$str('completionstatus_done', 'totara_tui')"
              :size="100"
              class="tui-samples__progressTrackerNav__icon--done"
            />
          </template>

          <template
            v-if="showCustomPopoverContent"
            v-slot:custom-popover-content
          >
            Custom popover content
          </template>

          <template v-slot="{ entry }">
            <!--
                ProgressTrackerNav doesn't know about what is going into its
                ProgressTrackerNavItems, but will iterate over entries and drop in
                the supplied content to a new ProgressTrackerNavItem for each entry.
              -->
            States are:<br />{{ entry.states }}
          </template>
        </ProgressTrackerNav>
      </div>
      <div class="tui-samples__progressTrackerNav__content">
        <Range
          v-model:value="achievementItemsToShow"
          :value="achievementItemsToShow"
          :min="1"
          :max="itemsAchievement.length"
        />
        <br />

        <ProgressTrackerNav
          :key="getUniqueId()"
          :force-vertical="forceVertical"
          :gap="gap"
          :item-content-full-width="itemContentFullWidth"
          :item-content-overflow-hidden="itemContentOverflowHidden"
          :items="itemsAchievement.slice(0, achievementItemsToShow)"
          :label-opens-popover="labelOpensPopover"
          :popover-trigger-type="popoverTriggerType"
          :size="size"
          :uncontained-popover="uncontainedPopover"
        >
          <template v-if="showCustomIcon" v-slot:icon>
            <SuccessIcon
              :alt="$str('completionstatus_done', 'totara_tui')"
              :size="100"
              class="tui-samples__progressTrackerNav__icon--done"
            />
          </template>

          <template
            v-if="showCustomPopoverContent"
            v-slot:custom-popover-content
          >
            Custom popover content
          </template>

          <template v-slot="{ entry }">
            States are:<br />{{ entry.states }}
          </template>
        </ProgressTrackerNav>
      </div>
    </div>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Gap">
      <RadioGroup v-model:value="gap" :horizontal="true">
        <Radio value="small">Small</Radio>
        <Radio value="medium">Medium</Radio>
        <Radio value="large">Large</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        gap
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Size"
      helpmsg="Size only applies in horizontal mode"
    >
      <RadioGroup
        v-model:value="size"
        :disabled="forceVertical"
        :horizontal="true"
      >
        <Radio value="small">Small</Radio>
        <Radio value="medium">Medium</Radio>
        <Radio value="large">Large</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        size
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Item content full width">
      <RadioGroup v-model:value="itemContentFullWidth" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        itemContentFullWidth
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Item content overflow hidden">
      <RadioGroup v-model:value="itemContentOverflowHidden" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        itemContentOverflowHidden
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Force vertical">
      <RadioGroup v-model:value="forceVertical" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        forceVertical
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Label opens popover">
      <RadioGroup v-model:value="labelOpensPopover" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        labelOpensPopover
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Popover trigger type">
      <CheckboxGroup v-model:value="popoverTriggerType">
        <Checkbox value="click">Click</Checkbox>
        <Checkbox value="hover">Hover</Checkbox>
      </CheckboxGroup>
      <FormRowDetails :id="id">
        popoverTriggerType
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Uncontained popover">
      <RadioGroup v-model:value="uncontainedPopover" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        uncontainedPopover
      </FormRowDetails>
    </FormRow>

    <FormRow label="Marker mode">
      <p>
        <em
          >Supported but each mode has validators on allowed progressItem
          states, refer to implementation.</em
        >
      </p>
    </FormRow>
  </SamplesCtl>

  <SamplesCtl label="Slot options">
    <FormRow
      v-slot="{ id }"
      label="Show custom icon"
      helpmsg="Providing an empty slot removes the default icon"
    >
      <RadioGroup v-model:value="showCustomIcon" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        icon
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Custom popover content"
      helpmsg="By default the popover content is the description of the item"
    >
      <RadioGroup v-model:value="showCustomPopoverContent" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        custom-popover-content
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<style lang="scss">
.tui-samples__progressTrackerNav {
  display: flex;
  gap: 50px;

  &--vertical {
    flex-direction: row;

    .tui-samples__progressTrackerNav__content {
      width: calc(50% - 50px);
    }
  }
  &--horizontal {
    flex-direction: column;
  }
}

.tui-samples__progressTrackerNav__icon-done {
  width: rem-px(16);
  height: rem-px(16);
  margin-top: 2px;
}
</style>
