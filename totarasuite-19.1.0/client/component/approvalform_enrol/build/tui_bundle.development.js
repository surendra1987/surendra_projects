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

/***/ "./client/component/approvalform_enrol/src/js sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/).)*$":
/*!******************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/js/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/).)*$ ***!
  \******************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./approvalform\": \"./client/component/approvalform_enrol/src/js/approvalform.js\",\n\t\"./approvalform.js\": \"./client/component/approvalform_enrol/src/js/approvalform.js\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/approvalform_enrol/src/js sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/).)*$\";\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/js/_sync_^(?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!********************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \********************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/CourseLink\": \"./client/component/approvalform_enrol/src/components/CourseLink.vue\",\n\t\"./components/CourseLink.vue\": \"./client/component/approvalform_enrol/src/components/CourseLink.vue\",\n\t\"./components/CourseLinkInputSized\": \"./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue\",\n\t\"./components/CourseLinkInputSized.vue\": \"./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/approvalform_enrol/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/_sync_^(?");

/***/ }),

/***/ "./client/component/approvalform_enrol/tui.json":
/*!******************************************************!*\
  !*** ./client/component/approvalform_enrol/tui.json ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"approvalform_enrol\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"approvalform_enrol\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"approvalform_enrol\")\ntui._bundle.addModulesFromContext(\"approvalform_enrol\", __webpack_require__(\"./client/component/approvalform_enrol/src/js sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/).)*$\"));\ntui._bundle.addModulesFromContext(\"approvalform_enrol\", __webpack_require__(\"./client/component/approvalform_enrol/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/tui.json?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/js/approvalform.js":
/*!********************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/js/approvalform.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   fields: () => (/* binding */ fields)\n/* harmony export */ });\n/* harmony import */ var approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! approvalform_enrol/components/CourseLink */ \"approvalform_enrol/components/CourseLink\");\n/* harmony import */ var approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var approvalform_enrol_components_CourseLinkInputSized__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! approvalform_enrol/components/CourseLinkInputSized */ \"approvalform_enrol/components/CourseLinkInputSized\");\n/* harmony import */ var approvalform_enrol_components_CourseLinkInputSized__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(approvalform_enrol_components_CourseLinkInputSized__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var mod_approval_schema_form__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! mod_approval/schema_form */ \"mod_approval/schema_form\");\n/* harmony import */ var mod_approval_schema_form__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(mod_approval_schema_form__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tui/components/uniform */ \"tui/components/uniform\");\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(tui_components_uniform__WEBPACK_IMPORTED_MODULE_3__);\n/**\n * This file is part of Totara Enterprise Extensions.\n *\n * Copyright (C) 2023 onwards Totara Learning Solutions LTD\n *\n * Totara Enterprise Extensions is provided only to Totara\n * Learning Solutions LTD's customers and partners, pursuant to\n * the terms and conditions of a separate agreement with Totara\n * Learning Solutions LTD or its affiliate.\n *\n * If you do not have an agreement with Totara Learning Solutions\n * LTD, you may not access, use, modify, or distribute this software.\n * Please contact [licensing@totara.com] for more information.\n *\n * @author Jack Humphrey <jack.humphrey@totara.com>\n * @module approvalform_enrol\n */\n\n\n\n\n\nconst CourseLinkConnected = (0,mod_approval_schema_form__WEBPACK_IMPORTED_MODULE_2__.uniformFieldWrapper)((0,tui_components_uniform__WEBPACK_IMPORTED_MODULE_3__.createUniformInputWrapper)((approvalform_enrol_components_CourseLinkInputSized__WEBPACK_IMPORTED_MODULE_1___default())));\nconst fields = {\n  course_link: {\n    supports: {\n      edit: false\n    },\n    fieldComponent: CourseLinkConnected,\n    viewFieldComponent: (approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0___default()),\n    displayText(value) {\n      if (!value) {\n        return null;\n      }\n      try {\n        const data = JSON.parse(value);\n        return data.name;\n      } catch (e) {\n        return null;\n      }\n    }\n  }\n};\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/js/approvalform.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  props: {\n    value: String\n  },\n  computed: {\n    parsedValue() {\n      if (!this.value) {\n        return null;\n      }\n      try {\n        const data = JSON.parse(this.value);\n        return data;\n      } catch (e) {\n        return null;\n      }\n    },\n    url() {\n      return this.parsedValue && this.parsedValue.url || '#';\n    },\n    showPlainName() {\n      if (!this.parsedValue) {\n        return true;\n      }\n      return this.parsedValue.course_deleted ?? false;\n    },\n    name() {\n      return this.parsedValue && this.parsedValue.name || \"##str:get:course_name_placeholder,approvalform_enrol##\";\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLink.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! approvalform_enrol/components/CourseLink */ \"approvalform_enrol/components/CourseLink\");\n/* harmony import */ var approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/form/InputSizedText */ \"tui/components/form/InputSizedText\");\n/* harmony import */ var tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1__);\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    InputSizedText: (tui_components_form_InputSizedText__WEBPACK_IMPORTED_MODULE_1___default()),\n    CourseLink: (approvalform_enrol_components_CourseLink__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    value: String\n  }\n});\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  key: 0\n};\nconst _hoisted_2 = [\"href\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return $options.showPlainName ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"span\", _hoisted_1, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.name), 1 /* TEXT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"a\", {\n    key: 1,\n    href: $options.url\n  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($options.name), 9 /* TEXT, PROPS */, _hoisted_2));\n}\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLink.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a":
/*!**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a ***!
  \**********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_CourseLink = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"CourseLink\");\n  const _component_InputSizedText = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"InputSizedText\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_InputSizedText, null, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_CourseLink, {\n      value: $props.value\n    }, null, 8 /* PROPS */, [\"value\"])]),\n    _: 1 /* STABLE */\n  });\n}\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c":
/*!*********************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_CourseLink_vue_vue_type_template_id_e8c74b0c__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_CourseLink_vue_vue_type_template_id_e8c74b0c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./CourseLink.vue?vue&type=template&id=e8c74b0c */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c\");\n\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLink.vue?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a":
/*!*******************************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a ***!
  \*******************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_CourseLinkInputSized_vue_vue_type_template_id_3de7731a__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_CourseLinkInputSized_vue_vue_type_template_id_3de7731a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./CourseLinkInputSized.vue?vue&type=template&id=3de7731a */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a\");\n\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLink.vue":
/*!***************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLink.vue ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _CourseLink_vue_vue_type_template_id_e8c74b0c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CourseLink.vue?vue&type=template&id=e8c74b0c */ \"./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=template&id=e8c74b0c\");\n/* harmony import */ var _CourseLink_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CourseLink.vue?vue&type=script&lang=js */ \"./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_CourseLink_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_CourseLink_vue_vue_type_template_id_e8c74b0c__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/approvalform_enrol/src/components/CourseLink.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLink.vue?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue":
/*!*************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _CourseLinkInputSized_vue_vue_type_template_id_3de7731a__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CourseLinkInputSized.vue?vue&type=template&id=3de7731a */ \"./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=template&id=3de7731a\");\n/* harmony import */ var _CourseLinkInputSized_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CourseLinkInputSized.vue?vue&type=script&lang=js */ \"./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_CourseLinkInputSized_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_CourseLinkInputSized_vue_vue_type_template_id_3de7731a__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js":
/*!***************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_CourseLink_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_CourseLink_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./CourseLink.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLink.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLink.vue?");

/***/ }),

