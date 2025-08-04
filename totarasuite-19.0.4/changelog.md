Release 19.0.4 (02nd May 2025):  
===============================  
  
Important  
---------  
  
  TL-44293 Added check for empty keys array to redis and memcached implementation of delete_many  
  
    Previous versions of the Redis extension for PHP ignored the missing parameter,  
    but a recent update caused cache deletion to fail with an exception when asked  
    to delete noting.  
  
    Without this patch, upgrading PHP (even to the same minor version) on a site  
    using Redis for caching can prevent users from creating a new course.  
  
  
  
Security issues  
---------------  
  
  TL-42851 Prevented API errors from revealing absolute paths in normal error mode  
  TL-43031 Added security check related to the CSV report export format  
  
    Added a new warning to the security report when a site uses the CSV report  
    export format. CSV (Optimised for Excel) should be used instead.  
  
  TL-43046 Removed 5x multiplier from guest session expiration (CVE-2024-55648)  
  TL-43912 Fixed a redirect problem with the SSOSAML authentication plugin  
  TL-44112 Removed hidden grades on some reports for users without permissions (CVE-2025-32045)  
  TL-44479 Updated TeX filter to prevent remote code execution (CVE-2024-40446)  
  
  
Performance improvements  
------------------------  
  
  TL-44131 Improved performance when loading tiles in Explore catalog blocks  
  
    We improved the queries used to retrieve information needed to display the tiles  
    in Explore catalog blocks resulting in a reduction in the number of database  
    queries.  
  
  
  
Improvements  
------------  
  
  TL-42258 Increased the program 'fullname' database field length to better support multi-language  
  
    Increased the program ‘fullname’ database field length from 254 to 1333  
    characters.  
  
  TL-42430 Improved accessibility of notification close buttons  
  
    Made notification close buttons more descriptive for screen readers  
  
  TL-43887 Added 'Require passing grade' completion option to external tool activity  
  
    It is now possible to configure completion of external tool activities to  
    require a passing grade.  
  
    This change can be opted out by adding $CFG->revert_TL_43887_until_t20 = true;  
    in your config.php file. However it will be enforced for Totara 20.  
  
  TL-43973 Improved accessibility on the multiselect legacy adder  
  
    Users using the legacy adder can now remove selected items with keyboard only  
    and “x” icon is shown on all selected items at all times and not just on  
    hover.  
  
  TL-44018 Added Totara 20.0.0's definition to the environment checks page  
  
  
Bug fixes  
---------  
  
  TL-35659 Added user data purging support for active applications in approval workflows  
  TL-36096 Added aria-expanded to all Tui Dropdown triggers that were missing it  
  TL-37948 Fixed the error in the 'Self-registration requests' report when including the 'Tenant Member' column  
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
  
  TL-40371 Fixed seminar custom fields not being saved when a job assignment is selected during signup  
  TL-40682 Added multi-language support for the Oauth2 plugin  
  TL-40775 Removed extra role assignments when changing assigned roles in enrolment methods for courses  
  
    This change can be opted in by adding $CFG->enable_TL_40775_until_T20 = true; in  
    your config.php file. However it will be enforced for Totara 20.  
  
  TL-40871 Fixed the language menu on the legacy login page to display only when the setting is enabled  
  TL-42160 Fixed a problem where some reports with null values were unable to be exported when using PHP 8.1 or greater  
  TL-43297 Prevented the autofill of username and password fields when creating a new user  
  TL-43453 Hid 'Create playlist' option when user has no permission to create playlists  
  
    Additionally, the ‘Create new’ button in Your Library has been hidden  
    altogether if the user does not have permission to create any items.  
  
  TL-43519 Fixed console error when user lacks view capability for current learning block  
  
    Current learning block js would attempt to run even when the block was not  
    rendered  
  
  TL-43524 Added padding top and bottom to chromeless block content to improve readability  
  TL-43716 Fixed email HTML header and footer customisations for the Inspire theme  
  
    Previously we hard-coded the 'category' setting for testing on the Inspire  
    settings page, we've removed the hard-coded value so that it falls back to the  
    default value 'brand' and correctly applies the HTML to emails.  
  
  TL-43932 Added multi language support to approval workflows drop-down menu options  
  TL-43997 Fixed the encrypted key rollover job to skip non-encrypted configuration entries  
  TL-44020 Fixed an accessibility failure where the dismiss button on a notification toast was accessible via the keyboard even though it's parent element had aria-hidden  
  TL-44147 Fixed an error when collapsing the Inspire sidebar while the user is logged out  
  TL-44160 Fixed an accessibility issue on the TreeViewNode component where an aria-controls attribute was referencing an element that didn't exist  
  TL-44188 Added title to the 'Create user' page  
  TL-44190 Fixed a PHP warning that can show when a cURL call is blocked by the IP address blacklist  
  TL-44295 Fixed Totara Goals not being locked down properly on non-Perform flavours  
  
    This change only affects existing sites without Perform flavour.  
  
    It makes sure goals features are disabled unless legacy goals were in use on the  
    site.  
  
  TL-44298 Disabled goals choice setting for flavours that do not include Perform features  
  
    As a consequence of this patch, sites without Perform features can still keep  
    legacy goals enabled if currently in use, but cannot re-enable once it’s  
    turned off in administration settings.  
  
  TL-44501 Fixed an error when cache attempts to read a file that is empty  
  
  
  
  
  
Release 19.0.3 (26th March 2025):  
=================================  
  
Security issues  
---------------  
  
  TL-43050 Improved validation to restrict users from viewing others with a specified tag (CVE-2024-55644)  
  TL-43612 Cleaned drop zone label text in ddimageortext question type (CVE-2025-26528)  
  TL-43614 Fixed that Feedback responses did not always properly respect separate groups modes (CVE-2025-26526)  
  TL-43788 MSA-25-0008: IDOR in badges allows disabling of arbitrary badges (CVE-2025-26531)  
  TL-43800 Added a new critical security report that will warn if the setting wkhtml2pdf is enabled  
  
    We strongly recommend disabling wkhtml2pdf as an export option and relying on  
    the regular built in PDF exports. From Totara 20 wkhtmltopdf support is removed.  
  
  
  
Performance improvements  
------------------------  
  
  TL-40368 Improved performance for reseting / archiving course completions for courses with lots of users by running actions in bulk  
  
  
Improvements  
------------  
  
  TL-42378 Improved spacing between the user image and and a glossary entry  
  TL-43586 Added Custom script for migrating theme settings from Ventura to Inspire  
  TL-43709 Added system status check for ephemeral configuration flags  
  
    From time to time, Totara bug fixes will include settings that allow system  
    administrators to temporarily revert to previous behaviour until the next major  
    release. This check attempts to detect any use of those settings in config.php,  
    and report them on the system status report, or via the check CLI at `php  
    server/admin/cli/checks.php`.  
  
  
  
Bug fixes  
---------  
  
  TL-33788 Fixed an error when trying to update the content of a learning plan containing hidden programs  
  TL-41329 Improved performance of the "delete_completion_logs" task  
  
    Limited delete_completion_logs task to 5 minutes  
  
  TL-41793 Fixed Totara goal snapshots not showing up for deleted goals on closed performance activity sections  
  TL-42588 Updated notification roles from "log" to "status" for better screen reader accessibility  
  TL-42591 Fixed issue where editing a hierarchy item could incorrectly move it to a different framework  
  TL-43244 Added exit activity button to SCORM activities  
  
    Added an "exit activity" button to SCORM activities so that when activities are  
    dependent on the SCORM being completed then it can be "exited" which will take  
    the user to the start page of the activity. This updates the activities and  
    unlocks any dependent activities that were previously restricted.  
  
  TL-43313 Added missing variable in catalog filter results  
  
    Added missing {{native}} field to {{mobile_findlearning_filter_catalog}} query.  
    The lack of this field meant that if you ran a search on the mobile app, then  
    opened a course it would always assume it was not a mobile friendly course, and  
    open it via webview instead of natively.  
  
  TL-43613 MDL-83941: Fixed issue where users could browse unsearchable tag collections (CVE-2025-26527)  
  TL-43795 Fixed an upgrade error with block_html instances missing data for custom classes  
  TL-43796 Fixed debug warnings in catalog when a playlist has limited visibility and not shared with any individuals  
  
  
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
  
  * Dan Marsden at Catalyst - TL-43795  
  
  
  
  
  
Release 19.0.2 (05th March 2025):  
=================================  
  
Bug fixes  
---------  
  
  TL-43778 Fixed maturity level  
  
  
  
  
  
