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
  <h2>SidePanelNav</h2>
  A navigation menu integrated inside a <code>SidePanel</code> component. A side
  panel nav normally contains <code>SidePanelNavButtonItem</code>,
  <code>SidePanelNavLinkItem</code> and <code>SidePanelNavGroup</code>.
  <SamplesExample class="tui-sidePanelNavExample">
    <Grid>
      <GridItem :units="3">
        <SidePanel
          :initially-open="true"
          :show-button-control="false"
          :sticky="false"
        >
          <SidePanelNav
            v-model:value="selectedItem"
            v-bind="configNav"
            @change="navChange"
          >
            <SidePanelNavGroup
              v-for="(group, id) in itemGroups"
              :key="id"
              :title="group.title || null"
            >
              <template v-if="id == 0" v-slot:heading-side>
                <ButtonIcon
                  :aria-label="'More'"
                  :styleclass="{ circle: true, xsmall: true }"
                >
                  <AddIcon />
                </ButtonIcon>
              </template>

              <template v-if="group.type == 'links'">
                <SidePanelNavLinkItem
                  v-for="item in group.items"
                  :id="item.id"
                  :key="item.id"
                  :text="item.text"
                  :url="item.action"
                >
                  <ButtonIcon
                    v-if="item.id == 11"
                    :aria-label="'More'"
                    :styleclass="{ circle: true, xsmall: true }"
                  >
                    <AddIcon />
                  </ButtonIcon>
                </SidePanelNavLinkItem>
              </template>

              <template v-else>
                <SidePanelNavButtonItem
                  v-for="item in group.items"
                  :id="item.id"
                  :key="item.id"
                  :text="item.text"
                  :action="item.action"
                >
                  <ButtonIcon
                    v-if="item.id == 8"
                    :aria-label="'More'"
                    :styleclass="{ circle: true, xsmall: true }"
                  >
                    <AddIcon />
                  </ButtonIcon>
                </SidePanelNavButtonItem>
              </template>
            </SidePanelNavGroup>
          </SidePanelNav>
        </SidePanel>
      </GridItem>

      <GridItem :units="9" :shrinks="true">
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
          sit amet erat ex. Sed ac felis auctor, molestie orci eget, semper
          tortor. Curabitur non elementum nisi. Donec sit amet venenatis ligula,
          at congue massa. In ac dolor ante. Mauris faucibus, nulla consectetur
          scelerisque efficitur, risus leo pharetra mi, non vehicula tellus elit
          eget massa. Quisque feugiat eros et aliquam tempus.
        </p>

        <p>
          Maecenas ut ornare sapien. Nulla sed rutrum ante. Pellentesque
          habitant morbi tristique senectus et netus et malesuada fames ac
          turpis egestas. In turpis purus, feugiat sed commodo vitae, hendrerit
          in tortor. Ut sed risus dolor. Vestibulum sed sapien ultrices ipsum
          interdum facilisis nec non augue. Nulla et tellus id ipsum congue
          aliquet in a quam. Aenean cursus dolor vitae arcu egestas, vel
          interdum justo bibendum. Cras convallis nulla sit amet eros interdum,
          a aliquet metus fermentum. Fusce dictum est libero, vitae rhoncus
          lectus lacinia sed. Nunc ullamcorper eros a arcu hendrerit laoreet.
          Phasellus elementum feugiat orci, sed consequat mauris luctus vel.
          Praesent id dolor id lorem ultrices feugiat.
        </p>
      </GridItem>
    </Grid>
  </SamplesExample>
  <SamplesCtl>
    <FormRow
      label="v-model:value"
      required
      helpmsg="The two-way binding value of the selected nav item id."
    >
      <InputText v-model:value="selectedItem" />
      <FormRowDetails>
        <code>v-model:value</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="ariaLabel"
      helpmsg="Specifies the aria-label of a side menu."
    >
      <InputText v-model:value="configNav.ariaLabel" />
      <FormRowDetails>
        <code>aria-label: string</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area for the nav items, mainly <code>SidePanelNavGroup</code>,
      <code>SidePanelNavLinkItem</code> and <code>SidePanelNavButtonItem</code>.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="input">
      Triggered when a nav item is selected.
      <FormRowDetails>
        <code>input: (id) => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="change">
      Triggered when a nav item is selected.
      <FormRowDetails>
        <code>change: ({ action: string, id: number|string }) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <h2>SidePanelNavButtonItem</h2>
  The button type nav item in a <code>SidePanelNav</code> component.
  <SamplesExample>
    <SidePanelNavButtonItem v-bind="configBtn">
      <ButtonIcon
        :aria-label="'More'"
        :styleclass="{ circle: true, xsmall: true }"
      >
        <AddIcon />
      </ButtonIcon>
    </SidePanelNavButtonItem>
  </SamplesExample>
  <SamplesCtl>
    <FormRow required label="id" helpmsg="Specifies a nav button's unique id.">
      <InputText v-model:value="configBtn.id" />
      <FormRowDetails>
        <code>id: string|number</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="action"
      helpmsg="Specifies a nav button's action name, which will be emitted along with its id through the change event on a side nav panel."
    >
      <InputText v-model:value="configBtn.action" />
      <FormRowDetails>
        <code>action: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="text" helpmsg="Specifies a nav button's label text.">
      <InputText v-model:value="configBtn.text" />
      <FormRowDetails>
        <code>text: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="notification"
      helpmsg="Specifies whether show the notification indicator on a nav button."
    >
      <ToggleSwitch
        v-model:value="configBtn.notification"
        aria-label="set notification toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>notification: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="notificationText"
      helpmsg="Specifies a nav button's notification text for screen reader and accessibility purpose."
    >
      <InputText v-model:value="configBtn.notificationText" />
      <FormRowDetails>
        <code>notificationText: string</code>
      </FormRowDetails>
      <FormRowDefaults
        >getString('updated_recently', 'totara_core')</FormRowDefaults
      >
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area at the end of a nav item for placing icon or extra
      information.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="select">
      Triggered when a nav item is clicked.
      <FormRowDetails>
        <code>select: ({ action: string, id: number|string }) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <h2>SidePanelNavLinkItem</h2>
  The link type nav item in a <code>SidePanelNav</code> component.
  <SamplesExample>
    <SidePanelNavLinkItem v-bind="configLink">
      <ButtonIcon
        :aria-label="'More'"
        :styleclass="{ circle: true, xsmall: true }"
      >
        <AddIcon />
      </ButtonIcon>
    </SidePanelNavLinkItem>
  </SamplesExample>
  <SamplesCtl>
    <FormRow required label="id" helpmsg="Specifies a nav link's unique id.">
      <InputText v-model:value="configLink.id" />
      <FormRowDetails>
        <code>id: string|number</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="text" helpmsg="Specifies a nav link's label text.">
      <InputText v-model:value="configLink.text" />
      <FormRowDetails>
        <code>text: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="url" helpmsg="Specifies a nav link's url.">
      <InputText v-model:value="configLink.url" disabled />
      <FormRowDetails>
        <code>url: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="notification"
      helpmsg="Specifies whether show the notification indicator on a nav link."
    >
      <ToggleSwitch
        v-model:value="configLink.notification"
        aria-label="set notification toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>notification: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="notificationText"
      helpmsg="Specifies a nav link's notification text for screen reader and accessibility purpose."
    >
      <InputText v-model:value="configLink.notificationText" />
      <FormRowDetails>
        <code>notificationText: string</code>
      </FormRowDetails>
      <FormRowDefaults
        >getString('updated_recently', 'totara_core')</FormRowDefaults
      >
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area at the end of a nav item for placing icon or extra
      information.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="select">
      Triggered when a nav item is clicked. The parameter
      <code>action</code> contains the provided url.
      <FormRowDetails>
        <code>select: ({ action: string, id: number|string }) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <h2>SidePanelNavGroup</h2>
  Grouping a list of nav items and providing title to them.
  <SamplesExample>
    <SidePanelNavGroup :title="configGroup.title">
      <template #heading-side>
        <ButtonIcon
          :aria-label="'More'"
          :styleclass="{ circle: true, xsmall: true }"
        >
          <AddIcon />
        </ButtonIcon>
      </template>
      <SidePanelNavButtonItem
        id="groupItemBtn"
        action="groupItemBtnAct"
        text="nav item"
      />
    </SidePanelNavGroup>
  </SamplesExample>
  <SamplesCtl>
    <FormRow required label="title" helpmsg="Specifies a nav group's title.">
      <InputText v-model:value="configGroup.title" />
      <FormRowDetails>
        <code>title: string</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area for nav items.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="heading-side">
      The content area at the end of the group title row.
      <FormRowDetails>
        <code>heading-side</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="select">
      Triggered when a nav item in a nav group is clicked.
      <FormRowDetails>
        <code>select: ({ action: string, id: number|string }) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script setup>
