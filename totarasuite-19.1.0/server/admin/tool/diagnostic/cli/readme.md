## Diagnostics for support

## Configuration

The tool will use the configuration in the ../config/config.json file if present. If the file is not present it will fall back to the defaults provided in the config.json.dist file.

If you want to change the configuration for the CLI tool please copy the file ../config/config.dist to ../config/config.dist and modify this file instead.

In most cases the default configuration should be sufficient.

If you are using the web based tool (/admin/tool/diagnostic/index.php) then the set of providers used can be chosen for each export via the interface.

## Usage

Either use the web based tool or run 

```
php run_diagnostics.php --help
```

to display the help text on how to use the CLI tool.

## Exported files

Please note that the CLI tool generates the file in a temp folder in the configured data root of the site.

The task `\tool_diagnostic\task\delete_diagnostic_files_task` makes sure files are kept minimum for 15 and maximum for 60 minutes. 

In addition, the task `\core\task\file_temp_cleanup_task` also makes sure temporary files are only kept for maximum 1 week.