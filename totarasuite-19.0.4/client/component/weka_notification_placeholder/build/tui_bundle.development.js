/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./client/component/weka_notification_placeholder/src/js sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/).)*$":
/*!*****************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/js/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/).)*$ ***!
  \*****************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./extension\": \"./client/component/weka_notification_placeholder/src/js/extension.js\",\n\t\"./extension.js\": \"./client/component/weka_notification_placeholder/src/js/extension.js\",\n\t\"./plugin\": \"./client/component/weka_notification_placeholder/src/js/plugin.js\",\n\t\"./plugin.js\": \"./client/component/weka_notification_placeholder/src/js/plugin.js\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/weka_notification_placeholder/src/js sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/).)*$\";\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/js/_sync_^(?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \*******************************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/nodes/Placeholder\": \"./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue\",\n\t\"./components/nodes/Placeholder.vue\": \"./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue\",\n\t\"./components/suggestion/Placeholder\": \"./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue\",\n\t\"./components/suggestion/Placeholder.vue\": \"./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/weka_notification_placeholder/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/_sync_^(?");

/***/ }),

/***/ "./server/lib/editor/weka/extensions/notification_placeholder/webapi/ajax/placeholders.graphql":
/*!*****************************************************************************************************!*\
  !*** ./server/lib/editor/weka/extensions/notification_placeholder/webapi/ajax/placeholders.graphql ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"weka_notification_placeholder_placeholders\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"context_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_integer\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"pattern\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_text\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"resolver_class_name\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_text\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"placeholders\"},\"name\":{\"kind\":\"Name\",\"value\":\"weka_notification_placeholder_placeholders\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"context_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"context_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"pattern\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"pattern\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"resolver_class_name\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"resolver_class_name\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"__typename\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"label\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"key\"},\"arguments\":[],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/lib/editor/weka/extensions/notification_placeholder/webapi/ajax/placeholders.graphql?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/tui.json":
/*!*****************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/tui.json ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"weka_notification_placeholder\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"weka_notification_placeholder\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"weka_notification_placeholder\")\ntui._bundle.addModulesFromContext(\"weka_notification_placeholder\", __webpack_require__(\"./client/component/weka_notification_placeholder/src/js sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/).)*$\"));\ntui._bundle.addModulesFromContext(\"weka_notification_placeholder\", __webpack_require__(\"./client/component/weka_notification_placeholder/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/tui.json?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/js/extension.js":
/*!****************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/js/extension.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var weka_notification_placeholder_components_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! weka_notification_placeholder/components/nodes/Placeholder */ \"weka_notification_placeholder/components/nodes/Placeholder\");\n/* harmony import */ var weka_notification_placeholder_components_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(weka_notification_placeholder_components_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var editor_weka_extensions_Base__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! editor_weka/extensions/Base */ \"editor_weka/extensions/Base\");\n/* harmony import */ var editor_weka_extensions_Base__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(editor_weka_extensions_Base__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var _plugin__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./plugin */ \"./client/component/weka_notification_placeholder/src/js/plugin.js\");\n/**\n * This file is part of Totara Enterprise Extensions.\n *\n * Copyright (C) 2021 onwards Totara Learning Solutions LTD\n *\n * Totara Enterprise Extensions is provided only to Totara\n * Learning Solutions LTD's customers and partners, pursuant to\n * the terms and conditions of a separate agreement with Totara\n * Learning Solutions LTD or its affiliate.\n *\n * If you do not have an agreement with Totara Learning Solutions\n * LTD, you may not access, use, modify, or distribute this software.\n * Please contact [licensing@totaralearning.com] for more information.\n *\n * @author Arshad Anwer <arshad.anwer@totaralearning.com>\n * @module weka_notification_placeholder\n */\n\n\n\n\nclass PlaceholderExtension extends (editor_weka_extensions_Base__WEBPACK_IMPORTED_MODULE_1___default()) {\n  nodes() {\n    return {\n      totara_notification_placeholder: {\n        schema: {\n          group: 'inline',\n          inline: true,\n          attrs: {\n            key: {\n              default: undefined\n            },\n            label: {\n              default: undefined\n            }\n          },\n          parseDOM: [{\n            tag: 'span.tui-placeholder__text',\n            getAttrs(dom) {\n              try {\n                return {\n                  key: dom.getAttribute('data-key'),\n                  label: dom.getAttribute('data-label')\n                };\n              } catch (e) {\n                return {};\n              }\n            }\n          }],\n          toDOM(node) {\n            return ['span', {\n              class: 'tui-placeholder__text',\n              'data-key': node.attrs.key,\n              'data-label': node.attrs.label\n            }, '[' + node.attrs.label + ']'];\n          }\n        },\n        component: (weka_notification_placeholder_components_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_0___default())\n      }\n    };\n  }\n  plugins() {\n    return [(0,_plugin__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(this.editor, this.options.resolver_class_name)];\n  }\n}\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (opt => new PlaceholderExtension(opt));\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/js/extension.js?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/js/plugin.js":
/*!*************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/js/plugin.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   REGEX: () => (/* binding */ REGEX),\n/* harmony export */   \"default\": () => (/* export default binding */ __WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/util */ \"tui/util\");\n/* harmony import */ var tui_util__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_util__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var ext_prosemirror_state__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ext_prosemirror/state */ \"ext_prosemirror/state\");\n/* harmony import */ var ext_prosemirror_state__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(ext_prosemirror_state__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var editor_weka_helpers_suggestion__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! editor_weka/helpers/suggestion */ \"editor_weka/helpers/suggestion\");\n/* harmony import */ var editor_weka_helpers_suggestion__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(editor_weka_helpers_suggestion__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var weka_notification_placeholder_components_suggestion_Placeholder__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! weka_notification_placeholder/components/suggestion/Placeholder */ \"weka_notification_placeholder/components/suggestion/Placeholder\");\n/* harmony import */ var weka_notification_placeholder_components_suggestion_Placeholder__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(weka_notification_placeholder_components_suggestion_Placeholder__WEBPACK_IMPORTED_MODULE_3__);\n/**\n * This file is part of Totara Enterprise Extensions.\n *\n * Copyright (C) 2021 onwards Totara Learning Solutions LTD\n *\n * Totara Enterprise Extensions is provided only to Totara\n * Learning Solutions LTD's customers and partners, pursuant to\n * the terms and conditions of a separate agreement with Totara\n * Learning Solutions LTD or its affiliate.\n *\n * If you do not have an agreement with Totara Learning Solutions\n * LTD, you may not access, use, modify, or distribute this software.\n * Please contact [licensing@totaralearning.com] for more information.\n *\n * @author Arshad Anwer <arshad.anwer@totaralearning.com>\n * @module weka_notification_placeholder\n */\n\n\n\n\n\nconst REGEX = new RegExp(`\\\\[([a-z_:]+]?)?$`, 'ig');\n\n/**\n *\n * @param {Editor} editor\n * @param {String} resolverClassName\n * @return {Plugin}\n */\n/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__(editor, resolverClassName) {\n  const key = new ext_prosemirror_state__WEBPACK_IMPORTED_MODULE_1__.PluginKey('placeholders');\n  let suggestion = new (editor_weka_helpers_suggestion__WEBPACK_IMPORTED_MODULE_2___default())(editor);\n  return new ext_prosemirror_state__WEBPACK_IMPORTED_MODULE_1__.Plugin({\n    key: key,\n    view() {\n      return {\n        /**\n         *\n         * @param {EditorView} view\n         */\n        update: (0,tui_util__WEBPACK_IMPORTED_MODULE_0__.debounce)(view => {\n          const {\n            text,\n            active,\n            range\n          } = this.key.getState(view.state);\n          suggestion.destroyInstance();\n          if (!text || !active) {\n            return;\n          } else if (!view.editable) {\n            // Editor is disabled, do not apply anything.\n            return;\n          }\n\n          // remove [ when passing value to state/component\n          const ammendedText = text.slice(1);\n          suggestion.showList({\n            view,\n            component: {\n              name: 'totara_notification_placeholder',\n              component: (weka_notification_placeholder_components_suggestion_Placeholder__WEBPACK_IMPORTED_MODULE_3___default()),\n              attrs: (key, label) => {\n                return {\n                  key: key,\n                  label: label\n                };\n              },\n              props: {\n                resolverClassName: resolverClassName,\n                contextId: editor.identifier.contextId,\n                pattern: ammendedText\n              }\n            },\n            state: {\n              text: ammendedText,\n              active,\n              range\n            }\n          });\n        }, 250)\n      };\n    },\n    state: {\n      init() {\n        return {\n          active: false,\n          range: {},\n          text: null\n        };\n      },\n      /**\n       *\n       * @param {Transaction} transaction\n       * @param {Object} oldState\n       *\n       * @return {Object}\n       */\n      apply(transaction, oldState) {\n        // Reset last index in order to perform the regex again at the start of the string.\n        REGEX.lastIndex = 0;\n        return suggestion.apply(transaction, oldState, REGEX);\n      }\n    },\n    props: {\n      /**\n       *\n       * @param {EditorView} view\n       * @param {KeyboardEvent} event\n       */\n      handleKeyDown(view, event) {\n        if (event.key === 'Escape' || event.key === 'Esc') {\n          const {\n            active\n          } = this.getState(view.state);\n          if (!active) {\n            return false;\n          }\n          suggestion.destroyInstance();\n          view.focus();\n          event.stopPropagation();\n\n          // Returning true to stop the the propagation in the parent editor.\n          return true;\n        }\n      }\n    }\n  });\n}\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/js/plugin.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var editor_weka_components_nodes_BaseNode__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! editor_weka/components/nodes/BaseNode */ \"editor_weka/components/nodes/BaseNode\");\n/* harmony import */ var editor_weka_components_nodes_BaseNode__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(editor_weka_components_nodes_BaseNode__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var totara_notification_components_json_editor_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! totara_notification/components/json_editor/nodes/Placeholder */ \"totara_notification/components/json_editor/nodes/Placeholder\");\n/* harmony import */ var totara_notification_components_json_editor_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(totara_notification_components_json_editor_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Placeholder: (totara_notification_components_json_editor_nodes_Placeholder__WEBPACK_IMPORTED_MODULE_1___default())\n  },\n  extends: (editor_weka_components_nodes_BaseNode__WEBPACK_IMPORTED_MODULE_0___default()),\n  computed: {\n    placeholderKey() {\n      const attrs = this.attrs;\n      if (!attrs.key) {\n        return '';\n      }\n      return attrs.key;\n    },\n    displayName() {\n      const attrs = this.attrs;\n      if (!attrs.label) {\n        return '';\n      }\n      return attrs.label;\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/dropdown/Dropdown */ \"tui/components/dropdown/Dropdown\");\n/* harmony import */ var tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/dropdown/DropdownItem */ \"tui/components/dropdown/DropdownItem\");\n/* harmony import */ var tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_loading_Loader__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/loading/Loader */ \"tui/components/loading/Loader\");\n/* harmony import */ var tui_components_loading_Loader__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_loading_Loader__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var weka_notification_placeholder_graphql_placeholders__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! weka_notification_placeholder/graphql/placeholders */ \"./server/lib/editor/weka/extensions/notification_placeholder/webapi/ajax/placeholders.graphql\");\n\n\n\n\n// GraphQL queries\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Dropdown: (tui_components_dropdown_Dropdown__WEBPACK_IMPORTED_MODULE_0___default()),\n    DropdownItem: (tui_components_dropdown_DropdownItem__WEBPACK_IMPORTED_MODULE_1___default()),\n    Loader: (tui_components_loading_Loader__WEBPACK_IMPORTED_MODULE_2___default())\n  },\n  props: {\n    contextId: {\n      type: [Number, String],\n      required: true\n    },\n    resolverClassName: {\n      type: String,\n      required: true\n    },\n    location: {\n      required: true,\n      type: Object\n    },\n    pattern: {\n      required: true,\n      type: String\n    }\n  },\n  emits: ['dismiss', 'item-selected'],\n  apollo: {\n    placeholders: {\n      query: weka_notification_placeholder_graphql_placeholders__WEBPACK_IMPORTED_MODULE_3__[\"default\"],\n      fetchPolicy: 'network-only',\n      variables() {\n        return {\n          pattern: this.pattern,\n          context_id: this.contextId,\n          resolver_class_name: this.resolverClassName\n        };\n      }\n    }\n  },\n  data() {\n    return {\n      placeholders: []\n    };\n  },\n  computed: {\n    showSuggestions() {\n      return this.$apollo.loading || this.placeholders.length > 0;\n    },\n    positionStyle() {\n      return {\n        left: `${this.location.x}px`,\n        top: `${this.location.y}px`\n      };\n    }\n  },\n  watch: {\n    showSuggestions(active) {\n      if (!active) {\n        this.$emit('dismiss');\n      }\n    }\n  },\n  methods: {\n    /**\n     *\n     * @param {Number} key\n     * @param {String} label\n     */\n    pickPlaceholder({\n      key,\n      label\n    }) {\n      this.$emit('item-selected', {\n        id: key,\n        text: label\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794 ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Placeholder = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Placeholder\", true);\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_Placeholder, {\n    \"placeholder-key\": $options.placeholderKey,\n    \"display-name\": $options.displayName\n  }, null, 8 /* PROPS */, [\"placeholder-key\", \"display-name\"]);\n}\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"sr-only\"\n};\nconst _hoisted_2 = {\n  class: \"tui-wekaPlaceholderSuggestion__label\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Loader = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Loader\");\n  const _component_DropdownItem = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"DropdownItem\");\n  const _component_Dropdown = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Dropdown\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n    class: \"tui-wekaPlaceholderSuggestion\",\n    style: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeStyle)($options.positionStyle)\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Dropdown, {\n    separator: true,\n    open: $options.showSuggestions,\n    \"inline-menu\": true,\n    onDismiss: _cache[0] || (_cache[0] = $event => _ctx.$emit('dismiss'))\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_1, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:matching_placeholders,editor_weka##\") + \": \", 1 /* TEXT */), _ctx.$apollo.loading ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_DropdownItem, {\n      key: 0,\n      disabled: true\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Loader, {\n        loading: true\n      })]),\n      _: 1 /* STABLE */\n    })) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($data.placeholders, (placeholder, index) => {\n      return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_DropdownItem, {\n        key: index,\n        \"no-padding\": true,\n        onClick: $event => $options.pickPlaceholder(placeholder)\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(placeholder.label), 1 /* TEXT */)]),\n        _: 2 /* DYNAMIC */\n      }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"onClick\"]);\n    }), 128 /* KEYED_FRAGMENT */))]),\n    _: 1 /* STABLE */\n  }, 8 /* PROPS */, [\"open\"])], 4 /* STYLE */);\n}\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794":
/*!***************************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794 ***!
  \***************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Placeholder_vue_vue_type_template_id_31426794__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Placeholder_vue_vue_type_template_id_31426794__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Placeholder.vue?vue&type=template&id=31426794 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794\");\n\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e":
/*!********************************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e ***!
  \********************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Placeholder_vue_vue_type_template_id_9d46c78e__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_Placeholder_vue_vue_type_template_id_9d46c78e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./Placeholder.vue?vue&type=template&id=9d46c78e */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e\");\n\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1591.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1591.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1591.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1591.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1591.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1591.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1591.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1591.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1591.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue":
