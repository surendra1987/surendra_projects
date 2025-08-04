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

/***/ "./client/component/pathway_learning_plan/src sync recursive ^(?:(?%21\\/(?:internal%7C__[a-z]*__)\\/%7C^.\\/(?:global_styles%7Ctooling%7Ctests)\\/%7C^.\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\/%7Cjs\\/)).)*$":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./client/component/pathway_learning_plan/src/ sync ^(?:(?%21\/(?:internal%7C__[a-z]*__)\/%7C^.\/(?:global_styles%7Ctooling%7Ctests)\/%7C^.\/(?:build.config%7Cupgrade.txt$%7Ctooling\/%7Cjs\/)).)*$ ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

eval("var map = {\n\t\"./components/achievements/AchievementDisplay\": \"./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue\",\n\t\"./components/achievements/AchievementDisplay.vue\": \"./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue\"\n};\n\n\nfunction webpackContext(req) {\n\tvar id = webpackContextResolve(req);\n\treturn __webpack_require__(id);\n}\nfunction webpackContextResolve(req) {\n\tif(!__webpack_require__.o(map, req)) {\n\t\tvar e = new Error(\"Cannot find module '\" + req + \"'\");\n\t\te.code = 'MODULE_NOT_FOUND';\n\t\tthrow e;\n\t}\n\treturn map[req];\n}\nwebpackContext.keys = function webpackContextKeys() {\n\treturn Object.keys(map);\n};\nwebpackContext.resolve = webpackContextResolve;\nmodule.exports = webpackContext;\nwebpackContext.id = \"./client/component/pathway_learning_plan/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\";\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/_sync_^(?");

/***/ }),

/***/ "./server/totara/competency/pathway/learning_plan/webapi/ajax/competency_plans.graphql":
/*!*********************************************************************************************!*\
  !*** ./server/totara/competency/pathway/learning_plan/webapi/ajax/competency_plans.graphql ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n\n    var doc = {\"kind\":\"Document\",\"definitions\":[{\"kind\":\"OperationDefinition\",\"operation\":\"query\",\"name\":{\"kind\":\"Name\",\"value\":\"pathway_learning_plan_competency_plans\"},\"variableDefinitions\":[{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]},{\"kind\":\"VariableDefinition\",\"variable\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"}},\"type\":{\"kind\":\"NonNullType\",\"type\":{\"kind\":\"NamedType\",\"name\":{\"kind\":\"Name\",\"value\":\"core_id\"}}},\"directives\":[]}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"pathway_learning_plan_competency_plans\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"user_id\"}}},{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"},\"value\":{\"kind\":\"Variable\",\"name\":{\"kind\":\"Name\",\"value\":\"assignment_id\"}}}],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"learning_plans\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"id\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"can_view\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[],\"directives\":[]},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"description\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"scale_value\"},\"arguments\":[],\"directives\":[],\"selectionSet\":{\"kind\":\"SelectionSet\",\"selections\":[{\"kind\":\"Field\",\"name\":{\"kind\":\"Name\",\"value\":\"name\"},\"arguments\":[],\"directives\":[]}]}},{\"kind\":\"Field\",\"alias\":{\"kind\":\"Name\",\"value\":\"date\"},\"name\":{\"kind\":\"Name\",\"value\":\"date_assigned\"},\"arguments\":[{\"kind\":\"Argument\",\"name\":{\"kind\":\"Name\",\"value\":\"format\"},\"value\":{\"kind\":\"EnumValue\",\"value\":\"DATE\"}}],\"directives\":[]}]}}]}}]};\n    /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (doc);\n  \n\n//# sourceURL=webpack:///./server/totara/competency/pathway/learning_plan/webapi/ajax/competency_plans.graphql?");

/***/ }),

