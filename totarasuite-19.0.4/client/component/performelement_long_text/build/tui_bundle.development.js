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

/***/ "./client/component/performelement_long_text/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \**************************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/LongTextAdminEdit\": \"./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue\",\n\t\"./components/LongTextAdminEdit.vue\": \"./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue\",\n\t\"./components/LongTextAdminSummary\": \"./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue\",\n\t\"./components/LongTextAdminSummary.vue\": \"./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue\",\n\t\"./components/LongTextAdminView\": \"./client/component/performelement_long_text/src/components/LongTextAdminView.vue\",\n\t\"./components/LongTextAdminView.vue\": \"./client/component/performelement_long_text/src/components/LongTextAdminView.vue\",\n\t\"./components/LongTextParticipantForm\": \"./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue\",\n\t\"./components/LongTextParticipantForm.vue\": \"./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue\",\n\t\"./components/LongTextParticipantPrint\": \"./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue\",\n\t\"./components/LongTextParticipantPrint.vue\": \"./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue\",\n\t\"./components/WekaWrapper\": \"./client/component/performelement_long_text/src/components/WekaWrapper.js\",\n\t\"./components/WekaWrapper.js\": \"./client/component/performelement_long_text/src/components/WekaWrapper.js\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/performelement_long_text/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/_sync_^(?");

/***/ }),

/***/ "./server/mod/perform/element/long_text/webapi/ajax/get_draft_id.graphql":
/*!*******************************************************************************!*\
  !*** ./server/mod/perform/element/long_text/webapi/ajax/get_draft_id.graphql ***!
  \*******************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"performelement_long_text_get_draft_id\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"section_element_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"participant_instance_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"draft_id\"},\"name\":{\"kind\":\"Name\",\"value\":\"performelement_long_text_get_draft_id\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"section_element_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"section_element_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"participant_instance_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"participant_instance_id\"}}}],\"directives\":[]}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/mod/perform/element/long_text/webapi/ajax/get_draft_id.graphql?");

/***/ }),

