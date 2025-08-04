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

  @author Brian Barnes <brian.barnes@totara.com>
  @module totara_program
-->
<template>
  <div>
    <Button
      :text="
        isCertif
          ? $str('clonecertification', 'totara_certification')
          : $str('cloneprogram', 'totara_program')
      "
      :aria-label="$str('cloneprogramx', 'totara_program', fullname)"
      @click="open = true"
    />
    <ConfirmationModal
      :id="id"
      :fullname="fullname"
      :is-certif="isCertif"
      :open="open"
      @dismiss="open = false"
    />
  </div>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import ConfirmationModal from 'totara_program/components/clone/ConfirmationModal';

export default {
  components: {
    Button,
    ConfirmationModal,
  },

  props: {
    id: {
      type: Number,
      required: true,
    },

    fullname: {
      type: String,
      required: true,
    },

    isCertif: {
      type: Boolean,
      required: true,
    },
  },

  data() {
    return {
      open: false,
    };
  },
};
</script>