/***/ "./client/component/pathway_learning_plan/tui.json":
/*!*********************************************************!*\
  !*** ./client/component/pathway_learning_plan/tui.json ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

eval("!function() {\n\"use strict\";\n\nif (globalThis.tui?._bundle.isLoaded(\"pathway_learning_plan\")) {\n  console.warn(\n    '[tui bundle] The bundle \"' + \"pathway_learning_plan\" +\n    '\" is already loaded, skipping initialisation.'\n  );\n  return;\n};\ntui._bundle.register(\"pathway_learning_plan\")\ntui._bundle.addModulesFromContext(\"pathway_learning_plan\", __webpack_require__(\"./client/component/pathway_learning_plan/src sync recursive ^(?:(?%21\\\\/(?:internal%7C__[a-z]*__)\\\\/%7C^.\\\\/(?:global_styles%7Ctooling%7Ctests)\\\\/%7C^.\\\\/(?:build.config%7Cupgrade.txt$%7Ctooling\\\\/%7Cjs\\\\/)).)*$\"));\n}();\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/tui.json?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! totara_competency/components/achievements/AchievementLayout */ \"totara_competency/components/achievements/AchievementLayout\");\n/* harmony import */ var totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var tui_components_links_ActionLink__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! tui/components/links/ActionLink */ \"tui/components/links/ActionLink\");\n/* harmony import */ var tui_components_links_ActionLink__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(tui_components_links_ActionLink__WEBPACK_IMPORTED_MODULE_1__);\n/* harmony import */ var tui_components_datatable_Cell__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! tui/components/datatable/Cell */ \"tui/components/datatable/Cell\");\n/* harmony import */ var tui_components_datatable_Cell__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(tui_components_datatable_Cell__WEBPACK_IMPORTED_MODULE_2__);\n/* harmony import */ var tui_components_datatable_ExpandCell__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! tui/components/datatable/ExpandCell */ \"tui/components/datatable/ExpandCell\");\n/* harmony import */ var tui_components_datatable_ExpandCell__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(tui_components_datatable_ExpandCell__WEBPACK_IMPORTED_MODULE_3__);\n/* harmony import */ var tui_components_datatable_Table__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! tui/components/datatable/Table */ \"tui/components/datatable/Table\");\n/* harmony import */ var tui_components_datatable_Table__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(tui_components_datatable_Table__WEBPACK_IMPORTED_MODULE_4__);\n/* harmony import */ var pathway_learning_plan_graphql_competency_plans__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! pathway_learning_plan/graphql/competency_plans */ \"./server/totara/competency/pathway/learning_plan/webapi/ajax/competency_plans.graphql\");\n// Components\n\n\n\n\n\n// GraphQL\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    AchievementLayout: (totara_competency_components_achievements_AchievementLayout__WEBPACK_IMPORTED_MODULE_0___default()),\n    ActionLink: (tui_components_links_ActionLink__WEBPACK_IMPORTED_MODULE_1___default()),\n    Cell: (tui_components_datatable_Cell__WEBPACK_IMPORTED_MODULE_2___default()),\n    ExpandCell: (tui_components_datatable_ExpandCell__WEBPACK_IMPORTED_MODULE_3___default()),\n    Table: (tui_components_datatable_Table__WEBPACK_IMPORTED_MODULE_4___default())\n  },\n  inheritAttrs: false,\n  props: {\n    assignmentId: {\n      required: true,\n      type: Number\n    },\n    userId: {\n      required: true,\n      type: Number\n    }\n  },\n  emits: ['loaded'],\n  data: function () {\n    return {\n      plans: []\n    };\n  },\n  apollo: {\n    plans: {\n      query: pathway_learning_plan_graphql_competency_plans__WEBPACK_IMPORTED_MODULE_5__[\"default\"],\n      context: {\n        batch: true\n      },\n      variables() {\n        return {\n          assignment_id: this.assignmentId,\n          user_id: this.userId\n        };\n      },\n      update({\n        pathway_learning_plan_competency_plans: plans\n      }) {\n        this.$emit('loaded');\n        return plans;\n      }\n    }\n  },\n  computed: {\n    /**\n     * Check if data contains learning plan\n     *\n     * @return {Boolean}\n     */\n    hasPlans() {\n      return this.plans.learning_plans;\n    },\n    /**\n     * Check if a scale value has been set\n     *\n     * @return {Boolean}\n     */\n    hasValue() {\n      return this.hasPlans && this.plans.scale_value != null;\n    }\n  },\n  methods: {\n    /**\n     * Return URL for plan\n     *\n     * @param {Integer} planId\n     * @return {String}\n     */\n    getPlanUrl(planId) {\n      return this.$url('/totara/plan/component.php', {\n        c: 'competency',\n        id: planId\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B6%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02":
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02 ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* binding */ render)\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"vue\");\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);\n\nconst _hoisted_1 = {\n  class: \"tui-pathwayLearningPlanAchievement\"\n};\nconst _hoisted_2 = {\n  key: 0,\n  class: \"tui-pathwayLearningPlanAchievement__empty\"\n};\nconst _hoisted_3 = {\n  class: \"tui-pathwayLearningPlanAchievement__content\"\n};\nconst _hoisted_4 = {\n  class: \"tui-pathwayLearningPlanAchievement__overview\"\n};\nconst _hoisted_5 = {\n  class: \"tui-pathwayLearningPlanAchievement__title\"\n};\nconst _hoisted_6 = {\n  key: 0,\n  class: \"tui-pathwayLearningPlanAchievement__value\"\n};\nconst _hoisted_7 = {\n  class: \"tui-pathwayLearningPlanAchievement__value-title\"\n};\nconst _hoisted_8 = {\n  key: 1,\n  class: \"tui-pathwayLearningPlanAchievement__noValue\"\n};\nconst _hoisted_9 = {\n  class: \"tui-pathwayLearningPlanAchievement__summary\"\n};\nconst _hoisted_10 = {\n  class: \"tui-pathwayLearningPlanAchievement__summary-header\"\n};\nconst _hoisted_11 = [\"innerHTML\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_ExpandCell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ExpandCell\");\n  const _component_Cell = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Cell\");\n  const _component_ActionLink = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ActionLink\");\n  const _component_Table = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"Table\");\n  const _component_AchievementLayout = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"AchievementLayout\");\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" String if no plans available \"), !$options.hasPlans ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:no_available_learning_plans,pathway_learning_plan##\"), 1 /* TEXT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {\n    key: 1\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" Learning plan proficiency content \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_AchievementLayout, null, {\n    left: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h4\", _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:achievement_via_learning_plan,pathway_learning_plan##\"), 1 /* TEXT */), $options.hasValue ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_6, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_7, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.plans.scale_value.name), 1 /* TEXT */), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(_ctx.$str.__r(\"##str:get:set_on,pathway_learning_plan##\", _ctx.plans.date)), 1 /* TEXT */)])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_8, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:no_rating_set,pathway_learning_plan##\"), 1 /* TEXT */))])]),\n    right: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Table, {\n      data: _ctx.plans.learning_plans,\n      \"expandable-rows\": true,\n      class: \"tui-pathwayLearningPlanAchievement__list\"\n    }, {\n      row: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n        row,\n        expand,\n        expandState\n      }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" learning plan, that can't be viewed \"), !row.can_view ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {\n        key: 0\n      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ExpandCell, {\n        header: true\n      }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Cell, {\n        size: \"11\"\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(\"##str:get:no_permission_view_plan,pathway_learning_plan##\"), 1 /* TEXT */)]),\n        _: 1 /* STABLE */\n      })], 64 /* STABLE_FRAGMENT */)) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, {\n        key: 1\n      }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" learning plan expand cell \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ExpandCell, {\n        \"aria-label\": row.name,\n        \"expand-state\": expandState,\n        onClick: $event => expand()\n      }, null, 8 /* PROPS */, [\"aria-label\", \"expand-state\", \"onClick\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\" learning plan name cell \"), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_Cell, {\n        size: \"11\",\n        \"column-header\": \"##str:get:name,pathway_learning_plan##\"\n      }, {\n        default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)((0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(row.name), 1 /* TEXT */)]),\n        _: 2 /* DYNAMIC */\n      }, 1032 /* PROPS, DYNAMIC_SLOTS */, [\"column-header\"])], 64 /* STABLE_FRAGMENT */))]),\n      \"expand-content\": (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(({\n        row\n      }) => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_9, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h5\", _hoisted_10, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(row.name), 1 /* TEXT */), row.description ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n        key: 0,\n        class: \"tui-pathwayLearningPlanAchievement__summary-body\",\n        innerHTML: row.description\n      }, null, 8 /* PROPS */, _hoisted_11)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ActionLink, {\n        href: $options.getPlanUrl(row.id),\n        text: \"##str:get:view_plan,pathway_learning_plan##\",\n        class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)('tui-pathwayLearningPlanAchievement__summary-button'),\n        styleclass: {\n          primary: true,\n          small: true\n        }\n      }, null, 8 /* PROPS */, [\"href\", \"text\"])])]),\n      _: 1 /* STABLE */\n    }, 8 /* PROPS */, [\"data\"])]),\n    _: 1 /* STABLE */\n  })])], 2112 /* STABLE_FRAGMENT, DEV_ROOT_FRAGMENT */))]);\n}\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?./node_modules/babel-loader/lib/index.js??ruleSet%5B1%5D.rules%5B16%5D.use%5B0%5D!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D!./node_modules/source-map-loader/dist/cjs.js??ruleSet%5B1%5D.rules%5B2%5D.use%5B0%5D");

/***/ }),

