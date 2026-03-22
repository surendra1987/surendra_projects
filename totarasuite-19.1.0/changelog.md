Release 19.1.0 (08th July 2025):  
================================  
  
Important  
---------  
  
  TL-43428 Updated the list of countries in lang/en/countries.php as per ISO 3166-1  
  
    Source: https://www.iso.org/obp/ui/  
  
    The significant changes are:  
  
    * North Macedonia  
    * Eswatini  
    * Netherlands (Kingdom of the)  
    * Taiwan (Province of China)  
    * Holy See  
  
    All other changes are formal, such as changing the letter case of the ‘And’  
    (the current ISO uses the lower case ‘and’).  Our own existing modifications  
    of the list (such as having just ‘United States’ and ‘United Kingdom’  
    instead of the full long name) were kept.  
  
  TL-44293 Added check for empty keys array to redis and memcached implementation of delete_many  
  
    Previous versions of the Redis extension for PHP ignored the missing parameter,  
    but a recent update caused cache deletion to fail with an exception when asked  
    to delete nothing.  
  
    Without this patch, upgrading PHP (even to the same minor version) on a site  
    using Redis for caching can prevent users from creating a new course.  
  
  
  
Security issues  
---------------  
  
  TL-42851 Prevented API errors from revealing absolute paths in normal error mode  
  TL-43031 Added security check related to the CSV report export format  
  
    Added a new warning to the security report when a site uses the CSV report  
    export format. CSV (Optimised for Excel) should be used instead.  
  
  TL-43046 Removed 5x multiplier from guest session expiration (CVE-2024-55648)  
  TL-43048 Improved handling of group access to ensure correct record visibility (CVE-2024-55646)  
  TL-43050 Improved validation to restrict users from viewing others with a specified tag (CVE-2024-55644)  
  TL-43202 Added a new warning to the security report if local IP addresses have not been blocked  
  TL-43204 Fixed an insecure redirect problem  
  TL-43220 Improved output cleaning of json_editor emoji node  
  TL-43231 Improved handling of special characters in json_editor renderer  
  TL-43368 Updated the metadata fetch functionality to use the local CURL system  
  TL-43607 Fixed SQL injection risk in course search module list filter (MSA-25-0010) (CVE-2025-26533)  
  TL-43612 Cleaned drop zone label text in ddimageortext question type (CVE-2025-26528)  
  TL-43614 Fixed that Feedback responses did not always properly respect separate groups modes (CVE-2025-26526)  
  TL-43615 Fixed arbitrary file read risk through pdfTeX in TeX filter (CVE-2025-26525)  
  TL-43788 Fixed IDOR in badges allowing disabling of arbitrary badges (CVE-2025-26531 / MSA-25-0008)  
  TL-43800 Added a new critical security report that will warn if the setting wkhtml2pdf is enabled  
  
    We strongly recommend disabling wkhtml2pdf as an export option and relying on  
    the regular built in PDF exports. From Totara 20 wkhtmltopdf support is removed.  
  
  TL-43912 Fixed a redirect problem with the SSOSAML authentication plugin  
  TL-44112 Removed hidden grades on some reports for users without permissions (CVE-2025-32045)  
  TL-44468 Backported MDL-84473: Fixed a security problem with EQUELLA repository (CVE-2025-3642)  
  TL-44469 Fixed an issue with Dropbox and serialisation (CVE-2025-3641)  
  TL-44479 Updated TeX filter to prevent remote code execution (CVE-2024-40446)  
  TL-45240 Disabled caching on the login page (CVE-2025-49513)  
  TL-45250 Fixed external API's disable introspection setting  
  
  
