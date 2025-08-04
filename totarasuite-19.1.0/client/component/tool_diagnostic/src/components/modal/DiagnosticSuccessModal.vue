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

  @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
  @module tool_diagnostic
-->

<template>
  <Modal
    :dismissable="dismissable"
    :aria-labelledby="$id('title')"
    class="tui-diagnosticSuccessModal"
  >
    <ModalContent
      :close-button="true"
      :title-id="$id('title')"
      :title-visible="false"
      @ok="$emit('ok')"
    >
      <template v-slot:title>
        {{ $str('success_modal_title', 'tool_diagnostic') }}
      </template>
      <div class="tui-diagnosticSuccessModal__container">
        <div class="tui-diagnosticSuccessModal__container-icon">
          <Success size="700" state="success" />
        </div>
        <div class="tui-diagnosticSuccessModal__container-box">
          <p class="tui-diagnosticSuccessModal__container-title">
            {{ $str('success_modal_title', 'tool_diagnostic') }}
          </p>

          <p>
            {{ $str('file_created', 'tool_diagnostic', filename) }}
          </p>
        </div>
      </div>
      <template v-slot:buttons>
        <ButtonGroup>
          <Button
            :styleclass="{ small: true }"
            :text="$str('ok', 'tool_diagnostic')"
            @click="$emit('ok')"
          />
        </ButtonGroup>
      </template>
    </ModalContent>
  </Modal>
</template>
<script>
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import Success from 'tui/components/icons/Success';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Button from 'tui/components/buttons/Button';

export default {
  components: {
    Button,
    ButtonGroup,
    Modal,
    ModalContent,
    Success,
  },

  props: {
    filename: {
      type: String,
      required: true,
    },
  },

  emits: ['ok'],

  data() {
    return {
      dismissable: {
        overlayClose: false,
        esc: false,
        backdropClick: false,
      },
    };
  },
};
</script>

<style lang="scss">
.tui-diagnosticSuccessModal {
  &__container {
    display: flex;

    &-title {
      @include font(h4);
    }

    &-box {
      gap: var(--gap-2);
      padding-left: var(--gap-4);
      overflow-wrap: anywhere;
    }
  }
}
</style>
