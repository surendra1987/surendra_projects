<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totaralearning.com>
  @module format_pathway
-->

<script>
import { h } from 'vue';
import pending from 'tui/pending';

export default {
  props: {
    html: String,
  },

  watch: {
    html(value) {
      this.setHtml(value);
    },
  },

  mounted() {
    this.setHtml(this.html);
  },

  methods: {
    setHtml(html) {
      this.$el.innerHTML = html;

      // tell any listening code (mostly just tui) we have some new nodes
      document.dispatchEvent(
        new CustomEvent('nodes-updated', {
          detail: { nodes: [this.$el] },
        })
      );

      this.execJs();

      // Wait a moment (1 second) for scripts to finish their execution just in case there's some page jump
      setTimeout(this.anchorHashScrollIntoView, 1000);
    },

    async execJs() {
      const done = pending('pathway');

      for (const script of this.$el.querySelectorAll('script')) {
        const newScript = document.createElement('script');
        [...script.attributes].forEach(attr => {
          newScript.setAttribute(attr.name, attr.value);
        });

        const loadedPromise = script.src
          ? new Promise(resolve => {
              newScript.addEventListener('load', () => {
                resolve();
              });
            })
          : null;
        newScript.appendChild(document.createTextNode(script.innerHTML));
        script.parentNode.replaceChild(newScript, script);
        if (loadedPromise) {
          await loadedPromise;
        }
      }
      done();
    },

    anchorHashScrollIntoView() {
      const anchorHash = window.location.hash;

      // No hash, nothing to scroll to!
      if (!anchorHash) {
        return;
      }

      document.querySelector(anchorHash).scrollIntoView();
    },
  },

  render() {
    return h('div');
  },
};
</script>