New features  
------------  
  
  TL-39249 Added ability to remove and restore access to closed participant instances for users with the Manage participant instances capability  
  
    Users with the Manage participant instances capability can now remove or restore  
    access to closed participant instances.  
  
    * When access is removed, the participant can no longer access the activity via  
      that instance or see any reference to it.  
    * If the participant has other active assignments to the same activity, they  
      will still retain access through those remaining instances.  
    * Other users assigned to the activity can still view the removed participant's  
      responses.  
    * All performance activity queries that return instance data will now check  
      whether access has been removed.  
  
  TL-43166 Added external API service totara_reportbuilder_get_report for querying select reports  
  
    A new GraphQL service has been created to enable users to retrieve reports  
    created in Totara that have been made available to the API user.  Details for  
    this new service can be found in the schema under  
    totara_reportbuilder_get_report. The endpoint requires the Report ID, and  
    optionally accepts a sort column, page number, and saved search ID. Each of  
    these behaves as they do in the UI.  
  
  TL-43427 Added GraphQL services for seminars  
  
    The following GraphQL services have been added to the seminars activity module  
    ({{mod_facetoface}}):  
  
    * Query {{mod_facetoface_seminar}} - Get a single seminar  
    * Query {{mod_facetoface_seminars_in_course}} - Query a list of seminars within  
      a specific course  
    * Query {{mod_facetoface_event}} - Get a single seminar event  
    * Query {{mod_facetoface_events}} - Query a list of events  
    * Query {{mod_facetoface_event_user_booking}} - Gets the user booking for a  
      given seminar event  
    * Mutation {{mod_facetoface_event_cancel_user_booking}} - Cancel a user  
      booking/signup for a seminar event  
    * Mutation {{mod_facetoface_event_create_user_booking}} - Create a user  
      booking/signup for a seminar event  
  
    New installations will have all necessary capabilities assigned to the API user  
    archetype by default. Upgraded Totara sites will need to manually assign the  
    following capabilities to the service user to use these new services:  
  
    * {{mod/facetoface:signup}}  
    * {{mod/facetoface:view}}  
    * {{mod/facetoface:viewallsessions}}  
  
  TL-43476 Added a Capability for accessing Tui samples page and removed the page's login check  
  
    * Added a Capability 'totara/tui:samples' for accessing Tui samples page  
    * Removed the require_login check in /totara/tui/index.php  
  
  TL-44011 Added self check-in functionality to seminars  
  
    Seminars now support self check-in via a new set of settings that define a  
    self-attendance window.  
  
    When enabled, users can mark themselves as attended to a seminar event by  
    either:  
  
    * Visiting a provided link, or  
    * Scanning a *QR code*  
  
    This feature provides greater flexibility for attendance tracking and reduces  
    administrative overhead for facilitators.  
  
  TL-44174 Added setting to exclude courses from the catalogue  
  
    A new *Exclude from catalogue* checkbox has been added to the course settings  
    page, under *Visibility* settings.  
  
    When enabled, the course will be hidden from all catalogue views, including  
    Explore, Mobile, and Grid catalogues. The course will still be accessible  
    through other channels, such as:  
  
    * Current learning  
    * Programs and plans  
    * Direct links  
  
    This provides admins with greater control over which courses appear in the  
    catalogue without affecting course availability elsewhere.  
  
  TL-44200 Added webhooks  
  
    Totara now comes with webhooks. This allows a third party to subscribe to events  
    that happen in Totara and have them sent as they occur.  
  
    This opens up the 900+ events in Totara to send out delta updates as they occur.  
    The requests are signed so that third parties can verify that the content comes  
    directly from Totara and hasn’t been tampered with. Webhooks also ships with a  
    data sanitation hook, allowing custom plugins to redact or expose the data that  
    is sent out.  
  
  TL-44414 Added the Excimer profiling plugin  
  
    If you have the Excimer PHP extension installed, this plugin can be used to  
    capture and investigate slow performing requests.  
  
  TL-44443 Added self enrolment options for programs and certifications  
  
    It is now possible to configure programs and certifications to allow learners to  
    self-enrol. This is achieved through a new assignment type called Groups.  
  
    In the Assignments interface within programs and certifications, administrators  
    now have the option of creating one or more assignment groups. A group is a  
    collection of users whose members can be controlled within the program or  
    certification. When created, administrators can give the group a name and  
    description which are used to inform learners as to the purpose of the  
    assignment group. A completion date can be set on a group, and all users who  
    join the group will be given that due date.  
  
    Administrators can configure each group to allow self-enrolment and, separately,  
    withdrawal. When ‘Allow learners to enrol’ is enabled, learners can enrol in  
    the group using the Enrol button provided on the program or certification  
    overview page. Likewise, if ‘Allow learners to withdraw’ is enabled,  
    learners can use the Enrolment options link to remove themselves from the group.  
  
    As well as allowing self-enrolment, the new group assignment type can be used as  
    an easier way to manage individual assignments. Administrators can optionally  
    manually manage the individuals within groups. Administrators can choose to  
    disable both learner enrolment and withdrawal - in this case the group will only  
    contain users manually added by an administrator. This provides the benefit of  
    being able to configure the completion date for all users in the group at once,  
    rather than setting it for each individual individually. Additionally, managing  
    individuals in groups rather than as Individual assignments means that the main  
    Assignments list will be easier to manage.  
  
  TL-44818 Added a new settings page for enabling new features and improvements in minor releases  
  
  
Performance improvements  
------------------------  
  
  TL-40368 Improved performance for resetting / archiving course completions for courses with a large amount of users by running actions in bulk  
  TL-42203 Added an option to reports to disable the visibility check when rendering user profile links  
  
    Complex setups can significantly slow down profile visibility checks in reports.  
    With this change, a new option is now available on the performance tab. If this  
    option is enabled, the link to the user's profile will be displayed without  
    checking for a valid relationship. If no relationship exists, clicking on the  
    profile link will result in a ‘permission denied’ error.  
  
  TL-42960 Optimised audience, organisations and positions preloading in course restriction settings  
  TL-43184 Improved the performance of generating role maps for approval workflows  
  TL-43188 Improved the performance for mobile completed learning API calls  
  
    Previously the completed learning API was fetching the entirety of a users  
    completed learning, before combining learning types, and paginating. This could  
    potentially lead to a performance hit for users with large amounts of completed  
    learning, so we have limited this to the 100 most recently completed items of  
    each type.  
  
  TL-43676 Improved performance of menu item checks for course category and content marketplace administration  
  TL-44131 Improved performance when loading tiles in Explore catalog blocks  
  
    We improved the queries used to retrieve information needed to display the tiles  
    in Explore catalog blocks resulting in a reduction in the number of database  
    queries.  
  
  TL-45056 Added caching for user profile custom fields to improve HR Import performance when importing many custom fields for a user  
  
  
