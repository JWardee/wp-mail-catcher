=== Mail logging - WP Mail Catcher ===
Contributors: Wardee
Tags: mail logging, email log, email logger, logging, email logging, mail, crm
Requires at least: 4.7
Tested up to: 6.1
Requires PHP: 7.2
Stable tag: 2.0.5
License: GNU General Public License v3.0
License URI: https://raw.githubusercontent.com/JWardee/wp-mail-catcher/master/LICENSE
Donate link: https://paypal.me/jamesmward

Logging your mail will stop you from ever losing your emails again! This fast, lightweight plugin (under 140kb in size!) is also useful for debugging or backing up your messages.

== Description ==
Logging your mail will stop you from ever losing your emails again! This fast, lightweight plugin (under 140kb in size!) is also useful for debugging or backing up your messages.

Just install and activate then all of your contact form emails will be logged and saved to your DB with no additional configuration required.

View and manage all your form submissions through the default WordPress interface. An email failed to send? With a single click you can resend the backed up email.

Send emails out directly from the WordPress interface. Also includes support for attachments.

Immediately find out if your contact form submission was successfully sent.

## Features
* Zero setup required - just install and away you go
* Minimalistic - no overbloated features you never use weighing your site down - under 140kb in size!
* Bulk export emails to CSV for easy inclusion into Excel or any other program
* Compose new emails with the WordPress controls you're already familiar with
* Resend your emails in bulk
* Debugging - see exactly which file and code line was responsible for sending the email, along with any errors encountered
* Manage what user permissions can see the logs
* Routinely have your logs cleared out at a specified time - or keep them forever
* Need to be notified when there's a problem sending your mail? We've got hooks that allow you to do just that
* Completely free

## Hooks and actions
* `wp_mail_catcher_mail_success` is triggered when a message is sent and logged successfully. It has a single argument that is an array containing the log
  * `id` related to the id in the `mail_catcher_logs` MySQL table
  * `time` relative, readable time to when the log was saved
  * `email_to` the email address(es) that the message was sent to
  * `subject` the subject line of the message
  * `message` the contents of the message
  * `status` an integer depicting if the message was sent successfully or not (1 = sent successfully. 0 = sending failed)
  * `error` the error that occurred - if any
  * `backtrace_segment` a json_encoded object that shows which file and line the mail was initially triggered from
  * `attachments` a list of any attachments that were sent along with the email
  * `additional_headers` a list of any headers that were sent
  * `attachment_file_paths` a list of the location of any attachments that were sent
  * `timestamp` a unix timestamp of when the email was sent
  * `is_html` a boolean, that will be true if the message is a html email and false if not
  * `email_from` the from value of the email
* `wp_mail_catcher_mail_failed` is triggered when a message failed to send and logged successfully. It has a single argument that is an array containing the log (same as the arguments for `wp_mail_catcher_mail_success`)
* `wp_mail_catcher_deletion_intervals` is a filter that should return an array where each key is an amount of time in seconds, and the value is the label. Used to determine when a message has expired and should be deleted

== Frequently Asked Questions ==
= Is this really free?  =

Yup, completely 100% free, no premium add-ons or anything like that.

= Does that include adverts/nagging =

Yes, there are no adverts/annoying messages asking you to "upgrade to pro" or anything similar, 100% of the features are available.

= What plugins are supported? =

Anything that doesn't unhook the native wp_mail function is supported, this includes but not limited to:
* WooCommerce
* Contact Form 7
* MailGun
* SparkPost
* Easy WP SMTP (excluding their test email function)
* SendGrid
* BuddyPress