/***/ "./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02":
/*!*********************************************************************************************************************************!*\
  !*** ./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02 ***!
  \*********************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1145_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_53405a02__WEBPACK_IMPORTED_MODULE_0__.render)\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_ruleSet_1_rules_16_use_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_1145_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_2_use_0_AchievementDisplay_vue_vue_type_template_id_53405a02__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!../../../../../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./AchievementDisplay.vue?vue&type=template&id=53405a02 */ \"./node_modules/babel-loader/lib/index.js??ruleSet[1].rules[16].use[0]!./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[2].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02\");\n\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1148.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1148.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1148.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss":
/*!***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1148.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1148.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1148.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss ***!
  \***************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (() => {

eval("// extracted by mini-css-extract-plugin\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1148.use%5B0%5D!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1148.use%5B1%5D!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1148.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/vue-loader/dist/exportHelper.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-loader/dist/exportHelper.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
eval("\nObject.defineProperty(exports, \"__esModule\", ({ value: true }));\n// runtime helper for setting properties on components\n// in a tree-shakable way\nexports[\"default\"] = (sfc, props) => {\n    const target = sfc.__vccOpts || sfc;\n    for (const [key, val] of props) {\n        target[key] = val;\n    }\n    return target;\n};\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/dist/exportHelper.js?");

/***/ }),