/*!*********************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Placeholder_vue_vue_type_template_id_31426794__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Placeholder.vue?vue&type=template&id=31426794 */ \"./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=template&id=31426794\");\n/* harmony import */ var _Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Placeholder.vue?vue&type=script&lang=js */ \"./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Placeholder_vue_vue_type_template_id_31426794__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue":
/*!**************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _Placeholder_vue_vue_type_template_id_9d46c78e__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Placeholder.vue?vue&type=template&id=9d46c78e */ \"./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=template&id=9d46c78e\");\n/* harmony import */ var _Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Placeholder.vue?vue&type=script&lang=js */ \"./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js\");\n/* harmony import */ var _Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss */ \"./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_Placeholder_vue_vue_type_template_id_9d46c78e__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Placeholder.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/nodes/Placeholder.vue?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1588_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_Placeholder_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./Placeholder.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1588.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?");

/***/ }),

/***/ "./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss":
/*!***********************************************************************************************************************************************!*\
  !*** ./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss ***!
  \***********************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1591.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1591.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1591.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1591.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1591.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1591.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?vue&type=style&index=0&id=9d46c78e&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1591_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1591_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1591_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_Placeholder_vue_vue_type_style_index_0_id_9d46c78e_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/weka_notification_placeholder/src/components/suggestion/Placeholder.vue?");

/***/ }),