Improvements  
------------  
  
  TL-37496 Added a check to the performance overview report for users with the prohibit capability flag  
  
    The performance overview report now includes a check that respects the prohibit  
    capability flag.  
  
    This ensures that users explicitly prohibited from accessing certain performance  
    data (via capability settings) are correctly excluded from report results -  
    enhancing alignment with role-based permission controls.  
  
  TL-39382 Enabled AI course tag suggestions generated for draft course content  
  TL-41009 Added pagination to the categories in the courses and categories page  
  
    Pagination has been introduced for the categories list on the Courses and  
    categories page.  
  
    This feature improves performance and usability when managing large numbers of  
    categories.  
  
  TL-41805 Added information about pathway format to the course format help text  
  TL-41897 Added support for filters in the mobile find learning API  
  TL-41993 Added support for the progress bar to the mobile catalogue API  
  TL-42136 Improved positioning of buttons when restoring a backed up course  
  TL-42258 Increased the program 'fullname' database field length to better support multi-language  
  
    Increased the program ‘fullname’ database field length from 254 to 1333  
    characters.  
  
  TL-42378 Improved spacing between the user image and a glossary entry  
  TL-42401 Improved the user interface for managing filters in a course  
  TL-42430 Improved accessibility of notification close buttons  
  TL-42521 Added total number of Totara goal tasks to product usage data   
  TL-42522 Added total number of Totara goal comments to product usage data  
  TL-42589 Improved accessibility by adding aria-live attribute to announce results when filtering a report  
  TL-42651 Added configurable fallback SQL 'LIKE' search to catalogue  
  
    A new fallback SQL LIKE search has been added to enhance catalogue search  
    functionality.  
  
    This fallback can be configured in catalogue settings to run:  
  
    * Always  
    * Only when no results are found  
    * Or never  
  
    This feature provides more flexible search behavior, especially when exact  
    matches aren't available.  
  
  TL-42704 Increased side panel height to match the content  
  TL-43005 Improved the alignment of sections and activities when editing courses  
  TL-43089 Trigger events for assigning and un-assigning audiences from programs  
  
    This change can be opted out by adding {{$CFG->revert_TL_43089_until_t20 =  
    true;}} in your {{config.php}} file. However it will be enforced for Totara 20.  
  
  TL-43185 Added the ability to rotate the client secret for a specific external API client  
  
    When the client secret for an external API client is rotated, all existing  
    access tokens are deleted, and the client secret value is regenerated. This  
    means any external integrations relying on the existing client secret value will  
    need to be updated, and any sessions relying on existing access tokens will be  
    invalidated.  
  
  TL-43212 Improved the error string when the mobile server is not reachable   
  TL-43386 Added options to compress (.zip) scheduled report attachments and limit file size  
  
    Within the settings of a Scheduled report, there is now a ‘Compress file’  
    checkbox which can be selected to compress the file size of the scheduled  
    report.  
  
  TL-43487 Added IP whitelisting support for external API clients  
  
    This update introduces the ability to restrict external API client access to  
    specific IP addresses via whitelisting.  
  
    * Requests from non-whitelisted IPs are rejected based on the configured *API  
      debug level*:  
    ** {{None}}: returns ‘404 Not Found’  
    ** {{Normal}}: returns ‘403 Forbidden’  
    ** {{Developer}}: returns a detailed GraphQL error  
    * Blocked requests are logged server-side for auditing  
    * Site-level IP restrictions remain in effect as an additional layer of control  
    * If no whitelist is configured, access is unrestricted, unless limited by  
      existing site-level security settings  
  
    This feature enhances security by allowing tighter control over which clients  
    can access external APIs.  
  
  TL-43528 Added timemodified as properties for hierarchy positions and organisations in GraphQL and the ability to filter records on since_timemodified  
  
    Added {{timemodified}} property to {{hierarchy_position_position}},  
    {{totara_hierarchy_position}}, {{hierarchy_organisation_organisation}}, and  
    {{totara_hierarchy_organisation}}. Added {{since_timemodified}} filter to the  
    {{hierarchy_position_positions}} and {{hierarchy_organisation_organisations}}  
    queries.  
  
  TL-43586 Added Custom script for migrating theme settings from Ventura to Inspire  
  TL-43616 Added mobile API support for selected offline activities  
  
    Added support for offline activity types in the mobile API, including:  
  
    * Labels  
    * Pages  
    * Files  
    * Certificates  
  
    These activity types are now accessible via mobile endpoints, improving support  
    for offline learning scenarios.  
  
  TL-43631 Updated default capabilities of API user archetype to include `totara/hierarchy:vieworganisationframeworks` and `totara/hierarchy:viewpositionframeworks`  
  
    Previously, the API user archetype (role) did not include the necessary  
    capabilities to view the organisation and position frameworks on the position  
    and organisation return types.  
  
    On fresh installs, these capabilities will be automatically added to the API  
    user archetype. On existing installs, the capabilities will need to be manually  
    assigned to the archetype. See  
    https://totara.help/docs/edit-a-role|https://totara.help/docs/edit-a-role|smart-link  
    for more information.  
  
  TL-43634 Added handling for OAuth 2.0 responses that contain an error parameter   
  TL-43709 Added system status check for ephemeral configuration flags  
  
    From time to time, Totara bug fixes will include settings that allow system  
    administrators to temporarily revert to previous behaviour until the next major  
    release. This check attempts to detect any use of those settings in config.php,  
    and report them on the system status report, or via the check CLI at `php  
    server/admin/cli/checks.php`.  
  
  TL-43820 Changed the behaviour for HR Import to add a confirmation prompt that displays when an import source contains fewer records than the site does and the 'Source contains all records' setting is set to 'Yes'  
  
    When a user provides a CSV or external database with fewer records than the site  
    has, and the ‘Source contains all records’ option is set to 'Yes', the  
    existing records in the site are deleted and replaced with the records defined  
    in the import source.  
  
    This change adds a message that displays the percentage difference between the  
    number of records in the import source versus the number of existing records in  
    the site. This message must be confirmed before the import runs to ensure the  
    user acknowledges the potential data loss impact of running the import.  
  
  TL-43886 Added Security overview report and Site performance overview report as new options to the Diagnostics for support tool  
  TL-43887 Added 'Require passing grade' completion option to external tool activity  
  
    It is now possible to configure completion of external tool activities to  
    require a passing grade.  
  
    This change can be opted out by adding $CFG->revert_TL_43887_until_t20 = true;  
    in your config.php file. However it will be enforced for Totara 20.  
  
  TL-43934 Added support for using a query builder in repository-only contexts  
  
    Introduced {{core\orm\query\query_builder_to_repository_adapter}}, an adapter  
    that allows a query builder to be used in situations where only a repository is  
    permitted - such as in data provider classes.  
  
  TL-43973 Improved accessibility on the multiselect legacy adder  
  
    Users using the legacy adder can now remove selected items with keyboard only  
    and “x” icon is shown on all selected items at all times and not just on  
    hover.  
  
  TL-44018 Added Totara 20.0.0's definition to the environment checks page  
  TL-44119 Changed the behaviour for HR Import to prevent importing an empty source when the 'Source contains all records' setting is set to 'Yes'  
  
    Previously, if a user provided an empty CSV or external database, and the  
    ‘Source contains all records’ setting was set to ‘Yes’, importing would  
    result in all the records of that element type being deleted.  
  
    With this change, the new behaviour will be to log an exception and prevent that  
    element from importing. This does not prevent subsequent elements from running  
    if they have a valid file.  
  
  TL-44127 Wrapped update_certification_task operations in a try-catch and transaction block  
  
    Previously, the totara_certification\task\update_certification_task scheduled  
    task would stop processing if a failure occurred for one record. It would not  
    process the following records.  
  
    The next time the task ran, it would try to process the same record again and  
    possibly fail again. As the records are probably ordered, it means that the  
    records following the failing record would never be processed.  
  
    This patch wraps the operations which might fail in a transaction and catch any  
    exceptions and continues to the next record.  
  
  TL-44130 Added usage data for catalogue configuration  
  TL-44184 Updated Recommendation block cards to match catalogue card styling  
  TL-44277 Updated Latest badges block to center-align badges  
  TL-44279 Added rounded corners to the Featured links block and fixed width of the full-width no-margin link  
  
    This update improves the visual consistency of the Featured links block:  
  
    * Added rounded corners to block tiles to match the standard card style,  
      (excluding the full-width, no-margin tile, which retains its original shape)  
    * Fixed layout of the full-width, no-margin tile so it correctly displays  
      without margins at desktop width when using the Inspire theme  
  
  TL-44280 Added wider spacing to the Featured link tile content and aligned text typography styling to match the design system  
  TL-44296 Improved spacing on the upcoming Events block  
  TL-44303 Removed horizontal padding for Calendar block content when it has no border  
  TL-44430 Updated the scheduled reports task to identify which report is being processed  
  
    When the report builder scheduled report task is executed on the cron, the  
    system now outputs the scheduled report ID and report name prior to each report  
    being generated. This can assist with debugging performance issues.  
  
  TL-44686 Separated Trending content into its own block, distinct from the Recommended for you block  
  
    As of Totara 19.1, the Trending recommendation type has been split into a  
    dedicated block, separate from the Recommended for you block.  
  
    * The Trending recommendation type is still supported in 19.1 for backward  
      compatibility  
    * It is planned for removal in Totara 20  
  
    This change improves clarity and content targeting by distinguishing trending  
    items from personalised recommendations  
  
  TL-44727 Added "Did you mean" spellcheck suggestions to catalogue search  
  
    This feature can be enabled in the catalogue configuration settings.  
    It requires the PSpell PHP extension to be installed, as well as the user’s  
    selected language to be installed.  
  
  TL-44881 Updated the English help text for the 'Locked' field in the Edit Dashboard form to improve clarity  
  TL-44917 Fixed HR Import jobs run via the user interface from blocking other page activity  
  
    Previously, running an HR Import job through the user interface would lock the  
    user’s session, preventing them from performing other actions or loading pages  
    in additional tabs.  
  
    This update unlocks the session after the import job starts, allowing users to  
    continue working elsewhere in the system without encountering error messages.  
  
  TL-44923 Improved error message when session cannot be started  
  
    Previously, the error shown when a session couldn’t be obtained only suggested  
    a server issue. The updated message now also indicates that the error may be  
    caused by another parallel request still in progress, helping users better  
    understand the possible cause.  
  
  TL-44931 Improved the clarity of the heading for the dashboard edit page  
  
    The heading on the dashboard edit page now better reflects the action being  
    performed:  
  
    * When creating a dashboard, the heading is now 'Create dashboard'  
    * When editing a dashboard, the heading displays the dashboard’s name  
  
    This change improves clarity and aligns the page title with the user’s current  
    task.  
  
  TL-45088 Renamed the 'Experimental' category under Development in Site Administration to 'New and experimental'  
  
  