/***/ "./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue":
/*!***************************************************************************************************!*\
  !*** ./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue ***!
  \***************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _AchievementDisplay_vue_vue_type_template_id_53405a02__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=template&id=53405a02 */ \"./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=template&id=53405a02\");\n/* harmony import */ var _AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=script&lang=js */ \"./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n/* harmony import */ var _AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss */ \"./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss\");\n/* harmony import */ var _node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../../../../node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_AchievementDisplay_vue_vue_type_template_id_53405a02__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (__exports__);\n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js":
/*!***************************************************************************************************************************!*\
  !*** ./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js ***!
  \***************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_1145_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"])\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_1145_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_node_modules_source_map_loader_dist_cjs_js_ruleSet_1_rules_6_use_0_AchievementDisplay_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!../../../../../../node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./AchievementDisplay.vue?vue&type=script&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-1145.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./node_modules/source-map-loader/dist/cjs.js??ruleSet[1].rules[6].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=script&lang=js\");\n \n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss":
/*!************************************************************************************************************************************************!*\
  !*** ./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss ***!
  \************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (/* reexport default from dynamic */ _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0___default.a)\n/* harmony export */ });\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1148.use[0]!../../../../../tooling/webpack/css_raw_loader.js??clonedRuleSet-1148.use[1]!../../../../../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1148.use[2]!../../../../../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss */ \"./node_modules/mini-css-extract-plugin/dist/loader.js??clonedRuleSet-1148.use[0]!./client/tooling/webpack/css_raw_loader.js??clonedRuleSet-1148.use[1]!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-1148.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?vue&type=style&index=0&id=53405a02&lang=scss\");\n/* harmony import */ var _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(const __WEBPACK_IMPORT_KEY__ in _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = () => _node_modules_mini_css_extract_plugin_dist_loader_js_clonedRuleSet_1148_use_0_tooling_webpack_css_raw_loader_js_clonedRuleSet_1148_use_1_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_1148_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AchievementDisplay_vue_vue_type_style_index_0_id_53405a02_lang_scss__WEBPACK_IMPORTED_MODULE_0__[__WEBPACK_IMPORT_KEY__]\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n \n\n//# sourceURL=webpack:///./client/component/pathway_learning_plan/src/components/achievements/AchievementDisplay.vue?");

/***/ }),

/***/ "totara_competency/components/achievements/AchievementLayout":
/*!***********************************************************************************************!*\
  !*** external "tui.require(\"totara_competency/components/achievements/AchievementLayout\")" ***!
  \***********************************************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("totara_competency/components/achievements/AchievementLayout");

/***/ }),

/***/ "tui/components/datatable/Cell":
/*!*****************************************************************!*\
  !*** external "tui.require(\"tui/components/datatable/Cell\")" ***!
  \*****************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/datatable/Cell");

/***/ }),

/***/ "tui/components/datatable/ExpandCell":
/*!***********************************************************************!*\
  !*** external "tui.require(\"tui/components/datatable/ExpandCell\")" ***!
  \***********************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/datatable/ExpandCell");

/***/ }),

/***/ "tui/components/datatable/Table":
/*!******************************************************************!*\
  !*** external "tui.require(\"tui/components/datatable/Table\")" ***!
  \******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/datatable/Table");

/***/ }),

/***/ "tui/components/links/ActionLink":
/*!*******************************************************************!*\
  !*** external "tui.require(\"tui/components/links/ActionLink\")" ***!
  \*******************************************************************/
/***/ ((module) => {

"use strict";
module.exports = tui.require("tui/components/links/ActionLink");

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
/******/ 	var __webpack_exports__ = __webpack_require__("./client/component/pathway_learning_plan/tui.json");
/******/ 	
/******/ })()
;