Release 19.0.1 (28th February 2025):  
====================================  
  
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
  
    All other changes are formal, such as changing the letter case of the "And" (the  
    current ISO uses the lower case "and").  Our own existing modifications of the  
    list (such as having just "United States" and "United Kingdom" instead of the  
    full long name) were kept.  
  
  
  
Security issues  
---------------  
  
  TL-43048 Improved handling of group access to ensure correct record visibility (CVE-2024-55646)  
  TL-43202 Added a new warning to the security report if local IP addresses have not been blocked  
  TL-43204 Fixed an insecure redirect problem  
  TL-43220 Improved output cleaning of json_editor emoji node  
  TL-43231 Improved handling of special characters in json_editor renderer  
  TL-43368 Updated the metadata fetch functionality to use the local CURL system  
  TL-43607 MSA-25-0010: SQL injection risk in course search module list filter (CVE-2025-26533)  
  
    Patch an SQL injection risk that was identified in the moodle module list filter  
    within course search  
  
  TL-43615 Fixed arbitrary file read risk through pdfTeX in TeX filter (CVE-2025-26525)  
  
  
Performance improvements  
------------------------  
  
  TL-42203 Added an option to reports to disable the visibility check when rendering user profile links  
  
    Complex setups can significantly slow down profile visibility checks in reports.  
    With this change, a new option is now available on the performance tab. If this  
    option is enabled, the link to the user's profile will be displayed without  
    checking for a valid relationship. If no relationship exists, clicking on the  
    profile link will result in a ‘permission denied’ error.  
  
  TL-42960 Optimised audience, organisations and positions preloading in course restriction settings  
  
  
Improvements  
------------  
  
  TL-41805 Added information about pathway format to the course format help text  
  TL-42521 Added total number of Totara goal tasks to product usage data.  
  TL-42522 Added total number of Totara goal comments to product usage data.  
  TL-42589 Improved accessibility by adding aria-live attribute to announce results when filtering a report  
  TL-42704 Increased side panel height to match the content  
  TL-43089 Trigger events for assigning and un-assigning audiences from programs  
  
    This change can be opted out by adding {{$CFG->revert_TL_43089_until_t20 =  
    true;}} in your {{config.php}} file. However it will be enforced for Totara 20.  
  
  TL-43212 Improved the error string when the mobile server is not reachable  
  TL-43528 Added timemodified as properties for hierarchy positions and organisations in GraphQL and the ability to filter records on since_timemodified.  
  
    Added {{timemodified}} property to {{hierarchy_position_position}},  
    {{totara_hierarchy_position}}, {{hierarchy_organisation_organisation}}, and  
    {{totara_hierarchy_organisation}}. Added {{since_timemodified}} filter to the  
    {{hierarchy_position_positions}} and {{hierarchy_organisation_organisations}}  
    queries.  
  
  TL-43631 Updated default capabilities of API user archetype to include `totara/hierarchy:vieworganisationframeworks` and `totara/hierarchy:viewpositionframeworks`  
  
    Previously, the API user archetype (role) did not include the necessary  
    capabilities to view the organisation and position frameworks on the position  
    and organisation return types.  
  
    On fresh installs, these capabilities will be automatically added to the API  
    user archetype. On existing installs, the capabilities will need to be manually  
    assigned to the archetype. See  
    https://totara.help/docs/edit-a-role|https://totara.help/docs/edit-a-role|smart-link  
    for more information.  
  
  
  
Bug fixes  
---------  
  
  TL-38355 Ensured that guests can view activities on a pathway course  
  TL-38698 Fixed users being unsubscribed when subscription mode changes from 'Forced subscription' to 'Auto subscription'  
  TL-39006 Removed whitespace from the bootstrap breadcrumb separator  
  TL-39906 Fixed some race conditions with localcache when the cache is purged on a busy site  
  
    Mustache, htmlpurifier and RequireJS will all check if the cache directory is  
    writeable, and if not log a message to the debugging logs but otherwise serve  
    the content.  
  
    If you have directly edited the caching files for these libraries in localcache  
    you may need to check your customisations are still writing content as expected.  
  
  TL-40261 Fixed an issue where cohort role category context was not updating after deleting a category  
  TL-40450 Fixed an issue in the user upload tool that was blocking uploads for users with unique profile fields  
  TL-41065 Removed HTML tags from 'Element response' column of 'Performance activity response data' report when exporting as CSV or Excel  
  TL-41331 Fixed bug in audience sync enrolment method due to deleting context in role  
  TL-41642 Fixed wrong parameter in program due dates report  
  TL-41771 Fixed an error when the report block was added at the top of the page  
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
  TL-42435 Moving activities between course sections is now done in a database transaction to avoid broken sequences if something goes wrong  
  TL-42497 Updated the border colour of the active pagination button to match its background colour through the use of a variable  
  TL-42581 Removed tab index wrapping content in a YUI modal  
  TL-42584 Improved screen reader text of icons when managing courses, programs and certifications  
  TL-42590 Improved accessibility when filtering seminar sessions  
  TL-42603 Fixed error when exporting report with tenant filter  
  TL-42694 Fixed being able to add members to a workspace even if an exception was thrown  
  TL-42709 Fixed error message while searching for users in comments with mixed context  
  TL-42800 Fixed application dashboard table column values overlap  
  
    Fixed the ‘applicant’ column values overlapping with the ‘submitted on’  
    column in workflow application dashboard table.  
  
  TL-42802 Removed double borders from competency scale action buttons  
  TL-42810 Fixed toggle switch active disabled state colour  
  TL-42828 Fixed incorrect aria attribute on competency assignment list rows  
  TL-42887 Fixed approval workflow application header action buttons height  
  TL-42898 Removed empty link 'more help' from help icon popover  
  TL-42934 Fixed the reset button on Seminars 'Upcoming Events' filter not being translatable  
  TL-42981 Fixed the formatting of seminar descriptions created using Weka when included in iCalendar attachments  
  TL-43008 Fixed a situation where duplicates could be shown when viewing another user's Library  
  TL-43357 Fixed string encoding for course activity completion report when export Excel-compatible option used  
  TL-43481 Fixed Tui build error when --vendor parameter passed  
  TL-43508 Fixed catalog URL incorrectly parsed when using multiple filters/ordering  
  TL-43589 Fixed missing entries in thirdpartylibs.xml  
  
  
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
  
  
  
Tui front end framework  
-----------------------  
  
  TL-43181 Fixed race condition in useParamState  
  
    Fixed a race condition that could occur when calling .push() or .replace()  
    followed by immediately updating .value, which would cause the second update to  
    not be reflected in the URL.  
  
  
  
Contributions  
-------------  
  
  * Petter Fogelqvist at Aleido - TL-43481  
  
  
  
  
  
Release 19.0 (23rd January 2025):  
=================================  
  
Important  
---------  
  
  TL-37359 The Basis and Roots themes have now been removed  
  
    Deprecated since Totara 14, these themes have now been removed from the  
    codebase. If you are using them directly, your site will automatically be  
    switched to the Inspire theme. If you are using a theme based off Roots or  
    Basis, the theme will need to be updated to be based off 'inspire' (recommended),  
    'legacy', or 'ventura' instead.  
  
  TL-39317 Vue has been upgraded to version 3  
  
    This is a major change; most Vue components will need to be updated. Vue 3  
    brings new requirements, such as all components having an {{emits}} field, and  
    new ways of doing things.  
  
    We recommend reviewing the migration guides in the Totara developer  
    documentation, along with the official Vue documentation, and  
    client/component/tui/src/upgrade.txt.  
  
    * https://totara.atlassian.net/wiki/spaces/DEV/pages/287572322/Totara+19+Vue+3+migration+guide  
    * https://totara.atlassian.net/wiki/spaces/DEV/pages/121187767/Developing+for+the+AJAX+GraphQL+API  
    * https://v3-migration.vuejs.org/breaking-changes/  
  
    Changes to component internals as a result of the migration are not listed in  
    upgrade.txt, as this impacts nearly every component. If you are overriding any  
    components in your theme, we recommend reviewing the component after the  
    migration.  
  
  TL-40011 Updated library scssphp/scssphp to version 1.12.1  
  
    This library update introduces a backwards-incompatible change to SASS.  
    Expressions applied to custom CSS variables will no longer work unless they are  
    escaped.  
  
    Existing behaviour (will no longer work):  
  
    --char-length-scale: $tui-char-length-scale;  
  
    --my-var: tui-char-length(20);  
  
    Must be changed to:  
  
    --char-length-scale: #{$tui-char-length-scale};  
  
    --my-var: #{tui-char-length(20)};  
  
    If the expressions are not properly wrapped they will be skipped and remain as  
    is in the resulting CSS. This comes from upgrading from scssphp 1.0.5 to 1.12.1.  
  
    For more information see  
    https://sass-lang.com/documentation/breaking-changes/css-vars/  
  
  TL-40021 Updated minimum PHP and database versions required for Totara  
  
    Totara requires the minimum of the following to run:  
  
    * PHP 8.1  
    * PostgreSQL 13  
    * MariaDB 10.5.4  
    * MySQL 8.0.1  
    * Microsoft SQL Server 15.0  
  
  TL-40693 Added Inspire theme to encapsulate select opt-in UX improvements  
  
    The Inspire theme renders the main menu in a new side-positioned tui component,  
    offering a more modern, task-based layout. This also offers more flexibility  
    with adding and removing top-level menu items, and how the logo is displayed.  
    The new menu surfaces essential learner tasks and provides a similar experience  
    across desktop, tablet and smartphone devices.  
  
    The new theme also changes default settings for block and dock defaults, to  
    simplify parts of the UI by removing dockable and collapsible controls. Finally,  
    new theme settings, default images and UI colour changes are also included in  
    this theme.  
  
    The Inspire theme is now the default theme for new Totara installations, but  
    does not overtake the currently set site theme on upgrade.  
  
    Site’s upgrading to Totara 19 can choose to switch to the new Inspire theme or  
    remain with their current theme.  
  
  TL-41040 The root font size has been updated to align better with the web at large  
  
    1rem is now equal to 16px instead of 10px. There is a SCSS function 'rem-px'  
    available to easily convert from pixels to rem.  
  
    Before: 6.4rem  
    After: 4rem or rem-px(64)  
  
