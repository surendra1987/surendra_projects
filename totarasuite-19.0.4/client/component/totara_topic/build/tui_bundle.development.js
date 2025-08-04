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

/***/ "./client/component/totara_topic/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!**************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/totara_topic/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \**************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/form/TopicsSelector\": \"./client/component/totara_topic/src/components/form/TopicsSelector.vue\",\n\t\"./components/form/TopicsSelector.vue\": \"./client/component/totara_topic/src/components/form/TopicsSelector.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/totara_topic/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/_sync_^(?");

/***/ }),

/***/ "./server/totara/topic/webapi/ajax/find_topics.graphql":
/*!*************************************************************!*\
  !*** ./server/totara/topic/webapi/ajax/find_topics.graphql ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"totara_topic_find_topics\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"search\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_text\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"exclude\"}},\"type\":{\"kind\":\"ListType\",\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"param_integer\"}}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"topics\"},\"name\":{\"kind\":\"Name\",\"value\":\"totara_topic_find_topics\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"search\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"search\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"exclude\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"exclude\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"__typename\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"value\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"PLAIN\"}}],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"catalog\"},\"arguments\":[],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/topic/webapi/ajax/find_topics.graphql?");

/***/ }),

/***/ "./client/component/totara_topic/tui.json":
/*!************************************************!*\
  !*** ./client/component/totara_topic/tui.json ***!
  \************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"totara_topic\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"totara_topic\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"totara_topic\")\ntui._bundle.addModulesFromContext(\"totara_topic\", __webpack_require__(\"./client/component/totara_topic/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/totara_topic/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_tag_TagList__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/tag/TagList */ \"tui/components/tag/TagList\");\n/* harmony import */ var tui_components_tag_TagList__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_tag_TagList__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var totara_topic_graphql_find_topics__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! totara_topic/graphql/find_topics */ \"./server/totara/topic/webapi/ajax/find_topics.graphql\");\n\n\n// GraphQL queries\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    TagList: (tui_components_tag_TagList__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    selectedTopics: {\n      type: Array,\n      default() {\n        return [];\n      },\n      validator(prop) {\n        let items = Array.prototype.filter.call(prop, item => {\n          return !('value' in item) || !('id' in item);\n        });\n        return 0 === items.length;\n      }\n    },\n    inputPlaceholder: String,\n    disabled: {\n      type: Boolean,\n      default: false\n    }\n  },\n  emits: ['change'],\n  apollo: {\n    topics: {\n      query: totara_topic_graphql_find_topics__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n      variables() {\n        return {\n          search: this.searchTerm,\n          exclude: this.selectedTopicIds\n        };\n      }\n    }\n  },\n  data() {\n    return {\n      searchTerm: ''\n    };\n  },\n  computed: {\n    selectedTopicIds() {\n      if (0 === this.selectedTopics.length) {\n        return [];\n      }\n      return Array.prototype.map.call(this.selectedTopics, ({\n        id\n      }) => id);\n    },\n    pickedTopics() {\n      return Array.prototype.map.call(this.selectedTopics, ({\n        value,\n        id\n      }) => {\n        return {\n          id: id,\n          text: value\n        };\n      });\n    },\n    /**\n     * Get the topics to display in the topic dropdown (topics less selected topics).\n     * Reduces number of ajax requests, and improves UI response time\n     */\n    displayTopics() {\n      if (!this.topics) {\n        return [];\n      }\n      return this.topics.filter(topic => {\n        let contains = this.selectedTopics.filter(selected => {\n          return selected.id == topic.id;\n        });\n        return contains.length === 0;\n      });\n    }\n  },\n  methods: {\n    /**\n     * Selects a topic and moves it to the selected tags list\n     *\n     * @param {Number} id the id of the tag being selected\n     * @param {String} value the human readible name of the tag beins selected\n     */\n    async selectTopic({\n      id,\n      value\n    }) {\n      const selectedTopics = Array.prototype.concat.call(this.selectedTopics, {\n        id,\n        value\n      });\n      this.$emit('change', selectedTopics);\n      await this.$nextTick();\n      if (this.displayTopics.length === 0) {\n        // all viewable items have been selected - reset the filter\n        this.searchTerm = '';\n        this.$apollo.queries.topics.refetch();\n      }\n    },\n    /**\n     *\n     * @param {Number} id\n     */\n    removeTopic({\n      id\n    }) {\n      const selectedTopics = Array.prototype.filter.call(this.selectedTopics, item => {\n        return item.id !== id;\n      });\n\n      // Need to update the list again.\n      this.$emit('change', selectedTopics);\n      this.$apollo.queries.topics.refetch();\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500 ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_TagList = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"TagList\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_TagList, {\n    class: \"tui-topicsSelector\",\n    filter: $data.searchTerm,\n    items: $options.displayTopics,\n    tags: $options.pickedTopics,\n    \"input-placeholder\": $props.inputPlaceholder,\n    loading: _ctx.$apollo.loading,\n    disabled: $props.disabled,\n    onFilter: _cache[0] || (_cache[0] = $event => $data.searchTerm = $event),\n    onSelect: $options.selectTopic,\n    onRemove: $options.removeTopic\n  }, {\n    item: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      item: {\n        value\n      }\n    }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(value), 1 /* TEXT */)]),\n    _: 1 /* STABLE */\n  }, 8 /* PROPS */, [\"filter\", \"items\", \"tags\", \"input-placeholder\", \"loading\", \"disabled\", \"onSelect\", \"onRemove\"]);\n}\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500":
/*!************************************************************************************************************!*\
  !*** ./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500 ***!
  \************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1528_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_TopicsSelector_vue_vue_type_template_id_321df500__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1528_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_TopicsSelector_vue_vue_type_template_id_321df500__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./TopicsSelector.vue?vue&type=template&id=321df500 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500\");\n\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1531.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1531.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1531.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1531.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1531.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1531.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1531.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1531.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1531.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/totara_topic/src/components/form/TopicsSelector.vue":
/*!******************************************************************************!*\
  !*** ./client/component/totara_topic/src/components/form/TopicsSelector.vue ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _TopicsSelector_vue_vue_type_template_id_321df500__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TopicsSelector.vue?vue&type=template&id=321df500 */ \"./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=template&id=321df500\");\n/* harmony import */ var _TopicsSelector_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TopicsSelector.vue?vue&type=script&lang=js */ \"./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js\");\n/* harmony import */ var _TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss */ \"./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_TopicsSelector_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_TopicsSelector_vue_vue_type_template_id_321df500__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/totara_topic/src/components/form/TopicsSelector.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?");

/***/ }),

/***/ "./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js":
/*!******************************************************************************************************!*\
  !*** ./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1528_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_TopicsSelector_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1528_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_TopicsSelector_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./TopicsSelector.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1528.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?");

/***/ }),

/***/ "./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss":
/*!***************************************************************************************************************************!*\
  !*** ./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss ***!
  \***************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1531.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1531.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1531.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1531.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1531.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1531.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/totara_topic/src/components/form/TopicsSelector.vue?vue&type=style&index=0&id=321df500&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1531_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1531_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1531_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TopicsSelector_vue_vue_type_style_index_0_id_321df500_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/totara_topic/src/components/form/TopicsSelector.vue?");

/***/ }),

/***/ "tui/components/tag/TagList":
/*!**************************************************************!*\
  !*** external "tui.require(\"tui/components/tag/TagList\")" ***!
  \**************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/tag/TagList");

/***/ }),

/***/ "vue":
/*!***************************************!*\
  !*** external "tui.require(\"vue\")" ***!
  \***************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("vue");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/totara_topic/tui.json");
/******/ 	
/******/ })()
;