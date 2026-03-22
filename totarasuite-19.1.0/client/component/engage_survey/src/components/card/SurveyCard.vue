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
  @module engage_survey
-->

<template>
  <BaseCard
    component-name="engage_survey"
    :bookmarked="innerBookmarked"
    :show-bookmark="showBookmark"
    :extra="extraData"
    :instance-id="instanceId"
    :actions="actions"
    :footnotes="footnotes"
    :show-footnotes="showFootnotes"
  >
    <template v-slot="{ actionList }">
      <LearningCard
        class="tui-engageSurveyCard__cardWrapper"
        :title="name"
        :actions="actionList"
        :href="currentUrl()"
      >
        <template v-slot:hero="{ popFront }">
          <DragHandleIcon
            v-if="itemDraggable"
            :class="['tui-engageSurveyCard__drag', popFront]"
          />
        </template>
        <template v-slot:body>
          <div class="tui-engageSurveyCard__subtitleRow">
            <div class="tui-engageSurveyCard__subtitle">
              {{ $str('survey', 'engage_survey') }}
            </div>
            <div class="tui-engageSurveyCard__subtitleRight">
              <AccessIcon v-if="access != 'PUBLIC'" :access="access" />
            </div>
          </div>
          <div class="tui-engageSurveyCard__content">
            <template v-if="voted && !editable">
              <SurveyQuestionResult
                v-for="({ votes, id, options, answertype }, index) in questions"
                :key="index"
                :options="options"
                :question-id="id"
                :total-votes="votes"
                :answer-type="answertype"
              />
            </template>
            <template v-else>
              <div v-if="showEdit">
                {{ $str('noresult', 'engage_survey') }}
              </div>
            </template>
            <template v-if="voted && !editable">
              <div class="tui-engageSurveyCard__resultFootNote">
                <div>{{ voteMessage }}</div>
              </div>
            </template>
          </div>
        </template>
      </LearningCard>
    </template>
  </BaseCard>
</template>

<script>
import AccessIcon from 'totara_engage/components/icons/access/computed/AccessIcon';
import BaseCard from 'totara_engage/components/card/BaseCard';
import { cardMixin } from 'totara_engage/index';
import DragHandleIcon from 'tui/components/icons/DragHandle';
import LearningCard from 'tui/components/card/LearningCard';
import SurveyQuestionResult from 'engage_survey/components/card/result/SurveyQuestionResult';

export default {
  components: {
    AccessIcon,
    BaseCard,
    DragHandleIcon,
    LearningCard,
    SurveyQuestionResult,
  },

  mixins: [cardMixin],

  data() {
    let extraData = {},
      questions = [];

    if (this.extra) {
      extraData = JSON.parse(this.extra);
    }

    if (extraData.questions) {
      questions = Array.prototype.slice.call(extraData.questions);
    }

    let actionItems = [];
    if (extraData.editable) {
      actionItems.push({
        label: this.$str('editsurvey', 'engage_survey'),
        inMenu: true,
        onClick: () => {
          window.location.href = this.$url(this.url, { page: 'edit' });
        },
      });
    }

    if (!extraData.voted && !extraData.editable) {
      actionItems.push({
        label: this.$str('votenow', 'engage_survey'),
        inMenu: true,
        onClick: () => {
          window.location.href = this.$url(this.url, { page: 'vote' });
        },
      });
    }

    return {
      show: {
        result: false,
        editModal: false,
      },
      innerBookmarked: this.bookmarked,
      questions: questions,
      voted: extraData.voted || false,
      extraData: JSON.parse(this.extra),
      actions: actionItems,
    };
  },

  computed: {
    voteMessage() {
      const questions = Array.prototype.slice.call(this.questions).shift();

      return this.$str('votemessage', 'engage_survey', {
        options: questions.options.length >= 3 ? 3 : 2,
        questions: questions.options.length,
      });
    },

    editable() {
      return this.extraData.editable || false;
    },

    showEdit() {
      return this.owned && this.editable;
    },
  },

  methods: {
    currentUrl() {
      return this.voted && !this.editable
        ? this.$url(this.url, { page: 'vote' })
        : this.$url(this.url, { page: 'edit' });
    },
  },
};
</script>

<style lang="scss">
.tui-engageSurveyCard {
  display: flex;
  flex-basis: 100%;
  flex-direction: column;

  &__cardWrapper {
    flex-grow: 1;

    &:hover {
      .tui-engageSurveyCard__drag {
        display: block;
      }
    }
  }

  &__drag {
    position: absolute;
    top: var(--gap-2);
    left: var(--gap-2);
    display: none;
  }

  &__subtitleRow {
    display: flex;
    flex-flow: row wrap;
    gap: gap(1);
  }

  &__subtitle {
    @include font(body-sm);
    flex: 1;
  }

  &__content {
    margin: auto 0;
    padding: var(--gap-4) 0;
  }
}
</style>