New features  
------------  
  
  TL-33266 Added a diagnostic plugin to assist with troubleshooting  
  
    A new plugin has been created to generate diagnostic information to assist with  
    getting support from Totara. When triggered by an admin, it collects  
    non-sensitive data about the Totara installation and allows it to be downloaded  
    as a zip file. The tool is accessible via the command line (CLI) or through the  
    ‘Diagnostic for support’ option under ‘System information’.  
  
    Admins can export the diagnostic data and attach it to support tickets if  
    requested by Totara Support. This information will help investigate reported  
    issues more efficiently.  
  
    For further details, please refer to the inline help within the tool and the  
    documentation on Totara Help.  
  
    Also available in 18.9 and later releases.  
  
  TL-38473 Added comments functionality to goals allowing discussions to occur on individual goals  
  
    Users and their managers can now add comments to their Totara goals, allowing  
    them to discuss the user's progress towards their goals. The Totara goal landing  
    page cards now also show the current comment count for each goal. As part of  
    this the existing total comment count used in side panels was updated to include  
    the number of comment replies in the total. Previously this count only reflected  
    top-level comments. Additionally notifications have been added for when a user  
    adds/modifies/replies to a comment in a Totara goal.  
  
    Two new notifications are available:  
  
    * Notifications to the _subject_ of the personal goal  
    * Notifications to any _other_ person who has previously created, modified, or  
      replied to a Totara goal comment  
  
    These notifications also have placeholders for:  
  
    * The comment details  
    * Goal name  
    * Goal subject details  
    * Goal commenter details  
  
  TL-38633 Added a filter bar and sorting option on the Totara goal landing page  
  
    The goal landing page filter bar allows for goals to be searched by name,  
    filtered by their current status and sorted by creation date, due date and  
    completion.  
  
  TL-39118 Introduced the AI integration framework and suggest course tags interaction  
  
    The new AI integration subsystem provides a structured approach to incorporate  
    AI tools and features into Totara via AI plugins.  
  
    * Developers can now define and introduce AI plugins with a clear list of  
      supported features  
    * New interactions can be added from any Totara component through these AI  
      plugins  
    * Added functionality to manage AI plugins and designate one as the default  
    * Defined a generative prompt feature that can be implemented by AI plugins  
  
    Also, we’ve introduced a ‘suggest course tags’ interaction which  
    demonstrates how to create an interaction.  
  
    Also available in 18.8 and later releases.  
  
  TL-39122 Introduced the ability to add courses to workspace libraries and playlists  
    Also available in 18.2 and later releases.  
  
  TL-39384 Added a task list to Totara goals allowing tasks to be created, managed and completed, and reporting on goal tasks  
  
    Totara goal tasks are a new feature that allow users to assign individual steps  
    to their goal. A goal task can have a 'resource' like a course assigned to it,  
    and can be marked as completed. There are five new API endpoints with for  
    'goal_task' operations. Events are now logged in the system log in regards to  
    actions involving goal tasks. A bug has been fixed in the exporting and purging  
    of user data in regards to Totara goals and goal tasks. Also, there is also a  
    new Totara Goal Tasks & Courses report source available.  
  
  TL-39837 Added Totara goals transition mode, allowing legacy personal and company goals to still be accessible  
  
    With Totara goals transition mode enabled, any existing legacy goals will still  
    be accessible, but users will not be able to create or edit legacy personal  
    goals. Legacy goal plugin strings have also been updated to easily identify the  
    different goal plugin types.  
  
    Legacy company goals remain editable in transition mode.  
  
  TL-39971 Added course self-enrolment approval using approval workflows  
  
    When approval workflows are enabled, and a suitable workflow exists, course  
    creators can now require manager and/or administrative approval on  
    self-enrolment instances. A learner requests approval from the enrolment options  
    screen, and submits an application with their reason for requesting access to  
    the course. Once the application is approved, the learner is automatically  
    enrolled on the course.  
  
    A new approvalform plugin has been created to manage enrolment requests and  
    related notifications. Support for enrolment approval can be added by a  
    developer to any enrolment plugin, including bulk-enrolment plugins.  
  
    When they create a request, the learner is technically enrolled on the course  
    with a ‘pending’ status; they cannot access the course or activities until  
    their request is approved.  
  
  TL-40710 Added tool_usagedata (Product Usage Analytics) to Totara  
  
    Product Usage Anlytics (usagedata) is an analytics tool used by Totara to  
    collect and query usage data of Totara instances to be used to improve the  
    product. Collected data will never include personally identifiable information  
    (PII) or sensitive data. All data is anonymous other than:  
  
    * The site identifier: Used to allow us to identify subsequent usage statistics  
      sent from this site.  
    * The site’s URL: Used to allow us to match the site's usage data to a  
      subscription. This is only used should we identify an issue with your site and  
      need to contact you.  
  
    For the full list of data collected by Product Usage Analytics, see the overview  
    page (Quick-access menu > System information > Collect usage data).  
  
    Also available in 18.11 and later releases.  
  
  TL-40729 Added additional catalogue display type and blocks-capable entry page  
  
    The new Explore option for the Catalogue default view (cataloguetype) has been  
    added. Explore is now the default catalogue type for new installations, while  
    existing sites will retain their current setting upon upgrade.  
  
    The entry point page for the new Explore catalogue is a blocks-capable page  
    containing a seperate Tui-built filter and search UI and resulting data display,  
    and one default block, the newly released Catalogue block.  
  
    The new Explore catalogue has two new APIs; Catalogue API and Learning Items  
    API.  
  
    Use of the filter and search UI interacts with the URL, to provide a  
    backwards-compatible UX for existing catalogue types, so that switching between  
    catalogue types can continue to make use of existing URL query parameters.  
  
    Existing out-of-the-box catalogue admin settings and UI remain in place, the new  
    UI provides a cleaner, more intuitive experience for users searching for and  
    consuming content.  
  
  TL-40745 Added learning card component  
  TL-41076 Added a new report source and embedded reports for users assigned roles in workflow override assignments  
  TL-41210 Added new catalogue block  
  
    A new block has been created that injects a Tui-built UX with GraphQL. This  
    block exposes catalogue results based on a supplied URL, where the URL is the  
    same URL generated when manipulating the Explore catalogue search and filter  
    controls. The block limits the number of cards displayed in the CardScroller  
    component, based on an admin configuration for the block, and also provides the  
    user a heading link to navigate to the catalogue (any catalogue type that  
    supports filtering) displaying all relevant filtered results via the URL.  
  
  TL-41258 Added a new report source and embedded reports for approvers assigned to workflows  
  TL-41700 Added the ability to create, update, delete and read organisations and positions to the external API  
  
    The following queries and mutations have been added along with the capability  
    checks. New installs of Totara will automatically include these new capabilities  
    on the {{API User}} role. However, existing Totara sites will need to manually  
    update the {{API User}} role to include the following capabilities to make use  
    of the new queries and mutations:  
  
    *Queries:*  
  
    * totara_hierarchy_organisation - totara/hierarchy:vieworganisation  
    * totara_hierarchy_organisations - totara/hierarchy:vieworganisation  
    * totara_hierarchy_position - totara/hierarchy:viewposition  
    * totara_hierarchy_positions - totara/hierarchy:viewposition  
  
    *Mutations:*  
  
    * totara_hierarchy_create_organisation - totara/hierarchy:createorganisation  
    * totara_hierarchy_delete_organisation - totara/hierarchy:deleteorganisation  
    * totara_hierarchy_update_organisation - totara/hierarchy:updateorganisation  
    * totara_hierarchy_create_position - totara/hierarchy:createposition  
    * totara_hierarchy_delete_position - totara/hierarchy:deleteposition  
    * totara_hierarchy_update_position - totara/hierarchy:updateposition  
  
  TL-41898 Added new internal and mobile queries for a user's completed learning  
  
    Added new graphql queries ‘my_completed_learning’ and  
    ‘mobile_completedlearning_my_items’ to retrieve the current user's completed  
    learning items.  
  
  TL-41996 Included tagging in workspaces and added them into the catalogue  
  
    The Grid catalogue can now be configured to include workspaces. Workspaces can  
    be filtered by tag, and the full text search searches the content and title of  
    the workspace.  
  
    As a result of this work, tags can now be added to workspaces.  
  