[Click here for a full list](https://github.com/JWardee/wp-mail-catcher#confirmed-support)

= What plugins are currently not supported =

* WP Mail Bank (unhooks wp_mail filters)
* Post SMTP (overrides wp_mail function and doesn't implement the same actions/filters)

If you'd like to see support for these plugins or any other plugins please leave a feature request in our [GitHub tracker](https://github.com/JWardee/wp-mail-catcher/issues)

= I've found an issue!/I have a great idea on how to improve this =

Great! Please leave a note in our (GitHub tracker)

== Screenshots ==
1. Send a quick email from your dashboard
2. Basic, no clutter options page
3. The table supports: sorting, exporting and resending
4. Supports column customisation and pagination

== Changelog ==

= 2.0.5 =

- Fix: Improved error handling when attempting to save a log without running the migration
- New: Added ability to force rerun database migrations
- Improvement: Database migrations will now happen automatically on plugin upgrade

= 2.0.4 =

- Fix: Resolved memory leak when saving a new log

= 2.0.3 =

- Fix: Auto deleting clearing all logs

= 2.0.2 =

- Improvement: Reduced memory usage when deleting expired logs
- Fix: Emails sent with the to address formatted with angled brackets are now escaped
- Possible fix: Aligned wp_mail handling to be much closer to older version as some people reported issues

= 2.0.1 =

- Fix: Bulk actions (delete, export, resend) now works

= 2.0.0 =

- Deprecation: Increased supported PHP version from 5.6 to 7.2
- New: Added partial lazy loading to reduce database strain
- New: Can now manually trigger removing of old logs
- New: Added support for wp_mail_content_type filter
- New: Implemented database upgrade system
- Fix: Old logs not being automatically deleted
- Fix: Pagination now works on search results or other filters
- Fix: BuddyPress error exception
- Fix: Re-implemented resending HTML emails
- Fix: PHP notice appearing on settings page under certain circumstances
- Fix: Scrolling not working correctly on mobile and tablets

= 1.5.4 =

- Fix: Auto delete wasn't always deleting Logs
- Fix: Fixed header syncing issue

= 1.5.3 =

- Fix: Cron job issue preventing logs from being auto deleted
- Fix: v1.5.0 broke compatibility with some other mail plugins

= 1.5.2 =

- Fix: PHP notice that appears if additional_headers column is corrupted

= 1.5.1 =

- Fix: Auto deleting timer isn't pulled through on settings page. Thanks to @oginomizuho

= 1.5.0 =

- New: Added beta BuddyPress support
- New: Can now auto delete messages that are over a specific age
- Fix: Minute/seconds being slightly off in export

= 1.4.1 =

- Fix: Log exports now show the correct date and time

= 1.4.0 =

- New: Refreshed log table UI
- New: Added 2 new actions `wp_mail_catcher_mail_success` and `wp_mail_catcher_mail_failed`

= 1.3.10 =

- Performance: Email previews are now loaded lazily

= 1.3.9 =

- Fix: Auto delete notification is always shown regardless of settings

= 1.3.8 =

- Fix: Logs per page screen option was being ignored
- Compatibility: Added support for WordPress 5.5 

= 1.3.7 =

- New: Can now see raw html code of an email if it's html enabled (open a message and go to the Debug tab)
- Update: npm dependencies updated

= 1.3.6 =

- Fix: Object serialization issue stability when a third party modifies the object
- Fix: Minor typo

= 1.3.5 =

- New: French translation

= 1.3.4 =

- New: Added search functionality, supports partial and exact matching for: to, subject, message, attachment names and email headers

= 1.3.3 =

- Fix: Improved support for multisite

= 1.3.2 =

- Fix: Child CSS class not matching parent

= 1.3.1 =

- Fix: Improved clarity of 'from' header
- Fix: Fixed issue with bulk deletion

= 1.3.0 =

- New: 'From' column now included in admin table
- New: Screen options have been added that allow you to pick which columns are visible along with the number of logs per page
- Fix: Fixed an issue adding an attachment in the 'new message' modal
- Fix: Fixed custom headers being rendered incorrectly in the 'new message' modal
- Fix: Fixed bug whereby tables were not dropped when deleting a multi-site

= 1.2.4 =

- New: Exact time (including timestamp) mail was sent can now be seen in the debug panel and when you hover over the value in the sent column of the table
- Improvement: Significantly reduced file size of plugin
- Fix: Fixed error when calling wp_mail and passing an attachment as a string
- Fix: Namespaced CSS
- Fix: Fixed incorrect time being rendered
- Fix: Sorting columns now works

= 1.2.3 =

- New: When the number of logs exceeds a specific value (currently set to 100) then a warning appears. Upon trying to do an 'export all' a dialog opens to batch the exporting
- Fix: Fixed an issue with interacting with messages beyond the first page
- Fix: Non-html emails now have their spacing rendered correctly

= 1.2.2 =

- New: Added new filtering system that allows only successful or failed messages to be seen
- New: Added 'export all' button
- Improvement: Removed carbon dependency, reducing the plugin size significantly from 322kb to 53kb (zipped)
- Improvement: Added basic caching system so repeated, identical database calls are avoided

= 1.2.1 =

- Fix: Hotfix for html emails not rendering correctly

= 1.2.0 =

- New: Added support for foreign characters
- New: Added link to settings page from the plugins page
- Fix: Fixed issue where non-html emails lost their line breaks

= 1.1.0 =

- Fix: 'Failed security check' message appearing when trying to perform any bulk actions
- Fix: Exporting with no attachments but with additional headers causes the wrong column to be populated
- Fix: HTML emails cause modal styling problem
- Fix: Long file names cause the content to spill over modal
- Fix: Admin notices makes 'New Message' button fall out of alignment
