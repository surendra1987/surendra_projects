<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module totara_competency
-->

<template>
  <div class="tui-competencyCopyReviewingSelection">
    <Loader
      class="tui-competencyCopyReviewingSelection__loading"
      :loading="loading"
    >
      <!-- Framework heading -->
      <h3 class="tui-competencyCopyReviewingSelection__heading">
        {{ $str('selected_competencies', 'totara_competency') }}
      </h3>

      <!-- Competency count -->
      <div class="tui-competencyCopyReviewingSelection__count">
        {{ $str('competencies', 'totara_competency', totalCompetencies) }}
      </div>

      <!-- Competency list -->
      <CompetencySelectionTable
        class="tui-competencyCopyReviewingSelection__list"
        :competencies="competencies.items"
        :loading="false"
        :reviewing-selection="true"
        :selected-competencies="selectedCompetencies"
        @update="$emit('update', $event)"
      />
    </Loader>

    <!-- Load more button -->
    <div class="tui-competencyCopyReviewingSelection__paging">
      <Button
        v-if="!lastPage"
        :loading="loading"
        :text="$str('load_more', 'totara_competency')"
        @click="$emit('next-page')"
      />
    </div>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import CompetencySelectionTable from 'totara_competency/components/competency_copy/CompetencyCopySelectionTable';
import Loader from 'tui/components/loading/Loader';

export default {
  components: {
    Button,
    CompetencySelectionTable,
    Loader,
  },

  props: {
    // List of selected competencies data
    competencies: {
      type: Object,
    },
    // Last page of competencies
    lastPage: {
      type: Boolean,
    },
    // Competency content is currently loading
    loading: {
      type: Boolean,
    },
    // List of selected competency ID's
    selectedCompetencies: {
      type: Array,
      required: true,
    },
  },

  emits: ['update', 'next-page'],

  computed: {
    /**
     * The total number of selected competencies
     *
     * @return {Number}
     */
    totalCompetencies() {
      return this.selectedCompetencies.length;
    },
  },
};
</script>

<style lang="scss">
.tui-competencyCopyReviewingSelection {
  &__count {
    font-weight: var(--label-weight);
  }

  &__heading {
    @include font(h4);
    margin: 0;
  }

  &__loading {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }

  &__paging {
    display: flex;
    justify-content: center;
  }

  & > * + * {
    margin-top: var(--gap-4);
  }
}
</style>