Security issues  
---------------  
  
  TL-38660 Tightened the revision range that can be used when serving CSS or JavaScript to limit cache poisoning (CVE-2023-5548)  
  
    The valid range of revisions used to serve cached CSS or JavaScript files is  
    limited between the time of the last release and 60 seconds greater than the  
    server time. If a revision is used that is outside of this range then the  
    content is served directly with no caching headers.  
  
  TL-42120 Fixed the CSRF token not validating when switching to edit mode on user dashboards  
  
Visual improvements  
-------------------  
  
  TL-36728 Improved display of the 'Add restriction' modal  
  TL-41202 Removed border from Engage card time indicator  
  TL-41499 Information about the application and workflow in approval workflows has shifted into a popover  
  TL-41859 Added border-radius and streamlined padding on editing mode block regions  
  TL-42045 Updated default Totara colour palette from greens to blues  
  TL-42786 It is now easier to see which icon is selected in the Totara icon picker  
  
Improvements  
------------  
  
  TL-11232 Added decimal and integer custom field types, for collecting numerical values  
  
    'Minimum', 'Maximum' and 'Step' attributes can be used to define what values  
    pass validation.  
  
    Also available in 18.2 and later releases.  
  
  TL-38097 Improved the formatting of the seminar room notification placeholder  
    Also available in 18.9 and later releases.  
  
  TL-38155 Introduced a new CLI script that allows admins finer control of ad hoc tasks  
    Also available in 18.1 and later releases.  
  
  TL-38157 Changed card image ratio to 16:9  
  
    Areas affected:  
  
    * Catalogue page  
    * Resources, surveys and playlists  
    * Workspace library  
    * Recommenders (resource related tab)  
  
  TL-38203 Added notification configuration in tenants and categories  
  
    Notifications can now be configured in tenants and categories. Notifications in  
    each context are inherited from their ancestor contexts, so changes made to  
    notifications at the site level will affect notifications in tenants, and can be  
    overridden in each tenant.  
  
    Also available in 18.2 and later releases.  
  
  TL-38579 Added hook to alter reports before they are processed  
  
    We added a new hook 'totara_reportbuilder\hook\scheduled_report_pre_process'  
    which can be used to alter reports before they are processed.  
  
    Also available in 18.3 and later releases.  
  
  TL-38615 Added a landing page for managers to view their team members' goals  
  
    Managers can now view their team members' Totara goals by going to Develop >  
    Team, then clicking the 'Goals' link under a team member's name. This page is  
    identical to what the team member sees, and the manager can add/edit/delete  
    goals for that team member as well.  
  
    The manager goal landing page is also accessible from the team member's  
    performance overview page (Develop > Team > Performance overview). If the  
    manager clicks on the goals section title, they will see all of the goals for  
    that team member. Clicking on one of the displayed goals in the various overview  
    progress sections will take the manager to that specific goal for the team  
    member.  
  
    Also available in 18.3 and later releases.  
  
  TL-38656 Added switch for performance-based category management pages  
  
    The existing course, program, and certification management pages, start to run  
    into performance issues if there are a large number of categories. We have added  
    a new config $CFG->simplified_category_management which when enabled will switch  
    those management pages to performance-based versions of category management.  
    These pages are paginated, flattened category trees that drill down instead of  
    expanding. The existing management pages offer more ease of functionality, so it  
    is only recommended to try out this setting you are running into performance  
    issues on the current pages.  
  
  TL-38856 Replaced the filter bar on the performance activity page bringing in a new UI with a more configurable set of filters  
  
    The search filter is now the only filter immediately displayed, all other  
    filters were moved to a popup. The ‘Your progress’ filter was changed from a  
    single select to a multi-select. The overdue activities toggle and the exclude  
    completed toggle were both removed. There was also the addition of a new  
    multi-select filter ‘Activity status’.  
  
    Also available in 18.3 and later releases.  
  
  TL-38861 Improved display of modal when dropping a file into a course  
    Also available in 18.1 and later releases.  
  
  TL-38863 Changed perform goal overview API messages to be clearer  
    Also available in 18.2 and later releases.  
  
  TL-38874 Improved styling of icons when using the legacy program content interface  
    Also available in 18.1 and later releases.  
  
  TL-38943 Improved button spacing for Tenant Domain Managers on program/certification management page  
    Also available in 18.1 and later releases.  
  
  TL-39097 Removed upgrade step that fixed very old calendar entries for seminars  
  
    [TL-36545], released in earlier versions of Totara (16.16, 17.10, 18.0),  
    included an upgrade step that fixed seminar calendar entries from bug [TL-21346]  
    in Totara 12.10 and below. The database query which found the incorrect calendar  
    entries was not performant on sites with many thousands of seminar events,  
    resulting in very long upgrade times. The calendar entries themselves are likely  
    several years old at this point, so the step has been removed.  
  
    Also available in 18.2 and later releases.  
  
  TL-39098 Allow usage of tags in user-generated content  
  
    The topics functionality (formerly part of Totara Engage) has now been merged  
    with tags. Admins can manage a tag collection, which groups resources,  
    playlists, and surveys into a single tag area, they can also combine that tag  
    area with their default collection so that learners can search for tags from the  
    one default collection. Note: resources, surveys, playlists and surveys will  
    always share the same tag collection and cannot be independently configured.  
  
  TL-39120 PDF attachments in Weka will now open in a new tab instead of downloading  
    Also available in 18.1 and later releases.  
  
  TL-39187 Added URL in user-generated resource-related events  
  
    Previously, it was not possible for Site Administrators to know when a resource  
    had been created so that it could be reviewed.  
  
    The event monitoring tool uses the value returned from an event's get_url()  
    method to populate the ‘link’ placeholder. Usually, most event classes have  
    been written to return a valid URL but it is not compulsory practice to do so -  
    this is the case with the resource-related events.  
  
    This ticket corrects this; now all the resource-related events (except remove  
    article) provide a URL.  
  
    Also available in 18.7 and later releases.  
  
  TL-39195 Added the ability to schedule 'program assigned' messages after the assignment  
  
    With the updated UI separating program assignments and the setting of the due  
    date for the assignment, it was found that sometimes the instant ‘assigned  
    notification’ would send before the due date was entered. This would lead to  
    notifications that used the due date replacement variable saying 'No due date  
    set'. These can now be scheduled to go out the next day, giving the admin plenty  
    of time to set the due date.  
  
    Also available in 18.3 and later releases.  
  
  TL-39222 'Allow PDF embedding' setting has been removed from the file module as it is now redundant  
    Also available in 18.2 and later releases.  
  
  TL-39232 Added a multi-select filter to competency reports  
  
    The new filter allows reports to be based on multiple competencies, selected by  
    name. It is now the default filter for new competency reports.  
  
    Also available in 18.2 and later releases.  
  
  TL-39442 Automatically updated certification assignment due dates with fixed expiry setting of the certification  
  
    Normally, if a user is given a due date which is insufficient for them to  
    complete the certification before the due date is reached, such as if the future  
    due date is closer than the course set minimum time required, or if the due date  
    is in the past, then a time allowance exception is generated and an  
    administrator must resolve it manually. Now, in certifications which are  
    configured to use a fixed expiry date when calculating a user’s expiry date,  
    in assignments where the due date is set to a fixed completion date, if a  
    user’s due date would cause a time allowance exception then additional active  
    periods will be added to the due date for that learner until the time allowance  
    exception would be prevented.  
  
  TL-39517 Added user identifier to auth_ssosaml authentication error message  
  
    Given validation or authentication errors occur, and when we have a user  
    identifier to use, then we add the user identifier to the error message,  
    outputted to the user interface. This gives greater clarity over what’s  
    happening in the authentication flow.  
  
  TL-39698 Added generic authentication error for API access exceptions when global totara_api debug is set to NONE  
  
    Introduces a generic ‘Authentication error’ message for API access  
    exceptions when global Totara GraphQL API debug configuration is set to NONE  
    (generic messages only).  
  
    Previously authentication errors would return a generic ‘Internal server  
    error’ message. This change enhances integration with the Totara GraphQL API  
    by offering a consistent error message for detection of authentication issues.  
  
    This will facilitate smoother interaction with the Totara GraphQL API. For  
    example, enabling easier refresh token generation by giving a consistent message  
    format to detect.  
  
    The error returned is formatted as:  
    ```  
    {  
    "message": "Authentication error",  
    "extensions": {  
    "category": "api_access"  
    }  
    }  
    ```  
  
  TL-39708 Improvements to certifications and programs  
  
    Improved the learner experience in certifications and programs by uplifting the  
    UI for the learner view in programs and certifications, including a new header,  
    improved course cards, and a new Tui modal for requesting an extension.  
  
    Implemented certification cloning (consistent with program cloning). The details  
    checkbox will clone the certification tab details.  
  
    Implemented data usage metrics for programs and certifications.  
  
  TL-39743 Allowed email-based self-registration notification to include the first name, surname and username placeholders  
    Also available in 18.10 and later releases.  
  
  TL-39844 Added the Approvers recipient to seminar booking notification triggers  
  
    Seminar booking notifications can now be configured to be sent to the  
    appropriate approvers.  
  
    Also available in 18.9 and later releases.  
  
  TL-39846 Improved block skip link behaviour  
  
    Block skip links will now take you to the skip link for the next block, instead  
    of an empty element.  
  
    Also available in 18.5 and later releases.  
  
  TL-39857 Approval notification triggers are now only available within the workflow stage types in which they are able to be triggered  
    Also available in 18.7 and later releases.  
  
  TL-40067 Improved notification status checkbox aria label  
    Also available in 18.5 and later releases.  
  
  TL-40104 Introduced accessibility standard testing steps in Behat  
  
    This adds the ability for developers to test if pages meet specified  
    accessibility standards with Behat end-to-end testing. We have also introduced  
    the {{behat_base::has_tag}} method that can be used to check if a scenario has  
    the specified tag.  
  
  TL-40324 Updated the performance activity management page  
  
    Simplified the UI above the tabs, replacing the 'activate' card with a clearer  
    button and brought in the description for extra context.  
  
  TL-40347 The performance activity closure setting has been redesigned and moved to the 'closure and visibility' tab  
  
    The closure setting has been redesigned, making a clear distinction between  
    ‘section’ and ‘activity’ closure to help accommodate future improvements  
    we intend to make with this setting.  
  
  TL-40350 Updated default security settings to align with best practices.  
  
    The ‘Account lockout threshold’ now defaults to five attempts, while both  
    the ‘Account lockout observation window’ and ‘Account lockout duration’  
    now default to one hour.  
  
    These changes do not affect existing environments, but we strongly recommend  
    reviewing and updating these settings on current sites to enhance security.  
  
    Also available in 18.9 and later releases.  
  
  TL-40354 Simplified and modernised data processing for the assignments tab within performance activity administration  
  TL-40407 Added retrieval, sorting and filtering on timemodified to external API queries for job assignments  
  
    GraphQL query totara_job_job_assignments now includes a filters input field,  
    which includes since_timemodified for filtering job assignments to those  
    modified on or since the given date.  
  
    GraphQL type totara_job_job_assignment now includes field timemodified, which  
    details the time the job assignment was last modified. This field can be sorted  
    via the existing sort query input.  
  
    Also available in 18.7 and later releases.  
  
  TL-40408 Added totara_job_job_assignment query to API  
  
    Added the totara_job_job_assignment GraphQL query to the external queries,  
    allowing HR integrations to query for a single specific job assignment.  
  
    Also available in 18.7 and later releases.  
  
  TL-40428 Added a strike-through for completed Totara goal tasks to improve usability  
  TL-40449 Added the ability for participants to remove linked review content that had been previously selected in a performance activity  
  
    These are the conditions under which a user can remove the content for a linked  
    review question:  
  
    * The logged-in user is the one that selected the content (e.g. competencies,  
      goals) for the linked review question.  
    * The selector is currently editing/responding to the linked review question,  
      i.e. the selector’s participant instance progress is ‘in progress’.  
    * All other participants in that activity in which the linked review question  
      appears are view-only participants or they have not viewed the activity, i.e.  
      their participant instances' progress are ‘not started’.  
    * Additionally for Totara goal, legacy goal and competency linked review  
      questions:  
    ** None of the content has been rated, i.e., there is no rating record in the  
      related rating table.  
    ** If the underlying content (e.g. competency assignment, goal) was deleted from  
      the system, then the question can still be removed even if it had been rated. Of  
      course, the user still has to be the content selector, etc.  
  
    Once the system has determined the logged-in user can remove the linked review  
    question, an X will appear above the linked review question block. If the user  
    then clicks on this link and confirms the removal, then the review question  
    content (including responses to any sub questions) will be deleted. It will seem  
    as if the user never selected the content for review or responded to the  
    question in any way.  
  
  TL-40455 Manage approval workflows link is automatically added to the quick-access menu when the feature is enabled  
  TL-40468 Added a confirmation dialog when logging in and there is an existing active session  
    Also available in 18.7 and later releases.  
  
  TL-40573 Added a setting for section closure in the performance activity administration  
  
    The previous 'Close on completion' setting for performance activities has been  
    split up into two separate settings to enable more fine-grained control over  
    closure behaviour:  
  
    * The 'Section closure' setting controls whether a participant section will  
      close on completion  
    * The 'Activity closure' setting controls whether the activity will close for a  
      participant when all sections are complete  
  
    For activities that had the previous 'Close on completion' setting enabled, both  
    new settings will be enabled on upgrade, retaining existing behaviour.  
  
    Also on the participant view, submit button labels and confirmation messages  
    have been updated to communicate the closure behaviour more clearly. The close  
    button from sections was also removed.  
  
  TL-40595 Improved job assignment selection when enrolment approval is required  
  
    The user will now see appropriate options and information based on how many job  
    assignments they have to choose from.  
  
  TL-40620 Added the ability for users to continue to add review items in a performance activity even after they have already added and confirmed others  
  
    The user can only add items when all participant instances except the selecting  
    participant's instance are not started, and the selecting participant’s  
    section is not closed, or they are view-only participants.  
  
  TL-40661 Updated the existing performance activity visibility setting UI and moved it to a new tab  
  
    Performance activities visibility setting wording has been updated to provide  
    more clarity, it has also been moved to a new 'Visibility and closure' tab to  
    allow it to be grouped with related settings.  
  
    A new activity_controls GraphQL query has also been introduced for the  
    performance activity settings, allowing for settings to fetch their data from a  
    single point making them more self-contained.  
  
  TL-40663 Basic details can now be updated directly on the performance activities manage page  
  
    The ‘general’ tab has now been removed from the manage performance  
    activities page, making the content tab the entry point. The form elements have  
    been moved to modal which can be triggered from a meatball menu at the top of  
    the page.  
  
  TL-40683 Removed 'required field' notation from approval workflow application view  
  TL-40746 Replaced core card component with learning card component  
  
    Replaced Tui grid with a simple CSS grid on the playlist and program pages. And  
    the following components were refactored to use the new learning card component:  
  
    * Courses in programs  
    * Workspaces and their contents  
    * Surveys  
    * Articles  
    * Playlists and their contents  
  
  TL-40962 Added a 'User profile access' setting to Engage feature configuration  
  
    Previously, enabling Engage features implied giving users access to the profile  
    of any other user, restricted only by tenant rules.  
  
    The new ‘User profile access’ setting makes this behaviour configurable.  
    When this setting is disabled, a site with Engage features will not allow  
    general profile access anymore. Normal access rules will apply instead. When  
    enabled, behaviour is the same as before.  
    On upgrade, the setting will be enabled to preserve existing behaviour.  
  
    On new installations, the setting will be disabled.  
  
    As part of this work, links to user profiles in Engage features have been made  
    conditional according to profile access rules.  
  
  TL-40964 Workspaces can now have multiple owners  
  
    As was the case previously, the person creating a workspace is taken as the  
    owner of the workspace. However, that person can now make any number of  
    workspace members into owners of the workspace. This allows the initial owner to  
    share workspace management responsibilities among multiple people, e.g. when  
    going on extended leave.  
  
    The additional owners have the same rights as the initial owner and they  
    themselves can add other owners as needed.  
  
    Owners can also remove owners (including the original owner and/or themselves)  
    provided there is at least one remaining owner in the workspace after the  
    removal process. Removed owners then become ordinary workspace members.  
  
    As part of this feature, the ‘transfer ownership’ option has been removed;  
    all related methods/constructs have been deprecated.  
  
    The workspace engagement report has been enhanced as well to include a new  
    ‘owners’ column.  
  
  TL-41031 Added new filter type and extended search input filter when managing approval workflows  
  
    Added assignment type filter dropdown and extended the search query when making  
    approver override changes in approval workflows.  
  
  TL-41072 Added additional metadata on the approval workflow application page  
  
    Assignment type, name and workflow name have been added to the application page  
    header section of approval workflows.  
  
  TL-41145 Clarified wording of new Inspire theme navigation colour settings  
  
    Due to the primary navigation now being rendered outside of the page area that  
    used to be referred to as the ‘header’, the colour setting wording has been  
    updated to reflect this separation.  
  
  TL-41156 Added an active state icon for bookmarks to enhance usability in the resource, playlist and workspace cards  
  
    Also changed the CSS colour on hover, and default state to improve visual  
    design.  
  
  TL-41176 Updated display of user-generated resources  
  
    Improved the slide-out side panel component for user-generated resources in Your  
    library. The component design has been improved to better align with both  
    Inspire and Ventura themes.  
  
  TL-41234 Improved product usage analytics by extending exceptions into a product usage-specific one  
  TL-41239 Updated single-section performance activities to have the same UI as multi-section activities  
  
    The single-section activities now also have the side panel and the section name  
    at the top, bringing in a more consistent UI. The section name is now  
    ‘Section’ by default.  
  
  TL-41257 Added a new userdata type to purge and export workflow approvers  
  TL-41442 Moved import CSV button in approval workflows  
  
    The import button for approval workflows used to appear in the meatballs menu,  
    however with this change and the addition of new reports, the import button now  
    appears on the relevant approval workflow reports.  
  
  TL-41550 Prevented casual use of calculated question types by requiring an additional capability  
  
    This patch introduces a new {{moodle/question:managecalculated}} capability as  
    an add on to the fix in [TL-41526], to mitigate the risk of executing  
    user-generated PHP code. Only users with this capability will be allowed to  
    create these types of questions:  
  
    * Calculated  
    * Calculated multichoice  
    * Calculated (simple)  
  
    For existing sites, this capability is given to any users who have the  
    {{moodle/question:add}} capability. It is recommended that admins review whether  
    users actually need to use calculated question types, and remove this capability  
    from any roles that do not. No special capability is required to use existing  
    calculated questions in a quiz.  
  
    For new sites, this capability is not assigned to any roles by default.  
  
    Also available in 18.10 and later releases.  
  
  TL-41680 Added a CSS class to user-generated content when rendered on the page  
  TL-41775 Improved tab order when editing a course  
    Also available in 18.12 and later releases.  
  
  TL-41788 Removed side padding from block titles in chromeless mode  
  
    When in chromeless mode, with block borders turned off, when block headers are  
    still turned on, they will no longer have left/right padding indentation.  
  
  TL-41880 Improved the efficiency of the approval workflows role map by limiting it to relevant contexts only  
  
    Additionally improved efficiency of course visibility and approval workflows  
    capability maps by removing incremental ID field.  
  
    Also available in 18.13 and later releases.  
  
  TL-41894 Adjusted paragraph spacing to be 16px (1rem)  
  TL-41896 Fixed deprecation warning for PHP 8.2 support in calculatedmulti question test helper class  
  
    Previously, the server/question/type/calculatedmulti/tests/helper.php class  
    added dynamic properties to a test calculatedmulti question. However, this  
    resulted in a deprecation warning in PHP 8.2; it could even become an error in  
    later PHP versions.  
  
    This patch removes the dynamic property addition.  
  
  TL-41928 Improved the UI of top-level questions on a performance activity to clearly separate questions  
  TL-41954 Added a 'merge all sections' option to first section in performance activity admin UI  
  TL-41968 Aligned the single and multiple section UI on performance activity content admin tab  
  TL-42064 Removed the multiple sections toggle setting from performance activities admin UI  
  
    Multiple section value is now handled by adding more than one section or  
    removing all but one section.  
  
  TL-42083 Course completion records not created until pending user enrolment is approved  
  
    If approval is required to enrol in a course, the course completion record will  
    not be created until the user's enrolment has been approved. This means that a  
    not-yet-approved course will not show in the Record of Learning.  
  
  TL-42108 Hide enrol button on admin pages in pathway format  
  TL-42276 Added capability to delete workflow in any state  
  TL-42277 Changed pathway enrolment button text for pending interactive enrolment  
  TL-42312 Improved accessibility of course page and dashboard headings  
  
    The top-level heading (h1) on course pages and dashboards is now the name of the  
    course/dashboard. These are visible by default on new installs and hidden when  
    upgrading from a previous version, but can be shown via a setting in course  
    settings/dashboard settings.  
  
    When headings are visible, the title of the navigation block defaults to 'Course  
    navigation' instead of the course title.  
  
    Further changes are likely to occur in the near future with page headings to  
    improve information architecture and accessibility across the platform.  
  
  TL-42316 Approval forms can now decide what a default workflow will look like  
  
    A new function approvalform_base::configure_default_workflow has been added  
    which can optionally be overridden to provide a different default workflow when  
    a user creates a new workflow.  
  
  TL-42354 Allow unassigned course enrolment approval workflows to be assigned to the system context  
  TL-42399 Added new event origin 'mobile' for events generated using the Totara mobile API  
  
    In addition, native mobile app authentication will trigger a 'user_loggedin'  
    event.  
  
  TL-42422 Provided additional information in the warning when deleting an approval workflow  
  TL-42426 Added the ability to update Totara custom fields via GraphQL mutations  
  
    Totara custom fields can now be implemented into GraphQL mutations, enabling the  
    modification of custom field data via the {{customfield_resolver_helper}} class.  
    Due to how some custom fields save data, only whitelisted custom fields can be  
    modified - these custom fields are: checkbox, menu, text, datetime, integer and  
    decimal.  
  
  TL-42443 Removed 'No enrolment key required' message when enrolling in a course  
  TL-42508 Adjusted goal task cancel button size for consistency  
  TL-42514 Added usage data analytics for Totara goal status counts  
  TL-42515 Automatically assign course approval enrolment applications to system or tenant context as appropriate  
  TL-42516 Adjusted typography and spacing on performance overview page  
  TL-42517 Added usage data analytics for Totara goal user counts  
  TL-42559 Adjusted performance activity admin typography spacing, sizes and weight to align with global changes  
  TL-42598 Adjusted performance activity typography spacing, sizes and weight to align with global changes  
  TL-42691 Added reset button for filters on the browse menu and removed its all option  
  TL-42713 Changed status and button messages for users after their course enrolment approval application is rejected  
  TL-42744 Addressed heading typography sizes on a performance activity  
  TL-42864 Returned error message when invalid control_keys value gets passed in a perform activity_controls API request  
  TL-42884 Added a message to the course sortorder cron job when the legacy sort order is disable  
  TL-42996 Fixed the namespacing for the orgs and positions external API  
  
    A change was made in TL-41700 which added organisations and positions. However,  
    as organisations and positions are sub-plugins of hierarchies, it makes sense  
    that these should sit within their respective areas. We've made this move with  
    this change and reverted the previous organisations and positions internal  
    endpoints back to their respective states.  
  
