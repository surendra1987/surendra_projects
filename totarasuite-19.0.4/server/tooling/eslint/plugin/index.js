/* eslint-env node */
/* eslint-disable */

const { Kind } = require('graphql');
const {
  requireGraphQLSchemaFromContext,
} = require('@graphql-eslint/eslint-plugin');

/**
 * @param {import("graphql").SelectionSetNode} ss
 * @param {string} name
 * @returns {boolean}
 */
function selectionSetHasExact(ss, name) {
  return ss.selections.some(
    x => x.kind === Kind.FIELD && x.name.value === name && !x.alias
  );
}

/**
 * @param {import("graphql").SelectionSetNode} ss
 * @returns {boolean}
 */
function selectionSetIsNormalised(ss) {
  if (
    selectionSetHasExact(ss, '__typename') &&
    selectionSetHasExact(ss, 'id')
  ) {
    return true;
  }

  // check fragments
  const inlineFragments = ss.selections.filter(
    x => x.kind == Kind.INLINE_FRAGMENT
  );
  if (
    inlineFragments.length > 0 &&
    inlineFragments.every(x => selectionSetIsNormalised(x.selectionSet))
  ) {
    return true;
  }

  return false;
}

module.exports = {
  rules: {
    /** @type {import("@graphql-eslint/eslint-plugin").GraphQLESLintRule<[], true>}  */
    'avoid-clobber': {
      create: context => {
        requireGraphQLSchemaFromContext('avoid-clobber', context);
        return ({
          Field(node) {
            if (
              // we don't care about scalar fields, only object fields
              !node.selectionSet ||
              // if parent is not normalised, this node doesn't need to be either
              !selectionSetIsNormalised(node.parent)
            ) {
              return;
            }

            const typeInfo = node.typeInfo();
            const astNode = typeInfo.fieldDef?.astNode;

            if (
              // fields with @cache(merge: ...) directives are fine
              astNode?.directives?.some(
                x =>
                  x.name.value === 'cache' &&
                  x.arguments?.some(x => x.name.value === 'merge')
              ) ||
              // list fields are also fine, as apollo will replace them without complaining
              astNode.type.kind === Kind.LIST_TYPE ||
              (astNode.type.kind === Kind.NON_NULL_TYPE &&
                astNode.type.type.kind === Kind.LIST_TYPE)
            ) {
              return;
            }

            if (
              // is an object field
              node.selectionSet &&
              // parent is normalised
              selectionSetIsNormalised(node.parent) &&
              // current is not normalised
              !selectionSetIsNormalised(node.selectionSet)
            ) {
              context.report({
                node: node.selectionSet,
                message:
                  'Cache data may be lost when replacing this non-normalised field of a normalised object. ' +
                  'More information: https://totara.atlassian.net/wiki/spaces/DEV/pages/121187767/Developing+for+the+AJAX+GraphQL+API',
              });
            }
          },
        });
      },
    },
  },
};
