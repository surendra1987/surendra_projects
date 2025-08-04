/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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

const t = require('@babel/types');

function createState({ filename }) {
  return {
    filename: filename,
    isVue: filename && filename.includes('.vue'),
  };
}

/**
 * @param {import('@babel/core').NodePath<import('@babel/types').ImportSpecifier>} importSpecifierPath
 * @returns
 */
function getImportSpecifierImportedName(importSpecifierPath) {
  const importedNode = importSpecifierPath.node.imported;
  if (t.isIdentifier(importedNode)) {
    return importedNode.name;
  } else if (t.isStringLiteral(importedNode)) {
    return importedNode.value;
  } else {
    throw new Error(
      `Expected import specifier to be either Identifier or StringLiteral, got ${importedNode.type}`
    );
  }
}

function stringOpLiteral(args, op) {
  const [keyArg, compArg] = args;
  if (compArg && !t.isStringLiteral(compArg)) {
    return null; // cannot replace dynamic component arg
  }
  const comp = compArg ? compArg.value : 'core';
  if (t.isStringLiteral(keyArg)) {
    // regular getString('foo', 'core') call
    return t.stringLiteral(`##str:${op}:${keyArg.value},${comp}##`);
  } else if (
    t.isConditionalExpression(keyArg) &&
    t.isStringLiteral(keyArg.consequent) &&
    t.isStringLiteral(keyArg.alternate)
  ) {
    // ternary key -- getString(var ? 'foo' : 'bar', 'core')
    return t.conditionalExpression(
      keyArg.test,
      t.stringLiteral(`##str:${op}:${keyArg.consequent.value},${comp}##`),
      t.stringLiteral(`##str:${op}:${keyArg.alternate.value},${comp}##`)
    );
  }

  // cannot replace - dynamic arguments
  return null;
}

/**
 *
 * @param {import('@babel/core').NodePath<import('@babel/types').CallExpression>} path
 * @param {string} op
 * @returns
 */
function stringOpCall(path, op) {
  const args = path.node.arguments;
  const replacementLiteral = stringOpLiteral(args, op);
  if (!replacementLiteral) {
    return null;
  }
  if (op === 'get') {
    if (args.length > 2) {
      return t.callExpression(
        t.memberExpression(path.node.callee, t.identifier('__r')),
        [replacementLiteral, ...args.slice(2)]
      );
    }
  }
  return replacementLiteral;
}

function replaceStringOpCall(path, op) {
  const replacement = stringOpCall(path, op);
  if (replacement) {
    path.replaceWith(replacement);
  }
}

/**
 * @param {import('@babel/core').NodePath} path
 * @param {string} name
 * @param {string} from
 * @param {string} filename
 */
function handleImportedCall(path, name, from, filename) {
  if (
    from === 'tui/i18n' ||
    (from.endsWith('./i18n') && /[/\\]tui[/\\]src[/\\]/.test(filename))
  ) {
    if (name === 'getString') {
      replaceStringOpCall(path, 'get');
    }
    if (name === 'hasString') {
      replaceStringOpCall(path, 'has');
    }
    if (name === 'langString') {
      const args = path.node.arguments;
      const replacementLiteral = stringOpLiteral(args, 'get');
      if (!replacementLiteral) {
        return;
      }
      const exp = t.callExpression(
        t.memberExpression(path.node.callee, t.identifier('__p')),
        [replacementLiteral, ...args]
      );
      path.replaceWith(exp);
    }
  }
}

/**
 * @param {import('@babel/core').NodePath} path
 */
function handleVueMethodCall(path, name) {
  switch (name) {
    case '$str':
      replaceStringOpCall(path, 'get');
      break;
    case '$tryStr':
      replaceStringOpCall(path, 'try');
      break;
    case '$hasStr':
      replaceStringOpCall(path, 'has');
      break;
  }
}

const traverser = {
  /** @param {import('@babel/core').NodePath} path */
  CallExpression(path, state) {
    const callee = path.node.callee;
    if (t.isIdentifier(callee)) {
      const binding = path.scope.getBinding(callee.name);
      if (binding && binding.kind === 'module' && binding.path.parentPath) {
        if (t.isImportSpecifier(binding.path.node)) {
          const importedName = getImportSpecifierImportedName(binding.path);
          const importedFrom = binding.path.parentPath.node.source.value;

          handleImportedCall(path, importedName, importedFrom, state.filename);
        }
        // can't be an ImportNamespaceSpecifier as it's an identifier callee (handled below)
        // there is ImportDefaultSpecifier too but i18n doesn't have a default export so we can ignore that
        return;
      }
    } else if (t.isMemberExpression(callee)) {
      if (t.isIdentifier(callee.object) && t.isIdentifier(callee.property)) {
        const binding = path.scope.getBinding(callee.object.name);
        if (binding && t.isImportNamespaceSpecifier(binding.path.node)) {
          const importedFrom = binding.path.parentPath.node.source.value;
          const calledName = callee.property.name;
          handleImportedCall(path, calledName, importedFrom);
        }
      }
      if (
        state.isVue &&
        (t.isThisExpression(callee.object) ||
          (t.isIdentifier(callee.object) && callee.object.name === '_ctx'))
      ) {
        if (t.isIdentifier(callee.property)) {
          handleVueMethodCall(path, callee.property.name);
        }
      }
    }
  },
};

module.exports = function() {
  return {
    visitor: {
      /**
       * @param {import('@babel/core').NodePath<import('@babel/types').Program>} path
       * @param {*} state
       */
      Program(path, state) {
        const subState = createState(state);
        // fast bail out if no tui/i18n import or not a vue file
        if (!subState.isVue && !state.file.code.includes('/i18n')) {
          return;
        }
        path.traverse(traverser, subState);
      },
    },
  };
};