Accessibility improvements  
--------------------------  
  
  TL-42296 Removed incorrect aria attribute on modal when creating a new approval workflow  
  
Bug fixes  
---------  
  
  TL-30989 Ensured that Tenant User Manager and Tenant Domain Manager roles exist when a site is installed with multitenancy enabled in config.php  
  
    Enabling or disabling multitenancy will also now purge caches, so that the  
    quick-access menu and other platform features are correct immediately after the  
    change.  
  
  TL-32440 Fixed a bug in approval workflows where workflow managers could not edit an application when it was in a waiting stage type  
  TL-37156 Fixed missing declared properties to prepare for PHP 8.2 support  
  TL-37536 Fixed deprecated '${' string interpolation for PHP 8.2 support  
  TL-38012 Addressed an issue where closed performance activities were still showing as overdue  
  TL-39363 Fixed undeclared dynamic properties in Totara for PHP 8.2 support  
  TL-39364 Fixed dynamic property declaration for PHP 8.2 in totara_plan component  
  TL-39365 Fixed PHP 8.2 & 8.3 compatibility issues  
  
    Fixed Horde framework PHP 8.2 & 8.3 compatibility issues.  
  
  TL-39626 Fixed incorrect callable references for PHP 8.2 support  
  TL-39627 Fixed calls to the deprecated get_class() PHP function to be compatible with PHP 8.3  
  TL-39850 Moved the help icon in search filters to outside of the legend element for improved accessibility  
  TL-39902 Fixed incorrect roles appearing as options on the Permissions > User Policies page  
  
    The predefined Workspace Owner, Workspace Creator, API User, Workflow Manager,  
    Workflow Approver and IntelliBoard API User roles were not categorised correctly  
    on the User Policies page, meaning they were available to be mapped in  
    situations where it did not make sense (such as setting Workspace Creator as a  
    role for visitors).  
  
  TL-40712 Prevent graphql-eslint from picking up IDE GraphQL config  
  TL-40801 Escaped the SCSS variables used for chart colours  
  TL-40887 Switched approval workflow organisation and position assignments idnumber value from shortname to idnumber  
  
    Previously organisations and position assignments in approval workflows would be  
    linked using the shortname. However it is possible for a site to have multiple  
    organisations or positions with the same shortname which can cause problems.  
    With this change the idnumber field is now used to create this association.  
  
  TL-41047 Fixed the progress tracker and side-panel navigation button text alignment in the pathway course format  
  TL-41073 Fixed tags removal and input focus upon selection in TagList component  
  
    After removing a tag it would longer show up in the tags search, so could not be  
    re-added. Also tags were being removed on the backspace key event when search  
    query characters were present. Upon tag item selection, focus is now moved to  
    the input field for a better user experience.  
  
  TL-41151 Fixed inconsistent layout margins at different breakpoints on a workspace page  
  TL-41330 Fixed an inconsistency for enrolment checks in the user access controller  
  
    Previously, enrolment checks in the user access controller were only limited to  
    course containers when used without a context. There was no such limitation when  
    used in a course context, leading to unexpected behaviour in some edge cases  
    involving workspaces.  
  
    With this change, the enrolments checks are consistently limited to course  
    containers.  
  
  TL-41427 Removed JSON formatting from catalogue search content  
  
    Before this patch, formatting terms such as ‘paragraph’ and ‘bold’ were  
    not being filtered out. Now, when a user searches for one of those terms, items  
    will only be found if their content intentionally contains those terms. The  
    catalogue content will be updated the first time the ‘Refresh the catalog  
    data’ scheduled task runs after upgrade.  
  
  TL-41485 Fixed the null error on DOM object for Weka editor  
  
    The error occurred when trying to make a change on an amended notification  
    subject/body by the parent.  
  
  TL-41632 Ensured padding is included for goals on the goal landing page for production builds  
  TL-41736 Fixed unit tests being unable to run in process isolation mode due to invalid dataproviders  
  
    Unit test dataproviders are serialized when called in isolation, meaning any  
    dataprovider payload must also be serializable by PHP. Assertions, closures and  
    PHP resources cannot be used inside dataproviders.  
  
  TL-41739 Fixed issue where 'Your workspaces' page would not render when workspaces have long names  
  TL-41780 Prevented progressTrackerNav tracking dots from using a higher z-index than page layout elements  
  
    This change should make z-index usage of this component more reliable, as the  
    z-index it is using is not needed, but when it is applied, it can cause stacking  
    order issues.  
  
  TL-41862 Updated schema version on manifest file for MSteam resubmission  
  TL-42115 Fixed Weka editor link checkbox option on save  
  
    This will now save the state of the Weka editor link option for ‘Open in new  
    window’ on save.  
  
  TL-42419 Fixed a PHP warning message showing when a column heading is not provided for a column option in reportbuilder  
  TL-42429 Fixed a bug on the tagList component where entering backspace will not set focus to the input field  
  TL-42541 Fixed a bug where a prop in the TreeViewNode component had the wrong type  
  TL-42555 Fixed unit test on program due date calculation  
  TL-42567 Fixed requesting enrolment approval after deleting previous application  
  TL-42619 Added tabindex to the close button in popover  
  TL-42649 Fixed a rare issue where the recently viewed block JS throws an exception  
  TL-42654 Fixed horizontal scrollbar on legacy goals framework page  
  TL-42675 Fixed potential accessibility issue with the Popover component by allowing it to be closed with the 'Escape' key  
  TL-42707 Fixed problem where workflow approver wouldn't see new applications until logging out and back in  
  TL-42724 Fixed missing page title when adding a new hierarchy  scale  
  TL-42729 Prevented sorting by 2 fields in the perform user_goals API query to avoid errors when in combination with a cursor  
  TL-42793 Fixed error with combining provider filter with other filters such as learning type filter in catalog page  
  TL-42805 Fixed tenant domain manager accessing organisation and positions when creating workflow assignment overrides  
  
    The capabilities `totara/hierarchy:vieworganisationframeworks` and  
    `totara/hierarchy:viewpositionframeworks` are now required to add organisation  
    and position assignment overrides, respectively.  
  
  TL-42809 Prevented the closure_type field in 'override_global_participation_settings' API requests from (initially) accepting a null value  
  TL-42849 Fixed Totara playlist GraphQL type formatting to avoid XSS injection  
  TL-42854 Reduced the number of database queries made when an API request is made to 'perform_goal_get_goal_tasks'  
  TL-42859 Updated title description for participant assignment settings in performance activities  
  TL-42865 Fixed issue where charts on competency profile could escape from their bounds  
  TL-42878 Fixed theme settings default images reflecting current theme instead of the theme being edited  
  TL-42894 Fixed workflow type not displaying description on edit page  
  TL-42931 Prevented managers from responding to a program or certification extension request more than once  
  TL-42932 Fixed tenant domain manager can not create workflow within other tenants  
  TL-42946 Added a condition to avoid rendering empty pre-row  
  TL-42969 Fixed the footer not loading on the delete audience confirmation page  
  TL-42980 Fixed a PHP deprecated warning in the access library functions  
  TL-43001 Added missing page title to the hierarchy custom fields page  
  TL-43036 Ensured each approval form has a unique name so that it links to the correct application  
  TL-43037 Fixed that back button from course enrolment application changes destination  
  TL-43088 Fixed approval override if assignment type is only cohort  
  TL-43145 Fixed instances where optional properties were declared before required properties  
  
