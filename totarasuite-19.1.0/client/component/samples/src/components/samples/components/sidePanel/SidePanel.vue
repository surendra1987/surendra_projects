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
  @module tui
-->

<template>
  <h2>SidePanel</h2>
  Often working with <code>Grid</code> components,
  <code>SidePanel</code> provides extra information to the main content and
  supports open/close interaction.
  <SamplesExample>
    <div class="tui-sidePanelExample">
      <Grid>
        <GridItem :units="6">
          <SidePanel v-bind="config">
            <Grid
              class="tui-sidePanelExample__content"
              direction="vertical"
              :use-vertical-gap="false"
            >
              <GridItem :units="3">
                <h2>Title (original left)</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <h3>Comments</h3>
              </GridItem>
              <GridItem :units="8">
                <p>
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                  Suspendisse sit amet erat ex. Sed ac felis auctor, molestie
                  orci eget, semper tortor. Curabitur non elementum nisi.
                </p>

                <p>
                  Donec sit amet venenatis ligula, at congue massa. In ac dolor
                  ante. Mauris faucibus, nulla consectetur scelerisque
                  efficitur, risus leo pharetra mi, non vehicula tellus elit
                  eget massa. Quisque feugiat eros et aliquam tempus.
                </p>

                <p>
                  Maecenas ut ornare sapien. Nulla sed rutrum ante. Pellentesque
                  habitant morbi tristique senectus et netus et malesuada fames
                  ac turpis egestas.
                </p>

                <p>
                  In turpis purus, feugiat sed commodo vitae, hendrerit in
                  tortor. Ut sed risus dolor. Vestibulum sed sapien ultrices
                  ipsum interdum facilisis nec non augue. Nulla et tellus id
                  ipsum congue aliquet in a quam.
                </p>

                <p>
                  Aenean cursus dolor vitae arcu egestas, vel interdum justo
                  bibendum. Cras convallis nulla sit amet eros interdum, a
                  aliquet metus fermentum. Fusce dictum est libero, vitae
                  rhoncus lectus lacinia sed.
                </p>

                <p>
                  Nunc ullamcorper eros a arcu hendrerit laoreet. Phasellus
                  elementum feugiat orci, sed consequat mauris luctus vel.
                  Praesent id dolor id lorem ultrices feugiat.
                </p>

                <p>
                  Praesent posuere nunc ipsum, egestas vestibulum odio ultrices
                  in. Duis suscipit lorem nisi, sed viverra odio blandit sit
                  amet. Fusce ultrices nibh odio, nec eleifend nibh efficitur
                  in.
                </p>

                <p>
                  Quisque eget lectus pulvinar, ornare enim sit amet, interdum
                  ligula. Donec lobortis magna ut malesuada tincidunt. Aenean
                  consectetur augue sit amet nulla blandit, eget feugiat eros
                  gravida.
                </p>

                <p>
                  Nulla pulvinar commodo congue. Nam sed sem at enim gravida
                  feugiat. Nunc eget lorem feugiat sem iaculis suscipit id ut
                  nisl. Nunc in ullamcorper nulla. Praesent eu sem feugiat est
                  faucibus dapibus. Fusce fermentum ipsum vitae nulla viverra
                  imperdiet.
                </p>
              </GridItem>
              <GridItem :units="1">
                <p>Some tags....</p>
              </GridItem>
            </Grid>
          </SidePanel>
        </GridItem>

        <GridItem :units="6">
          <SidePanel
            :initially-open="true"
            direction="rtl"
            :overflows="false"
            :flush="false"
            chromeless
            animated
          >
            <aside>
              <Grid
                direction="vertical"
                :use-vertical-gap="false"
                :style="{
                  height: '100%',
                }"
              >
                <GridItem :units="3">
                  <h2>Title (original right)</h2>
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                  </p>
                  <h3>Comments</h3>
                </GridItem>
                <GridItem :units="8">
                  <p>
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Suspendisse sit amet erat ex. Sed ac felis auctor, molestie
                    orci eget, semper tortor. Curabitur non elementum nisi.
                  </p>

                  <p>
                    Donec sit amet venenatis ligula, at congue massa. In ac
                    dolor ante. Mauris faucibus, nulla consectetur scelerisque
                    efficitur, risus leo pharetra mi, non vehicula tellus elit
                    eget massa. Quisque feugiat eros et aliquam tempus.
                  </p>

                  <p>
                    Maecenas ut ornare sapien. Nulla sed rutrum ante.
                    Pellentesque habitant morbi tristique senectus et netus et
                    malesuada fames ac turpis egestas.
                  </p>
                </GridItem>
                <GridItem :units="1">
                  <p>Some tags....</p>
                </GridItem>
              </Grid>
            </aside>
          </SidePanel>
        </GridItem>
      </Grid>
    </div>
  </SamplesExample>
  <SamplesCtl>
    <FormRow label="open" helpmsg="Specifies the open state of a side menu.">
      <ToggleSwitch
        v-model:value="config.open"
        aria-label="set open toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>open: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>undefined</FormRowDefaults>
    </FormRow>
    <FormRow label="initiallyOpen">
      <template #help-message>
        If <code>open</code> is undefined, <code>initiallyOpen</code> specifies
        whether a side menu should be opend on mounted.
      </template>
      <ToggleSwitch
        v-model:value="config.initiallyOpen"
        aria-label="set initiallyOpen toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>initiallyOpen: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>false</FormRowDefaults>
    </FormRow>
    <FormRow
      label="animated"
      helpmsg="Specifies whether a side menu has open/close animation."
    >
      <ToggleSwitch
        v-model:value="config.animated"
        aria-label="set animated toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>animated: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>true</FormRowDefaults>
    </FormRow>
    <FormRow
      label="sticky"
      helpmsg="Specifies whether a side menu sticks to top."
    >
      <ToggleSwitch
        v-model:value="config.sticky"
        aria-label="set sticky toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>sticky: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>true</FormRowDefaults>
    </FormRow>
    <FormRow
      label="flush"
      helpmsg="Specifies whether a side menu is flush to the page header and footer."
    >
      <ToggleSwitch
        v-model:value="config.flush"
        aria-label="set flush toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>flush: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>true</FormRowDefaults>
    </FormRow>
    <FormRow
      label="overflows"
      helpmsg="Specifies whether a side menu has scroll bar when the content is too long."
    >
      <ToggleSwitch
        v-model:value="config.overflows"
        aria-label="set overflows toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>overflows: boolean</code>
      </FormRowDetails>
      <FormRowDefaults>true</FormRowDefaults>
    </FormRow>
    <FormRow label="direction" helpmsg="">
      <RadioGroup v-model:value="config.direction" name="direction" horizontal>
        <Radio value="ltr">Left to right</Radio>
        <Radio value="rtl">Right to left</Radio>
      </RadioGroup>
      <FormRowDetails>
        <code>direction: 'ltr'|'rtl'</code>
      </FormRowDetails>
      <FormRowDefaults>ltr</FormRowDefaults>
    </FormRow>
    <FormRow
      label="fixContentWidth"
      helpmsg="Whether the SidePanel's inner content should have a fixed width when its state is expanding or collapsing. When set to true, a fixed-width will be applied, preventing reflow of SidePanel contents during transitions"
    >
      <ToggleSwitch
        v-model:value="config.fixContentWidth"
        aria-label="set fixContentWidth toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>fixContentWidth: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="minHeight"
      helpmsg="Specifies the minimum pixel height of a side menu."
    >
      <InputNumber v-model:value.number="config.minHeight" />
      <FormRowDetails>
        <code>minHeight: number</code>
      </FormRowDetails>
      <FormRowDefaults>
        initial
      </FormRowDefaults>
    </FormRow>
    <FormRow
      label="chromeless"
      helpmsg="Specifies whether to remove a side panel's container style"
    >
      <ToggleSwitch
        v-model:value="config.chromeless"
        aria-label="set chromeless toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>chromeless: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area of a side panel.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="sidepanel-expanding">
      The event is triggered when a side panel start to open.
      <FormRowDetails>
        <code>sidepanel-expanding: () => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="sidepanel-expanded">
      The event is triggered when a side panel is opened.
      <FormRowDetails>
        <code>sidepanel-expanded: () => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="sidepanel-collapsing">
      The event is triggered when a side panel start to close.
      <FormRowDetails>
        <code>sidepanel-collapsing: () => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="sidepanel-collapsed">
      The event is triggered when a side panel is closed.
      <FormRowDetails>
        <code>sidepanel-collapsed: () => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script setup>
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import SidePanel from 'tui/components/sidepanel/SidePanel';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import RadioGroup from 'tui/components/form/RadioGroup';
import Radio from 'tui/components/form/Radio';
import InputNumber from 'tui/components/form/InputNumber';
import { reactive } from 'vue';

const config = reactive({
  direction: 'ltr',
  fixContentWidth: true,
  initiallyOpen: true,
  animated: true,
  sticky: true,
  minHeight: 0,
  flush: true,
  overflows: true,
  chromeless: false,
  open: undefined,
});
</script>

<style lang="scss">
.tui-sidePanelExample {
  height: 100%;

  &__content {
    padding: var(--gap-8);
  }
}
</style>