Visual improvements  
-------------------  
  
  TL-42883 Improved the creation of performance activities, the first section will now be set to edit mode by default  
  TL-44498 Improved label alignment in self-enrolment card layout  
  
  
Bug fixes  
---------  
  
  TL-33788 Fixed an error when trying to update the content of a learning plan containing hidden programs  
  TL-35659 Added the ability to purge user data for active applications in approval workflows  
  TL-36096 Added aria-expanded to all Tui Dropdown triggers that were missing it  
  TL-37948 Fixed an error in the 'Self-registration requests' report that occurred when the Tenant Member column was included  
  
    The issue was caused by a missing database join, leading to a DB error when  
    generating reports containing this column.  
  
  TL-38355 Ensured that guests can view activities on a pathway course  
  TL-38698 Fixed users being unsubscribed when subscription mode changes from 'Forced subscription' to 'Auto subscription'  
  TL-39006 Removed whitespace from the bootstrap breadcrumb separator  
  TL-39836 Removed site policy consent requirement for external API and legacy web services requests  
  
    Previously, site policy consent was required for service users making external  
    API and legacy web service requests. This caused issues where service users -  
    used for external calls to Totara APIs, which weren’t designed for user logins  
    - were forced to log in and consent to the site policy when the site policy was  
    enabled or updated.  
  
    This update removes the site policy consent requirement for requests originating  
    from GraphQL or legacy web services.  
  
    Note: Service users will still need to consent to the site policy if they are  
    used within the normal user interface.  
  
  TL-39906 Fixed some race conditions with localcache when the cache is purged on a busy site  
  
    Mustache, htmlpurifier and RequireJS will check if the cache directory is  
    writeable. If it is not, they will log a message to the debugging logs but will  
    still serve the content.  
  
    If you have directly edited the caching files for these libraries in localcache,  
    you may need to check that your customisations are still writing content as  
    expected.  
  
  TL-40189 Fixed forced delivery channels not overriding recipient 'Disable all notifications' setting  
  
    When using legacy notifications, it was possible to force delivery of  
    notifications, even when the recipient had turned on the ‘Disable all  
    notifications’ setting. In centralised notifications, it was intended that  
    forcing a delivery channel would have the same result. This has been fixed. This  
    change also re-enables notifications to be shown in the bell popup, allowing  
    recipients to see notifications they received in the past or new notifications  
    sent by forced delivery.  
  
    This change can be opted out by adding $CFG->revert_TL_40189_until_T20= true; in  
    your config.php file. However it will be enforced for Totara 20.  
  
  TL-40261 Fixed an issue where cohort role category context was not updating after deleting a category  
  TL-40292 Improved accessibility on assignment submission table  
  TL-40320 Fixed error on My Goals page caused by duplicates created when 2 operations ran at same time  
  TL-40371 Fixed seminar custom fields not being saved when a job assignment is selected during signup  
  TL-40450 Fixed an issue in the user upload tool that was blocking uploads for users with unique profile fields  
  TL-40682 Added multi-language support for the Oauth2 plugin  
  TL-40775 Removed extra role assignments when changing assigned roles in enrolment methods for courses  
  
    This change can be opted in by adding $CFG->enable_TL_40775_until_T20 = true; in  
    your config.php file. However it will be enforced for Totara 20.  
  
  TL-40822 Fixed an issue where the seminar signups report was containing duplicate records for session attendance  
  TL-40827 Fixed learning plan not respecting the global ‘Default role’ or ‘Enrolment period’ settings for new instances  
  TL-40871 Fixed the language menu on the legacy login page to display only when the setting is enabled  
  TL-41033 Fixed error when showing custom profile fields that are both locked and required  
  TL-41065 Removed HTML tags from 'Element response' column of 'Performance activity response data' report when exporting as CSV or Excel  
  TL-41222 Fixed an accessibility issue where tabbing would move focus incorrectly on an un-contained dropdown  
  TL-41329 Improved performance of the 'delete_completion_logs' task  
  
    Limited delete_completion_logs task to 5 minutes  
  
  TL-41331 Fixed bug in audience sync enrolment method due to deleting context in role  
  TL-41642 Fixed wrong parameter in program due dates report  
  TL-41712 Fixed page layout in some situations with the Pathway course format  
  TL-41771 Resolved a dependency error when adding the block_totara_report_manager block to a dashboard's top region  
  TL-41793 Fixed Totara goal snapshots not showing up for deleted goals on closed performance activity sections  
  TL-41949 Disallowed 'Reset course completion' when the course is part of a program or certification   
  
    Previously, after releasing Totara 15.1, the site manager was able to reset  
    course completion (archived and reset the user course completions) even if a  
    course is part of a program and/or certification.  
  
    This change is reverting the functionality before Totara 15.1 was released. The  
    change introduces a new configuration  
    {{$CFG->allow_course_completion_reset_for_program_courses}} which will force a  
    course completion to archive and reset the user completion records with program  
    and/or certification assigned.  
  
  TL-42086 Fixed a division by zero error in the SCORM save_offline_attempts query for mobile API   
  TL-42160 Fixed a problem where some reports with null values were unable to be exported when using PHP 8.1 or greater  
  TL-42435 Moving activities between course sections is now done in a database transaction to avoid broken sequences if something goes wrong  
  TL-42497 Updated the border colour of the active pagination button to match its background colour through the use of a variable  
  TL-42581 Removed tab index wrapping content in a YUI modal  
  TL-42584 Improved screen reader text of icons when managing courses, programs and certifications  
  TL-42588 Updated notification roles from 'log' to 'status' for better screen reader accessibility  
  TL-42590 Improved accessibility when filtering seminar sessions  
  TL-42591 Fixed issue where editing a hierarchy item could incorrectly move it to a different framework  
  TL-42603 Fixed error when exporting report with tenant filter  
  TL-42686 Allowed decimal and integer custom fields to be optional  
  
    Decimal and integer custom fields can now be set as optional in forms. If a user  
    does not specify a value, the default value for the field will be used.  
  
  TL-42694 Fixed being able to add members to a workspace even if an exception was thrown  
  TL-42709 Fixed error message while searching for users in comments with mixed context  
  TL-42736 Improved learning plan and cohort alert user interface issues  
  TL-42800 Fixed application dashboard table column values overlap  
  
    Fixed the ‘applicant’ column values overlapping with the ‘submitted on’  
    column in workflow application dashboard table.  
  
  TL-42802 Removed double borders from competency scale action buttons  
  TL-42810 Updated the toggle switch to display the correct colour when in an active disabled state  
  TL-42828 Fixed incorrect ARIA attribute on competency assignment list rows  
  TL-42834 Fixed CSS styling for the title and help icon on the ‘Edit proficiency value by assignment' page  
  TL-42874 Fixed incorrect aria-labelledby attribute on the Engage contribution modal  
  TL-42887 Fixed approval workflow application header action buttons height  
  TL-42898 Removed empty link 'more help' from help icon popover  
  TL-42934 Fixed the reset button on Seminars 'Upcoming Events' filter not being translatable  
  TL-42981 Fixed the formatting of seminar descriptions created using Weka when included in iCalendar attachments  
  TL-43008 Fixed a situation where duplicates could be shown when viewing another user's Library  
  TL-43059 Fixed footnote display, including reviewed item count, for workspace library cards loaded using the Load more button.  
  TL-43244 Added exit activity button to SCORM activities  
  
    Added an "exit activity" button to SCORM activities so that when activities are  
    dependent on the SCORM being completed then it can be "exited" which will take  
    the user to the start page of the activity. This updates the activities and  
    unlocks any dependent activities that were previously restricted.  
  
  TL-43297 Prevented the autofill of username and password fields when creating a new user  
  TL-43313 Added missing variable in catalog filter results  
  
    Added missing {{native}} field to {{mobile_findlearning_filter_catalog}} query.  
    The lack of this field meant that if you ran a search on the mobile app, then  
    opened a course it would always assume it was not a mobile friendly course, and  
    open it via webview instead of natively.  
  
  TL-43357 Fixed string encoding for course activity completion report when export Excel-compatible option used  
  TL-43399 Fixed some incorrectly named graphql queries for mobile sub-plugins  
  
    Previously ‘mobile_currentlearning_my_items’ and  
    ‘mobile_completedlearning_my_items’ were misnamed and defaulting to the core  
    versions ‘totara_mobile_current_learning’ and  
    ‘totara_mobile_completed_learning’. These queries have been fixed for use in  
    the mobile app, the default queries remain operational for backwards  
    compatibility.  
  
  TL-43453 Hid 'Create playlist' option when user has no permission to create playlists  
  
    Additionally, the ‘Create new’ button in Your Library has been hidden  
    altogether if the user does not have permission to create any items.  
  
  TL-43481 Fixed a Tui build error that occurred when using the --vendor parameter  
  TL-43508 Fixed catalog URL incorrectly parsed when using multiple filters/ordering  
  TL-43519 Fixed console error when user lacks view capability for current learning block  
  
    Current learning block js would attempt to run even when the block was not  
    rendered  
  
  TL-43524 Added padding top and bottom to chromeless block content to improve readability  
  TL-43569 Excluded instance results where the user does not have access from being shown on the performance activity 'Select participants' page  
  TL-43589 Fixed missing entries in thirdpartylibs.xml  
  TL-43613 MDL-83941: Fixed issue where users could browse unsearchable tag collections (CVE-2025-26527)  
  TL-43716 Fixed email HTML header and footer customisations for the Inspire theme  
  
    Previously we hard-coded the 'category' setting for testing on the Inspire  
    settings page, we've removed the hard-coded value so that it falls back to the  
    default value 'brand' and correctly applies the HTML to emails.  
  
  TL-43795 Fixed an upgrade error with block_html instances missing data for custom classes  
  TL-43796 Fixed debug warnings in catalog when a playlist has limited visibility and not shared with any individuals  
  TL-43825 Fixed rendering of special characters in catalogue block title  
  TL-43932 Enabled multi-language support for drop-down options in approval workflows  
  TL-43963 Prevented updates to the timestart field when multiple API calls are made to the enrol_manual_enrol_user service  
  TL-43997 Fixed the encrypted key rollover job to skip non-encrypted configuration entries  
  TL-44020 Fixed an accessibility failure where the dismiss button on a notification toast was accessible via the keyboard even though its parent element had aria-hidden  
  TL-44147 Fixed an error when collapsing the Inspire sidebar while the user is logged out  
  TL-44160 Fixed an accessibility issue on the TreeViewNode component where an aria-controls attribute was referencing an element that didn't exist  
  TL-44183 Fixed error message in explore catalog when all filters are removed  
  TL-44188 Added title to the 'Create user' page  
  TL-44190 Fixed a PHP warning that can show when a cURL call is blocked by the IP address blacklist  
  TL-44289 Fixed an issue on some browsers where reloading the page would incorrectly show a form resubmission warning  
  TL-44295 Fixed Totara Goals not being locked down properly on non-Perform flavours  
  
    This change only affects existing sites without Perform flavour.  
  
    It makes sure goals features are disabled unless legacy goals were in use on the  
    site.  
  
  TL-44298 Disabled goals choice setting for flavours that do not include Perform features  
  
    As a consequence of this patch, sites without Perform features can still keep  
    legacy goals enabled if currently in use, but cannot re-enable once it’s  
    turned off in administration settings.  
  
  TL-44339 Fixed an accessibility issue with colour contrast in the current learning block  
  
    Implemented consistent white background for "Sets" with a faint grey border.  
  
  TL-44375 Fixed a bug where the user profile picture was unintentionally visible to screen readers  
  TL-44376 Fixed an accessibility issue by removing menu bar roles on the legacy primary navigation  
  TL-44377 Fixed a bug when SAML metadata was signed  
  
    In previous versions if you enabled the “Sign metadata” option, the  
    signature would be applied and then the metadata would be formatted. The act of  
    formatting the metadata though would change the signature and invalidate it.  
  
    With this fix when the metadata is signed we no longer format it for visibility,  
    leaving it as it was exactly when signed.  
  
  TL-44378 Changed the parent container role from 'log' to 'list' to properly contain message items with role 'listitem'  
  TL-44380 Updated ARIA attributes for message, notification, and admin menu popovers  
  TL-44495 Fixed an issue with the positioning of the user tour on inspire theme navigation items  
  
    Also added a border radius to the tour popover.  
  
  TL-44501 Fixed an error when cache attempts to read a file that is empty  
  TL-44532 Fixed a bug where the uniform FormField component would have an empty aria-describedby attribute  
  TL-44597 Updated JMeter script  
  TL-44653 Fixed a LTI (external tool) authentication issue with JWT  
  
    Fixed JWT handling for LTI (Learning Tools Interoperability) authentication to  
    support strict encoding.  
  
  TL-44679 Added missing ARIA attributes on grid catalog details popover  
  TL-44698 Fixed require passing grade completion criteria not being checked in external tool  
  TL-44712 Fixed filestore cache so when a file cannot be unserialised it will log a debug message instead of crashing the entire site  
  TL-44742 Fixed a bug where users who login via SAML were unable to launch LTI activities  
  TL-44788 Added userdata classes for AI interactions  
  TL-45007 Fixed a problem when installing Totara without the openssl extension  
  TL-45036 Courses set to the Single Activity format are now available in the Recently Viewed block  
  TL-45047 Fixed a user interface issue where the short name would not wrap correctly in admin settings  
  TL-45125 Updated mobile language strings to match current app requirements  
  TL-45204 Added an incrementation to query complexity for unresolved GraphQL types  
  
    Previously, unresolved types in GraphQL queries or mutations were not factored  
    into query complexity calculations. This could lead to large responses that  
    bypass API limits, causing unexpected server load.  
  
    This update ensures that unresolved types now contribute to overall query  
    complexity - if the query or mutation supports it.  
  
    In version 19.1.0, this behavior can be temporarily disabled using the  
    configuration value: revert_TL_45204_until_T20.  
  
  
  