/***/ "./client/component/performelement_long_text/tui.json":
/*!************************************************************!*\
  !*** ./client/component/performelement_long_text/tui.json ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"performelement_long_text\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"performelement_long_text\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"performelement_long_text\")\ntui._bundle.addModulesFromContext(\"performelement_long_text\", __webpack_require__(\"./client/component/performelement_long_text/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/tui.json?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/WekaWrapper.js":
/*!*********************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/WekaWrapper.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! editor_weka/WekaValue */ \"editor_weka/WekaValue\");\n/* harmony import */ var editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_0__);\n/**\n * This file is part of Totara Enterprise Extensions.\n *\n * Copyright (C) 2020 onwards Totara Learning Solutions LTD\n *\n * Totara Enterprise Extensions is provided only to Totara\n * Learning Solutions LTD's customers and partners, pursuant to\n * the terms and conditions of a separate agreement with Totara\n * Learning Solutions LTD or its affiliate.\n *\n * If you do not have an agreement with Totara Learning Solutions\n * LTD, you may not access, use, modify, or distribute this software.\n * Please contact [licensing@totaralearning.com] for more information.\n *\n * @author Mark Metcalfe <mark.metcalfe@totaralearning.com>\n * @module performelement_long_text\n */\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  props: {\n    value: {\n      type: Object,\n      required: false\n    }\n  },\n  emits: ['update'],\n  data() {\n    return {\n      content: this.value ? editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_0___default().fromDoc(this.value) : editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_0___default().empty()\n    };\n  },\n  methods: {\n    /**\n     * @param {WekaValue} value\n     */\n    update(value) {\n      if (value.isEmpty) {\n        this.$emit('update', null);\n      }\n      this.$emit('update', value.getDoc());\n    }\n  },\n  render() {\n    return this.$slots.default({\n      value: this.content,\n      update: this.update\n    });\n  }\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/WekaWrapper.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var mod_perform_components_element_PerformAdminCustomElementEdit__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! mod_perform/components/element/PerformAdminCustomElementEdit */ \"mod_perform/components/element/PerformAdminCustomElementEdit\");\n/* harmony import */ var mod_perform_components_element_PerformAdminCustomElementEdit__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(mod_perform_components_element_PerformAdminCustomElementEdit__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    PerformAdminCustomElementEdit: (mod_perform_components_element_PerformAdminCustomElementEdit__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  inheritAttrs: false,\n  props: {\n    identifier: String,\n    isRequired: Boolean,\n    rawTitle: String,\n    settings: Object\n  },\n  emits: ['display', 'update'],\n  data() {\n    return {\n      initialValues: {\n        rawTitle: this.rawTitle,\n        identifier: this.identifier,\n        responseRequired: this.isRequired\n      }\n    };\n  }\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var mod_perform_components_element_PerformAdminCustomElementSummary__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! mod_perform/components/element/PerformAdminCustomElementSummary */ \"mod_perform/components/element/PerformAdminCustomElementSummary\");\n/* harmony import */ var mod_perform_components_element_PerformAdminCustomElementSummary__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(mod_perform_components_element_PerformAdminCustomElementSummary__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    PerformAdminCustomElementSummary: (mod_perform_components_element_PerformAdminCustomElementSummary__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  inheritAttrs: false,\n  props: {\n    identifier: String,\n    isRequired: Boolean,\n    settings: Object,\n    title: String\n  },\n  emits: ['display']\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/form/Form */ \"tui/components/form/Form\");\n/* harmony import */ var tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/form/FormRow */ \"tui/components/form/FormRow\");\n/* harmony import */ var tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! editor_weka/components/Weka */ \"editor_weka/components/Weka\");\n/* harmony import */ var editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! editor_weka/WekaValue */ \"editor_weka/WekaValue\");\n/* harmony import */ var editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_3__);\n\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Form: (tui_components_form_Form__WEBPACK_IMPORTED_MODULE_0___default()),\n    FormRow: (tui_components_form_FormRow__WEBPACK_IMPORTED_MODULE_1___default()),\n    Weka: (editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_2___default())\n  },\n  inheritAttrs: false,\n  props: {\n    title: String\n  },\n  data() {\n    return {\n      emptyValue: editor_weka_WekaValue__WEBPACK_IMPORTED_MODULE_3___default().empty()\n    };\n  }\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js":
/*!******************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js ***!
  \******************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var mod_perform_components_element_ElementParticipantFormContent__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! mod_perform/components/element/ElementParticipantFormContent */ \"mod_perform/components/element/ElementParticipantFormContent\");\n/* harmony import */ var mod_perform_components_element_ElementParticipantFormContent__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(mod_perform_components_element_ElementParticipantFormContent__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/uniform */ \"tui/components/uniform\");\n/* harmony import */ var tui_components_uniform__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_uniform__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_reform_FormScope__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/reform/FormScope */ \"tui/components/reform/FormScope\");\n/* harmony import */ var tui_components_reform_FormScope__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_reform_FormScope__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! editor_weka/components/Weka */ \"editor_weka/components/Weka\");\n/* harmony import */ var editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var performelement_long_text_components_WekaWrapper__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! performelement_long_text/components/WekaWrapper */ \"performelement_long_text/components/WekaWrapper\");\n/* harmony import */ var performelement_long_text_components_WekaWrapper__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(performelement_long_text_components_WekaWrapper__WEBPACK_IMPORTED_MODULE_4__);\n/* harmony import */ var tui_validation__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! tui/validation */ \"tui/validation\");\n/* harmony import */ var tui_validation__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(tui_validation__WEBPACK_IMPORTED_MODULE_5__);\n/* harmony import */ var performelement_long_text_graphql_get_draft_id__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! performelement_long_text/graphql/get_draft_id */ \"./server/mod/perform/element/long_text/webapi/ajax/get_draft_id.graphql\");\n\n\n\n\n\n\n// GraphQL queries\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    ElementParticipantFormContent: (mod_perform_components_element_ElementParticipantFormContent__WEBPACK_IMPORTED_MODULE_0___default()),\n    FormField: tui_components_uniform__WEBPACK_IMPORTED_MODULE_1__.FormField,\n    FormScope: (tui_components_reform_FormScope__WEBPACK_IMPORTED_MODULE_2___default()),\n    Weka: (editor_weka_components_Weka__WEBPACK_IMPORTED_MODULE_3___default()),\n    WekaWrapper: (performelement_long_text_components_WekaWrapper__WEBPACK_IMPORTED_MODULE_4___default())\n  },\n  props: {\n    element: Object,\n    error: String,\n    isDraft: Boolean,\n    isExternalParticipant: Boolean,\n    participantInstanceId: {\n      type: [String, Number],\n      required: false\n    },\n    subjectInstanceId: {\n      type: [String, Number],\n      required: false\n    },\n    path: {\n      type: [String, Array],\n      default: ''\n    },\n    sectionElement: Object\n  },\n  data() {\n    return {\n      draftFileId: 0\n    };\n  },\n  apollo: {\n    /**\n     * Get the draft file area id to be used for temporarily storing uploaded files.\n     */\n    draftFileId: {\n      query: performelement_long_text_graphql_get_draft_id__WEBPACK_IMPORTED_MODULE_6__[\"default\"],\n      variables() {\n        return {\n          section_element_id: this.sectionElement.id,\n          participant_instance_id: this.participantInstanceId\n        };\n      },\n      update({\n        draft_id: draftFileId\n      }) {\n        return draftFileId;\n      },\n      skip() {\n        // File upload is problematic for external participants\n        // and it is not needed for the view-only form (no participant instance id).\n        return this.isExternalParticipant || !this.participantInstanceId;\n      }\n    }\n  },\n  computed: {\n    /**\n     * Have the required queries been loaded?\n     * @return {Boolean}\n     */\n    loaded() {\n      return this.draftFileId || this.isExternalParticipant;\n    },\n    /**\n     * An array of validation rules for the element.\n     * The rules returned depend on if we are saving as draft or if a response is required or not.\n     *\n     * @return {(function|object)[]}\n     */\n    validations() {\n      if (!this.isDraft && this.element && this.element.is_required) {\n        return [tui_validation__WEBPACK_IMPORTED_MODULE_5__.v.required()];\n      }\n      return [];\n    }\n  },\n  methods: {\n    /**\n     * Process the form values.\n     *\n     * @param {Object} value\n     * @return {Object|null}\n     */\n    process(value) {\n      if (!value || !value.response) {\n        return null;\n      }\n      return {\n        draft_id: this.draftFileId,\n        weka: value.response\n      };\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js":
