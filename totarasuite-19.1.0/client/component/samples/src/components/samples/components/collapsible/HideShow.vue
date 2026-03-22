<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

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
    <h2>HideShow</h2>
    Hide show component for collapsing additional content.

    <SamplesExample>
      <HideShow
        aria-hide-content-text="Hide this extra content"
        aria-show-content-text="Show this extra content"
        v-bind="config"
      >
        <template v-slot:trigger="{ controls, text, toggleContent }">
          <Button
            :aria-controls="controls"
            :styleclass="{ primary: true, small: true, transparent: true }"
            :text="text"
            @click="toggleContent"
          />
        </template>

        <template v-slot:content>
          <div>
            Collapsible content on mobile
            <ol>
              <li>1</li>
              <li>2</li>
              <li>3</li>
              <li>4</li>
              <li>5</li>
              <li>6</li>
              <li>7</li>
              <li>8</li>
              <li>9</li>
              <li>10</li>
              <li>11</li>
              <li>12</li>
              <li>13</li>
              <li>14</li>
              <li>15</li>
              <li>16</li>
              <li>17</li>
              <li>18</li>
              <li>19</li>
              <li>20</li>
            </ol>
          </div>
        </template>
      </HideShow>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Collapsible only for mobile">
        <ToggleSwitch
          v-model:value="config.mobileOnly"
          aria-label="set mobileOnly toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>mobileOnly: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Display content before toggle">
        <ToggleSwitch
          v-model:value="config.contentBeforeToggle"
          aria-label="set contentBeforeToggle toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>contentBeforeToggle: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Narrow trigger">
        <ToggleSwitch
          v-model:value="config.narrowTrigger"
          aria-label="set narrowTrigger toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>narrowTrigger: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Toggle sticky">
        <ToggleSwitch
          v-model:value="config.sticky"
          aria-label="set sticky toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>sticky: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Initially expanded">
        <ToggleSwitch
          v-model:value="config.initiallyExpanded"
          aria-label="set initiallyExpanded toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>initiallyExpanded: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Show content Text"
        helpmsg="The button text of a show button"
      >
        <InputText :id="id" v-model:value="config.showContentText" />
        <FormRowDetails :id="id">
          <code>showContentText: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Hide content Text"
        helpmsg="The button text of a hide button"
      >
        <InputText :id="id" v-model:value="config.hideContentText" />
        <FormRowDetails :id="id">
          <code>hideContentText: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Aria region label"
        helpmsg="The aria-label of a Hide/Show toggle"
      >
        <InputText :id="id" v-model:value="config.ariaRegionLabel" />
        <FormRowDetails :id="id">
          <code>ariaRegionLabel: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="Content">
        The main content area of a hide/show block.
        <FormRowDetails>
          <code>content</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="Trigger">
        The trigger content of a hide/show block.
        <FormRowDetails>
          <code>#trigger = "{controls, expanded, text, toggleContent}"</code
          ><br />
          <code>controls: string</code> The element id of the trigger<br />
          <code>expanded: boolean</code> Whether the content is expanded<br />
          <code>text: string</code> The show or hide text of the trigger<br />
          <code>toggleContent: ()=>void</code> The methos to toggle content
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import HideShow from 'tui/components/collapsible/HideShow';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    Button,
    FormRow,
    FormRowDetails,
    HideShow,
    SamplesExample,
    SamplesCtl,
    ToggleSwitch,
    FormRowDefaults,
    InputText,
  },

  data() {
    return {
      config: {
        ariaRegionLabel: 'content region description',
        hideContentText: 'Hide content',
        showContentText: 'Show content',
        mobileOnly: false,
        narrowTrigger: false,
        sticky: false,
        contentBeforeToggle: false,
        initiallyExpanded: false,
      },
    };
  },
};
</script>
