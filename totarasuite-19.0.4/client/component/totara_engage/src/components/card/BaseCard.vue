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

  @author Arshad Anwer <arshad.anwer@totara.com>
  @module totara_engage
-->

<template>
  <div class="tui-engageBaseCard">
    <slot :action-list="actionList" />
    <Footnotes v-if="showFootnotes" :footnotes="footnotes" />
  </div>
</template>

<script>
import BookmarkActive from 'totara_engage/components/icons/BookmarkActiveIcon';
import Bookmark from 'tui/components/icons/Bookmark';
import Footnotes from 'totara_engage/components/card/Footnotes';

// GraphQL
import updateBookmark from 'totara_engage/graphql/update_bookmark';

export default {
  components: {
    Footnotes,
  },

  props: {
    bookmarked: Boolean,
    showBookmark: Boolean,
    extra: Object,
    // Name for bookmark graphql
    componentName: {
      type: String,
      required: true,
    },
    instanceId: {
      required: true,
      type: [String, Number],
    },
    actions: Array,
    showFootnotes: Boolean,
    footnotes: Array,
  },

  data() {
    return {
      innerBookMarked: this.bookmarked,
      actionList: [],
    };
  },

  watch: {
    innerBookMarked(newVal, oldVal) {
      const strToMatch = oldVal
        ? this.$str('unbookmark', 'totara_engage')
        : this.$str('bookmark', 'totara_engage');
      let bookmarkAction = this.actionList?.find(x => x.label === strToMatch);
      if (!bookmarkAction) {
        return;
      }

      bookmarkAction.label = newVal
        ? this.$str('unbookmark', 'totara_engage')
        : this.$str('bookmark', 'totara_engage');
      bookmarkAction.icon = newVal ? BookmarkActive : Bookmark;
    },
  },

  mounted() {
    let items = [];
    if (this.showBookmark && !this.extra.editable) {
      items.push({
        label: this.bookmarked
          ? this.$str('unbookmark', 'totara_engage')
          : this.$str('bookmark', 'totara_engage'),
        icon: this.bookmarked ? BookmarkActive : Bookmark,
        persistent: true,
        onClick: this.updateBookmark,
      });
    }
    this.actionList = this.actions ? this.actions.concat(items) : items;
  },

  methods: {
    updateBookmark() {
      this.innerBookMarked = !this.innerBookMarked;
      this.$apollo.mutate({
        mutation: updateBookmark,
        refetchAll: false,
        refetchQueries: ['totara_engage_contribution_cards'],
        variables: {
          itemid: this.instanceId,
          component: this.componentName,
          bookmarked: this.innerBookMarked,
        },
      });
    },
  },
};
</script>

<style lang="scss">
.tui-engageBaseCard {
  display: flex;
  flex-basis: 100%;
  flex-direction: column;
}
</style>