Database upgrades  
-----------------  
  
  TL-41350 Increased the Fullname column character limit to 1333 characters for the tool_recyclebin_category database table  
  
  
Technical changes  
-----------------  
  
  TL-41377 Fixed the get_certiftimebase calculation not using user's current window open date  
  
    When a certification was configured to use the expiry date for completion  
    calculations, when calculating if a user’s completion occurred within the  
    recertification window, it was incorrectly using the certifications current  
    window period, rather than the window open date used to open the user’s  
    recertification window. If a user completed a certification after their  
    recertification window opened but outside the certification’s  
    currently-calculated window period, this would cause the completion to be  
    ignored and the user would need to recertify immediately. This change causes the  
    user’s window open date to be used in the calculation.  
  
    This change includes a change to the function get_certiftimebase. This function  
    now accepts an additional parameter $timewindowopens. This parameter is optional  
    in Totara 19.1.0 and below and will default to the original behavior if not  
    provided, but is required in Totara 20 and above.  
  
    The new behavior can be opted out by adding {{$CFG->revert_TL_41377_until_t20 =  
    true;}} in your {{config.php}} file. However, it will be enforced for Totara 20.  
  
  TL-43993 New OAuth2 issuers will be encrypted when created  
  TL-44251 Added the ability to run independent performance tests in PHPUnit  
  TL-44529 Added a 'tui_props' hook so that plugins can inject or alter page props if necessary  
  TL-44777 Added a new result type for graphql mutations for richer responses  
  
    A new mutation resolver type, mutation_result, has been added. It accepts the  
    following properties:  
  
    * success (boolean) – Indicates whether the mutation was successful.  
    * return_url (optional string) – A URL the client can redirect to, if  
      applicable.  
    * code (optional string) – A machine-readable status or error code.  
    * message (optional string) – A human-readable message providing additional  
      context.  
  
    These properties allow the server to return richer contextual information that  
    the client can use to guide follow-up actions.  
  
  TL-44789 Refactored core_ai classes to improve developer experience  
  
    The AI framework has been upgraded to support the development of new types of  
    interactions, including generative prompts with file input and/or structured  
    output, and generative images. Example plugins will be released separately via  
    the Totara git repository.  
  
  TL-44831 Added Encrypt & TrustServerCertificate options for MSSQL PHPUnit tests  
  
  
