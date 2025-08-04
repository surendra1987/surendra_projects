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
  <Modal :aria-labelledby="$id('title')">
    <ModalContent
      :title="$str('add_fields', 'auth_ssosaml')"
      :title-id="$id('title')"
    >
      <div class="tui-auth_ssosaml-addMappingModal__boxes">
        <Checkbox
          v-for="option in mappingOptions"
          :key="option.id"
          :name="$id('option')"
          :disabled="isAlreadyAdded(option.id)"
          :checked="isChecked(option.id)"
          @change="handleCheckChange(option.id, $event)"
        >
          {{ option.label }}
        </Checkbox>
      </div>

      <template v-slot:footer-content>
        <div class="tui-auth_ssosaml-addMappingModal__footer">
          <div class="tui-auth_ssosaml-addMappingModal__selectAll">
            <Checkbox
              :checked="allSelected"
              name="selectAll"
              @change="handleSelectAllChange"
            >
              {{ $str('select_all_fields', 'auth_ssosaml') }}
            </Checkbox>
          </div>
          <ButtonGroup>
            <Button
              :text="$str('add', 'core')"
              :styleclass="{ primary: true }"
              @click="handleAdd"
            />
            <Button
              :text="$str('cancel', 'core')"
              @click="$emit('request-close')"
            />
          </ButtonGroup>
        </div>
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Checkbox from 'tui/components/form/Checkbox';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import { pull } from 'tui/util';

export default {
  components: {
    Button,
    ButtonGroup,
    Checkbox,
    Modal,
    ModalContent,
  },

  props: {
    mappingOptions: Array,
    addedMappings: Array,
  },

  emits: ['request-close', 'add'],

  data() {
    return {
      selectedOptions: [],
    };
  },

  computed: {
    allSelected() {
      return this.selectableIds.every(x => this.isChecked(x));
    },

    selectableIds() {
      return this.mappingOptions
        .map(x => x.id)
        .filter(x => !this.addedMappings.includes(x));
    },
  },

  methods: {
    isChecked(id) {
      return this.selectedOptions.includes(id) || this.isAlreadyAdded(id);
    },

    isAlreadyAdded(id) {
      return this.addedMappings.includes(id);
    },

    handleAdd() {
      const added = this.selectedOptions.filter(
        x => !this.addedMappings.includes(x)
      );
      this.$emit('add', added);
    },

    handleCheckChange(id, value) {
      if (value) {
        this.selectedOptions.push(id);
      } else {
        pull(this.selectedOptions, id);
      }
    },

    handleSelectAllChange() {
      if (this.allSelected) {
        this.selectedOptions = [];
      } else {
        this.selectedOptions = [...this.selectableIds];
      }
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-addMappingModal {
  &__boxes {
    padding-top: var(--gap-6);
    columns: auto rem-px(200);
    & > * {
      break-inside: avoid;
      margin-bottom: var(--gap-4);
    }
  }

  &__footer {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
  }

  &__selectAll {
    display: flex;
    align-items: center;
    padding-bottom: var(--gap-4);
  }

  @media (min-width: $tui-screen-sm) {
    &__footer {
      flex-direction: row;
    }

    &__selectAll {
      padding: 0;
    }
  }
}
</style>