/*!*******************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var tui_components_form_NotepadLines__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! tui/components/form/NotepadLines */ \"tui/components/form/NotepadLines\");\n/* harmony import */ var tui_components_form_NotepadLines__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(tui_components_form_NotepadLines__WEBPACK_IMPORTED_MODULE_0__);\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    NotepadLines: (tui_components_form_NotepadLines__WEBPACK_IMPORTED_MODULE_0___default())\n  },\n  props: {\n    data: Array\n  },\n  computed: {\n    /**\n     * Parse the stringified response markup\n     *\n     * @return {HTML}\n     */\n    responseData() {\n      if (!this.data || !this.data[0]) {\n        return '';\n      }\n      return JSON.parse(this.data[0]);\n    }\n  },\n  mounted() {\n    this.$_scan();\n  },\n  updated() {\n    this.$_scan();\n  },\n  methods: {\n    /**\n     * Required to handle Weka HTML.\n     */\n    $_scan() {\n      this.$nextTick().then(() => {\n        let content = this.$refs.content;\n        if (!content) {\n          return;\n        }\n        tui.scan(content);\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86 ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-longTextAdminEdit\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_PerformAdminCustomElementEdit = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"PerformAdminCustomElementEdit\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_PerformAdminCustomElementEdit, {\n    \"initial-values\": $data.initialValues,\n    settings: $props.settings,\n    onCancel: _cache[0] || (_cache[0] = $event => _ctx.$emit('display')),\n    onUpdate: _cache[1] || (_cache[1] = $event => _ctx.$emit('update', $event))\n  }, null, 8 /* PROPS */, [\"initial-values\", \"settings\"])]);\n}\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043":
/*!*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043 ***!
  \*****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-longTextAdminSummary\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_PerformAdminCustomElementSummary = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"PerformAdminCustomElementSummary\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_PerformAdminCustomElementSummary, {\n    identifier: $props.identifier,\n    \"is-required\": $props.isRequired,\n    settings: $props.settings,\n    title: $props.title,\n    onDisplay: _cache[0] || (_cache[0] = $event => _ctx.$emit('display'))\n  }, null, 8 /* PROPS */, [\"identifier\", \"is-required\", \"settings\", \"title\"])]);\n}\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8 ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-longTextAdminView\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Weka = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Weka\");\n  const _component_FormRow = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormRow\");\n  const _component_Form = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Form\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Form, {\n    \"input-width\": \"full\",\n    vertical: true\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormRow, null, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"\\n          We pass a dummy file ID, so the file attachment options are shown.\\n          They can't be interacted with due to the pointer-events: none css\\n        \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Weka, {\n        value: $data.emptyValue,\n        \"usage-identifier\": {\n          component: 'performelement_long_text',\n          area: 'response'\n        },\n        variant: \"description\",\n        \"file-item-id\": 1,\n        \"aria-label\": $props.title\n      }, null, 8 /* PROPS */, [\"value\", \"aria-label\"])]),\n      _: 1 /* STABLE */\n    })]),\n    _: 1 /* STABLE */\n  })]);\n}\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_Weka = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Weka\");\n  const _component_WekaWrapper = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"WekaWrapper\");\n  const _component_FormField = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormField\");\n  const _component_FormScope = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"FormScope\");\n  const _component_ElementParticipantFormContent = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ElementParticipantFormContent\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Handle the different view switching (read only / print / form),\\n  populate form content if editable and display others responses \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ElementParticipantFormContent, (0,vue__WEBPACK_IMPORTED_MODULE_0__.mergeProps)(_ctx.$attrs, {\n    element: $props.element,\n    error: $props.error,\n    \"is-draft\": $props.isDraft,\n    \"section-element\": $props.sectionElement\n  }), {\n    content: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n      labelId\n    }) => [$options.loaded ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_FormScope, {\n      key: 0,\n      path: $props.path,\n      process: $options.process\n    }, {\n      default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_FormField, {\n        name: \"response\",\n        validations: $options.validations,\n        \"char-length\": 50,\n        error: $props.error\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n          value: formValue,\n          update: formUpdate\n        }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_WekaWrapper, {\n          value: formValue,\n          onUpdate: formUpdate\n        }, {\n          default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n            value,\n            update\n          }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Weka, {\n            \"aria-label\": labelId,\n            value: value,\n            \"usage-identifier\": {\n              component: 'performelement_long_text',\n              area: 'response',\n              instanceId: $props.sectionElement.id\n            },\n            variant: \"description\",\n            \"file-item-id\": $data.draftFileId,\n            \"is-logged-in\": !$props.isExternalParticipant,\n            onInput: update\n          }, null, 8 /* PROPS */, [\"aria-label\", \"value\", \"usage-identifier\", \"file-item-id\", \"is-logged-in\", \"onInput\"])]),\n          _: 2 /* DYNAMIC */\n        }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"value\", \"onUpdate\"])]),\n        _: 2 /* DYNAMIC */\n      }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"validations\", \"error\"])]),\n      _: 2 /* DYNAMIC */\n    }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"path\", \"process\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]),\n    _: 1 /* STABLE */\n  }, 16 /* FULL_PROPS */, [\"element\", \"error\", \"is-draft\", \"section-element\"])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */);\n}\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6 ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-longTextParticipantPrint\"\n};\nconst _hoisted_2 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_NotepadLines = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"NotepadLines\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [$options.responseData && $options.responseData.length > 0 ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n    key: 0,\n    ref: \"content\",\n    innerHTML: $options.responseData\n  }, null, 8 /* PROPS */, _hoisted_2)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_NotepadLines, {\n    key: 1,\n    lines: 6\n  }))]);\n}\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86":
/*!**********************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86 ***!
  \**********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminEdit_vue_vue_type_template_id_e1e24e86__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminEdit_vue_vue_type_template_id_e1e24e86__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LongTextAdminEdit.vue?vue&type=template&id=e1e24e86 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86\");\n\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043":
/*!*************************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043 ***!
  \*************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminSummary_vue_vue_type_template_id_2b2f7043__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminSummary_vue_vue_type_template_id_2b2f7043__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LongTextAdminSummary.vue?vue&type=template&id=2b2f7043 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043\");\n\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8":
/*!**********************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8 ***!
  \**********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminView_vue_vue_type_template_id_76d8dfd8__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextAdminView_vue_vue_type_template_id_76d8dfd8__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LongTextAdminView.vue?vue&type=template&id=76d8dfd8 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8\");\n\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b":
/*!****************************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b ***!
  \****************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextParticipantForm_vue_vue_type_template_id_19a2215b__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextParticipantForm_vue_vue_type_template_id_19a2215b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LongTextParticipantForm.vue?vue&type=template&id=19a2215b */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b\");\n\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6":
/*!*****************************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6 ***!
  \*****************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextParticipantPrint_vue_vue_type_template_id_464862a6__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_LongTextParticipantPrint_vue_vue_type_template_id_464862a6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./LongTextParticipantPrint.vue?vue&type=template&id=464862a6 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6\");\n\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1218.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1218.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1218.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1218.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1218.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1218.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1218.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1218.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1218.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue":
/*!****************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LongTextAdminEdit_vue_vue_type_template_id_e1e24e86__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LongTextAdminEdit.vue?vue&type=template&id=e1e24e86 */ \"./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=template&id=e1e24e86\");\n/* harmony import */ var _LongTextAdminEdit_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LongTextAdminEdit.vue?vue&type=script&lang=js */ \"./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_LongTextAdminEdit_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LongTextAdminEdit_vue_vue_type_template_id_e1e24e86__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/performelement_long_text/src/components/LongTextAdminEdit.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue":
/*!*******************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue ***!
  \*******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LongTextAdminSummary_vue_vue_type_template_id_2b2f7043__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LongTextAdminSummary.vue?vue&type=template&id=2b2f7043 */ \"./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=template&id=2b2f7043\");\n/* harmony import */ var _LongTextAdminSummary_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LongTextAdminSummary.vue?vue&type=script&lang=js */ \"./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_LongTextAdminSummary_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LongTextAdminSummary_vue_vue_type_template_id_2b2f7043__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/performelement_long_text/src/components/LongTextAdminSummary.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminView.vue":
/*!****************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminView.vue ***!
  \****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LongTextAdminView_vue_vue_type_template_id_76d8dfd8__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LongTextAdminView.vue?vue&type=template&id=76d8dfd8 */ \"./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=template&id=76d8dfd8\");\n/* harmony import */ var _LongTextAdminView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LongTextAdminView.vue?vue&type=script&lang=js */ \"./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js\");\n/* harmony import */ var _LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss */ \"./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_LongTextAdminView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LongTextAdminView_vue_vue_type_template_id_76d8dfd8__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/performelement_long_text/src/components/LongTextAdminView.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue":
/*!**********************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue ***!
  \**********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LongTextParticipantForm_vue_vue_type_template_id_19a2215b__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LongTextParticipantForm.vue?vue&type=template&id=19a2215b */ \"./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=template&id=19a2215b\");\n/* harmony import */ var _LongTextParticipantForm_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LongTextParticipantForm.vue?vue&type=script&lang=js */ \"./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_LongTextParticipantForm_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LongTextParticipantForm_vue_vue_type_template_id_19a2215b__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/performelement_long_text/src/components/LongTextParticipantForm.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue":
/*!***********************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _LongTextParticipantPrint_vue_vue_type_template_id_464862a6__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LongTextParticipantPrint.vue?vue&type=template&id=464862a6 */ \"./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=template&id=464862a6\");\n/* harmony import */ var _LongTextParticipantPrint_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LongTextParticipantPrint.vue?vue&type=script&lang=js */ \"./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_LongTextParticipantPrint_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LongTextParticipantPrint_vue_vue_type_template_id_464862a6__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminEdit_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminEdit_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LongTextAdminEdit.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminEdit.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js":
/*!*******************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js ***!
  \*******************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminSummary_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminSummary_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LongTextAdminSummary.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminSummary.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextAdminView_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LongTextAdminView.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js":
/*!**********************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js ***!
  \**********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextParticipantForm_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextParticipantForm_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LongTextParticipantForm.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantForm.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextParticipantPrint_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1215_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_LongTextParticipantPrint_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./LongTextParticipantPrint.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1215.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextParticipantPrint.vue?");

/***/ }),

