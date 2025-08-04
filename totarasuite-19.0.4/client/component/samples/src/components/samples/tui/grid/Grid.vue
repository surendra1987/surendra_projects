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

  @author Dave Wallace <dave.wallace@totaralearning.com>
  @module samples
-->

<template>
  <div class="tui-grid-example">
    <SamplesExample>
      <Grid
        :direction="gridDirection"
        :use-horizontal-gap="useHorizontalGap"
        :use-vertical-gap="useVerticalGap"
        :max-units="maxUnits"
        :stack-at="stackAt"
        :gutter-size-horizontal="gutterSizeHorizontal + 'px'"
        :gutter-size-vertical="gutterSizeVertical + 'px'"
        :class="visualiseGutters ? 'tui-grid--debug' : ''"
        :style="{
          height:
            gridDirection === 'vertical' ? explicitGridHeight + 'px' : 'auto',
        }"
      >
        <GridItem
          v-for="(item, index) in gridItems"
          :key="index"
          :units="gridItems[index].units"
          :grows="gridItems[index].grows"
          :shrinks="gridItems[index].shrinks"
          :overflows="gridItems[index].overflows"
        >
          <Textarea
            :ref="'textarea-' + index"
            :placeholder="'GridItem content'"
            @input="handleTextareaInput(index, $event)"
          />

          <Popover :triggers="['click']">
            <template v-slot:trigger>
              <ButtonIcon
                class="tui-grid-example__itemOptions"
                aria-label="GridItem options"
                size="xs"
                variant="link"
              >
                <EditIcon />
              </ButtonIcon>
            </template>

            <p>Properties for this GridItem</p>
            <Form>
              <FormRow label="Units">
                <InputNumber
                  :placeholder="'(Integer)'"
                  :min="1"
                  :max="parseInt(maxUnits)"
                  @input="handleChange(index, 'units', parseInt($event))"
                />
              </FormRow>
              <FormRow label="Grows">
                <RadioGroup
                  v-model:value="gridItems[index].grows"
                  :horizontal="true"
                  @input="handleChange(index, 'grows', $event)"
                >
                  <Radio :value="true">True</Radio>
                  <Radio :value="false">False (default)</Radio>
                </RadioGroup>
              </FormRow>
              <FormRow label="Shrinks">
                <RadioGroup
                  v-model:value="gridItems[index].shrinks"
                  :horizontal="true"
                  @input="handleChange(index, 'shrinks', $event)"
                >
                  <Radio :value="false">False</Radio>
                  <Radio :value="true">True (default)</Radio>
                </RadioGroup>
              </FormRow>
              <FormRow label="Overflows">
                <RadioGroup
                  v-model:value="gridItems[index].overflows"
                  :horizontal="true"
                  @input="handleChange(index, 'overflows', $event)"
                >
                  <Radio :value="true">True</Radio>
                  <Radio :value="false">False (default)</Radio>
                </RadioGroup>
              </FormRow>
              <FormRow label="Order">
                <em>Supported, but refer to implementation</em>
              </FormRow>
              <FormRow label="HTML tag">
                <em>Supported, but refer to implementation</em>
              </FormRow>
            </Form>
          </Popover>
        </GridItem>
      </Grid>

      <br />
      <p>
        <Button text="Add GridItem" @click="addGridItem" />
        <Button text="Remove GridItem" @click="removeGridItem" />
      </p>

      <ToggleSwitch v-model:value="visualiseGutters" text="Visualise gutters" />
      <br />
    </SamplesExample>

    <SamplesPropCtl>
      <FormRow label="Max GridItem units before wrapping">
        <RadioGroup v-model:value="maxUnits" :horizontal="true">
          <Radio :value="'12'">12 (default)</Radio>
          <Radio :value="'16'">16</Radio>
        </RadioGroup>
      </FormRow>
      <FormRow label="Direction">
        <RadioGroup v-model:value="gridDirection" :horizontal="true">
          <Radio :value="'horizontal'">Horizontal (default)</Radio>
          <Radio :value="'vertical'">Vertical</Radio>
        </RadioGroup>

        <Range
          v-if="gridDirection === 'vertical'"
          v-model:value="explicitGridHeight"
          :min="600"
          :max="2000"
        />
      </FormRow>
      <FormRow label="Use a horizontal gap">
        <RadioGroup v-model:value="useHorizontalGap" :horizontal="true">
          <Radio :value="true">True (default)</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>
      <FormRow label="Use a vertical gap">
        <RadioGroup v-model:value="useVerticalGap" :horizontal="true">
          <Radio :value="true">True (default)</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>
      <FormRow label="Become stacked at width (px)">
        <RadioGroup v-model:value="stackAt" :horizontal="true">
          <Radio :value="0">0 (default)</Radio>
          <Radio :value="400">400</Radio>
        </RadioGroup>
      </FormRow>
      <FormRow label="Gutter between horizontal GridItems">
        <Range v-model:value="gutterSizeHorizontal" :min="5" :max="50" />
      </FormRow>
      <FormRow label="Gutter between vertical GridItems">
        <Range v-model:value="gutterSizeVertical" :min="5" :max="50" />
      </FormRow>

      <FormRow label="HTML tag for Grid container">
        <em>Supported, but refer to implementation</em>
      </FormRow>
    </SamplesPropCtl>
  </div>
