=== ZS Action Scheduler Optimizer ===
Contributors: zaferoz
Donate link: https://zafersoft.com
Tags: action scheduler, cleanup, cleaner, optimization, retention
Requires at least: 4.6
Tested up to: 6.5
Stable tag: 1.0.2
Version: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin optimizes Action Scheduler by clearing the Action Scheduler Actions table, truncating the logs, and modifying the retention period.

== Description ==

This plugin:
- Checks if the ActionScheduler actions and logs tables exist in the database.
- Deletes all completed, failed, and cancelled actions from the table and truncated the logs based on user input.
- Allows you to set the action scheduler purge period (retention period).
- Retrieves saved settings and fetch table sizes along with the number of rows.

== Installation ==

1. Upload the entire zs-action-scheduler-optimizer folder to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Usage ==

1. Navigate to Tools > ZS Action Scheduler Optimizer in your WordPress dashboard.
2. Choose whether to delete completed, failed, and cancelled actions.
3. Select your desired retention period for the action scheduler.
4. Click 'Save Changes'.

== Changelog ==

= 1.0.2 =
* Updated plugin code to allow translations.

= 1.0.1 =
* Added checks to ensure the required ActionScheduler tables exist before performing operations.
* Updated plugin version and tested up to WordPress version 6.4.3.

= 1.0.0 =
* Initial release of the plugin.

== Developer Information ==

Author: Zafer Oz
Author URI: https://zafersoft.com