/***/ "./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js":
/*!*************************************************************************************************************!*\
  !*** ./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js ***!
  \*************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_CourseLinkInputSized_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_825_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_CourseLinkInputSized_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./CourseLinkInputSized.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-825.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/approvalform_enrol/src/components/CourseLinkInputSized.vue?");

/***/ }),

/***/ "approvalform_enrol/components/CourseLinkInputSized":
/*!**************************************************************************************!*\
  !*** external "tui.require(\"approvalform_enrol/components/CourseLinkInputSized\")" ***!
  \**************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("approvalform_enrol/components/CourseLinkInputSized");

/***/ }),

/***/ "approvalform_enrol/components/CourseLink":
/*!****************************************************************************!*\
  !*** external "tui.require(\"approvalform_enrol/components/CourseLink\")" ***!
  \****************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("approvalform_enrol/components/CourseLink");

/***/ }),

/***/ "mod_approval/schema_form":
/*!************************************************************!*\
  !*** external "tui.require(\"mod_approval/schema_form\")" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_approval/schema_form");

/***/ }),

/***/ "tui/components/form/InputSizedText":
/*!**********************************************************************!*\
  !*** external "tui.require(\"tui/components/form/InputSizedText\")" ***!
  \**********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/InputSizedText");

/***/ }),

/***/ "tui/components/uniform":
/*!**********************************************************!*\
  !*** external "tui.require(\"tui/components/uniform\")" ***!
  \**********************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/uniform");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/approvalform_enrol/tui.json");
/******/ 	
/******/ })()
;