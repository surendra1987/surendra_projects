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

  @author Simon Chester <simon.chester@totara.com>
  @module tui
-->

<script setup>
import Table from 'tui/components/datatable/Table';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Cell from 'tui/components/datatable/Cell';

const fonts = [
  { name: 'H1', id: 'h1' },
  { name: 'H2', id: 'h2' },
  { name: 'H3', id: 'h3' },
  { name: 'H4', id: 'h4' },
  { name: 'H5', id: 'h5' },
  { name: 'H6', id: 'h6' },
  { name: 'Display large', id: 'display-lg', class: true },
  { name: 'Display medium', id: 'display-md', class: true },
  { name: 'Display small', id: 'display-sm', class: true },
  { name: 'Display xsmall', id: 'display-xs', class: true },
  { name: 'Body xlarge', id: 'body-xl', class: true },
  { name: 'Body large', id: 'body-lg', class: true },
  { name: 'Body', id: 'body', class: true },
  { name: 'Body small', id: 'body-sm', class: true },
  { name: 'Body xsmall', id: 'body-xs', class: true },
];

const headings = [
  { name: 'H1 heading', id: 'h1' },
  { name: 'H2 heading', id: 'h2' },
  { name: 'H3 heading', id: 'h3' },
  { name: 'H4 heading', id: 'h4' },
  { name: 'H5 heading', id: 'h5' },
  { name: 'H6 heading', id: 'h6' },
];

const weightMap = {
  700: 'Bold',
  600: 'Semibold',
  500: 'Medium',
  501: 'Medium',
  400: 'Regular',
};

// measure actual styles
const el = document.createElement('div');
document.body.appendChild(el);
fonts.forEach(font => {
  el.className = `tui-samples-typography__${font.id}`;
  const style = getComputedStyle(el);
  font.size = style.fontSize;
  font.weight = weightMap[style.fontWeight] ?? style.fontWeight;
  font.lineHeight = style.lineHeight;
});
headings.forEach(heading => {
  el.className = heading.id;
  const style = getComputedStyle(el);
  heading.size = style.fontSize;
  heading.weight = weightMap[style.fontWeight] ?? style.fontWeight;
  heading.lineHeight = style.lineHeight;
});
el.remove();
</script>

<template>
  <div class="tui-samples-typography">
    <div>
      <h2>Headings</h2>

      <Table :data="headings">
        <template v-slot:header-row>
          <HeaderCell size="6">Name</HeaderCell>
          <HeaderCell size="2">Size / Line height</HeaderCell>
          <HeaderCell size="2">Weight</HeaderCell>
          <HeaderCell size="4">Code</HeaderCell>
        </template>
        <template v-slot:row="{ row }">
          <Cell size="6" column-header="Name">
            <div :class="row.id">
              {{ row.name }}
            </div>
          </Cell>
          <Cell size="2" column-header="Size / Line height">
            {{ row.size }} / {{ row.lineHeight }}
          </Cell>
          <Cell size="2" column-header="Weight">{{ row.weight }}</Cell>
          <Cell size="4" column-header="Code">
            <div class="tui-samples-typography__codeCol">
              <div>
                <!-- prettier gets confused if constructed as a string without the &lt; etc -->
                &lt;{{ row.id }}&gt;Text&lt;/{{ row.id }}&gt;
              </div>
              <div>&lt;div class="{{ row.id }}"&gt;Text&lt;div&gt;</div>
            </div>
          </Cell>
        </template>
      </Table>
    </div>

    <div>
      <h2>Font variants</h2>

      <Table :data="fonts">
        <template v-slot:header-row>
          <HeaderCell size="6">Name</HeaderCell>
          <HeaderCell size="2">Size / Line height</HeaderCell>
          <HeaderCell size="2">Weight</HeaderCell>
          <HeaderCell size="4">Code</HeaderCell>
        </template>
        <template v-slot:row="{ row }">
          <Cell size="6" column-header="Name">
            <div :class="`tui-samples-typography__${row.id}`">
              {{ row.name }}
            </div>
          </Cell>
          <Cell size="2" column-header="Size / Line height">
            {{ row.size }} / {{ row.lineHeight }}
          </Cell>
          <Cell size="2" column-header="Weight">{{ row.weight }}</Cell>
          <Cell size="4" column-header="Code">
            <div class="tui-samples-typography__codeCol">
              <div>{{ `.el { @include font(${row.id}); }` }}</div>
              <div v-if="row.class">
                &lt;div class="text-{{ row.id }}"&gt;Text&lt;div&gt;
              </div>
            </div>
          </Cell>
        </template>
      </Table>
    </div>
  </div>
</template>

<style lang="scss">
.tui-samples-typography {
  display: flex;
  flex-flow: column;
  gap: var(--gap-12);

  $fonts: h1, h2, h3, h4, h5, h6, display-lg, display-md, display-sm, display-xs,
    body-xl, body-lg, body, body-sm, body-xs;

  @each $font in $fonts {
    &__#{$font} {
      @include font($font);
    }
  }

  &__codeCol {
    display: flex;
    flex-flow: column;
    gap: var(--gap-1);
    font-family: var(--font-family-monospace);
  }
}
</style>
