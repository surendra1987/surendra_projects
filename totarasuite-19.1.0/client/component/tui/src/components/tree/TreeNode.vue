<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module tui
-->

<template>
  <div
    class="tui-treeNode"
    :class="{
      'tui-treeNode--top': topLevel,
      'tui-treeNode--separator': separator && topLevel,
      'tui-treeNode--noPadding': noPadding,
    }"
  >
    <!-- Node bar -->
    <div class="tui-treeNode__bar">
      <!-- Node label (Can be text, button or link) -->

      <template v-if="$slots['custom-label']">
        <slot
          name="custom-label"
          :node-id="nodeId"
          :label="label"
          :link-url="linkUrl"
          :top-level="topLevel"
          :has-children="hasChildren"
        />
      </template>

      <template v-else>
        <Button
          v-if="labelType === 'button'"
          :id="nodeLabelId"
          class="tui-treeNode__bar-btn"
          :styleclass="{ transparent: true }"
          :text="label"
          @click="$emit('label-click', nodeId)"
        />

        <a
          v-else-if="labelType === 'link' && linkUrl"
          :id="nodeLabelId"
          class="tui-treeNode__bar-link"
          :href="linkUrl"
        >
          {{ label }}
        </a>

        <component
          :is="headerTag"
          v-else
          :id="nodeLabelId"
          class="tui-treeNode__bar-label"
        >
          {{ label }}
        </component>
      </template>

      <div v-if="sideContent" class="tui-treeNode__bar-side">
        <slot name="side" :side-content="sideContent" />
      </div>

      <div v-if="hasContent" class="tui-treeNode__trigger">
        <!-- Node expand trigger -->
        <ButtonIcon
          ref="trigger"
          class="tui-treeNode__trigger-btn"
          :styleclass="{ transparent: true }"
          :aria-expanded="open.toString()"
          :aria-controls="regionId"
          :aria-describedby="regionDescriptionId"
          :aria-label="label"
          data-tree-trigger="true"
          @click="toggleExpand()"
        >
          <CollapseIcon v-if="open" />
          <ExpandIcon v-else />
        </ButtonIcon>
      </div>
    </div>

    <span :id="regionDescriptionId" class="sr-only">
      {{ regionAccessibleLabel }}
    </span>

    <div v-if="open" :id="regionId" role="region" :aria-label="label">
      <!-- Sub-nodes -->
      <template v-if="depth !== depthLimit">
        <div
          v-for="(child, index) in children"
          :key="child.id"
          class="tui-treeNode__child"
        >
          <TreeNode
            :node-id="child.id"
            :children="child.children"
            :content="child.content"
            :depth="depth + 1"
            :depth-limit="depthLimit"
            :header-level="headerLevel"
            :label="child.label"
            :label-type="labelType"
            :link-url="child.linkUrl"
            :no-padding="noPadding"
            :open-list="openList"
            :position="index + 1"
            :siblings="children.length"
            :side-content="child.sideContent"
            @expanded="$emit('expanded', $event)"
            @label-click="$emit('label-click', $event)"
          >
            <template v-slot:content="{ content, label, labelledBy }">
              <slot
                name="content"
                :content="content"
                :label="label"
                :labelled-by="labelledBy"
              />
            </template>

            <template
              v-if="$slots['custom-label']"
              v-slot:custom-label="{ nodeId, label, linkUrl, hasChildren }"
            >
              <slot
                name="custom-label"
                :node-id="nodeId"
                :label="label"
                :link-url="linkUrl"
                :has-children="hasChildren"
              />
            </template>

            <template v-slot:side="{ sideContent }">
              <slot name="side" :side-content="sideContent" />
            </template>
          </TreeNode>
        </div>
      </template>

      <!-- Node leaves -->
      <div class="tui-treeNode__leaf">
        <slot
          name="content"
          :content="getOutputContent()"
          :label="label"
          :labelled-by="nodeLabelId"
        />
      </div>
    </div>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import CollapseIcon from 'tui/components/icons/Collapse';
import ExpandIcon from 'tui/components/icons/Expand';

