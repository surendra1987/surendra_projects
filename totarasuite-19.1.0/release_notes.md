# Totara 19 Technical Release Notes

A change management guide for Totara developers and implementers.

For a description of every new feature, improvement, and bug fixed in Totara 19, please start with our [change logs](changelog.md).

<a name="toc"></a>

## Table of contents

* [System requirements](#requirements)
* [Breaking code changes](#breaking)
* [GraphQL API](#gql)
* [Capabilities](#capabilities)
* [Scheduled tasks](#tasks)
* [Notifications](#notifications)
* [New settings](#settings)
* [Hooks](#hooks)
* [Events](#events)
* [Plugins](#plugins)
* [Tui components](#tui)
* [Deprecations](#deprecated)
* [Removals](#removed)

<a name="requirements"></a>

## System requirements  
These are summaries, see our [readme](readme.md) for details.

- **PHP**  
PHP 8.2 is now the recommended version.  
Removed support for PHP 7.4 and 8.0   
Added support for PHP 8.2 and 8.3


- **MariaDB**  
MariaDB 10.7 and 10.8 are no longer supported  
Removed support for MariaDB 10.4 and 10.3  
Added support for MariaDB 11.4  
  

- **MySQL**  
Removed support for MySQL 5.7  
Added support for MySQL 8.4  
  

- **PostgreSQL**  
PostgreSQL 11 and 12 are no longer supported  
Added support for PostgreSQL 16  
  

- **MSSQL** - No change  
Versions newer than MSSQL 2019 are not be supported.  
  

- **Python** (for Machine Learning Service)  
Supported Python versions are now 3.11 and 3.10.  
Removed support for all earlier versions of Python due lack of support in Machine Learning Service dependencies.
  

- **Node.js**
Minimum version of node.js for building Tui frontend is v20  
Removed support for node versions 19 and below
  

- **Web browsers** - no change
  

[Table of contents](#toc)

<a name="breaking"></a>

## Breaking and important code changes

Each component has an upgrade.txt which itemises all technical changes; search for `=== 19.0 ===` for full details.
  
Note that front-end components have their own upgrade.txt, so for example to discover full details of changes to Perform, see client/component/mod_perform/upgrade.txt and server/mod/perform/upgrade.txt.

### Vue and Tui
Totara has been upgraded to use Vue 3. This is a major upgrade with several breaking changes, and custom Vue components may need to be updated.
We have a detailed a migration guide in our Public developer documentation: [Totara 19 Vue 3 migration guide](https://totara.atlassian.net/wiki/spaces/DEV/pages/287572322/Totara+19+Vue+3+migration+guide), please contact Totara support if you have technical questions or run into any issues.
  
There are additional important changes and many deprecations listed in [tui/upgrade.txt](client/component/tui/upgrade.txt), including:
- The `<lang-strings>` block in .vue files is now ignored, and should be removed. Instead, calls to `$str()` are replaced with the actual value at runtime. Ensure you are using `$str` in a supported way by running `npm run tui-style-check`.
- The file structure of tui components has changed to improve bundling.
- The `tui.mount` interface has changed to return a Vue app instance rather than a component instance.
- Upload.vue now requires the ref on the child input to be set to `inputRef` from the slot prop.
- TagList no longer automatically shows the loading indicator when the search input changes. Manually pass the "loading" prop instead.
- Button has been improved, and can now perform all functions of ButtonIcon and ActionLink.

### Typography improvements
See [Typography improvements in Totara 19](https://totara.atlassian.net/wiki/spaces/DEV/pages/814055428/Typography+improvements+in+Totara+19) in our Public developer documentation for full details.
- Headings have been changed throughout the product to provide a consistent and accessible page structure, with a single h1 element on any page.
- Improvements to bottom margins on several base typography elements have been made, to improve the vertical rhythm and general readability of on-screen text.
- The size of 1rem has changed from 10px to 16px to be less weird, and there are new helpers for relative sizing and font definition.
- Backend endpoints and renderers have been updated to use page_main_heading() instead of heading()

### Themes
- Partial component overrides in themes have now been fully removed. You must override the entire component to replace it in a theme.
- A new default theme Inspire has been created, which implements additional settings, new default colours, and an optional left-hand "app style" navigation.
- Long-deprecated roots and basis themes have been removed
- When in chromeless mode, with Block borders turned off, when Block headers are still turned on, they will no longer have left/right padding indentation.

### Learning cards
The consistency of cards used to display learning items in grids and scrollers has been improved. The following components now use core learning cards:
- container_workspace OriginalSpaceCard.vue
- engage_article ArticleCard.vue
- engage_survey SurveyCard.vue
- totara_playlist PlaylistCard.vue 
- totara_program CoursesGrid.vue

### PHP 8.2 property name corrections / declarations / removals
A significant number of property names have been corrected and/or correctly declared and/or removed to support PHP 8.2, which raises a warning when an undeclared class property is set dynamically on an object instance. We have attempted to list these changes in upgrade.txt in the affected components.
  
We recommend staging all sites on PHP 8.3 with debugging enabled, in order to discover any bugs arising from typos or undeclared object properties in third-party or custom plugins.

### Seminar
The following notification classes have been removed from the `mod_facetoface\totara_notification\notification` namespace:
- `event_reservations_cancelled_for_managers` has been replaced by `manager_reservations_cancelled_for_subject`
- `event_reservations_expired_for_managers` has been replaced by `manager_reservations_expired_for_subject`
- `event_reservations_cancelled` has been replaced by `manager_reservations_cancelled`
- `event_reservations_expired` has been replaced by `manager_reservations_expired`

### Engage Topics
Topics have been refactored to work a lot more like normal tag collections. The tag collection which topics use can be changed to any tag collection. On installation, there is no longer a dedicated "Topics" tag collection, it uses the default tag collection instead. 
  
The setting `$CFG->topics_collection_id` is now redundant.
  
The topic and topic usage report sources have been hidden from the 'Create report' listing, and the 'topic management' and 'usage of topic' pages are no longer linked.

### Course container
Updated return type of `external::process_non_interactive_enrol` from bool to array `[?string 'url', bool 'success']`.

### OAuth2
`core\oauth2\api::connect_system_account()` no longer returns a true/false, it now returns an integer constant indicating the success state.

### Engage and workspaces
- Side navigation panel has been removed from Engage and several components unique to it have been deprecated.
- Many Tui components in client totara_engage have been upgraded, see upgrade.txt.
- Workspace Tui components have been upgraded in several ways, see client container_workspace upgrade.txt.
- The implementation of multiple workspace owners has caused a number of significant changes, see server container_workspace upgrade.txt.
- Several web endpoints in /totara/engage have been replaced with filter settings on /totara/engage/index.php

### Perform
- There have been a number of upgrades to Perform Tui components, see client mod_perform upgrade.txt if you customise Perform.

### Approval workflows
The behaviour of application_submission records has changed; previously there could be one unsuperseded submission per stage, with form_data that only applied to that stage. 
  
Submissions now have a full set of form_data, and there can only be one un-superseded draft, and one un-superseded submitted application_submission at a time, across all stages.

### Hierarchies
Totara hierarchies (competencies, legacy goals, organisations, positions) have been updated to consistently use data_provider classes for fetching filterable lists of items. A new method, `hierarchy::get_data_provider` returns the `data_provider` class for the specified prefix.
  
The position and organisation data providers no longer automatically filter for visible items. This filter must now be specified manually.
  
The mandatory filters property has now been deleted, please use the set filters instead.

### Job assignments
The behviour of users_share_relation() has been updated to check the relationships between managers, temporary managers, and appraisers.

### MS Teams bot
Renamed incorrectly named properties on trait `\totara_msteams\botfw\storage\traits\storage_bot`:
- `$bot_app_id` renamed to `$bot_id`
- `$bot_app_secret` renamed to `$bot_secret`  
  
The original properties were never referenced, and the correct properties were previously dynamically declared.

### Report builder
Added new `rb_config` option, `keep_session_open`. Can be used to keep sessions open, used when embedding reportbuilder as a block or another custom implementation where sessions may still be required on the page. Otherwise, the session will be closed before the report is generated, in order to prevent locking other pages during long-running reports.
  
Report builder `restore_saved_search()` now returns false if the search is unavailable, previously it would trigger an error.
  
Removed `embeddedparams` declarations from the following report sources, as it is unsupported in report sources:
- rb_source_appraisal
- rb_source_goal_custom
- rb_source_goal_details
- rb_source_goal_summary
  
Removed `defaultsortcolumn` and `defaultsortoder` declarations from the following report sources as they are unsupported outside of embedded reports:
- rb_source_competency_assignment_users
- rb_source_evidence_item
  
Added `rowheader` to report_builder_columns table and classes. If set the cell will render with a th tag insted of td.

### GraphQL stack
The `debugMessage` field from formatted error messages now exists beneath the extensions key, any references to `$error['debugMessage']` must be updated to `$error['extensions']['debugMessage']`

### Mobile events
Events triggered by calls to the mobile GraphQL API will have source set to 'mobile' rather than 'web'.

### Weka editor
- The attribute ref="content" in custom nodes should be replaced with data-node-view-content.
- Many components have been updated to pass an aria label to Weka for better accessibility. 

### Blocks
- The catalogue block is the first Totara block to use Tui content.
- Blocks now implement a 'customclass' setting via the base block class.
- The setting 'block_html_allowcssclasses' has been removed from HTML block.
- Removed undeclared and unused `$this->blockname` and/or `$this->version` assignments from all blocks.

### Core
The weblib `send_headers()` function has changed behaviour, the second property (`$cacheable`) is only applicable if `$CFG->allowhttpcache` is enabled.

[Table of contents](#toc)

<a name="gql"></a>

## GraphQL API

### Changes to external API

There are no breaking changes to the external API between Totara 18.0 and Totara 19.0.

For full documentation of the Totara 19.0 external API, see
https://graphql-schema.totara.com/docs/totara-19.0.html

### New capabilities added to the API User role archetype

The following new capabilities have been added to the apiuser archetype:
- **Create an organisation** `totara/hierarchy:createorganisation`		
- **Create a position** `totara/hierarchy:createposition`		
- **Delete an organisation** `totara/hierarchy:deleteorganisation`
- **Delete a position** `totara/hierarchy:deleteposition`
- **Update an organisation** `totara/hierarchy:updateorganisation`
- **Update a position** `totara/hierarchy:updateposition`
- **View an organisation** `totara/hierarchy:vieworganisationPrivacy risk`		
- **View a position** `totara/hierarchy:viewposition`

### New external API queries & mutations

#### Perform goal tasks API
The following have been added to support tasks on Perform goals.
- **perform_goal_get_goal_tasks**  
  List of tasks for a goal
- **perform_goal_create_goal_task** (mutation)  
  Add a new task to a goal  
- **perform_goal_update_goal_task** (mutation)  
  Change a specific task 
- **perform_goal_delete_goal_task** (mutation)  
  Delete a specific task  
- **perform_goal_set_goal_task_progress** (mutation)  
  Change only the progress of a specific task

#### Job assignments API
A new query has been added to fetch details of a specific job assignment.
- **totara_job_job_assignment**  
  Details of a specific job assignment.

#### Hierarchies API
New queries and mutations added to support managing organisations and positions.
- **hierarchy_organisation_organisation**  
  Return a specific organisation.
- **hierarchy_organisation_organisations**  
  Query to retrieve organisations.
- **hierarchy_organisation_create_organisation** (mutation)  
  Creates a new organisation.
- **hierarchy_organisation_update_organisation** (mutation)  
  Updates a specific target organisation with new properties.
- **hierarchy_organisation_delete_organisation** (mutation)  
  Mutation to delete an organisation.
- **hierarchy_position_position**  
  Return a specific position.
- **hierarchy_position_positions**  
  Query to retrieve positions.
- **hierarchy_position_create_position** (mutation)  
  Creates a new position.
- **hierarchy_position_update_position** (mutation)  
  Updates a specific target position with new properties.
- **hierarchy_position_delete_position** (mutation)  
  Mutation to delete a position.

#### Totara catalogue API
New queries to support discovery of learning items via catalogue.
- **totara_catalog_url_query_validation**  
  Query to validate catalog URL query string or structured query JSON.
- **totara_catalog_filters**  
  Query to fetch available catalog filters and filter options.
- **totara_catalog_items**  
  Query to fetch a page of catalog learning items.
- **totara_catalog_item_details**  
  Query to get details panel content for a learning item.
- **totara_catalog_search_filter_options**  
  Query to look up the top 10 matching options for a specific filter. Useful for filters with a lot of options, to narrow down the number of options presented to the user.
- **totara_catalog_top_items_grouped**  
  Query to fetch top catalog learning items grouped by type.

#### Custom fields support
New fields and types have been added to support custom fields in course, organisation, and position queries.

### Breaking changes to AJAX API (Tui)
A required field `sync_participant_instance_closure_type` was added to input type `mod_perform_override_global_participation_settings_input`.

### Breaking changes to Mobile API
No breaking changes.

[Table of contents](#toc)

<a name="capabilities"></a>

## New capabilities

### Core

- **Edit own language preference**  
  `moodle/user:editownlanguage`  
  Split from `moodle/user:editownprofile`, allows the user to change their language preference without necessarily being able to edit the rest of their profile.
- **Add or edit calculated questions**  
  `moodle/question:managecalculated`, provides fine-grained control over calculated quiz question types, which execute sanitised user input as PHP code.

### Perform

- **Manage staff participation**  
  `mod/perform:manage_staff_participation`  
- **Report on staff responses**  
  `mod/perform:report_on_staff_responses`
- **Manage comment notifications on goals**  
  `perform/goal:manage_comment_notifications`

### Approval workflows

- **Delete workflows that are draft, active or archived**  
  `mod/approval:delete_workflow`

### Catalogue block

- **Add a new catalogue block**  
  `block/totara_catalog:addinstance`
- **Add a new catalogue block to the My Learning page**  
  `block/totara_catalog:myaddinstance`

[Table of contents](#toc)

<a name="tasks"></a>

## New scheduled tasks

### Core

New task **Use legacy course sort order** adjusts global category and course sort order when new setting `use_legacy_course_sortorder` is enabled. 

### Product usage analytics

New task **Export usage statistics** to push usage data to Totara Cloud.

### Diagnostic tool for support

New task **Delete old diagnostic files** removes any diagnostic troubleshooting files that are more than 15 minutes old.


[Table of contents](#toc)

<a name="notifications"></a>

## New notifications

### Seminar

- Manager reservations cancelled
- Manager reservations expired

### Course enrolment approval

- Comment added to enrolment request (for applicant)
- Comment added to enrolment request (for others)
- Enrolment request awaiting approval
- Enrolment request approved (for applicant)
- Enrolment request approved (for others)
- Enrolment request denied (for applicant)
- Enrolment request denied (for others)
- Enrolment request submitted (for applicant)
- Enrolment request submitted (for others)
- Enrolment request withdrawn before submission

### Workspace

- User role changed / New role assigned in \[workspace:full_name\]


[Table of contents](#toc)

<a name="settings"></a>

## New settings

### Use legacy course sort order
`use_legacy_course_sortorder` (Course settings) default: no  
  
Previously, Totara maintained a unique sort order value for every category and course in the system. On a large site, this 
is a very expensive operation, so it has been removed in favour of using sort order values that are only locally-unique.  
  
When enabled, legacy course and category sort order will be maintained via a scheduled task, once a week by default.

### Allow page caching
`allow_http_cache` (HTTP security) default: no
  
When enabled, pages without a specific cacheable or non-cacheable flag will allow browsers to cache content for use when 
the back button is pressed. When disabled, browser caching is completely prevented for all pages, regardless of whether 
individual pages specify they can be cached or not.

### Enable AI
`enable_ai` (Experimental settings) default: no
  
When enabled, AI features and plugins will be accessible.

[Table of contents](#toc)

<a name="hooks"></a>

## New hooks

Please see the hook implementations for details.
  
- core\hook\send_file_options
- core_enrol\hook\enrol_instance_extra_settings_definition 
- core_enrol\hook\enrol_instance_extra_settings_display
- core_enrol\hook\enrol_instance_extra_settings_save
- core_enrol\hook\enrol_instance_extra_settings_validation
- core_question\hook\question_can_manage_type
- core_user\hook\profile_field_set_data
- mod_approval\hook\column_options
- mod_approval\hook\embedded_report_columns
- mod_approval\hook\workflow_version_pre_activate
- totara_cohort\hook\pre_delete_cohort_check
- totara_hierarchy\hook\pre_delete_framework_check
- totara_hierarchy\hook\pre_delete_item_check
- totara_reportbuilder\hook\scheduled_report_pre_process
- totara_tenant\hook\tenant_user_create_form_definition_complete

[Table of contents](#toc)

<a name="events"></a>

## New events

Please see the event implementations for details.
  
- Workspace member role changed - `container_workspace\event\user_role_changed`
- AI feature interaction - `core_ai\event\feature_interaction`
- User enrolments are about to be deleted in bulk - `core_enrol\event\pre_user_enrolment_bulk_deleted`
- User enrolment is about to be deleted - `core_enrol\event\pre_user_enrolment_deleted`
- User enrolment application approved - `core_enrol\event\user_enrolment_application_approved`
- User enrolment application created - `core_enrol\event\user_enrolment_application_created`
- Course re-shared - `engage_course\event\course_reshared`
- Course shared - `engage_course\event\course_shared`
- Application deleted - `mod_approval\event\application_deleted`
- Goal task created - `perform_goal\event\goal_task_created`
- Goal task deleted - `perform_goal\event\goal_task_deleted`
- Goal task progress changed - `perform_goal\event\goal_task_progress_changed`
- Goal task updated - `perform_goal\event\goal_task_updated`
- User unsuspended - `totara_core\event\user_unsuspended`

[Table of contents](#toc)

<a name="plugins"></a>

## New plugins

### Course enrolment approval form  
approvalform_enrol  
  
Enables course enrolment approval using approval workflows.
  
Note that this approvalform does not self-install. Please use the following command create the first enrolment workflow:
>`php dev/approval/enrol/create_workflow.php`

### Totara catalogue block
block_totara_catalog  
  
Allows display of top catalogue learning items matching any valid query.

### Collect usage data
tool_usagedata  
  
Optionally enables periodic submission of product usage data to Totara Cloud.

### Diagnostic tool for support
tool_diagnostic  
  
Allows admin to collect a set of information about the site to submit along with a support ticket to assist troubleshooting.

### Numeric custom fields
profilefield_decimal  
profilefield_integer 
  
Allows admins to attach numeric data to items which allow custom fields.

### OpenAI
ai_openai  
  
Adds support for OpenAI generative prompts as part of a new artificial intelligence subsystem.

### Inspire theme
theme_inspire  
  
An updated default theme with optional left-hand navigation.

### Engage course resource
engage_course  
  
Allows users to share courses in playlists and workspaces.

### Mobile completed learning
mobile_completedlearning  
  
Allows display of completed learning items in the Totara mobile app.

[Table of contents](#toc)

<a name="tui"></a>

## New Tui components

### New core components
- card/CardScroller.vue
- card/LearningCard.vue
- filters/SortBar.vue
- popover/Tooltip.vue
- tabs/TabBar.vue
- treeview/TreeView.vue
- treeview/TreeViewNode.vue

## New icons
- icons/ClearInput.js
- icons/Featured.js
- icons/LayoutReverse.js
- icons/MobileMenu.js
- icons/Question.js
- icons/Rating.js
- icons/Task.js
- icons/internal/obj/totara/featured.svg

[Table of contents](#toc)

<a name="deprecated"></a>

## Deprecations

### Core Tui
- buttons/Button.vue: prop `styleclass` is deprecated
- buttons/ButtonIcon.vue: prop `styleclass` is deprecated
- grid/GridItem.vue: `props.hyphens` is deprecated
- sidepanel/SidePanel.vue: `props.showButtonControl` is deprecated
- src/js/apollo_client.js is deprecated
- `toVueRequirements()` in src/js/i18n.js is deprecated
- src/js/i18n_vue_plugin.js is deprecated
- `tui.needsRequirements()`, `tui.loadRequirements()`, `tui.vueAssign`, and `tui.theme` are deprecated
- `getPropDefs()`, `getModelDef()`, `set()`, and `vueAssign()` in src/js/vue_util.js are deprecated
- `getQueryStringParam()` and `parseQueryString()` in src/js/internal/util/url.js are deprecated in favour of `parseParams()`
- src/js/polyfills/ResizeObserver.js is deprecated

### Legacy theme
A large number of CSS variables and mixins have been deprecated, see server/theme/legacy/scss/totara/css_variables/deprecated.scss

### Workspaces
- card/DiscussionCard.vue and card/DiscussionContentResultCard.vue: `computed.profileUrl` is deprecated
- create/ContributeWorkspace.vue component is deprecated
- form/WorkspaceTransferOwnerForm.vue component is deprecated
- head/EmptySpacesHeader.vue component is deprecated
- modal/WorkspaceTransferOwnerModal.vue component is deprecated
- sidepanel/WorkspaceControlMenu.vue component is deprecated
- sidepanel/WorkspaceMenu.vue component is deprecated
- pages/EmptySpacesPage.vue component is deprecated
- `\container_workspace\workspace::get_user_id()` is deprecated, use `owners()->first()?->id`
- `\container_workspace\workspace::remove_user()` is deprecated, use `workspace::remove_owners()`
- `\container_workspace\workspace::update_user()` is deprecated, use `workspace::add_owners()`
- `\container_workspace\local\workspace_helper::update_workspace_primary_owner()` is deprecated
- `\container_workspace\output\transfer_ownership_notification` class is deprecated
- `\container_workspace\task\notify_new_workspace_owner_task` class is deprecated
- `\container_workspace\webapi\resolver\mutation\change_primary_owner` class is deprecated
- AJAX GraphQL mutation `container_workspace_transfer_owner` is deprecated

### Seminar
`mod_facetoface\totara_notification\recipient\reservation_managers` has been deprecated. The notifications which were using this recipient now trigger notifications for each individual recipient.

### Perform
Tui components:
- manage_activity/content/ActivitySection.vue: `computed.isNew` is deprecated
- manage_activity/content/WorkflowSettings.vue component is deprecated
  
GraphQL input mod_perform_subject_instance_filters has deprecated these filters:
- "exclude_complete": select all participant_progress values except 'complete' instead.
- "overdue": this is replaced by the new activity_progress filter instead.
   
The following GraphQL resolvers (and their associated queries and mutations) have been deprecated:
- `mod_perform_update_activity` - please use "mod_perform_update_activity_basic_settings" instead
- `mod_perform_update_activity_workflow_settings` - please use "mod_perform_update_activity_closure_settings" instead. 
- `mod_perform_manual_relationship_selector_options` - please use "mod_perform_activity_controls" instead.
  
`\mod_perform\models\activity\subject_instance::get_static_instances()` is deprecated, use `subject_instance::get_subject_static_instances()` 

### Totara notifications (aka Centralised)
Deprecated endpoints `context_notification.php` and `notification_preference.php` in `https://sitename/totara/notification/`. The notification interface can now be accessed from any context using `notifications.php`.

### Pathway format
Deprecated internal AJAX mutation `format_pathway_request_non_interactive_enrol`, please use `format_pathway_request_non_interactive_enrol_v2` instead.

### Course grades
The `/grade/import/xml/import.php` endpoint is deprecated, use `/grade/import/xml/index.php`.

### Approval workflows
- `getSuccessMessageAsync()` and `getErrorMessageAsync()` in messages.js is deprecated
- `mod_approval\model\application\application_submission::supersede_submissions_for_stage` has been deprecated, because submissions are superseded automatically by `application_submission::publish()`
  
Deprecated several methods/constants on `application_editor` class, as the relevant functionality has been moved to `form_data`:
- by_application_id
- serve_file
- move_files_to_application_area
- copy_files_to_application
- FILE_COMPONENT
- FILE_AREA

### Content marketplace
Deprecated internal AJAX mutation `mod_contentmarketplace_request_non_interactive_enrol`, please use `mod_contentmarketplace_request_non_interactive_enrol_v2` instead.

### Visibility maps
Changes in namespace `\totara_core\local\visibility`:
- `base::sql_view_hidden` has been undeprecated
- `base::sql_view_hidden_exists` has been deprecated in favour of `base::sql_view_hidden`
- `map::sql_view_hidden_roles` has been undeprecated
- `map::sql_view_hidden_roles_subquery` has been deprecated in favour of `map:sql_view_hidden_roles`

### Engage 'Your library' endpoints
The following endpoints in https://site/totara/engage/ have been deprecated in favor of the unified endpoint https://site/totara/engage/index.php:
- search.php
- shared_with_you.php
- your_resources.php
- search_results.php
- saved_resources.php

### Enagage playlist
Deprecated `libraryView` URL param for the endpoint /totara/playlist/index.php

### Hierarchies
- `hierarchy_organisation\data_providers\organisations::fetch_paginated` has been deprecated, please use the `fetch()` method instead.
- `hierarchy_organisation\data_providers\positions::fetch_paginated` has been deprecated, please use the `fetch()` method instead.

### GraphQL stack
`client_aware_exception::getCategory` method is deprecated and replaced with `client_aware_exception::get_category`

### Totara comments
- comment/Comment.vue: `computed.profileUrl` is deprecated
- reply/Reply.vue: `computed.profileUrl` is deprecated
- `\totara_comment\interactor\comment_interactor::can_view_author()` and `reply_interactor::can_view_author()` are deprecated
- GraphQL fields `comment_interactor.can_view_author` and `reply_interactor.can_view_author` are also deprecated

### Report builder
- `\rb_source_goal_details::define_embeddedparams()` is deprecated

### Behat framework
- `\Moodle\BehatExtension\Driver\WebDriver::post_key()` is deprecated, use keyDown and keyUp

[Table of contents](#toc)

<a name="removed"></a>

## Removals

### Tui components
- Removed deprecated WorkspaceUserAdder.vue from container_workspace.
- The deprecated components ElementActions.vue, ExportRowAction.vue, SubjectInstanceActions.vue, and SubjectUserActions.vue have
  been removed from mod_perform.

### Roots and Basis themes
These themes were deprecated in Totara 13.

[Table of contents](#toc)
