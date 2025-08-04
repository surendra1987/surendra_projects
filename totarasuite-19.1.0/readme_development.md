# Developing for Totara

This document provides a quick developer overview. 
For detailed information and more extensive options please see our [developer documentation](https://help.totaralearning.com/display/DEV/).

There is a docker setup designed for development with Totara available [on github](https://github.com/totara/totara-docker-dev).

## General setup

### Site registration

Tell the registration system we're a development install.
```bash
$CFG->sitetype = 'development';
```

### Enabling debugging

```bash
$CFG->debug = E_ALL;
$CFG->debugpageinfo = 1;
$CFG->perfdebug = 15;
$CFG->debugdisplay = 1;
```

### Disabling caching

```php
// For better performance, keep this off when only working on tui (Vue) files
$CFG->themedesignermode = false;

$CFG->cachejs = false;
// Preferred method
$CFG->forced_plugin_settings['totara_tui'] = ['cache_js' => false, 'cache_scss' => false, 'development_mode' => true];
```

### Useful settings

```php
$CFG->passwordpolicy = false;
$CFG->allowuserthemes = 1;
$CFG->allowcoursethemes = 1;
$CFG->allowthemechangeonurl = 1;
$CFG->noemailever = 1;
// Support developer GraphQL access and prevent schema caching
define('GRAPHQL_DEVELOPMENT_MODE', true);
$CFG->cache_graphql_schema = false;
```

## Build processes

### Building Tui

Tui requires a build process to bundle src files into build files.
The following command will build both production and development builds.

```bash
npm install
npm run tui-build
```

For more information on the Tui build process see our (Build process developer documentation)[https://help.totaralearning.com/display/DEV/The+build+process]

### Building AMD and less themes

AMD and less based themes also still require a build process. This remains in the server directory, and operates as it 
did previously.

```bash
cd server
php composer.phar install
npm install
./node_modules/grunt/bin/grunt
```

For more information on the AMD and LESS themes build process see our [developer documentation](https://help.totaralearning.com/display/DEV/Working+With+LESS+in+Themes)

## Automated testing

Totara uses PHPUnit for unit and integration testing (predominantly integration testing), and Behat for acceptance testing.

### Running PHPUnit

The following is a quick overview. For further information see our [Unit testing developer documentation](https://help.totaralearning.com/display/DEV/Unit+testing).

- Quick overview of required config.php settings
```php
$CFG->phpunit_dataroot = $CFG->dataroot . '_phpunit';
$CFG->phpunit_prefix = 'p_';
$CFG->phpunit_dboptions = array_merge($CFG->dboptions, array(
    'dbschema' => 'phpunit'
));
```
- Quick copy + paste commands

```
# All commands run from the same folder as this readme.md file
# Initialise PHPUnit (run once before use):
php test/phpunit/phpunit.php init
# Run all tests
php test/phpunit/phpunit.php run
# Drop database ready to re-initialise:
php test/phpunit/phpunit.php util --drop
```

### Running Behat

The following is a quick overview. For further information see our (Acceptance testing developer documentation)[https://help.totaralearning.com/display/DEV/Acceptance+testing].
 
- Quick overview of required config.php settings
```php
$CFG->behat_dataroot = $CFG->dataroot . '_behat';
$CFG->behat_prefix = 'b_';
$CFG->behat_faildump_path = '/tmp';
$CFG->behat_dboptions = array_merge($CFG->dboptions, array(
    'dbschema' => 'behat'
));
```
- Quick copy paste commands

```php
# All commands run from the same folder as this readme.md file
# Initialise Behat (run once before use):
php test/behat/behat.php init
# Run all tests
php test/behat/behat.php run
# Drop database ready to re-initialise:
php test/behat/behat.php util --drop
```

### Integration testing

Many of the integrations within Totara require additional configuration in order to run automated tests.
The following instructions can be used configure Totara to run automated tests on integrations that support them but require configuration.

#### General session handling testing

Session handling is tested using the default file session handler.
This can be changed by adding the following define to your config.php file.
The value should be a valid session handler class name. Refer to config.example.php for those options.
```php
define('TEST_SESSION_HANDLER', '\core\session\redis5');
```
Please note that when running tests against another session handler you will need to ensure it is correctly configured in order for the tests to run and pass.
Refer to config.example.php for instructions on correctly configuring the session handler you wish to use.

#### Redis (Native) session handling tests

The Redis session handler requires additional configuration to run its automated tests.
It must be told which Redis instance to connect to for testing purposes.
The following define needs to be added to your config.php:
```php
define('TEST_SESSION_REDIS5_HOST', 'tcp://127.0.0.1'');
```
The value for the define will be used as $CFG->session_redis5_host when connecting, and should match the allowed patterns for that configuration.

#### Redis (Native) cache store tests

The Redis cache store requires additional configuration to run it's automated tests.
The following defines can be used to configure the Redis cache store that will be used for testing purposes.
```php
define('TEST_CACHESTORE_REDIS_TESTSERVERS', 'localhost:6379'); // Required
define('TEST_CACHESTORE_REDIS_PASSWORD', 'masterpassword123'); // Optional
```

Redis read replica's also support automated testing, but require further configuration.
Please note you must have Redis correctly installed and operating with replication.
```php
define('TEST_CACHESTORE_REDIS_READ_SERVER', 'localhost:63791'); // Required
define('TEST_CACHESTORE_REDIS_READ_PASSWORD', 'password123'); // Optional
```
