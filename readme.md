# WP Mail Catcher

[![Build Status](https://travis-ci.org/JWardee/wp-mail-catcher.svg?branch=master)](https://travis-ci.org/JWardee/wp-mail-catcher)

A fast, lightweight plugin that saves emails sent by your WordPress website.

## Features
* Zero setup required - just install and away you go
* Minimalistic - no overbloated features you never use weighing your site down
* Bulk export emails to CSV for easy inclusion into Excel or any other program
* Compose new emails with the WordPress controls you're already familiar with
* Resend your emails in bulk
* Debugging - see exactly which file and code line was responsible for sending the email, along with any errors encountered
* Manage what user permissions can see the logs
* Routinely have your logs cleared out at a specified time - or keep them forever
* Completely free

![alt text](https://raw.githubusercontent.com/JWardee/wp-mail-catcher/master/build/images/wp-mail-catcher-screenshot-1.png)


![alt text](https://raw.githubusercontent.com/JWardee/wp-mail-catcher/master/build/images/wp-mail-catcher-screenshot-3.png)


![alt text](https://raw.githubusercontent.com/JWardee/wp-mail-catcher/master/build/images/wp-mail-catcher-screenshot-2.png)

## Confirmed support
* [wp_mail](https://developer.wordpress.org/reference/functions/wp_mail/)
* [WooCommerce](https://wordpress.org/plugins/woocommerce/)
* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/)
* [MailGun](https://wordpress.org/plugins/mailgun/)
* [SparkPost](https://wordpress.org/plugins/sparkpost/)
* [Easy WP SMTP](https://wordpress.org/plugins/easy-wp-smtp/) (excluding their test email function)
* [SendGrid](https://en-gb.wordpress.org/plugins/sendgrid-email-delivery-simplified)
* Anything that uses wp_mail!

## Not currently supported
* [WP Mail Bank](https://wordpress.org/plugins/wp-mail-bank/) (unhooks wp_mail filters)
* [BuddyPress](https://en-gb.wordpress.org/plugins/buddypress/)

## Testing locally
1. Download the repo
2. cd into `testing` and run `composer install`
3. Run `bash ./bin/install-wp-tests.sh` from the `testing` directory
4. Run `phpunit` within the `testing` directory

## Found an issue, or have an idea on how we can improve?
Let us know in our [GitHub tracker!](https://github.com/JWardee/wp-mail-catcher/issues)

## Contributing
Contributions are always welcome, to get started do the following:
1. Pull the repo and run `composer install`
2. cd into `build/grunt` and run `npm install`
3. While inside of `build/grunt` run `grunt` this will watch your scss and js and compile any changes
4. Make sure your code conforms to [PSR-2 standards](http://www.php-fig.org/psr/psr-2/)
5. Ensure your changes pass all the unit tests
6. Submit your pull request!

## Additional resources
* [PSR-2 coding standards](http://www.php-fig.org/psr/psr-2/)
* [Installing composer](https://getcomposer.org/download/)
* [Installing grunt](https://gruntjs.com/getting-started/)