</template>

<script>
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import InputNumber from 'tui/components/form/InputNumber';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import Textarea from 'tui/components/form/Textarea';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import Range from 'tui/components/form/Range';
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import EditIcon from 'tui/components/icons/Edit';
import Popover from 'tui/components/popover/Popover';

export default {
  components: {
    Grid,
    GridItem,
    SamplesExample,
    SamplesPropCtl,
    Form,
    FormRow,
    Radio,
    RadioGroup,
    Range,
    InputNumber,
    Textarea,
    Button,
    ButtonIcon,
    EditIcon,
    Popover,
    ToggleSwitch,
  },

  data() {
    return {
      /**
       * Main Grid
       **/
      gridDirection: 'horizontal',
      useHorizontalGap: true,
      useVerticalGap: true,
      maxUnits: '12',
      stackAt: 0,
      gutterSizeHorizontal: 15,
      gutterSizeVertical: 15,
      explicitGridHeight: 600,
      /**
       * GridItems
       **/
      stubGridItem: {
        units: 4,
        grows: false,
        shrinks: true,
        overflows: false,
      },
      // Modified dynamically by add/remove buttons and also individual GridItem
      // prop settings popover component
      gridItems: [
        {
          units: 4,
          grows: false,
          shrinks: true,
          overflows: false,
        },
        {
          units: 4,
          grows: false,
          shrinks: true,
          overflows: false,
        },
        {
          units: 4,
          grows: false,
          shrinks: true,
          overflows: false,
        },
      ],
      visualiseGutters: false,
    };
  },

  methods: {
    /**
     * Adjust the size of the GridItem Textarea component so that we can show
     * the effects of `overflows` prop
     **/
    handleTextareaInput: function(index) {
      // need to use `$refs[*][0].$el` instead of just `$refs[*][0]` as the ref
      // returned from the latter refers to the Textarea Vue component, not the
      // actual DOM element
      let ref = this.$refs['textarea-' + index][0].$el;
      ref.style.height = '1px';
      ref.style.height = ref.scrollHeight + 'px';
    },

    /**
     * Update a GridItem's :units property reactively
     **/
    handleChange: function(index, prop, eventValue) {
      if (eventValue === null || typeof eventValue === 'undefined') {
        return;
      }

      // re-create gridItems Array index to invoke reactivity
      let newObj = this.createNewGridItem(this.gridItems[index]);
      newObj[prop] = eventValue;
      this.gridItems[index] = newObj;
    },

    /**
     * Adds a GridItem to the example Grid
     **/
    addGridItem: function() {
      this.gridItems.push(this.createNewGridItem());
    },

    /**
     * Removes a GridItem to the example Grid
     **/
    removeGridItem: function() {
      if (!this.gridItems.length) {
        return;
      }
      this.gridItems.pop();
    },

    /**
     * Create a new GridItem object with stub values
     **/
    createNewGridItem: function(oldItem) {
      let obj = {};
      Object.assign(obj, oldItem || this.stubGridItem);
      return obj;
    },
  },
};
</script>
<style lang="scss">
.tui-grid-example {
  .tui-grid-item {
    position: relative; /* parental control of popover trigger position */
    display: flex;
    background-color: var(--color-neutral-4);

    textarea {
      overflow: hidden;
    }

    .tui-popoverFrame {
      width: 500px;
      max-width: 500px;
    }
  }

  &__itemOptions {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 1;
  }

  .tui-grid--debug .tui-grid-item {
    border-color: var(--color-prompt-info);

    &--wrapped-gap .tui-grid-item--wrapped {
      border-color: var(--color-prompt-info);
    }
  }
}
</style>
