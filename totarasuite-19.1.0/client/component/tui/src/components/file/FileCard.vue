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
  @author Brian Barnes <brian.barnes@totaralearning.com>
  @module tui
-->

<template>
  <div class="tui-fileCard" :class="{ 'tui-fileCard--focus': focused }">
    <FileIcon
      :filename="filename"
      size="600"
      :custom-class="['tui-fileCard__icon']"
      :title="$str('filewithname', 'totara_core', filename)"
    />

    <div class="tui-fileCard__info">
      <a
        :href="downloadUrl"
        class="tui-fileCard__filename"
        target="_blank"
        @focus="focused = true"
        @blur="focused = false"
      >
        <div class="tui-fileCard__filename-text">
          {{ fileName }}
        </div>
        <div class="tui-fileCard__filename-ext">
          {{ fileExtension }}
        </div>
      </a>

      <p class="tui-fileCard__fileSize">
        <FileSize :size="fileSize" />
        <OpenNewWindow
          :size="null"
          :custom-class="['tui-fileCard__fileSize-icon']"
        />
      </p>
    </div>

    <div v-if="$slots.actions" class="tui-fileCard__actions">
      <slot name="actions" />
    </div>
    <a
      v-else-if="downloadUrl"
      :href="forceDownloadUrl"
      class="tui-fileCard__download"
      download
      :title="$str('download_attachment', 'totara_core')"
    >
      <DownloadIcon
        size="200"
        :custom-class="['tui-fileCard__download-icon']"
        :alt="$str('download', 'core')"
      />
    </a>
  </div>
</template>

<script>
import DownloadIcon from 'tui/components/icons/Download';
import OpenNewWindow from 'tui/components/icons/OpenInNewWindow';
import FileIcon from 'tui/components/icons/files/compute/FileIcon';
import FileSize from 'tui/components/file/FileSize';

export default {
  components: {
    DownloadIcon,
    OpenNewWindow,
    FileIcon,
    FileSize,
  },

  props: {
    /**
     * How big the file is (in bytes)
     */
    fileSize: {
      type: [String, Number],
      required: true,
    },

    /**
     * The name of the file (to be displayed)
     * Note: The extension must me correct to display the correct icon
     */
    filename: {
      type: String,
      required: true,
    },

    /**
     * The URL associated to the file
     * If this is not set, the file will not be downloadable
     */
    downloadUrl: {
      type: String,
      default: null,
    },
  },

  data() {
    return {
      focused: false,
    };
  },

  computed: {
    /**
     * Gets the extension of the file (if present)
     *
     * @returns {String|null}
     */
    fileExtension() {
      const separator = '.';
      if (!this.filename.includes(separator)) {
        // No dot.
        return null;
      }

      let parts = this.filename.split(separator);
      return this.$str('file_extension', 'totara_core', parts.pop());
    },

    /**
     * Gets the name of the file with or without the extension where appropriate
     *
     * @returns {string}
     */
    fileName() {
      const separator = '.';
      if (!this.filename.includes(separator)) {
        return this.filename;
      }

      let parts = this.filename.split(separator);
      return parts.shift();
    },

    /**
     * Append the forcedownload=1 query string parameter for the download link.
     * @returns {string|null}
     */
    forceDownloadUrl() {
      if (!this.downloadUrl) {
        return null;
      }

      return this.$url(this.downloadUrl, { forcedownload: 1 });
    },
  },
};
</script>

<style lang="scss">
.tui-fileCard {
  position: relative;
  display: flex;
  align-items: center;
  min-width: 0;
  padding: var(--gap-2);
  white-space: normal;
  border: var(--border-width-thin) solid var(--color-neutral-5);
  border-radius: var(--card-border-radius);
  isolation: isolate;

  &:hover {
    border-color: var(--color-state-hover);
    box-shadow: var(--shadow-2);
  }

  &--focus {
    @include tui-focus;
  }

  &__info {
    flex: 1;
    flex-direction: column;
    overflow: hidden;
  }

  &__fileSize {
    display: flex;
    gap: var(--gap-1);
    align-items: center;
    margin: 0;
    color: var(--color-neutral-6);
    font-size: font-size-px(11);
    white-space: nowrap;

    &-icon {
      font-size: font-size-px(12);
    }
  }

  &__filename {
    display: flex;
    &,
    &:link,
    &:hover,
    &:active,
    &:visited {
      color: var(--color-neutral-7);
      text-decoration: none;
      outline: none;
    }

    &-text {
      margin: 0;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &-ext {
      flex-shrink: 0;
    }

    &:after {
      position: absolute;
      inset: 0;
      content: '';
    }
  }

  &__icon {
    flex-shrink: 0;
    width: rem-px(32);
    margin-right: var(--gap-2);
    color: var(--color-neutral-7);
  }

  &--downloadable {
    cursor: pointer;
  }

  &__actions {
    z-index: 1;
    padding-left: var(--gap-4);
  }

  &__download {
    z-index: 1;
    display: flex;
    margin-left: var(--gap-4);
    padding: var(--gap-2);
    color: var(--color-state);
    border-radius: var(--btn-sm-radius);

    &:focus {
      @include tui-focus;
    }
  }
}
</style>
