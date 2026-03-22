/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { createApp, defineAsyncComponent, h, markRaw } from 'vue';
// "import x from 'tui/x'" syntax does not work in this file as we're
// too early in the load process, so we must use relative imports
import BundleLoader from './internal/BundleLoader';
import TotaraModuleStore from './internal/TotaraModuleStore';
import { memoize } from './util';
import theme from './theme';
import { showError } from './errors';

let _vuePlugins = null;
function getVuePlugins() {
  if (!_vuePlugins) {
    // lazy load plugins to avoid circular dependency on tui global
    _vuePlugins = [
      require('./tui_vue_plugin').default,
      require('./i18n_vue_plugin').default,
      require('./apollo/internal/tui_apollo_vue_plugin').default,
    ];
  }
  return _vuePlugins;
}

const loader = new BundleLoader();
const modules = new TotaraModuleStore({ bundleLoader: loader });

/**
 * TUI core API.
 *
 * This module provides core methods for interacting with TUI - loading modules,
 * mounting components, and using Vue.
 *
 * This is the primary way of interacting with TUI from the outside, provides
 * helpers such as `tui.require()` that are useful when writing Vue components,
 * and provides an interface used by the build tooling.
 */
const tui = {
  /**
   * Get all exports of the module with the provided ID.
   *
   * @param {string} id Module ID, e.g.
   *     'tui/components/Example' or 'vue'.
   * @return {*} Module exports.
   * @throws {Error} when module cannot be found.
   */
  require(id) {
    return modules.require(id);
  },

  /**
   * Asynchronously load the module with the provided ID if it is not loaded,
   * then return all its exports.
   *
   * @param {string} id Module ID, e.g.
   *   'tui/components/Example' or 'vue'.
   * @returns {Promise}
   *   resolving to module exports, or rejecting when module cannot be found or
   *   bundle load fails.
   */
  import(id) {
    return modules.import(id);
  },

  /**
   * Check if the provided module can be synchronously imported with require().
   *
   * Note that this does not guarantee that the import will succeed, just that
   * we already know if it will succeed or fail (i.e, it's safe to use
   * tui.require() rather than tui.import()).
   *
   * @param {string} id
   */
  syncImportable(id) {
    return modules.syncImportable(id);
  },

  /**
   * Get the default export of the provided module.
   *
   * @param {*} module Module exports.
   * @return {*} Default export.
   * @throws {Error} when module cannot be found.
   */
  defaultExport(module) {
    if (process.env.NODE_ENV !== 'production' && typeof module == 'string') {
      console.warn(
        '[tui] String passed to tui.defaultExport(). ' +
          'Did you mean to call tui.import()/tui.require() first?'
      );
    }
    return modules.default(module);
  },

  /**
   * Get an async component definition for the provided module ID.
   *
   * Can be rendered and will display a loading indicator until the component
   * has loaded.
   *
   * @function
   * @param {string} id
   * @returns {function}
   */
  asyncComponent: memoize(id => {
    const errorInfo = {};
    const component = defineAsyncComponent({
      loader: () => {
        const componentPromise = tui.loadComponent(id);
        componentPromise.catch(e => (errorInfo.error = e));
        return componentPromise;
      },
      loadingComponent: tui.defaultExport(
        tui.require('tui/components/loading/ComponentLoading')
      ),
      errorComponent: asyncComponentError(errorInfo),
      delay: 0,
    });
    component.toString = () => `[async component ${id}]`;
    return markRaw(component);
  }),

  /**
   * Load component ready to be rendered.
   *
   * @param {string} id
   * @returns {Promise}
   */
  async loadComponent(id) {
    return markRaw(modules.default(await modules.import(id)));
  },

  /**
   * Mount the specified component in the passed element.
   *
   * @param {import('vue').Component} component
   * @param {?object} props Props to pass to component
   * @param {(Element|string)} el Element to mount at, or selector.
   * @return {import('vue').App<Element>}
   */
  mount(component, props, el) {
    // warn for incorrect usage
    if (process.env.NODE_ENV !== 'production') {
      if (typeof component == 'object' && component.__esModule) {
        console.error(
          '[tui] All values exported from a component were passed to ' +
            'tui.mount() - you need to pass a single component only. ' +
            'Try passing through tui.defaultExport().'
        );
      } else if (typeof component == 'string') {
        console.error(
          '[tui] Strings cannot be passed to tui.mount() -- try ' +
            'passing it through await tui.loadComponent() first.'
        );
      }
    }

    return mount(component, props, el);
  },

  /**
   * Does this component have requirements that need to be loaded before it can
   * be rendered?
   *
   * @deprecated since Totara 19.0, requirements are resolved at build time
   * @param {object} component
   * @return {bool}
   */
  needsRequirements() {
    return false;
  },

  /**
   * Load data that needs to be loaded before rendering the component and any
   * child components.
   *
   * @deprecated since Totara 19.0, requirements are resolved at build time
   * @return {Promise} Promise resolving once requirements have been loaded.
   */
  loadRequirements() {
    return Promise.resolve();
  },

  /**
   * Search children of the specified element to find TUI components that need
   * to be initialised.
   *
   * @param {?Element} el Element to search, or document if not specified.
   */
  scan(el) {
    if (!el) {
      el = document;
    }
    const markers = Array.prototype.slice.call(
      el.querySelectorAll('[data-tui-component]')
    );
    return Promise.all(
      markers.map(async function(marker) {
        const container = document.createElement('div');
        container.className = 'tui-root';
        marker.replaceWith(container);

        try {
          const component = marker.getAttribute('data-tui-component');
          const rawProps = marker.getAttribute('data-tui-props');
          const props = rawProps ? JSON.parse(rawProps) : null;
          tui.mount(await tui.loadComponent(component), props, container);
        } catch (e) {
          tui.mount(
            await tui.loadComponent('tui/components/errors/ErrorPageRender'),
            { error: e },
            container
          );
          throw e;
        }
      })
    );
  },

  /** @deprecated since Totara 19.0 */
  vueAssign: Object.assign,

  useVuePlugin(plugin) {
    const vuePlugins = getVuePlugins();
    if (!vuePlugins.includes(plugin)) {
      vuePlugins.push(plugin);
    }
  },

  /**
   * Load the bundles needed for the provided Tui component.
   *
   * @private
   * @param {string} tuiComponent
   * @returns {Promise}
   */
  _loadTuiComponent(tuiComponent) {
    return loader.loadBundle(tuiComponent);
  },

  /**
   * Get a list of the loaded modules of the specified Tui component.
   *
   * @private
   * @param {string} tuiComponent
   * @returns {string[]}
   */
  _getLoadedComponentModules(tuiComponent) {
    return modules.getLoadedSubmodules(tuiComponent);
  },

  /**
   * Interface to theme
   *
   * @deprecated since Totara 19.0 -- import tui/theme directly
   */
  theme,

  /** @private */
  _modules: modules,

  /** @private */
  _bundle: {
    // proxy module store functions to provide a stable bundle interface:
    /** @private */
    addModulesFromContext: (idBase, context) =>
      modules.addFromContext(idBase, context),
    /** @private */
    addModule: (id, getter) => modules.addModule(id, getter),
    /** @private */
    register: name => loader.registerComponent(name),
    /** @private */
    isLoaded: name => loader.isComponentLoaded(name),
  },
};