Tui front end framework  
-----------------------  
  
  TL-41192 Improved Tui Samples page with cleaner components and updated structure  
  
    Enhancements to the Tui Samples page include:  
  
    * Removal of all code samples for a cleaner and more focused user interface  
    * General cleanup of sample components for consistency and clarity  
    * New sections added for events and slots, where applicable, to improve  
      documentation and usability  
  
    These changes aim to streamline the page and make it more useful for developers  
    and users exploring Tui components.  
  
  TL-43181 Fixed race condition in useParamState  
  
    Fixed a race condition that could occur when calling .push() or .replace()  
    followed by immediately updating .value, which would cause the second update to  
    not be reflected in the URL.  
  
  TL-43329 Reorganised Tui Samples component structure to align with the design system  
  
    The Tui Samples component structure has been reorganised to better reflect the  
    structure and guidelines of the design system.  
  
    This update improves consistency, discoverability, and maintainability of  
    component samples, making it easier for developers and designers to navigate and  
    align with system-wide standards.  
  
  TL-43775 Added a chromeless layout to Tui Samples  
  
    The chromeless layout on the Tui Samples page removes the header, navigation,  
    and footer, providing a cleaner, distraction-free view for showcasing user  
    interface components.  
  
  TL-43843 Added mechanism to navigate to Tui Samples for testing  
  
  
Recommendations engine  
----------------------  
  
  TL-43595 Upgraded the nltk library to 3.9.1 (CVE-2024-39705)  
  TL-43601 Upgraded the Waitress library to 3.0.2 (CVE-2024-49769)  
  TL-43719 Fixed an error that can appear when not enough interactions are provided with a new connection  
  
    This only occurs when running the machine learning service with enough users and  
    items, but no interactions, which is rare. With this patch, we now provide an  
    error in the model and healthcheck indicating if the number of interactions is  
    too few to make recommendations.  
  
  
  
Contributions  
-------------  
  
  * Catalyst IT - TL-44414  
  * Dan Marsden at Catalyst - TL-43795  
  * Petter Fogelqvist at Aleido - TL-43481  
  
