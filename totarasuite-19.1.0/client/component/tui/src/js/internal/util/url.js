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

import { config } from '../../config';
import { get, set } from './object';

/**
 * Format a URL parameter.
 *
 * @private
 * @param {string} key Parameter name
 * @param {*} value Parameter value
 */
function formatParam(key, value) {
  if (Array.isArray(value)) {
    return value
      .map((nestedVal, nestedKey) =>
        formatParam(key + '[' + encodeURIComponent(nestedKey) + ']', nestedVal)
      )
      .join('&');
  } else if (typeof value == 'object') {
    return Object.keys(value)
      .map(nestedKey =>
        formatParam(
          key + '[' + encodeURIComponent(nestedKey) + ']',
          value[nestedKey]
        )
      )
      .join('&');
  } else {
    return key + '=' + encodeURIComponent(value);
  }
}

/**
 * Format the provided parameters into a string separated by &
 *
 * @param {object=} params URL parameters.
 *   Map of keys to values.
 *   Objects and arrays are accepted as values and encoded using [].
 */
export function formatParams(params) {
  return Object.entries(params)
    .filter(([, value]) => value != null)
    .map(([key, value]) => formatParam(encodeURIComponent(key), value))
    .join('&');
}

/**
 * Generate URL
 *
 * @param {string} url Absolute or relative url.
 * @param {object=} params URL parameters.
 *   Map of keys to values.
 *   Objects and arrays are accepted as values and encoded using [].
 */
export function url(url, params) {
  const formattedParams = params && formatParams(params);
  if (formattedParams) {
    if (!url.includes('?')) {
      url += '?';
    }
    if (!url.endsWith('?') && !url.endsWith('&')) {
      url += '&';
    }
    url += formattedParams;
  }

  return url;
}

/**
 * Generate URL
 *
 * @param {string} path Absolute url or path beginning with /
 *   e.g. '/foo/bar.php', 'https://www.google.com/'
 * @param {object=} params URL parameters.
 *   Map of keys to values.
 *   Objects and arrays are accepted as values and encoded using [].
 */
export function totaraUrl(path, params) {
  // prepend with wwwroot if not absolute
  if (!/^(?:[a-z]+:)?\/\//.test(path)) {
    if (path[0] != '/') {
      throw new Error('`url` must be an absolute URL or begin with a /');
    }
    path = config.wwwroot + path;
    // if URL constructor is supported, pass it through to test that the url is valid
    if (typeof URL == 'function') {
      new URL(path);
    }
  }

  return url(path, params);
}

/**
 * Get URL for image.
 *
 * @param {string} name
 * @param {string} component
 * @return {string}
 */
export function imageUrl(name, component) {
  if (config.rev.theme > 0) {
    return totaraUrl(
      `/theme/image.php/${config.theme.name}/${component}/${config.rev.theme}/${name}`
    );
  } else {
    return totaraUrl(`/theme/image.php`, {
      theme: config.theme.name,
      component,
      rev: config.rev.theme,
      image: name,
    });
  }
}

/**
 * Parse URL parameters to an object.
 *
 * This is the inverse of formatParams().
 *
 * @param {string|URLSearchParams} search
 */
export function parseParams(search) {
  if (!(search instanceof URLSearchParams)) {
    search = new URLSearchParams(search);
  }

  const parsed = {};

  for (const [key, value] of search.entries()) {
    let keyParts = [];
    for (let [i, part] of key.split(/\[/).entries()) {
      // handle nested
      if (i > 0) {
        if (!part.endsWith(']')) {
          // malformed param
          keyParts = [key];
          break;
        }
        part = part.slice(0, -1);
      }
      keyParts.push(part);
    }

    if (keyParts.at(-1) === '') {
      keyParts.pop();
      let existing = get(parsed, keyParts);
      if (existing) {
        existing.push(value);
      } else {
        set(parsed, keyParts, [value]);
      }
    } else {
      set(parsed, keyParts, value);
    }
  }

  return parsed;
}

/**
 * Get a query string params value from the current url.
 *
 * @deprecated since Totara 19.0, please use parseParams instead, as this
 *   function does not handle escaping or nested fields
 * @param {string} name
 * @return {string}
 */
export function getQueryStringParam(name) {
  const params = window.location.search.substring(1).split('&');
  const parsedParams = {};

  params.forEach(param => {
    const split = param.split('=');
    parsedParams[split[0]] = split[1];
  });

  return parsedParams[name];
}

/**
 * Get a object of query string params from the current url.
 *
 * @deprecated since Totara 19.0, please use parseParams instead, as this
 *   function does not handle escaped keys or fully handle arrays
 * @param {object} params URL parameters window.location.search
 * @return {object}
 */
export function parseQueryString(params) {
  if (!params.startsWith('?') || !params.substring(1)) {
    return {};
  }

  params = params.substring(1).split('&');

  let parsedParams = {};

  params.forEach(item => {
    const split = item.split('=');
    const key = split[0];
    const value = decodeURIComponent(split[1]);
    let keyList = key.split(/\[(.*?)\]/).filter(item => item);
    set(parsedParams, keyList, value);
  });

  return parsedParams;
}
