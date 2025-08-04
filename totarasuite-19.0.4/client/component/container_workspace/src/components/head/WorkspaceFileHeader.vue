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

  @author Qingyang Liu <qingyang liu@totaralearning.com>
  @module container_workspace
-->
<template>
  <ButtonIcon
    variant="link"
    :text="$str('back', 'core')"
    @click="$emit('go-back')"
  >
    <BackArrow size="200" :alt="$str('back', 'core')" />
  </ButtonIcon>
</template>

<script>
import BackArrow from 'tui/components/icons/BackArrow';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';

export default {
  components: {
    BackArrow,
    ButtonIcon,
  },

  emits: ['go-back'],
};
</script>
