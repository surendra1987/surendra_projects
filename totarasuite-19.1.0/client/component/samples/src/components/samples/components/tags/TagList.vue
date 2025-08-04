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

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module samples
-->

<template>
  <div>
    <h2>TagList</h2>
    <p>
      A muti-select field for users to search and select items from a dropdown
      list.
    </p>
    <SamplesExample>
      <TagList
        v-bind="bindProps"
        class="tui-tagListWrapper"
        :tags="tags"
        :items="items"
        :loading="values.loading"
        :separator="values.separator"
        :close-on-click="values.closeOnClick"
        :label-name="values.labelName"
        :disabled="values.disabled"
        :input-placeholder="values.inputPlaceholder"
        :debounce-filter="values.debounceFilter"
        @select="select"
        @remove="remove"
        @filter="filter"
        @scrollbottom="scrollBottom"
      >
        <template v-slot:tag="{ tag }">
          <div class="tui-customTag">
            <Avatar :src="tag.url" alt="" size="xxsmall" />
            {{ tag.text }}
          </div>
        </template>
        <template v-slot:item="{ item }">
          <div>
            <Avatar :src="item.url" alt="" size="xxsmall" />
            {{ item.name }}
          </div>
        </template>
      </TagList>
      <br />
    </SamplesExample>
    <SamplesCtl>
      <Uniform :initial-values="values" @change="v => (values = v)">
        <FormRow v-slot="{ id }" label="items" required>
          Specify the list of dropdown item. Use the <code>item</code> slot to
          customize how each item is displayed.
          <FormRowDetails :id="id">
            <code>items: Array&lt;any&gt;</code>
          </FormRowDetails>
        </FormRow>
        <FormRow v-slot="{ id }" label="tags" required>
          Specify the selected tag list. Optionally, use the
          <code>tag</code> slot to customize how each tag is displayed.
          <FormRowDetails :id="id">
            <code>tags: Array&lt;{text:string}|any&gt;</code>
          </FormRowDetails>
        </FormRow>
        <FormRow v-slot="{ id }" label="virtualScrollOptions">
          To enable virtual scrolling in the dropdown list, specify the
          virtualScrollOptions. The <code>scrollBottom</code> event is triggered
          when the user scrolls to the bottom of the list. For more details on
          virtual scrolling, check out the VirtualScroll component.
          <FormRowDetails :id="id">
            <code
              >virtualScrollOptions: { dataKey: string!, ariaLabel: string!,
              start: number, offset: number, topThreshold: number,
              bottomThreshold: number, isLoading: boolean, }
            </code>
          </FormRowDetails>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="inputPlaceholder"
          helpmsg="Specifies the placeholder text in the input of a tag list."
        >
          <InputText v-model:value="values.inputPlaceholder" />
          <FormRowDetails :id="id">
            <code>inputPlaceholder: string</code>
          </FormRowDetails>
          <FormRowDefaults
            >getString('tag_list_placeholder', 'totara_core')</FormRowDefaults
          >
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="debounceFilter"
          helpmsg="Specifies whether to delay the filter event after user input."
        >
          <ToggleSwitch
            v-model:value="values.debounceFilter"
            name="debounceFilter"
            aria-label="debounceFilter toggle"
            toggle-only
          />
          <FormRowDetails :id="id">
            <code>debounceFilter: boolean</code>
          </FormRowDetails>
          <FormRowDefaults>false</FormRowDefaults>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="disabled"
          helpmsg="Specifies that a tag list should be disabled."
        >
          <ToggleSwitch
            v-model:value="values.disabled"
            name="disabled"
            aria-label="disabled toggle"
            toggle-only
          />
          <FormRowDetails :id="id">
            <code>disabled: boolean</code>
          </FormRowDetails>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="loading"
          helpmsg="Specifies that the dropdown list is loading."
        >
          <ToggleSwitch
            v-model:value="values.loading"
            aria-label="loading toggle"
            toggle-only
          />
          <FormRowDetails :id="id">
            <code>loading: boolean</code>
          </FormRowDetails>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="separator"
          helpmsg="Specifies whether to have separate line between dropdown options."
        >
          <ToggleSwitch
            v-model:value="values.separator"
            aria-label="separator toggle"
            toggle-only
          />
          <FormRowDetails :id="id">
            <code>separator: boolean</code>
          </FormRowDetails>
          <FormRowDefaults>false</FormRowDefaults>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="closeOnClick"
          helpmsg="Specifies whether to close the dropdown menu when an option is clicked."
        >
          <ToggleSwitch
            v-model:value="values.closeOnClick"
            aria-label="closeOnClick toggle"
            toggle-only
          />
          <FormRowDetails :id="id">
            <code>closeOnClick: boolean</code>
          </FormRowDetails>
          <FormRowDefaults>false</FormRowDefaults>
        </FormRow>
        <FormRow
          v-slot="{ id }"
          label="labelName"
          helpmsg="Specifies the aria-label attribute of a tag list."
        >
          <InputText v-model:value="values.labelName" />
          <FormRowDetails :id="id">
            <code>labelName: string</code>
          </FormRowDetails>
        </FormRow>
      </Uniform>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="item" required>
        Customize the dropdown item content. Use <code>item</code> and
        <code>index</code> to access each item's data and its index from the
        inner dropdown component.
        <FormRowDetails>
          <code>v-slot:item="{ item, index }"</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="tag">
        Customize the tag item content. Use <code>tag</code> to access each
        tag's data.
        <FormRowDetails>
          <code>v-slot:tag="{ tag }"</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="open">
        Triggered when the dropdown list is opened.
        <FormRowDetails>
          <code>open: () => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="filter">
        Triggered when an user inputs search text (after a debounce delay).
        Returns the input value.
        <FormRowDetails>
          <code>filter: (value: string) => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="remove">
        Triggered when an user clicks remove button on a tag or keydown
        Backspace in the input.
        <FormRowDetails>
          <code>remove: (tag: any, index: number) => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="select">
        Triggered when an user clicks an item in the dropdown list.
        <FormRowDetails>
          <code>select: (item: any, index: number) => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="scrolltop">
        Triggered when an user scrolls to the top of the virtual scroll dropdown
        list.
        <FormRowDetails>
          <code>select: () => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="scrollbottom">
        Triggered when an user scrolls to the bottom of the virtual scroll
        dropdown list.
        <FormRowDetails>
          <code>select: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Avatar from 'tui/components/avatar/Avatar';