import AddIcon from 'tui/components/icons/Add';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import SidePanel from 'tui/components/sidepanel/SidePanel';
import SidePanelNav from 'tui/components/sidepanel/SidePanelNav';
import SidePanelNavButtonItem from 'tui/components/sidepanel/SidePanelNavButtonItem';
import SidePanelNavGroup from 'tui/components/sidepanel/SidePanelNavGroup';
import SidePanelNavLinkItem from 'tui/components/sidepanel/SidePanelNavLinkItem';
import { reactive, ref } from 'vue';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputText from 'tui/components/form/InputText';

const itemGroups = [
  {
    items: [
      { id: 1, text: 'Item 1', action: '#item1' },
      { id: 2, text: 'Item 2', action: '#item2' },
      { id: 3, text: 'Item 3', action: '#item3' },
      { id: 4, text: 'Item 4', action: '#item4' },
      { id: 5, text: 'Item 5', action: '#item5' },
    ],
    title: 'Group 1a',
    type: 'links',
  },
  {
    items: [
      { id: 6, text: 'Item 6', action: '#item6' },
      { id: 7, text: 'Item 7', action: '#item7' },
      { id: 8, text: 'Item 8', action: '#item8' },
      { id: 9, text: 'Item 9', action: '#item9' },
      { id: 10, text: 'Item 10', action: '#item10' },
    ],
    title: 'Group 2b',
    type: 'buttons',
  },
  {
    items: [
      { id: 11, text: 'Item 11', action: '#item11' },
      { id: 12, text: 'Item 12', action: '#item12' },
      { id: 13, text: 'Item 13', action: '#item13' },
      { id: 14, text: 'Item 14', action: '#item14' },
    ],
    type: 'links',
  },
];

const selectedItem = ref(3);

const configNav = reactive({
  ariaLabel: 'side nav',
});

const configBtn = reactive({
  action: 'click me',
  id: 'myBtn',
  text: 'My Button',
  notification: true,
  notificationText: 'notice me',
});

const configLink = reactive({
  action: 'click me',
  id: 'myLink',
  text: 'My Link',
  notification: true,
  notificationText: 'notice me',
});

const configGroup = reactive({
  title: 'Group Title',
});

const navChange = selection => {
  console.log(selection);
};
</script>

<style lang="scss">
.tui-sidePanelNavExample {
  @include font(body);
}
</style>
