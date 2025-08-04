<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module mod_facetoface
-->

<script setup>
import { copyText } from 'tui/dom/clipboard';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import LinkIcon from 'tui/components/icons/Link';
import QrCodeIcon from 'tui/components/icons/QrCode';
import { notify } from 'tui/notifications';

const props = defineProps({
  url: {
    required: true,
    type: String,
  },
  selfAttendanceExpired: {
    required: true,
    type: Boolean,
  },
  expiredTime: {
    required: true,
    type: Boolean,
  },
  qrCode: {
    required: true,
    type: String,
  },
  fileName: {
    required: true,
    type: String,
  },
});

async function copyUrl() {
  copyText(props.url);
  await notify({
    message: this.$str('selfattendancecopysuccess', 'mod_facetoface'),
    type: 'success',
  });
}

function downloadQrCode() {
  const link = document.createElement('a');
  link.href = props.qrCode;
  link.download = props.fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

<template>
  <div class="tui-mod_facetoface-shareLink">
    <ButtonIcon
      class="tui-mod_facetoface-shareLink__copyButton"
      variant="link"
      :text="$str('selfattendancecopy', 'mod_facetoface')"
      :aria-label="$str('selfattendancecopy', 'mod_facetoface')"
      @click="copyUrl"
    >
      <LinkIcon />
    </ButtonIcon>
    <ButtonIcon
      variant="link"
      :text="$str('downloadqrcode', 'mod_facetoface')"
      :aria-label="$str('downloadqrcode', 'mod_facetoface')"
      @click="downloadQrCode"
    >
      <QrCodeIcon />
    </ButtonIcon>
    <p v-if="selfAttendanceExpired" class="tui-mod_facetoface-shareLink__text">
      {{ $str('selfattendance_expire', 'mod_facetoface', expiredTime) }}
    </p>
  </div>
</template>
<style lang="scss">
.tui-mod_facetoface-shareLink {
  margin-top: var(--gap-1);
  &__copyButton {
    margin-right: var(--gap-5);
  }
  &__text {
    color: var(--color-prompt-alert);
  }
}
</style>