// eslint-disable-next-line tui/no-tui-internal
tui.customBundle = tui._bundle;

/**
 * Internal mount function.
 *
 * Called once we have all information we need to mount the component.
 *
 * @param {import('vue').Component} component
 * @param {?object} props
 * @param {(Element|string)} el
 * @returns {import('vue').App<Element>} Vue app instance.
 */
function mount(component, props, el) {
  const ErrorBoundary = tui.defaultExport(
    tui.require('tui/components/errors/ErrorBoundary')
  );

  const TuiRoot = {
    name: 'TuiRoot',
    setup() {
      return () =>
        h(ErrorBoundary, () =>
          h(component, {
            ...props,
          })
        );
    },
  };

  const app = createApp(TuiRoot);
  getVuePlugins().forEach(x => app.use(x));
  app.config.errorHandler = vueErrorHandler;
  app.mount(el);
  return app;
}

function asyncComponentErrorRender() {
  const ErrorPageRender = tui.defaultExport(
    tui.require('tui/components/errors/ErrorPageRender')
  );
  return h(ErrorPageRender, {
    error: this.$options.errorInfo.error,
  });
}

function asyncComponentError(errorInfo) {
  return {
    errorInfo,
    render: asyncComponentErrorRender,
  };
}

function vueErrorHandler(error, vm) {
  console.error(error);
  showError(error, { vm });
}

export default tui;
