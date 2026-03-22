<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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
    <h2>CollapsibleGroupToggle</h2>
    Collapsible group toggle button, for expanding or collasping multiple
    regions at once.

    <SamplesExample>
      <ToggleSwitch
        v-model:value="expanded.first"
        aria-label="set indentContents toggle"
        text="Toggle first collapsible"
      />
      <ToggleSwitch
        v-model:value="expanded.second"
        aria-label="set indentContents toggle"
        text="Toggle second collapsible"
      />
      <ToggleSwitch
        v-model:value="expanded.third"
        aria-label="set indentContents toggle"
        text="Toggle third collapsible"
      />
      <CollapsibleGroupToggle v-model:value="expanded" v-bind="config" />

      <Collapsible v-model:value="expanded.first" label="ABC">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Nisi est sit amet
        facilisis magna etiam tempor orci eu. Imperdiet sed euismod nisi porta.
        Odio ut enim blandit volutpat maecenas volutpat blandit aliquam etiam.
        Justo eget magna fermentum iaculis eu non diam phasellus. Aenean et
        tortor at risus. Velit aliquet sagittis id consectetur purus ut. In hac
        habitasse platea dictumst quisque sagittis. Morbi tempus iaculis urna id
        volutpat. Lacinia at quis risus sed vulputate odio ut enim blandit.
        Velit dignissim sodales ut eu sem. Eu mi bibendum neque egestas congue
        quisque egestas. Dapibus ultrices in iaculis nunc sed augue lacus
        viverra. Vel turpis nunc eget lorem. Mollis nunc sed id semper risus in.
        Donec ac odio tempor orci. Id semper risus in hendrerit gravida rutrum
        quisque. Aenean euismod elementum nisi quis eleifend quam.
      </Collapsible>

      <Collapsible v-model:value="expanded.second" label="DEF">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Duis ut diam quam
        nulla porttitor massa id neque. Cursus risus at ultrices mi tempus
        imperdiet nulla.
      </Collapsible>

      <Collapsible v-model:value="expanded.third" label="GHI">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
        tempor incididunt ut labore et dolore magna aliqua. Duis ut diam quam
        nulla porttitor massa id neque. Cursus risus at ultrices mi tempus
        imperdiet nulla.
      </Collapsible>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="v-model:value"
        required
        helpmsg="The two-way binding value of a collapsible group. Assign each property to the collapsible blocks inside to control them individually."
      >
        <pre>{{ expanded }}</pre>
        <FormRowDetails :id="id">
          <code>v-model:value</code><br />
          <code>{[key: string]: boolean}</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="id" helpmsg="A collapsible block's id.">
        <InputText :id="id" v-model:value="config.id" />
        <FormRowDetails :id="id">
          <code>id: string|number</code>
        </FormRowDetails>
        <FormRowDefaults>generatedId()</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Button transparent"
        helpmsg="Specifies the 'Expand All' button's transparent style."
      >
        <ToggleSwitch
          v-model:value="config.transparent"
          aria-label="set transparent toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>transparent: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Align to end"
        helpmsg="Specifies the 'Expand All' button's alignment."
      >
        <ToggleSwitch
          v-model:value="config.alignEnd"
          aria-label="set alignEnd toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>alignEnd: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow v-slot="{ id }" label="Input">
        Triggered when a toggle button is clicked.
        <FormRowDetails :id="id">
          <code>input: (value) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Collapsible from 'tui/components/collapsible/Collapsible';
import CollapsibleGroupToggle from 'tui/components/collapsible/CollapsibleGroupToggle';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    Collapsible,
    CollapsibleGroupToggle,
    FormRow,
    FormRowDetails,
    SamplesExample,
    SamplesCtl,
    FormRowDefaults,
    ToggleSwitch,
    InputText,
  },

  data() {
    return {
      expanded: {
        first: false,
        second: true,
        third: false,
      },
      config: {
        id: '',
        transparent: false,
        alignEnd: true,
      },
    };
  },
};
</script>
