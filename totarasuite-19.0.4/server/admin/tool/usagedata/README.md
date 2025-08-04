Usage Data Tool
===============

The data we collected is used for the purpose of understanding our products usage so that we can build a progressively better product for you. We do not collect any personal identifiable information. The data is anonymous apart from the following items:
* The site identifier: Allows us to identify usage data sent from this site.
* The site URL: Allows us to match the sites usage data to a subscription.

To view all the data we collect, please navigate to the plugin's overview page via `Quick Access Menu > Configure Features > Collect Usage Data`

## How to develop export function

In order to create your own export class. You should consider the following step:
* Implement the interface of `tool_usagedata\export`
* Include the trait of `core\usagedata\helper`
* Create a summary text in your language pack and name the summary index to the format of [class name]_summary
* If you want to use different summary index format, you have to create your own get_summary function
* Every export class your created, you have to create a corresponding php unit test for it.
* No Personal Identifiable Information (PII) is to be extracted

## How export functions work

* The system will look for all class namespaces with `usagedata` and wrap that class in `tool_usagedata\local\data`
* When you want to export the data, `to_hierarchical_array()` will be called. It will call each usagedata export class and export results.
* The cron task will run every Saturday or Sunday between 00:00 and 04:00
* You can get the export data in CLI by running `php ./server/admin/tool/usagedata/cli/export.php`



