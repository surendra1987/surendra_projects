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
import { ref } from 'vue';
import SortBar from 'tui/components/filters/SortBar';

const sortBy = ref('name');

const options = [
  { id: 'name', label: 'Alphabetical' },
  { id: 'updated_at', label: 'Latest' },
];
</script>

<template>
  <div>
    <p>
      A component typically used above listings to control ordering and show the
      number of available items.
    </p>
    <hr />
    <SortBar v-model:sort-by="sortBy" :options="options">
      <template v-slot:start>
        {{ $str('listitemplural', 'totara_core', 9001) }}
      </template>
    </SortBar>
  </div>
</template>
