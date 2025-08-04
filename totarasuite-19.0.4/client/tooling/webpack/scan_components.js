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

const compsDir = 'client/component';

/**
 * @typedef {object} ComponentInfo
 * @property {string} name Frankenstyle component name, e.g. mod_foo
 * @property {string} path Path to component directory, e.g. client/component/mod_foo
 * @property {1|2} structureVersion 1 for original T13 structure with tui.json and misc non-built files inside src/,
 *   2 for T19+ structure where tui.json and non-built files are directly under the component directory.
 * @property {string} configPath
 * @property {object} config
 */

/**
 * Scan components under client/component directory
 * @returns {ComponentInfo[]}
 */
module.exports = function scanComponents({ rootDir, components, vendor }) {
  const potentialComponents =
    components ||
    fs
      .readdirSync(path.join(rootDir, compsDir), { withFileTypes: true })
      .filter(x => x.isDirectory())
      .map(x => x.name);

  const result = [];
  for (const name of potentialComponents) {
    let subpath;
    let structureVersion;
    if (fs.existsSync(path.join(rootDir, compsDir, name, 'tui.json'))) {
      subpath = 'tui.json';
      structureVersion = 2;
    } else if (
      fs.existsSync(path.join(rootDir, compsDir, name, 'src/tui.json'))
    ) {
      subpath = 'src/tui.json';
      structureVersion = 1;
    } else {
      continue;
    }

    const configFile = path.posix.join(compsDir, name, subpath);

    const config = JSON.parse(fs.readFileSync(path.join(rootDir, configFile)));
    if (!config || !config.component) {
      throw new Error(
        `${configFile} must contain an object containing a ` +
          `frankenstyle "component" property`
      );
    }

    if (name != config.component) {
      throw new Error(
        `${configFile} "component" property does not match component directory name`
      );
    }

    // filter
    if (vendor) {
      if (config.vendor != vendor) {
        continue;
      }
    }

    result.push({
      name,
      path: path.posix.join(compsDir, name),
      configPath: configFile,
      structureVersion,
      config,
    });
  }

  return result;
};