/***/ "./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss":
/*!*************************************************************************************************************************************!*\
  !*** ./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss ***!
  \*************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1218.use[0]!../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1218.use[1]!../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1218.use[2]!../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1218.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1218.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1218.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/performelement_long_text/src/components/LongTextAdminView.vue?vue&type=style&index=0&id=76d8dfd8&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1218_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1218_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1218_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LongTextAdminView_vue_vue_type_style_index_0_id_76d8dfd8_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/performelement_long_text/src/components/LongTextAdminView.vue?");

/***/ }),

/***/ "editor_weka/WekaValue":
/*!*********************************************************!*\
  !*** external "tui.require(\"editor_weka/WekaValue\")" ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("editor_weka/WekaValue");

/***/ }),

/***/ "editor_weka/components/Weka":
/*!***************************************************************!*\
  !*** external "tui.require(\"editor_weka/components/Weka\")" ***!
  \***************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("editor_weka/components/Weka");

/***/ }),

/***/ "mod_perform/components/element/ElementParticipantFormContent":
/*!************************************************************************************************!*\
  !*** external "tui.require(\"mod_perform/components/element/ElementParticipantFormContent\")" ***!
  \************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_perform/components/element/ElementParticipantFormContent");

/***/ }),

/***/ "mod_perform/components/element/PerformAdminCustomElementEdit":
/*!************************************************************************************************!*\
  !*** external "tui.require(\"mod_perform/components/element/PerformAdminCustomElementEdit\")" ***!
  \************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_perform/components/element/PerformAdminCustomElementEdit");

/***/ }),

