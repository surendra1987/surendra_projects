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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module core_course
-->

<template>
  <Modal
    :aria-labelledby="$id('course-picker')"
    :dismissable="dismissable"
    size="large"
  >
    <ModalContent
      :title="$str('selectacourse', 'core')"
      :title-id="$id('course-picker')"
    >
      <CoursePicker
        class="tui-core_course-coursePickerModal__picker"
        @input="handlePickerChange"
      />
      <template v-slot:footer-content>
        <ButtonGroup class="tui-core_course-coursePickerModal__buttons">
          <Button
            :disabled="!selectedCourse"
            :styleclass="{ primary: true }"
            :text="$str('add', 'core')"
            @click="handleAdd"
          />
          <Button :text="$str('cancel', 'core')" @click="handleCancel" />
        </ButtonGroup>
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
// Components
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import CoursePicker from 'core_course/components/course_picker/CoursePicker';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';

export default {
  components: {
    Button,
    ButtonGroup,
    CoursePicker,
    Modal,
    ModalContent,
  },

  emits: ['add', 'request-close'],

  data() {
    return {
      dismissable: {
        overlayClose: false,
        esc: true,
        backdropClick: false,
      },
      selectedCourse: null,
    };
  },

  methods: {
    /**
     * Emit the add event with the selected course
     */
    handleAdd() {
      this.$emit('add', this.selectedCourse);
    },

    /**
     * Emit the even to close the modal
     */
    handleCancel() {
      this.$emit('request-close');
    },

    /**
     * Update the selected course
     */
    handlePickerChange(value) {
      this.selectedCourse = value;
    },
  },
};
</script>

<style lang="scss">
.tui-core_course-coursePickerModal {
  &__picker {
    flex-grow: 1;
    height: rem-px(500);
  }

  &__buttons {
    flex-grow: 1;
    justify-content: flex-end;
  }
}
</style>