Database upgrades  
-----------------  
  
  TL-40857 Added support for MySQL 8.4  
  
    MySQL 8.4 is the new LTS version of MySQL. If upgrading from earlier than 8.0  
    you must first upgrade to 8.0 and then to 8.4, there is no direct 5.7 to 8.4  
    upgrade path available.  
  
    Please see MySQL’s documentation for more information:  
    https://dev.mysql.com/doc/refman/8.4/en/upgrade-paths.html  
  
  TL-40883 Added support for MariaDB 11.4  
  
Technical changes  
-----------------  
  
  TL-40017 Upgraded PHPUnit to version 10.5  
  
    With the upgrade of PHPUnit to 10.5 and ParaTest to 7.3 the default  
    phpunit.xml.dist has updated. If you have customised your own configuration  
    please check the documentation and make the appropriate changes:  
    https://docs.phpunit.de/en/10.5/configuration.html|https://docs.phpunit.de/en/10.5/configuration.html  
  
    See the developer documentation here:  
    [https://totara.atlassian.net/wiki/spaces/DEV/pages/461832193/Unit+testing+in+Totara+19|https://totara.atlassian.net/wiki/spaces/DEV/pages/461832193/Unit+testing+in+Totara+19]  
  
    Some notable changes are:  
  
    * All calls to {{tearDown}} and {{tearDownAfterClass}} must call the parent  
      method.  
    * PHP notices/deprecations/warnings/errors no longer automatically convert into  
      PHPUnit exceptions.  
    * Many functions in the mocking API have been deprecated.  
    * Data providers must be public and static, and must return data as a collection  
      of properties. If array keys are used, these must either be a label describing  
      the payload, or the exact property name. Mismatch between property names and  
      test method signatures will no longer work.  
  
    For the full list of changes that need to be applied to your own unit tests  
    please check out the PHPUnit documentation:  
    [https://docs.phpunit.de/en/10.5/index.html|https://docs.phpunit.de/en/10.5/index.html]  
  
  TL-40353 Libraries that are forked and patched by Totara are now locked to a specific tag, rather than a whole branch  
  TL-40691 Updated Behat to use web driver protocol instead of JSON wire protocol  
  
    To run Behat after this upgrade has been applied, the Behat configuration files  
    will need to be changed from using {{selenium2}} to {{webdriver}}. This can be  
    done either in your config.php or your behat_local.yml file.  
  
  TL-40701 Added support for PHP 8.2 and 8.3  
  TL-41321 Added core formatter for backed enums for GraphQL  
  
    Adds a new field formatter designed to handle backed enums which are now  
    supported in Totara v19+.  
  
  TL-41566 Added totara_customfield as a GraphQL schema type and implemented custom fields into the core_course GraphQL type  
  
    totara_customfield is now an available GraphQL type, representing Totara’s  
    custom fields for the GraphQL schema. The values returned include the definition  
    for the custom fields (including information such as their ID, full name,  
    default value, etc) as well as the value for each item’s custom field  
    information. Values are provided via a union type, as the data is specific for  
    each custom field type - for example, the location custom field has coordinates  
    (longitude and latitude) as well as display properties for Google maps, where as  
    the decimal custom field will include only the value. Raw values are also  
    included to provide a way to get the data as it is stored in the database. For  
    more information on the specific makeup of the Graphql schema, please see our  
    API documentation.  
  
    With this new GraphQL type, custom fields have been implemented into the  
    core_course GraphQL type, allowing for the retrieval of custom field information  
    for courses from the course GraphQL endpoints.  
  
  TL-41640 Added the ability to search for cohorts by name and idnumber using the GraphQL query  
  TL-41641 Added search filter to the organisation and position GraphQL queries  
  TL-41962 Added ability to retrieve execution context made by the PHPunit webapi helper  
  
    Previously, it was not possible to retrieve the execution context made by the  
    totara_webapi PHPunit helper. This meant it was difficult to test behaviors  
    related to the execution context, such as checking that a variable gets set.  
    This change allows the retrieval of the execution context made by the helper.  
  
  TL-41963 Fixed GraphQL union resolution when union is defined in a different schema  
  
    Previously the totara_webapi schema builder would search for the union, but stop  
    searching when it did not find the union in the first schema. This meant that if  
    a GraphQL union was defined in a different schema than the first one checked,  
    the schema builder would never find the union type. This fixes this, allowing  
    the schema builder to search all schemas for unions.  
  
  TL-42085 Fixed bug in GraphQL where default format would not be used when formatting an array of values  
  
    The formatter implementation was designed to allow for a default format to be  
    declared which would be used if the format was not passed. This worked when  
    formatting single scalar values but there was a bug, meaning it would give an  
    ‘Invalid format’ error when formatting an array of values the same way.  
  
  TL-42146 Fixed up perform response line field formatters  
  
    Addressed a couple of bugs with the perform response line field formatters. This  
    update removes the expectation that an array traversal was performed on the  
    formatters, allowing them to work as expected.  
  
  TL-42428 Added the ability to use language string variables in the the help icon component  
  
Tui front end framework  
-----------------------  
  
  TL-21921 Added a new Tui tooltip component  
  TL-24007 Cross-page toast notifications are now possible via notifyParams()  
  
    See the NotificationToast sample for usage.  
  
  TL-37596 The lang-strings block is no longer used  
  
    The <lang-strings> block is now ignored. Instead, calls to the  
    internationalisation system like $str() or getString() are now replaced directly  
    with the value of the language string by the server when they are served to the  
    client.  
  
  TL-40018 Updated npm dependencies, increased minimum Node.js requirement to v20  
  TL-40615 Button component has been updated  
  
    The Tui core button component has been rewritten. The `styleclass` prop has been  
    deprecated, and new props take its place. The CSS class name and internal  
    structure have also changed, if you have custom CSS targeting .tui-formBtn,  
    please review it and update to target .tui-btn instead. This new Button  
    component should be easier to style, with simplified CSS variable usage, and  
    fewer selectors.  
  
  TL-40782 Added CardScroller component to display a row of cards in a paged interface  
  TL-40904 Improved consistency of page padding handling with 'legacynolayout' layout  
  
    Removed 'legacynolayout' from pages that do not need it, and for the remaining  
    pages, moved the padding setting to a 'layout-page-padding' mixin.  
  
  TL-40952 Enabled use of Apollo through Vue 3 composition API  
  TL-41006 Removed hyphenation of text  
  
    tui-wordbreak--hyphens and tui-wordbreak--hard are now replaced with  
    overflow-wrap: break-word  
  
  TL-41041 Introduced intermediate sizing variables  
  
    The CSS variables --gap-base, --font-size-base, --icon-size-base, and  
    --line-height-base can now be set independently of the root rem value.  
  
  TL-41105 Updated 'Filters' button styling in FilterBarAreaPopover  
  
    Enlarged the clickable area and gave the button a rollover state to make it  
    easier to target and click.  
  
  TL-41784 Added SortBar core component  
  TL-41786 Added useParamState Vue composable to manage URL param state  
  TL-41787 Fixed performance issue with Tree component  
  TL-41918 Changed the recommended location of tui.json  
  
    Within a Tui component, 'tui.json' and any files that are not intended to be  
    part of the bundle (including upgrade.txt) should now be placed directly under  
    the component directory.  
  
    * Before: client/component/mod_example/src/tui.json  
    * After: client/component/mod_example/tui.json  
  
    The previous tui.json location will continue to work, however moving it to the  
    new location unlocks a new capability: everything in the src/ directory will be  
    bundled, and the js/ subdirectory is no longer required (but still supported).  
    It becomes a straight mapping from mod_example/xyz to src/xyz.  
  
    We are making this change in order to support easier to understand file  
    structures, especially when working with Vue 3 and the Composition API.  
  
    Theme overrides are now also simplified, as they may be placed under  
    overrides/(original import path) in the theme's src/ directory, for example:  
  
    * client/component/theme_example/src/overrides/tui/components/Button.vue  
  
  TL-41973 Added new core Tui TreeView component  
  
Library updates  
---------------  
  
  TL-34369 Updated symfony/process as used by behat to 6.4.8  
  TL-39990 Updated library ezyang/htmlpurifier to version 4.17.0  
  TL-39991 Updated library phpmailer/phpmailer to version 6.9.1  
  TL-39992 Updated webonyx/graphql-php to version 15.11.1  
  TL-39993 Replaced library box/spout with openspout/openspout version 4.23.0  
  TL-39994 Updated library simplepie/simplepie to version 1.8.0  
  TL-39996 Updated library sabberworm/php-css-parser to version 8.5.1  
  TL-39997 Updated library phpoffice/phpspreadsheet to version 2.0.0  
  TL-39999 Updated library adodb/adodb-php to version 5.22.7  
  TL-40000 Updated library michelf/php-markdown to version 2.0.0  
  TL-40005 Updated library matthiasmullie/minify to version 1.3.73  
  TL-40006 Updated library geoip2/geoip2 to version 3.0.0  
  TL-40008 Updated library goat1000/svggraph to version 3.20.0  
  TL-40009 Updated library setasign/fpdi to version 2.6.0  
  TL-40010 Updated wikimedia/less.php to version 4.2.1  
  TL-40013 Updated library litesaml/lightsaml to version v4.2.0  
  
    This also includes updating the dependencies symfony/http-foundation to 6.4.4,  
    symfony/deprecation-contracts to 3.4.0 and symfony/polyfill-php80 to 1.29.0.  
  
  TL-40016 Upgraded Behat to version 3.14  
  
    This also involves replacing mink-goutte-driver with mink-browserkit-driver.  
  
    If you have customised your test/behat/behat.yml configuration file, the  
    reference to goutte must change to browserkit_http. Refer to  
    test/behat/behat.yml.dist line 20 for the correct setup.  
  
  TL-40019 Server directory npm dependencies were updated, increased minimum Node.js requirement to v20  
  TL-41140 Upgraded optional library aws/aws-sdk-php to version 3.316  
  
    Also locking psr/http-message to the version 1 line to prevent a clash with the  
    required Totara libraries.  
  
Contributions  
-------------  
  
  * Wajdi Bshara at Aleido - TL-41700  
