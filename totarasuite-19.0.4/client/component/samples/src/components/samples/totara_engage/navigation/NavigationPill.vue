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

  @author Jack Humphrey <jack.humphrey@totara.com>
  @module samples
-->

<template>
  <div class="tui-samples-navigationPill">
    A pill style navigation component
    <SamplesExample>
      <nav class="tui-samples-navigationPill__pillBar">
        <NavigationPill
          text="Default"
          :selected="selected == 1"
          @click="setSelected(1)"
        />
        <NavigationPill
          text="Selected"
          :selected="selected == 2"
          @click="setSelected(2)"
        />
        <NavigationPill
          text="Default"
          :selected="selected == 3"
          @click="setSelected(3)"
        />
        <NavigationPill text="Disabled" disabled />
      </nav>
    </SamplesExample>

    <SamplesCode>
      <template v-slot:template>{{ codeTemplate }}</template>
      <template v-slot:script>{{ codeScript }}</template>
      <template v-slot:style>{{ codeStyle }}</template>
    </SamplesCode>
  </div>
</template>

<script>
import NavigationPill from 'totara_engage/components/navigation/NavigationPill';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCode from 'samples/components/sample_parts/misc/SamplesCode';

export default {
  components: {
    NavigationPill,
    SamplesExample,
    SamplesCode,
  },

  data() {
    return {
      selected: 2,
      codeTemplate: `<nav class="tui-samples-navigationPill__pillBar">
  <NavigationPill
    text="Default"
    :selected="selected == 1"
    @click="setSelected(1)"
  />
  <NavigationPill
    text="Selected"
    :selected="selected == 2"
    @click="setSelected(2)"
  />
  <NavigationPill
    text="Default"
    :selected="selected == 3"
    @click="setSelected(3)"
  />
  <NavigationPill text="Disabled" disabled />
</nav>
`,
      codeScript: `import NavigationPill from 'totara_engage/components/navigation/NavigationPill';

export default {
  components: {
    NavigationPill,
  },

  data() {
    return {
      selected: 2,
    };
  },

  methods: {
    setSelected(option) {
      this.selected = option;
    },
  },
};`,
      codeStyle: `.tui-samples-navigationPill {
  &__pillBar {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gap-3);
  }
}`,
    };
  },

  methods: {
    setSelected(option) {
      this.selected = option;
    },
  },
};
</script>

<style lang="scss">
.tui-samples-navigationPill {
  &__pillBar {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gap-3);
  }
}
</style>