/***/ "editor_weka/components/nodes/BaseNode":
/*!*************************************************************************!*\
  !*** external "tui.require(\"editor_weka/components/nodes/BaseNode\")" ***!
  \*************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("editor_weka/components/nodes/BaseNode");

/***/ }),

/***/ "editor_weka/extensions/Base":
/*!***************************************************************!*\
  !*** external "tui.require(\"editor_weka/extensions/Base\")" ***!
  \***************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("editor_weka/extensions/Base");

/***/ }),

/***/ "editor_weka/helpers/suggestion":
/*!******************************************************************!*\
  !*** external "tui.require(\"editor_weka/helpers/suggestion\")" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("editor_weka/helpers/suggestion");

/***/ }),

/***/ "ext_prosemirror/state":
/*!*********************************************************!*\
  !*** external "tui.require(\"ext_prosemirror/state\")" ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("ext_prosemirror/state");

/***/ }),

/***/ "totara_notification/components/json_editor/nodes/Placeholder":
/*!************************************************************************************************!*\
  !*** external "tui.require(\"totara_notification/components/json_editor/nodes/Placeholder\")" ***!
  \************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_notification/components/json_editor/nodes/Placeholder");

/***/ }),

/***/ "tui/components/dropdown/DropdownItem":
/*!************************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/DropdownItem\")" ***!
  \************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/DropdownItem");

/***/ }),

