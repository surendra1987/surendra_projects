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
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 */

const path = require('path');
const fs = require('fs');
const { dirMaps } = require('../lib/resolution');

// exclude internal dirs, test dirs
const bundleExclusionsRegex = /\/(?:internal|__[a-z]*__)\//;
const bundleInclusionsRegex = new RegExp(
  '^(?:(?!' + bundleExclusionsRegex.source + ').)*$'
);

// when including src/ directly, also exclude certain folders/files that should not be auto-exposed
const bundleExclusionsRegexSrc = /\/(?:internal|__[a-z]*__)\/|^.\/(?:global_styles|tooling|tests)\/|^.\/(?:build.config|upgrade.txt$|tooling\/|js\/)/;
const bundleInclusionsRegexSrc = new RegExp(
  '^(?:(?!' + bundleExclusionsRegexSrc.source + ').)*$'
);

/**
 * Process TUI entrypoint (tui.json)
 *
 * Generates code to implement TUI bundle for core/theme/etc.
 *
 * - Automatically detect defined folders (see lib/resolution.js) next to
 *   tui.json and add their contents to the module store
 * - Execute any entrypoints specified in tui.json (entry and preEntry)
 */
module.exports = function(jsonSource, map) {
  const options = this.getOptions();

  const config = JSON.parse(jsonSource);
  if (!config || !config.component) {
    throw new Error(
      'tui.json must be an object containing a frankenstyle "component" property'
    );
  }

  if (!config.vendor && !options.silent) {
    console.warn(
      `[tui.json loader] Configuration error in tui.json for ` +
        `${config.component}: "vendor" key is missing. Vendor should be set ` +
        `to a unique string for each organisation producing Tui components.`
    );
  }

  const compStr = JSON.stringify(config.component);

  let source = '!function() {\n"use strict";\n\n';

  // bail out if this bundle is already loaded
  source += `if (globalThis.tui?._bundle.isLoaded(${compStr})) {
  console.warn(
    '[tui bundle] The bundle "' + ${compStr} +
    '" is already loaded, skipping initialisation.'
  );
  return;
};
`;

  const isLegacyDir = this.context.endsWith('src');

  const srcDir = isLegacyDir ? this.context : this.context + '/src';

  if (fs.existsSync(path.resolve(srcDir, './global_styles/static.scss'))) {
    source += `require("${
      isLegacyDir ? '.' : './src'
    }/global_styles/static.scss");\n`;
  }

  // execute pre-entry code
  // this is used by tui to set up the module store etc before we add to it below
  if (config.preEntry) {
    source +=
      'require(' +
      JSON.stringify(this.utils.contextify(this.context, config.preEntry)) +
      ');\n';
  }

  // register bundle
  source += `tui._bundle.register(${compStr})\n`;

  // auto import certain folders
  if (isLegacyDir) {
    dirMaps.forEach(dirMap => {
      if (fs.existsSync(path.join(srcDir, dirMap.path))) {
        source += genAddModules(
          config.component + dirMap.idBaseSuffix,
          dirMap.path
        );
      }
    });
  } else {
    // Recognise "js" folder too, in order to support straight moves of tui.json without changing internal structure.
    if (fs.existsSync(path.join(this.context, 'src/js'))) {
      source += genAddModules(config.component, './src/js');
    }
    // mod_foo/xyz -> src/xyz
    if (fs.existsSync(path.join(this.context, 'src'))) {
      source += genAddModules(config.component, './src', bundleInclusionsRegexSrc);
    }
  }

  // expose specified modules from node_modules in the module store
  source += genExposeCode(config);

  // import specified entry point (this can be used for e.g. any code you want to run on page load)
  if (config.entry) {
    // use require here as import is hoisted
    source +=
      'require(' +
      JSON.stringify(this.utils.contextify(this.context, config.entry)) +
      ');\n';
  }

  source += '}();';

  // would be better to derive from entry filename, but i can't find a way to
  // get that (probably by design as a module could be used by multiple entries)
  this.emitFile('dependencies.json', genDependenciesJson(config));

  return this.callback(null, source, map);
};

/**
 * Generate code to add modules at the provided path to the tui module store.
 *
 * @param {string} prefix Module ID prefix, e.g. mod_foo/components
 * @param {string} contextPath Path relative to context directory (component directory), e.g. './src/components'
 * @returns {string}
 */
function genAddModules(prefix, contextPath, regex = bundleInclusionsRegex) {
  return (
    'tui._bundle.addModulesFromContext(' +
    JSON.stringify(prefix) +
    ', require.context(' +
    JSON.stringify(contextPath) +
    ', true, /' +
    regex.source +
    '/));\n'
  );
}

/**
 * Generate code for exposing the specified node modules in the module store.
 *
 * @param {object} config Parsed tui.json
 * @returns {string}
 */
function genExposeCode(config, options) {
  let code = '';

  if (!config.exposeNodeModules) {
    return code;
  }

  if (config.component != 'tui') {
    if (!options || !options.silent) {
      console.warn(
        `[tui.json loader] Configuration error in tui.json for ` +
          `${config.component}: exposeNodeModules is only supported in ` +
          `tui.`
      );
    }
    return code;
  }

  config.exposeNodeModules.forEach(function(item) {
    code +=
      `tui._bundle.addModule(${JSON.stringify(item)}, function() { ` +
      `return require(${JSON.stringify(item)}); });\n`;
  });

  return code;
}

/**
 * Generate content for dependencies.json.
 *
 * @param {object} config Parsed tui.json
 * @returns {string}
 */
function genDependenciesJson(config) {
  const dependencies = [];
  if (config.dependencies) {
    config.dependencies.map(name => {
      dependencies.push({ name });
    });
  }
  return JSON.stringify({ dependencies }, null, 2) + '\n';
}
