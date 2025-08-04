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
  @module totara_engage
-->

<template>
  <!--
    A compute component for rendering different card within engage. This is a wrapper for the card.
   -->
  <component
    :is="component"
    v-bind="propAttributes"
    @deleted="refetch"
    @updated="refetch"
  />
</template>

<script>
import tui from 'tui/tui';

export default {
  props: {
    /**
     * We can only use this prop as generic object to catch all the type of card. Then within this component
     * we can actually computing the actual props that is needed for the actual card.
     */
    cardAttribute: {
      type: Object,
      required: true,
      validator(prop) {
        return 'tuicomponent' in prop && 'component' in prop;
      },
    },
    showFootnotes: {
      type: Boolean,
      default: false,
    },
    labelId: {
      type: String,
      default: '',
      required: false,
    },
    itemDraggable: Boolean,
  },

  emits: ['refetch'],

  data() {
    return {
      loaded: false,
    };
  },

  computed: {
    /**
     *
     * @return {Object}
     */
    propAttributes() {
      const card = Object.assign({}, this.cardAttribute);
      let extra = {};
      if (card.extra) {
        if (typeof card.extra === 'string') {
          // Convert from json string to obj
          extra = JSON.parse(card.extra);
        } else if (typeof card.extra === 'object') {
          extra = card.extra;
        }
      }

      return {
        key: `${card.component}-${card.instanceid}`,
        instanceId: card.instanceid || 0,
        name: card.name,
        summary: card.summary || null,
        access: card.access,
        timeCreated: card.timecreated,

        // All the user's information
        userId: card.user.id,
        userFullName: card.user.fullname,
        userProfileImageUrl: card.user.profileimageurl,
        userProfileImageAlt: card.user.profileImageAlt || '',

        // A way to pass more data to the card instance.
        extra: card.extra,

        topics: card.topics,
        rating: extra.rating || 0,
        bookmarked: card.bookmarked,
        totalComments: card.comments,
        totalReactions: card.reactions,
        sharedbycount: card.sharedbycount,
        owned: card.owned,
        url: card.url,

        // Default this to zero for now.
        numberOfPeopleRated: 0,

        showFootnotes: this.showFootnotes,
        footnotes: card.footnotes,

        labelId: this.labelId,

        showBookmark:
          'interactor' in card ? card.interactor.can_bookmark : false,
        itemDraggable: this.itemDraggable,
      };
    },

    component() {
      return tui.asyncComponent(this.cardAttribute.tuicomponent);
    },
  },

  methods: {
    refetch() {
      this.$emit('refetch');
    },
  },
};
</script>
