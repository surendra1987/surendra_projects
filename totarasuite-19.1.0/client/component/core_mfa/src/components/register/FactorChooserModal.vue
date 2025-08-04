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

  @author Simon Chester <simon.chester@totaralearning.com>
  @module core_mfa
-->

<template>
  <Modal :aria-labelledby="$id('title')">
    <ModalContent
      :title="$str('choose_a_factor', 'mfa')"
      :title-id="$id('title')"
      :close-button="true"
    >
      <div>
        <FactorChooser
          :factors="factors"
          :href="
            factor => $url('/mfa/register_factor.php', { name: factor.id })
          "
        />
      </div>

      <template v-slot:buttons>
        <ButtonCancel @click="$emit('request-close')" />
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ButtonCancel from 'tui/components/buttons/Cancel';
import FactorChooser from 'core_mfa/components/factors/FactorChooser';

export default {
  components: {
    Modal,
    ModalContent,
    ButtonCancel,
    FactorChooser,
  },

  props: {
    factors: { required: true, type: Array },
  },

  emits: ['request-close'],
};
</script>
