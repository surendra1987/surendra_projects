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

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module editor_weka
-->

<template>
  <div data-node-view-content class="tui-wekaAttachments" />
</template>

<script>
import BaseNode from 'editor_weka/components/nodes/BaseNode';

export default {
  extends: BaseNode,
};
</script>

<style lang="scss">
.tui-wekaAttachments {
  display: grid;
  grid-template-columns: #{unquote(
      'repeat(auto-fill, minmax(min(15.625rem, 100%), 1fr))'
    )};
  gap: var(--gap-2);
  width: 100%;
  white-space: normal;
}
</style>