/***/ "tui/components/dropdown/Dropdown":
/*!********************************************************************!*\
  !*** external "tui.require(\"tui/components/dropdown/Dropdown\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/dropdown/Dropdown");

/***/ }),

/***/ "tui/components/loading/Loader":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/loading/Loader\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/loading/Loader");

/***/ }),

/***/ "tui/util":
/*!********************************************!*\
  !*** external "tui.require(\"tui/util\")" ***!
  \********************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/util");

/***/ }),

/***/ "vue":
/*!***************************************!*\
  !*** external "tui.require(\"vue\")" ***!
  \***************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("vue");

/***/ }),

/***/ "weka_notification_placeholder/components/nodes/Placeholder":
/*!**********************************************************************************************!*\
  !*** external "tui.require(\"weka_notification_placeholder/components/nodes/Placeholder\")" ***!
  \**********************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("weka_notification_placeholder/components/nodes/Placeholder");

/***/ }),

/***/ "weka_notification_placeholder/components/suggestion/Placeholder":
/*!***************************************************************************************************!*\
  !*** external "tui.require(\"weka_notification_placeholder/components/suggestion/Placeholder\")" ***!
  \***************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("weka_notification_placeholder/components/suggestion/Placeholder");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/weka_notification_placeholder/tui.json");
/******/ 	
/******/ })()
;