export default {
  name: 'TreeNode',

  components: {
    Button,
    ButtonIcon,
    CollapseIcon,
    ExpandIcon,
  },

  props: {
    nodeId: [String, Number],
    children: Array,
    content: [Array, Object],
    depth: Number,
    depthLimit: Number,
    headerLevel: {
      type: Number,
      default: 2,
      validator: level => [1, 2, 3, 4, 5, 6].includes(level),
    },
    label: String,
    labelType: String,
    linkUrl: String,
    openList: Array,
    position: Number,
    separator: Boolean,
    siblings: Number,
    sideContent: Object,
    topLevel: Boolean,
    noPadding: Boolean,
  },

  emits: ['label-click', 'expanded'],

  data() {
    return {
      open: false,
    };
  },

  computed: {
    /**
     * Provide label ID for accessibility tags
     *
     * @return {String}
     */
    nodeLabelId() {
      return this.$id('label');
    },

    /**
     * Provide the correct header tag for node label
     *
     */
    headerTag() {
      return 'h' + this.headerLevel;
    },

    /**
     * Check if there is custom content
     *
     * @return {Boolean}
     */
    hasContent() {
      if (this.content === null || this.content === undefined) {
        return this.hasChildren;
      }
      if (Array.isArray(this.content) || this.content instanceof String) {
        return this.content.length > 0 || this.hasChildren;
      }

      if (Object.keys(this.content).length > 0) {
        return true;
      }
      return this.hasChildren;
    },

    /**
     * Check if there is at least one child tree node.
     *
     * @return {Boolean}
     */
    hasChildren() {
      return this.children != null && this.children.length > 0;
    },

    /**
     * Provide region description ID for accessibility tags
     *
     * @return {String}
     */
    regionDescriptionId() {
      return this.$id('regionDesc');
    },

    /**
     * Provide region ID for accessibility tags
     *
     * @return {String}
     */
    regionId() {
      return this.$id('region');
    },

    /**
     * Provide accessibility label for region
     *
     * @return {String}
     */
    regionAccessibleLabel() {
      return this.$str('a11y_tree_region_summary', 'totara_core', {
        depth: this.depth,
        label: this.label,
        position: this.position,
        siblings: this.siblings,
      });
    },
  },

  watch: {
    /**
     * Check if this node should be expanded
     *
     */
    openList: {
      handler(list) {
        this.setOpenState(list);
      },
      deep: true,
    },
  },

  mounted() {
    this.setOpenState(this.openList);
  },

  methods: {
    /**
     * set the open (expanded) state of the node
     *
     * @param {Array} list
     */
    setOpenState(list) {
      this.open = list.includes(this.nodeId);
    },

    /**
     * Propagate expanded value change to parent
     *
     */
    toggleExpand() {
      this.$emit('expanded', {
        key: this.nodeId,
        expanded: !this.open,
      });
    },

    /**
     * Get content for the slot
     *
     * @return {Object}
     */
    getOutputContent() {
      // If no content & no depth limit
      if (this.content == null && !this.depthLimit) {
        return null;
      }

      const content = Array.isArray(this.content)
        ? [...this.content]
        : { ...this.content };

      content.subContent = [];

      // Get content data from removed children
      if (this.depth === this.depthLimit && this.children.length) {
        this.children.forEach((subNode, index) => {
          content.subContent[index] = this.getSubContent(subNode);
        });
      }

      return content;
    },

    /**
     * Get the content from nodes removed by the depth limit
     *
     * @param {Object} node
     * @return {Object}
     */
    getSubContent(node) {
      if (!node.children.length) {
        return node.content;
      }

      let content = {
        ...node.content,
        subContent: [],
      };

      node.children.forEach((subNode, index) => {
        content.subContent[index] = this.getSubContent(subNode);
      });

      return content;
    },
  },
};
</script>

<style lang="scss">
.tui-treeNode {
  position: relative;
  width: 100%;
  &--top {
    padding: var(--gap-1) 0;
  }

  &:not(&--top) {
    padding-left: var(--gap-6);
  }

  &--separator {
    &:after {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      border-bottom: var(--border-width-thin) solid var(--color-neutral-5);
      content: '';
    }
  }

  &__trigger {
    display: flex;
    align-items: center;
    padding: 0 var(--gap-1);
  }

  &__bar {
    display: flex;
    width: 100%;
    min-width: 0;

    &-btn {
      flex-grow: 1;
      line-height: 1.2;
      text-align: left;
      -ms-word-break: break-all;
      word-break: break-word;
    }

    &-label,
    &-link {
      flex-grow: 1;
      margin: 0;
      -ms-word-break: break-all;
      word-break: break-word;
      hyphens: none;
    }

    &-label {
      @include font(body);
    }

    &-link {
      &:focus,
      &:hover {
        text-decoration: none;
      }
    }

    &-side {
      flex-shrink: 0;
      margin-left: auto;
    }
  }

  &__child {
    margin: 0;
    padding-top: var(--gap-2);
    list-style: none;
  }

  &--noPadding {
    padding: 0;
    .tui-treeNode__child {
      padding: 0;
    }
  }
}
</style>
