# H5P code structure
## Overview
The H5P moodle plugin is an adapter to allow moodle/totara to work with the H5P framework. It does this by having the adapter code inside moodle H5P mod. This mod then includes the H5P framework base code via submodules. These submodules are discussed further below, but basically there are 3 submodules (library, editor, reporting). H5P connects these 3 base framework submodules with the platform through interfaces, most notably the `H5PFrameworkInterface`. 

## H5P Activities
H5P allows users with sufficient permissions to download and install h5p activity types (e.g. mark the words) that can be used on courses. These activities can either by downloaded from H5P directly through the editor, or can be uploaded by a user via a .h5p file. Activities cannot be covered here as they is a wide range of activities. H5P created activity types each have their own repo (e.g. mark the words: https://github.com/h5p/h5p-mark-the-words), however they also provide a specification for creating H5P compatible activites for third party developers (https://h5p.org/documentation/developers/h5p-specification). H5P activities themselves are responsible for creating their own upgrade paths, managing bugs which are specific to the activities. This module is only for providing a way for the h5p framework (made up of the 3 submodules) to talk to the underlying LMS - totara in this case.

## H5P Submodules
H5P in terms of integrations can be thought of as a framework. There is the part that deals with the platform which H5P is running on - in this case the code directly in the H5P moodle module, and then there is the generic code which H5P have written to work across the integrations which are included as submodules within the plugin. There are the submodules:
- library - (https://github.com/h5p/h5p-php-library)
- editor - (https://github.com/h5p/h5p-editor-php-library)
- reporting - (https://github.com/h5p/h5p-php-report)
**Note** - Any changes required for code in these submodules should be submitted as a PR back upstream to their repos - these repos are all managed by H5P so it will come at their discreation as to whether or not they are accepted in.

### Library
This submodule provides a number of classes that are used throught the H5P workflow. A lot of the key code is stored in `library/h5p.classes.php`. Some of these classes are:
- `H5PFrameworkInterface` - this defines the functions which the platform specific framework needs to implement - in particular this is what the plugin codes framework.php file implements to map moodle/totara specific workings to work with H5P. The interface defines methods to handle things such as where to save data to, how to download h5p files, how to update libraries, how to check permisions.
- `H5PValidator` - this class handles the logic for validation of H5P libraries being uploaded via the editor of downloaded from H5P. Some of the checks it does are:
	- It checks the user has the ability to update libraries (this is handled by the moodle mod specific code - checks for capability 'mod/hvp:updatelibraries'
	- checks .h5p uploads, it extracs the .h5p file (literally a .zip with .h5p extension) - then compares each file against a regex list of accepted file types (see library/h5p.classes.php::H5PCore::$defaultContentWhitelist & library/h5p.classes.php::H5PCore::$defaultLibraryWhiltelistExtras).
	- ensures the filename matches a specific format which includes the major/minor release version as part of the file name
	- ensures a h5p.json file exists - this defins what the h5p package should / shouldn't have
- `H5PStorage` - This clas is used for saving H5P and delete H5P packages - the h5p moodle mod code uses the framework.php to create a wrapper class around the moodle filestorage class to do the actual saving of the files. Again this class does a check that the user can perform the save - which ultimately comes back to the module checking the user has the 'mod/hvp:updateLibraries' capability.
- `H5PExport` - This class is used for exporting zips. This is called when a user downloads a H5P package of an activity. The export functionality relies on a config option, it htat option is on all users are allowed to download h5p files. the h5p files contain the activity content, but now user data.
- `H5PContentValidator` - provies a range of functions for validating content. The validator is used by the H5P editor, and in the H5PValidator class to validate content input by the user. The functions in this class covers a range of types:
	- text - via `validateText` - will either run htmlspecialchars, or runs through it's `filter_xss` function if the $semantics->tags is set. This will allow a list of tabs to be accepted, e.g. if you include table, it'll alow allow tr, td, th, colgroup, thead, tobdy etc.
	- number - can be used with a max and min value to ensure the number is within range, and allows rounding to take place if requested
	- boolean
	- list - can loop through a list and apply the other validator functions against each item in the list
	- group - works like list - will recursively go through the fields and run the other validator functions on each field
	- file - grabs the files path, mime type - checks the width, height, codecs, bitrate, Checks these values against the `getCopyrightSemantics` for each field pulled from the file
	- image - does the same as file
	- video - does the same as file 
	- audio - does the same as file
	- select - loops through each option in a select and calls htmlspecialchars
	- library - this is used by the H5PValidator - it uses the h5p library that's been uploaded and meets the requires mentioned above in the `H5PValidator`
	- `filter_xss` - This code is used to prevent xss, it chcks html entities are well formed, no tags contain urls with disallowed protocol (e.g. javascript:), ensures tags and attributes are well formed, removes characters and constructs that trick browsers (e.g. removing null characters, replace & with &amp, replace numeric entities.
- `H5PCore` - This class does the mapping of the passed in framework instance to a common interface to wrap the interface into with the common functions e.g. loading Content, loading content dependencies, loading h5p dependcies (e.g. jquery, h5p frontend scripts), loading h5p activities, saving h5p activities.
- Enum classes - this file also includes a number of classes just lists of constants (H5PPermission, H5PDisplayOptionBehaviour, H5PContentHubSyncStatus, H5PContentStatus, H5PHubEndpoints

### Editor
The editor submodule is responsible for the workings of the H5P activity editor that the course creator uses to create the activities, install H5P activity types from the hub hub, export h5p activities to .h5p files, uploading .h5p files.
The H5P editor itself relies on ckeditor for handling user input. Interactions between the editor and the h5p code happen via ajax (e.g. getting a full list of the h5p activities that are installed / available). The ajax calls go through to `h5peditor-ajax.class.php` and the function which captures these calls is `action`. 
The action function has a large switch statement which then sends off the underlying class and function to do the work. The majority of editor specific work (such as getting libraries installed, validating user input, processing .h5p files from upload / download etc is done via the `H5PEditor` class, located at `editor/h5peditor.class.php`. This class then calls on the likes of the library::H5PContentValidator to handle validation of the input that the editor sends in.

### Reporting
This code is responsible for generating reports for the H5P activities. It has a number of classes which revolve around managing the display of the reporting data. The handling of permission checking prior to the user viewing the report done from withing `hvp/locallib.php` which is not part of the submodule but part of the mod specific code. This has a capability check in place - `mod/hvp:viewallresults` to view all results for the activity, or `mod/hvp:viewresults` for a user to be able to view their own activities. The user would access the reporting page from the course admin > grades link in the Administration block. 
The rendering of the reporting html is handled by the one of the processors in `reporting/type-processors` - these classes all extend the `TypeProcessor` class, which defines a generateReport function which runs a filter over the HTML prior to being output the the end sure. The purifier code can be found in : `reporting/html-purifier/HthmlReportPurifier.php`.
The code which call the type processors to generate the HTML is located in `reporting/h5p-report.class.php` - This takes the users supplied answers from the DB (hvp_xapi_results) and sends them to the appropriate type-processor, then ensures that the html purifier's `filter_xss` function is called on the data prior to the end user receiving it.

## Mod Specific Code
### classes/framework.php
The moodle/totara module has a class (hvp/classes/framework.php), thisuclass implements `H5PFrameworkInterface` and supplies a singleton for instantiation `mod_hvp\framework::instance`. This function maps mod classes to that handle the moodle/totara side of things (such as file storage, capability checking, url) and creates instances of the H5P library classes (which the H5P framework - library) defines. The framework is then used by the H5P framework to handle majority of the work.

### classes/file_storage.php
This class implements the `H5PFileStorage` interface defined in the library submodule (`library/h5p-file-storage.interface.php`). This file works as an adapter between providing the functionality required to implement the interface, and getting the data to save using the moodle file_storage class. It does this by having the functions call `get_file_storage` which is a global function provided in `lib/moodlelib.php` which is part of the core totara codebase.

### classes/xapi_result.php
This class is responsible for storing and deleting data in the `hvp_xapi_results` table. This table holds the details of what the user submitted to complete the activity.

### classes/content_user_data.php
This class is responsible for storing and deleting data in the `hvp_content_user_data` table. This table holds the users progress, in order for this to happen the plugin setting to store user progress needs to be turned on.

# DB Structure
Here is a list of all the db tables that H5P has: 
- hvp - This holds the data that is specific to the course activity, the activity specific settings are stored in the `json_content` column.
- hvp_auth - stores tokens for authenticating users for different actions
- hvp_content_hub_cache - the libraries and there versions are cached, this db stores the last time that the cache was cleared and h5p was pinged to check for updates to the libraries
- hvp_content_user_data - this stores a users progression on an activity - this relies on the plugin setting 'Save content state' being checked.
- hvp_contents_libraries - holds the libraries that h5p has downloaded 
- hvp_counters - how many activities are using a specific h5p library
- hvp_events - log of events from specific H5P actions - e.g. installing a library, viewing results for a particular activity, library upgrade, library deleted.
- hvp_libraries - the libraries installed with their major, minor and patch version, also the semantics column which is used by the validator to validate content - See `H5PContentValidator` mentioned above.
- hvp_libraries_cachedassets - used to know which cache to clear when a library is updated 
- hvp_libraries_hub_cache - the hub libraries and their versions since the last time the h5p hub was checked against.
- hvp_libraries_languages - the languages that each h5p activity type supports
- hvp_libraries_libraries - mapping table of libraries to the other libraries which they require
- hvp_tmpfiles - a temporary table to store files until the content is uploaded - done via the editor - deleted via a scheduled task
- hvp_xapi_results - holds the user submitted answers from the h5p activities

## ER Diagram:
![H5P ER Diagram](mermaid-diagram-2023-05-18-115518.svg "H5P ER Diagram")

### Mermaid ER Diagram code:
```
erDiagram
	hvp {
		bigserial id PK
		bigint course FK
		varchar(255) name
		text intro
		smallint introformat
		text json_content
		varchar(127) embed_type
		bigint disable
		bigint main_library_id FK
		varchar(127) content_type
		text authors
		varchar(255) source
		smallint year_from
		smallint year_to
		varchar(63) license
		varchar(15) license_version
		text changes
		text license_extras
		text author_comments
		varchar(32) default_language
		text filtered
		varchar(255) slug
		bigint timecreated
		bigint timemodified
		smallint completionpass
		bigint shared
		bigint synced
		bigint hub_id
		varchar(255) a11y_title       
	}
	hvp_auth {
		bigserial id PK 
		bigint user_id FK
		bigint created_at
		varchar(64) secret
	}
	hvp_content_hub_cache {
		bigserial id PK
		varchar(31) language
		text json
		bigint last_checked
	}
	hvp_content_user_data {
		bigserial id PK
		bigint user_id FK
		bigint hvp_id FK
		bigint sub_content_id
		varchar(127) data_id
		text data
		smallint preloaded
		smallint delete_on_content_change
	}
	hvp_contents_libraries {
		bigserial id PK
		bigint hvp_id FK
		bigint library_id FK
		varchar(10) dependency_type
		smallint drop_css
		bigint weight

	}
	hvp_counters {
		bigserial id PK
		varchar(63) type
		varchar(127) library_name
		varchar(31) library_version
		bigint num
	}
	hvp_events {
		bigserial id PK
		bigint user_id FK
		bigint created_at
		varchar(63) type
		varchar(63) sub_type
		bigint content_id
		varchar(255) content_title
		varchar(127) library_name
		varchar(31) library_version
	}
	hvp_libraries {
		bigserial id PK
		varchar(255) machine_name
		varchar(255) title
		smallint major_version
		smallint minor_version
		smallint patch_version
		smallint runnable
		smallint fullscreen
		varchar(255) embed_types
		text preloaded_js
		text preloaded_css
		text drop_library_css
		text semantics
		smallint restricted
		varchar(1000) tutorial_url
		smallint has_icon
		text add_to
		text metadata_settings
	}
	hvp_libraries_cachedassets {
		bigserial id PK         
		bigint library_id FK
		varchar(64) hash
	}
	hvp_libraries_hub_cache {
		bigserial id PK
		varchar(255) machine_name
		smallint major_version
		smallint minor_version
		smallint patch_version
		smallint h5p_major_version
		smallint h5p_minor_version
		varchar(255) title
		text summary
		text description
		varchar(511) icon
		bigint created_at
		bigint updated_at
		smallint is_recommended
		bigint popularity
		text screenshots
		text license
		varchar(511) example
		varchar(511) tutorial
		text keywords
		text categories
		varchar(511) owner
	}
	hvp_libraries_languages {
		bigserial id PK
		bigint library_id FK
		varchar(255) language_code
		text language_json
	}
	hvp_libraries_libraries {
		bigserial id PK
		bigint library_id FK
		bigint required_library_id FK
		varchar(255) dependency_type
	}
	hvp_tmpfiles {
		bigint id PK
	}
	hvp_xapi_results {
		bigserial id PK
		bigint content_id
		bigint user_id FK
		bigint parent_id FK
		varchar(127) interaction_type
		text description
		text correct_responses_pattern
		text response
		text additionals
		integer raw_score
		integer max_score
	}
	course }|--|{ hvp : has
	user }o--o{ hvp_auth : has
	hvp_content_hub_cache
	hvp_content_user_data }|--|{ user : "belongs to"
	hvp_content_user_data }|--|| hvp : "belongs to"
	hvp_contents_libraries }|--|| hvp : "library used by"
	hvp_contents_libraries }|--|| hvp_libraries : "library used"
	hvp_counters
	hvp_events ||--|| user : "triggered by"
	hvp_libraries
	hvp_libraries_cachedassets ||--|| hvp_libraries : "cache for"
	hvp_libraries_hub_cache
	hvp_libraries_languages }|--|| hvp_libraries : "language for"
	hvp_libraries_libraries }|--|{ hvp_libraries : "libraries depencies for"
	hvp_tmpfiles
	hvp_xapi_results }o--|| hvp : "h5p activity"
	hvp_xapi_results }o--|| user : "users results"
```