import TagList from 'tui/components/tag/TagList';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import { createSquareImage } from 'samples/components/sample_parts/misc/placeholder_generator';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import { Uniform, FormRow } from 'tui/components/uniform';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import { uniqueId } from 'tui/util';

let counter = 0;

const getPageData = count => {
  const dataItems = [];
  for (let i = 0; i < count; i++) {
    counter++;
    dataItems.push({
      id: uniqueId(),
      url: createSquareImage('#f66'),
      name: 'Mike',
    });
  }
  return dataItems;
};

const pageSize = 10;

export default {
  components: {
    Avatar,
    TagList,
    SamplesCtl,
    Uniform,
    FormRow,
    FormRowDetails,
    InputText,
    SamplesExample,
    ToggleSwitch,
    FormRowDefaults,
  },

  data() {
    return {
      tags: [],
      fetchData: [],
      searchItem: '',
      withAvatar: false,
      counter: counter,
      values: {
        virtualScroll: false,
        loading: false,
        separator: false,
        closeOnClick: false,
        labelName: '',
        disabled: false,
        inputPlaceholder: 'Input Name...',
        debounceFilter: false,
      },
      virtualScrollOpt: {
        dataKey: 'id',
        ariaLabel: 'aria-label',
        isLoading: false,
      },
    };
  },

  computed: {
    bindProps() {
      if (this.values.virtualScroll) {
        return {
          virtualScrollOptions: this.virtualScrollOpt,
        };
      }
      return {};
    },

    items() {
      const items = this.fetchData.filter(
        item => !this.tags.some(tag => item.id === tag.id)
      );
      if (this.searchItem) {
        return items.filter(item =>
          item.name.toUpperCase().includes(this.searchItem.toUpperCase())
        );
      }
      return items;
    },
  },

  mounted() {
    this.fetchData = [
      {
        id: 1001,
        url: createSquareImage('#f66'),
        name: 'Mike',
      },
      {
        id: 1002,
        url: createSquareImage('#fc6'),
        name: 'John',
      },
      {
        id: 1003,
        url: createSquareImage('#ff6'),
        name: 'Eric',
      },
      {
        id: 1004,
        url: createSquareImage('#3f9'),
        name: 'George',
      },
      {
        id: 1005,
        url: createSquareImage('#39f'),
        name: 'Mike',
      },
      {
        id: 1006,
        url: createSquareImage('#c6f'),
        name: 'John',
      },
      {
        id: 1007,
        url: createSquareImage('#f66'),
        name: 'Eric',
      },
      {
        id: 1008,
        url: createSquareImage('#fc6'),
        name: 'George',
      },
      {
        id: 1009,
        url: createSquareImage('#ff6'),
        name: 'Mike',
      },
      {
        id: 10010,
        url: createSquareImage('#3f9'),
        name: 'John',
      },
      {
        id: 10011,
        url: createSquareImage('#39f'),
        name: 'Eric',
      },
      {
        id: 10012,
        url: createSquareImage('#c6f'),
        name: 'George',
      },
    ];
  },

  methods: {
    select(item) {
      const { name, id, url, alt } = item;
      this.tags.push({ text: name, id, url, alt });
      this.searchItem = '';
    },
    remove(tag) {
      this.tags = this.tags.filter(t => t !== tag);
    },
    filter(value) {
      this.searchItem = value;
    },
    scrollBottom() {
      if (this.virtualScrollOpt.isLoading) {
        return;
      }

      this.virtualScrollOpt.isLoading = true;

      setTimeout(() => {
        this.virtualScrollOpt.isLoading = false;
        this.fetchData = this.fetchData.concat(getPageData(pageSize));
      }, 500);
    },
  },
};
</script>

<style lang="scss">
.tui-customTag {
  padding: rem-px(4);
  border: 1px solid var(--btn-text-color);
  border-radius: 6px;
}
</style>
