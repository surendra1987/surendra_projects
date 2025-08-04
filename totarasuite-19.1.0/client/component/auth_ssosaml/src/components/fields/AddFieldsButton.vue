<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module auth_ssosaml
-->

<template>
  <div>
    <ButtonIcon
      :styleclass="{ small: true }"
      :text="$str('add_fields', 'auth_ssosaml')"
      :aria-label="$str('add_fields', 'auth_ssosaml')"
      @click="modalOpen = true"
    >
      <AddIcon />
    </ButtonIcon>
    <ModalPresenter :open="modalOpen" @request-close="modalOpen = false">
      <AddFieldsModal
        :mapping-options="mappingOptions"
        :added-mappings="addedMappings"
        @add="handleAdd"
      />
    </ModalPresenter>
  </div>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import AddIcon from 'tui/components/icons/Add';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import AddFieldsModal from 'auth_ssosaml/components/fields/AddFieldsModal';

export default {
  components: {
    ButtonIcon,
    AddIcon,
    ModalPresenter,
    AddFieldsModal,
  },

  props: {
    mappingOptions: Array,
    addedMappings: Array,
  },

  emits: ['add'],

  data() {
    return {
      modalOpen: false,
    };
  },

  methods: {
    handleAdd(items) {
      this.modalOpen = false;
      this.$emit('add', items);
    },
  },
};
</script>