/***/ "mod_perform/components/element/PerformAdminCustomElementSummary":
/*!***************************************************************************************************!*\
  !*** external "tui.require(\"mod_perform/components/element/PerformAdminCustomElementSummary\")" ***!
  \***************************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("mod_perform/components/element/PerformAdminCustomElementSummary");

/***/ }),

/***/ "performelement_long_text/components/WekaWrapper":
/*!***********************************************************************************!*\
  !*** external "tui.require(\"performelement_long_text/components/WekaWrapper\")" ***!
  \***********************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("performelement_long_text/components/WekaWrapper");

/***/ }),

/***/ "tui/components/form/FormRow":
/*!***************************************************************!*\
  !*** external "tui.require(\"tui/components/form/FormRow\")" ***!
  \***************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/FormRow");

/***/ }),

/***/ "tui/components/form/Form":
/*!************************************************************!*\
  !*** external "tui.require(\"tui/components/form/Form\")" ***!
  \************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/Form");

/***/ }),

/***/ "tui/components/form/NotepadLines":
/*!********************************************************************!*\
  !*** external "tui.require(\"tui/components/form/NotepadLines\")" ***!
  \********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/form/NotepadLines");

/***/ }),

/***/ "tui/components/reform/FormScope":
/*!*******************************************************************!*\
  !*** external "tui.require(\"tui/components/reform/FormScope\")" ***!
  \*******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/reform/FormScope");

/***/ }),

/***/ "tui/components/uniform":
/*!**********************************************************!*\
  !*** external "tui.require(\"tui/components/uniform\")" ***!
  \**********************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/uniform");

/***/ }),

/***/ "tui/validation":
/*!**************************************************!*\
  !*** external "tui.require(\"tui/validation\")" ***!
  \**************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/validation");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/performelement_long_text/tui.json");
/******/ 	
/******/ })()
;