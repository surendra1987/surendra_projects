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

/***/ "./client/component/criteria_othercompetency/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/criteria_othercompetency/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/achievements/AchievementDisplay\": \"./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue\",\n\t\"./components/achievements/AchievementDisplay.vue\": \"./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/criteria_othercompetency/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/_sync_^(?");

/***/ }),

/***/ "./server/totara/criteria/plugins/othercompetency/webapi/ajax/achievements.graphql":
/*!*****************************************************************************************!*\
  !*** ./server/totara/criteria/plugins/othercompetency/webapi/ajax/achievements.graphql ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"criteria_othercompetency_achievements\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"instance_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"criteria_othercompetency_achievements\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"instance_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"instance_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"current_user\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"aggregation_method\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"required_items\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"is_valid\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"items\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"assigned\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"self_assignable\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"competency\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"fullname\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"description\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"value\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"proficient\"},\"arguments\":[],\"directives\":[]}]}}]}}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/criteria/plugins/othercompetency/webapi/ajax/achievements.graphql?");

/***/ }),

/***/ "./client/component/criteria_othercompetency/tui.json":
/*!************************************************************!*\
  !*** ./client/component/criteria_othercompetency/tui.json ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"criteria_othercompetency\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"criteria_othercompetency\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"criteria_othercompetency\")\ntui._bundle.addModulesFromContext(\"criteria_othercompetency\", __webpack_require__(\"./client/component/criteria_othercompetency/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var totara_criteria_components_achievements_CompetencyAchievementDisplay__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! totara_criteria/components/achievements/CompetencyAchievementDisplay */ \"totara_criteria/components/achievements/CompetencyAchievementDisplay\");\n/* harmony import */ var totara_criteria_components_achievements_CompetencyAchievementDisplay__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(totara_criteria_components_achievements_CompetencyAchievementDisplay__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var criteria_othercompetency_graphql_achievements__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! criteria_othercompetency/graphql/achievements */ \"./server/totara/criteria/plugins/othercompetency/webapi/ajax/achievements.graphql\");\n// Components\n\n// GraphQL\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    CompetencyAchievementDisplay: (totara_criteria_components_achievements_CompetencyAchievementDisplay__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    assignmentId: {\n      required: true,\n      type: Number\n    },\n    instanceId: {\n      required: true,\n      type: Number\n    },\n    userId: {\n      required: true,\n      type: Number\n    }\n  },\n  emits: ['loaded'],\n  data() {\n    return {\n      achievements: {\n        items: []\n      }\n    };\n  },\n  apollo: {\n    /**\n     * Fetch a criteria set for other competency completion\n     *\n     * @return {Object}\n     */\n    achievements: {\n      query: criteria_othercompetency_graphql_achievements__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n      context: {\n        batch: true\n      },\n      variables() {\n        return {\n          assignment_id: this.assignmentId,\n          instance_id: this.instanceId,\n          user_id: this.userId\n        };\n      },\n      update({\n        criteria_othercompetency_achievements: achievements\n      }) {\n        this.$emit('loaded');\n        return achievements;\n      }\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_CompetencyAchievementDisplay = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"CompetencyAchievementDisplay\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_CompetencyAchievementDisplay, {\n    type: \"otherCompetency\",\n    achievements: $data.achievements,\n    \"user-id\": $props.userId,\n    onSelfAssigned: _cache[0] || (_cache[0] = $event => _ctx.$apollo.queries.achievements.refetch())\n  }, null, 8 /* PROPS */, [\"achievements\", \"user-id\"]);\n}\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec":
/*!************************************************************************************************************************************!*\
  !*** ./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec ***!
  \************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_975_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_fb969dec__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_975_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_fb969dec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./AchievementDisplay.vue?vue&type=template&id=fb969dec */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec\");\n\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue":
/*!******************************************************************************************************!*\
  !*** ./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _AchievementDisplay_vue_vue_type_template_id_fb969dec__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=template&id=fb969dec */ \"./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=fb969dec\");\n/* harmony import */ var _AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=script&lang=js */ \"./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_AchievementDisplay_vue_vue_type_template_id_fb969dec__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************!*\
  !*** ./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_975_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_975_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./AchievementDisplay.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-975.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/criteria_othercompetency/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "totara_criteria/components/achievements/CompetencyAchievementDisplay":
/*!********************************************************************************************************!*\
  !*** external "tui.require(\"totara_criteria/components/achievements/CompetencyAchievementDisplay\")" ***!
  \********************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_criteria/components/achievements/CompetencyAchievementDisplay");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/criteria_othercompetency/tui.json");
/******/ 	
/******/ })()
;