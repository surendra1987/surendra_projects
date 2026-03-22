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
  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module samples
-->

<template>
  <SamplesExample>
    <Tree
      v-model:value="expandedList"
      class="tui-sample__tree"
      :depth-limit="treeDepth"
      :label-type="labelType"
      :separator="separator"
      :tree-data="tree"
      :no-padding="noPadding"
      @label-click="labelClicked"
    >
      <template
        v-slot:content="{
          content,
        }"
      >
        <template v-if="content && content.items">
          <MultiSelect v-model:value="selection" :options="content.items" />
        </template>

        <template v-if="content && content.subContent">
          <MultiSelect
            v-model:value="selection"
            :options="flattenedGroup(content.subContent)"
          />
        </template>
      </template>

      <template
        v-slot:side="{
          sideContent,
        }"
      >
        <div class="tui-sample__tree-side">
          <InfoIconButton :aria-label="sideContent.label">
            {{ sideContent.label }}
          </InfoIconButton>
          Side
        </div>
      </template>
    </Tree>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Label type">
      <RadioGroup v-model:value="labelType" :horizontal="true">
        <Radio :value="null">Text</Radio>
        <Radio value="button">Button</Radio>
        <Radio value="link">Link</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>labelType: string</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Separator">
      <RadioGroup v-model:value="separator" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>separator: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Tree depth">
      <RadioGroup v-model:value="treeDepth" :horizontal="true">
        <Radio :value="2">2</Radio>
        <Radio :value="3">3</Radio>
        <Radio :value="4">4</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>depthLimit: number</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="No padding">
      <RadioGroup v-model:value="noPadding" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>noPadding: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InfoIconButton from 'tui/components/buttons/InfoIconButton';
import MultiSelect from 'tui/components/filters/MultiSelectFilter';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Tree from 'tui/components/tree/Tree';

export default {
  components: {
    FormRow,
    FormRowDetails,
    InfoIconButton,
    MultiSelect,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
    Tree,
  },

  data() {
    return {
      expandedList: ['northisland'],
      labelType: null,
      selection: [],
      separator: false,
      noPadding: false,
      treeDepth: 4,
      tree: [
        {
          id: 'continents',
          label: 'Continents',
          linkUrl: '#Continents',
          content: {},
          children: [
            {
              id: 'africa',
              label: 'Africa',
              linkUrl: '#Africa',
              content: {},
              children: [
                {
                  id: 'southafrica',
                  label: 'South Africa',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'capetown',
                        label: 'Cape Town',
                      },
                      {
                        id: 'johannesburg',
                        label: 'Johannesburg',
                      },
                    ],
                  },
                },
              ],
            },

            {
              id: 'antarctica',
              label: 'Antarctica',
              linkUrl: '#Antarctica',
              content: {
                items: [
                  {
                    id: 'carlinibase',
                    label: 'Carlini Base',
                  },
                ],
              },
              children: [],
            },

            {
              id: 'asia',
              label: 'Asia',
              linkUrl: '#Asia',
              content: {},
              children: [
                {
                  id: 'japan',
                  label: 'Japan',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'osaka',
                        label: 'Osaka',
                      },
                      {
                        id: 'tokyo',
                        label: 'Tokyo',
                      },
                    ],
                  },
                },
              ],
            },

            {
              id: 'europe',
              label: 'Europe',
              linkUrl: '#Europe',
              content: {},
              children: [
                {
                  id: 'france',
                  label: 'France',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'lyon',
                        label: 'Lyon',
                      },
                      {
                        id: 'marseille',
                        label: 'Marseille',
                      },
                      {
                        id: 'paris',
                        label: 'Paris',
                      },
                    ],
                  },
                },
                {
                  id: 'germany',
                  label: 'Germany',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'berlin',
                        label: 'Berlin',
                      },
                      {
                        id: 'frankfurt',
                        label: 'Frankfurt',
                      },
                      {
                        id: 'hamburg',
                        label: 'Hamburg',
                      },
                    ],
                  },
                },
              ],
            },

            {
              id: 'northamerica',
              label: 'North America',
              linkUrl: '#NorthAmerica',
              content: {},
              children: [
                {
                  id: 'mexico',
                  label: 'Mexico',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'Mexicocity',
                        label: 'Mexico city',
                      },
                      {
                        id: 'monterrey',
                        label: 'Monterrey',
                      },
                    ],
                  },
                },
              ],
            },

            {
              id: 'oceanic',
              label: 'Oceanic',
              linkUrl: '#oceanic',
              content: {},
              children: [
                {
                  id: 'Australia',
                  label: 'Australia',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'melbourne',
                        label: 'Melbourne',
                      },
                      {
                        id: 'sydney',
                        label: 'Sydney',
                      },
                    ],
                  },
                },
                {
                  id: 'newzealand',
                  label: 'New Zealand',
                  children: [
                    {
                      id: 'northisland',
                      label: 'North island',
                      children: [],
                      content: {
                        items: [
                          {
                            id: 'Auckland',
                            label: 'Auckland',
                          },
                          {
                            id: 'Wellington',
                            label: 'Wellington',
                          },
                        ],
                      },
                    },
                    {
                      id: 'southisland',
                      label: 'South island',
                      children: [],
                      content: {
                        items: [
                          {
                            id: 'Dunedin',
                            label: 'Dunedin',
                          },
                          {
                            id: 'Christchurch',
                            label: 'Christchurch',
                          },
                        ],
                      },
                    },
                  ],
                  content: {},
                },
              ],
            },

            {
              id: 'southamerica',
              label: 'South America',
              linkUrl: '#SouthAmerica',
              content: {},
              children: [
                {
                  id: 'peru',
                  label: 'Peru',
                  linkUrl: '#peru',
                  children: [],
                  content: {
                    items: [
                      {
                        id: 'arequipa',
                        label: 'Arequipa',
                      },
                      {
                        id: 'lima',
                        label: 'Lima',
                      },
                    ],
                  },
                  sideContent: { label: 'Example content' },
                },
              ],
            },
          ],
        },
        {
          id: 'planets',
          label: 'Planets',
          linkUrl: '#Planets',
          content: {
            items: [
              {
                id: 'earth',
                label: 'Earth',
              },
              {
                id: 'mars',
                label: 'Mars',
              },
            ],
          },
          children: [
            {
              id: 'jupiter',
              label: 'Jupiter',
              linkUrl: '#Jupiter',
              content: {},
              children: [],
            },
          ],
        },
      ],
    };
  },

  methods: {
    /**
     * Flatten sub element data
     * This allows for content to be displayed
     * from nodes removed by the depth limit
     *
     * @param {Array} data
     * @return {Array}
     */
    flattenedGroup(data) {
      let flatList = [];
      data.forEach(x => {
        if (x.items) {
          flatList = flatList.concat(x.items);
        }

        if (x.subContent) {
          x.subContent.forEach(y => {
            flatList = flatList.concat(y.items);
          });
        }
      });

      return flatList;
    },

    /**
     * Output id from clicked label
     *
     * @param {Array} label
     */
    labelClicked(label) {
      console.log(label);
    },
  },
};
</script>

<style lang="scss">
.tui-sample__tree {
  max-width: 300px;

  &-side {
    display: flex;
  }
}
</style>
