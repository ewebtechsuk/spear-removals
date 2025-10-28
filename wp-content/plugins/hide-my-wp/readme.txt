===WP Ghost===

1. Install the Plugin

- Log In as an Administrator on your WordPress dashboard.
- In the WordPress menu, go to Plugins > Add New Plugin tab.
- Click on the Upload Plugin button from the top of the page.
- Click to browse and upload the hide-my-wp.zip file.
- After the upload, click the Activate Plugin button to activate the plugin.

2. Activate the plugin

- From the plugins list, click on the Settings link to go to plugin’s settings.
- Now enter the Activation Token from your account into the activation field.
- Click to activate and start the plugin setup.

3. Select Safe Mode or Ghost Mode

- Go to WP Ghost > Change Paths > Level of Security
- Choose between 2 levels of security: Safe Mode and Ghost Mode.
- Customize the paths as you like and click the Save button to apply changes.
- Follow the WP Ghost instructions based on your server configuration.

Enjoy WP Ghost!
John

== Changelog ==
= 8.2.11 (21 Mar 2025) =
* Update - Compatibility with the WP 6.8
* Update - Firewall compatibility with WooCommerce
* Fixed - Function _load_textdomain_just_in_time was called incorrectly

= 8.2.10 (10 Mar 2025) =
* Update - Compatibility with the new WP Engine rewrite rules
* Fix - File security when the rewrite rules are not loaded correctly
* Fix - Prevent Brute Force from updating the warning text without space when switched off
* Fix - Prevent PHP warning when IP address unknown in Brute Force IP check
* Fix - Load i18n on login page for password-strength-meter messages when the Clean Login option is activated
* Fix - Detect if parent theme has caps when child theme is activated
* Fix - Dynamic file mapping to load through index.php for better compatibility with all server types

= 8.2.04 (07 Mar 2025) =
* Update - Add the option to customize all active and inactive themes
* Update - The plugin update library from PucFactory
* Fix - Brute Force error in comments when no recaptcha option is selected
* Fix - WP Multisite root directory for custom WP directory installation

= 8.2.03 (04 Mar 2025) =
* Update - Security update on wp-activate.php path call
* Fix - Headers check on Brute Force to get the real IP behind Proxy
* Fix - Admin layout issue when other plugins notification is loading in Wp Ghost settings
* Fix - Remove newlines from the rewrite rules

= 8.2.01 (26 Feb 2025) =
* Update - Translations in all languages for the last changes
* Fix - Brute Force compatibility and bugs
* Fix - Include parent theme in the custom theme name list if the child theme is loaded

= 8.2.00 (25 Feb 2025) =
* Update - Add Google reCaptcha Enterprise
* Update - Increase security on Brute Force feature
* Update - Compatibility with Sucuri plugin on Events Log and Brute Force
* Update - Add the _HMWP_CONFIG_DIR_ constant to define the config root path
* Fix - Get the real IP address behind proxy

= 8.1.04 (06 Feb 2025) =
* Update - New WP Ghost Dashboard design
* Update - Login Attempt and Blocked IPs chart in WP Ghost Dashboard
* Update - Email Alerts log report in WP Ghost Dashboard
* Fix - Paths changed in dynamically loaded CSS and JS files
* Fix - Prevent redirecting URLs to hidden paths on config rules issue
* Fix - Prevent hiding the wp-admin on config rules issue
* Fix - Prevent changing the wp-admin on config rules issue

= 8.1.03 (22 Jan 2025) =
* Update - Knowledge Base links and responsive layout
* Update - GeoIP Country database for Geo-Blocking
* Fix - Config update issue when saving the whitelist from Level Of Security

= 8.1.02 (14 Jan 2025) =
* Update - Added the AI support in the plugin settings page
* Update - Remove the help icons for the plugin whitelabel option with custom domain
* Fix - Prevent changing the login path in posts slug
* Fix - Advanced Pack install domain not found error

= 8.1.01 (04 Jan 2025) =
* Update - Changed Hide My WP Ghost plugin name with short WP Ghost
* Update - WP Ghost comes with a new plugin logo in 2025
* Update - More security on REST API for user listing when User Security is activated
* Update - Plugin Security and Firewall rules

= 8.0.21 (21 Dec 2024) =
* Update - Added gif and tiff to media redirect in Hide WP Common Paths
* Update - Allow activating hmwp_manage_settings capability only for a user using Roles & Capabilities plugin
* Fix - Layout and improved functionality

= 8.0.20 (04 Nov 2024) =
* Update - Compatibility with WP 6.7
* Update - Compatibility with LiteSpeed Quic Cloud IP addresses automatically
* Fix - Litespeed cache plugin compatibility and set /cache/ls directory by default
* Fix - Whitelist website IP address on REST API disable to be able to be accessed by the installed plugins

= 8.0.19 (20 Oct 2024) =
* Fix - Compatibility with LiteSpeed when CDN is not set
* Fix - Change paths when www. prefix exists on the domain

= 8.0.17 (12 Oct 2024) =
* Update - Compatibility with WP Rocket Background CSS loader
* Update - Compatibility with LiteSpeed Cache CDN
* Update - Map Litespeed cache directory in URL Mapping
* Fix - Remove dynamic CSS and JS when Text Mapping is switched off
* Fix - Prevent changing wp-content and wp-includes paths in deep URL location and avoid 404 errors

= 8.0.16 (10 Oct 2024) =
* Update - Layouts, colors
* Update - Added Drupal 11 in CMS simulation
* Update - Set 404 Not Found error as default option for hidden paths
* Fix - Compatibility with Wordfence Scan
* Fix - Changed deprecated PHP functions
* Fix - Warnings when domain schema is not identified for the current website
* Fix - Redirect to homepage the newadmin when user is not logged in

= 8.0.15 (03 Oct 2024) =
* Fix - Compatibility with WP 6.6.2
* Fix - Compatibility with Squirrly SEO buffer when other cache plugins are active
* Fix - Compatibility with Autoptimize minify

= 8.0.14 (07 Sept 2024) =
* Update - Added the option to select all Countries in Geo Blocking
* Update - Brute Force compatibility with UsersWP plugin
* Update - Whitelist path to not check Brute force reCaptcha in case of login whitelist paths

= 8.0.13 (23 Aug 2024) =
* Update - Added the option to disable Copy & Paste separately
* Fix - PHP Error on HMWP_Models_Files due to the not found class
* Fix - Small Bugs

= 8.0.12 (15 Aug 2024) =
* Update - Compatibility with Wordfence

= 8.0.11 (14 Aug 2024) =
* Update - Plugin security and compatibility with WP 6.6.1 and PHP 8.3
* Update - Adding wp-admin path extensions into firewall when user is not logged in

= 8.0.10 (11 Aug 2024) =
* Fix - Google reCaptcha on frontend popup to load google header if not already loaded
* Fix - Hide New Login Path to allow redirects from custom paths: lost password, signup and disconnect
* Fix - WP Multisite active plugins check to ignore inactive plugins
* Fix - Small bugs

= 8.0.09 (10 Aug 2024) =
* Update - Add security preset loading options in Hide My WP > Restore
* Fix - Library integrity on the update process
* Fix - Cookie domain on WP multisite to redirect to new login path when changing sites from the network
* Fix - Brute Force shortcode to work with different login forms

= 8.0.07 (01 Aug 2024) =
* Fix - Compatibility with WP 6.6
* Fix - Security update on wp-login.php and login.php

= 8.0.06 (29 July 2024) =
* Update - Compatibility with WordPress 6.5.5
* Update - Added the option to immediately block a wrong username in Brute Force
* Update - Sub-option layouts
* Fix - File Permission check to receive the correct permissions when is set stronger than required
* Fix - Hide login.php path together with wp-login.php path from being redirect to the new login
* Fix - Small bugs

= 8.0.05 (18 July 2024) =
* Update - Added more path in Frontend Test to make sure the settings are okay before confirmation
* Fix - Compatibility with Wordfence to not remove the rules from htaccess
* Fix - Filter words in 8G Firewall that might be used in article slugs
* Fix - Trim error in cookie when main domain cookie is set
* Fix - Login header hooks to not remove custom login themes

= 8.0.03 (03 July 2024) =
* Fix - isPluginActive check error when is_plugin_active is not yet declared
* Fix - Disable clicks and keys to work without jQuery
* Fix - Compatibility with Wordfence scan process

= 8.0.02 (22 June 2024) =
* Fix - Show error messages in Temporary login when a user already exists
* Fix - Temporary users to work on WP Multisite > Subsites

= 8.0.01 (20 June 2024) =
* Fix - Login security when Elementor login form is created and Brute Force is active
* Fix - Login access when member plugins are used for login process
* Fix - Firewall warning on preg_match bot check in firewall.php

= 8.0.00 (15 June 2024) =
* Update - Added Country Blocking & Geo Security feature
* Update - Added Firewall blacklist by User Agent
* Update - Added Firewall blacklist by Referrer
* Update - Added Firewall blacklist by Hostname
* Update - Added 'Send magic link login' option in All Users user row actions on Hide My WP Advanced Pack plugin
* Update - Added the option to select the level of access for an IP address in whitelist
* Removed - Mysql database permission check as WordPress 6.5 handles DB permissions more secure
* Moved - Firewall section was moved to the main menu as includes more subsections
* Fix - 8G Firewall compatibility with all page builder plugins

= 7.3.05 (30 May 2024) =
* Update - Compatibility with WPEngine rules on wp-admin and wp-login.php
* Update - New Feature added 'Magic Login URL' on Hide My WP Advanced Pack plugin
* Fix - Prevent firewall to record all triggered filters as fail attempts
* Fix - Remove filter on robots when 8G firewall is active
* Fix - Frontend Login Check popup to prevent any redirect to admin panel in popup test
* Fix - Prevent redirect the wp-admin to new login when wp-admin path is hidden

= 7.3.04 (28 May 2024) =
* Update - Search option in Hide My WP > Overview > Features
* Update - Send Temporary Logins in Events log
* Fix - Don't show Temporary Logins & 2FA in main menu when deactivated

= 7.3.03 (22 May 2024) =
* Update - 8G Firewall on User Agents filters
* Update - Compatibility with WP 6.5.3
* Update - Load the options when white label plugin is installed
* Fix - Restore settings error on applying the paths
* Fix - Prevent redirect the wp-admin to new login when wp-admin path is hidden

= 7.3.01 (17 May 2024) =
* Update - Added translation in more languages like Arabic, Spanish, Finnish, French, Italian, Japanese, Dutch, Portuguese, Russian, Chinese
* Fix - 'wp_redirect' when function is not yet declared in brute force
* Fix - 'wp_get_current_user' error in events log when function is not yet declared

= 7.3.00 (02 May 2024) =
* Update - Added the option to detect and fix all WP files and folders permissions in Security Check
* Update - Added the option to fix wp_ database prefix in Security Check
* Update - Added the option to fix admin username in Security Check
* Update - Added the option to fix salt security keys in Security Check
* Update - Layout and Fonts to integrate more with WordPress fonts
* Update - 7G & 8G firewall compatibility to work with more WP plugins and themes

= 7.2.07 (22 Feb 2024)=
* Update - Added the option on Apache to insert the firewall rules into htaccess
* Fix - Screen 120dpi display layout
* Fix - Hide reCaptcha secret key in Settings

= 7.2.06 (09 Jan 2024) =
* Update - Added the 8G Firewall filter
* Update - Added the option to block the theme detectors
* Update - Added the option to block theme detectors crawlers by IP & agent
* Update - Added compatibility with Local by Flywheel 
* Update - Firewall loads during WP load process to work on all server types
* Fix - Load most firewall filters only in frontend to avoid compatibility issues with analytics plugins in admin dashboard 
* Fix - Avoid loading recaptcha on Password reset link
* Fix - Avoid blocking ajax calls on non-admin users when the Hide wp-admin from non-admin users is activated

= 7.2.05 (28 Oct 2023) =
* Update - Added the option ot manage/cancel the plan on Hide My WP Cloud
* Fix - Custom login path issues on Nginx servers
* Fix - Issues when the rules are not added correctly in config file and need to be handled by HMWP
* Fix - Don't change the admin path when ajax path is not changed to avoid ajax errors

= 7.2.04 (18 Oct 2023) =
* Compatibility with WP 6.5
* Update - Compatibility with CloudPanel & Nginx servers
* Fix - Warning in Nginx for $cond variable

= 7.2.03 (15 Oct 2023) =
* Compatibility with PHP 8.3 & WP 6.4.3
* Update - Compatibility with Hostinger
* Update - Compatibility with InstaWP
* Update - Compatibility with Solid Security Plugin (ex iThemes Security)
* Update - Added the option to block the API call by rest_route param
* Update - Added new detectors in the option to block the Theme Detectors
* Update - Security Check for valid WP paths
* Fix - Don't load shortcode recapcha for logged users
* Fix - Rewrite rules for the custom  wp-login path on Cloud Panel and Nginx servers
* Fix - Issue on change paths when WP Multisite with Subcategories
* Fix - Hide rest_route param when Rest API directory is changed
* Fix - Multilanguage support plugins
* Fix - Small bugs & typos

= 7.2.02 (25 Sept 2023) =
* Update - Add shortcode on BruteForce 'hmwp_bruteforce' for any login form
* Update - Add security schema on ssl websites when changing relative to absolute paths
* Update - Compatibility with WP 6.4.2 & PHP 8.3
* Fix - Change the paths in cache files when WP Multisite with Subdirectories
* Fix - Small bugs in rewrite rules

= 7.2.01 (20 Sept 2023) =
* Update - Compatibility with WP 6.4.1 & PHP 8.3
* Update - The Frontend Check to check the valid changed paths
* Update - The Security Check to check the plugins updated faster and work without error with Woocommerce update process
* Update - Compatibility with Solid Security Plugin (ex iThemes Security)
* Update - Hidden wp-admin and wp-login.php on file error due to config issue
* Update - Hide rest_route param when Rest API directory is changed
* Update - Add emulation for Drupal 10 and Joomla 5
* Fix - Hide error when there are invalid characters in theme/plugins directory name
* Fix - Small bugs

= 7.2.00 (05 Aug 2023) =
* Update - Added the 2FA feature with both Code Scan and Email Code
* Update - Added the option to add random number for static files to avoid caching when users are logged to the website
* Fix - Added the option to pass the 2FA and Brute Force protection when using the Safe URL
* Fix - Tweaks redirect for default path wasn't saved correctly
* Fix - Small Bugs

= 7.1.17 (18 July 2023) =
* Fix - File extension blocked on wp-includes when WP Common Paths are activated
* Fix - Remove hidemywp from file download when the new paths are saved

= 7.1.16 (10 July 2023) =
* Update - Compatibility with WP 6.3.1
* Update - Compatibility with WPML plugin
* Update - Security on Brute Force for the login page
* Fix - Small Bugs

= 7.1.15 (02 July 2023) =
* Update - Compatibility with WP 6.3
* Update - Security Check Report for debugging option when debug display is set to off
* Update - Security Check Report for the URLs and files to follow the redirect and check if 404 error

= 7.1.13 (30 June 2023) =
* Update - Compatibility with more 2FA plugins
* Update - Compatibility with ReallySimpleSSL
* Fix - Small bugs

= 7.1.11 (14 June 2023) =
* Update - Compatibility with FlyingPress plugin
* Update - Use WordPress function for ajax requests
* Fix - Remove infinite loading icon on settings backup action
* Fix - Small bugs

= 7.1.10 (30 May 2023) =
* Update - Compatibility with WP 6.2.2
* Fix - Update checker to work with the latest WordPress version
* Fix - Hide wp-login.php path for WP Engine server with PHP 7.0

= 7.1.08 (26 May 2023) =
* Update - Added the user role 'Other' for unknown user roles
* Update - Sync the new login with the Cloud to keep a record of the new login path and safe URL
* Update - Compatibility with WP 6.2.2
* Fix - Typos and small bugs

= 7.1.07 (19 May 2023) =
* Update - Compatibility with WPEngine hosting
* Update - Compatibility with WP 6.2.1
* Fix - Loading on defaut ajax and json paths when the paths are customized
* Fix - Compatibility issues with Siteground when Ewww plugin is active
* Fix - To chnage the Sitegroud cache on Multisite in the background

= 7.1.06 (16 May 2023) =
* Update - Compatibility with Siteground
* Update - Compatibility with Avada when cache plguins are enabled

= 7.1.05 (05 May 2023) =
* Update - Add compatibility for Cloud Panel servers
* Update - Add the option to select the server type if it's not detected by the server
* Fix - Remove the rewrites from WordPress section when the plugin is deactivated
* Fix - User roles names display on Tweaks

= 7.1.04 (03 May 2023) =
* Update - File processing when the rules are not set correctly
* Update - Security headers default values
* Fix - Compatibilities with the last versions of other plugins
* Fix - Reduce resource usage on 404 pages from version 7.1.03

= 7.1.02 (24 Apr 2023) =
* Update - Compatibility with other plugins
* Update - UI & UX to guide the user into the recommended settings
* Update - Compatibility with WP User Manager plugin
* Update - Security in Brute force option to work with more plugins
* Update - Compatibility with Ewww Image Optimizer plugin CDN option
* Fix - Increased plugins speed on compatibility check
* Fix - Common paths extensions check in settings

= 7.0.15 (04 Apr 2023) =
* Update - Add the option to check the frontend and prevent broken layouts on settings save
* Update - Brute Force protection on lost password form
* Update - Compatibility with MemberPress plugin
* Fix - My account link on multisite option

= 7.0.14 (23 Mar 2023) =
* Update - Compatibility with WP 6.2
* Update - Added the option to whitelist URLs
* Update - Added the sub-option to  show a white-screen on Inspect Element for desktop
* Update - Added the options to hook the whitelisted/blacklisted IPs 
* Fix - small bugs / typos / UI

= 7.0.13 (28 Feb 2023) =
* Update - Compatibility with PHP 8 on Security Check

= 7.0.12 (20 Feb 2023) =
* Compatibile with WP 6.2
* Fix - Handle the physical custom paths for wp-content and uploads set by the site owner
* Fix - Compatibility with more plugins and themes

= 7.0.11 (26 Ian 2023) =
* Update - Remove the atom+xml meta from header
* Update - Save all section on backup restore

= 7.0.10 (19 Dec 2022) =
* Update - Remove the noredirect param if the redirect is fixed
* Update - Check the XML and TXT URI by REQUEST_URI to make sure the Sitemap and Robots URLs are identified
* Update - Check the rewrite rules on WordPress Automatic updates too
* Fix - To remove the version from URL even if the 'ver' param doesn't have any value
* Fix - Typo in Security Check

= 7.0.05 (22 Nov 2022) =
* Update - Fix login path on different backend URL from home URL

= 7.0.04 (25 Oct 2022) =
* Update - Compatibility with WP 6.1
* Update - Add More security to XML RPC
* Update - Add GeoIP flag in Events log to see the IP country
* Update - Compatibility with LiteSpeed servers and last version of WordPress

= 7.0.03 =
* Update - Add the Whitelabel IP option in Security Level and allow the Whitelabel IP addresses to pass login recaptcha and hidden URLs
* Fix - Allow self access to hidden paths to avoid cron errors on backup/migration plugins
* Fix - White screen on iphone > safari when disable inspect element option is on

= 7.0.02 (28 Sept 2022) =
* Update - Add the Brute Force protection on Register Form to prevent account spam
* Update - Added the option to prioritize the loading of HMWP Ghost plugin for more compatibility with other plugins
* Update - Compatibility with LiteSpeed servers and last version of WordPress
* Update - Compatibility with FlyingPress by adding the hook for fp_file_path on critical CSS remove process
* Fix - Remove the get_site_icon_url hook to avoid any issue on the login page with other themes
* Fix - Compatibility with ShortPixel webp extention when Feed Security is enabled
* Fix - Fixed the ltrim of null error on PHP 8.1 for site_url() path
* Fix - Disable Inspect Element on Mac for S + MAC combination and listen on Inspect Element window

= 7.0.01 (12 Sept 2022)=
* Update - Added Temporary Login feature
* Fix - Not to hide the image on login page when no custom image is set in Appearance > Customize > Site Logo
* Update - Compatibility with Nicepage Builder plugin
* Update - Compatibility with WP 6.0.2

= 6.0.24 (29 July 2022)=
* Update - Add custom emulator/generator name in the website header

= 6.0.23 (25 July 2022)=
* Update - Compatibility with the last version of Flywheel including Redirects
* Fix - Don't show brute force math error for pages where the Brute Force is not loaded
* Fix - Compatibility with Breakdance plugin
* Fix - Fixed the ltrim of null error on PHP 8.1 for site_url() path

= 6.0.22 (28 June 2022)=
* Fix - URL Mapping for Nginx servers to prevent 404 pages
* Fix - PHP error in Security Check when the X-Powered-By header is not string
* Fix - Compatibility with Wp-Rocket last version

= 6.0.21 (21 June 2022)=
* Fix - infinite loop in admin panel

= 6.0.20 (03 June 2022)=
* Update - Compatibility with Coming Soon & Maintenance Mode PRO
* Update - New feature added to automatically redirect the logged users to the admin dashboard
* Update - Security Check report for minimum PHP version and visible custom login
* Fixed the hidden URLs process
* Fixed the site_url() and home_url() issue when they are different
* Add compatibility with WordPress 6.0

= 6.0.19 (19 May 2022)=
* Update - Add compatibility with Elementor Builder plugin for WP Multisite
* Update - Tested/Update Compatibilities with more themes and plugins

= 6.0.18 (03 May 2022)=
* Update - Add compatibility with LiteSpeed webp images
* Update - Update Compatibilities
* Fix - Small Bugs

= 6.0.16 (01 Mar 2022)=
* Update - Added compatibility with Backup Guard Plugin
* Update - Prevent affecting the cron processes on Wordfence & changing the paths during the cron process
* Update - Change the WP-Rocket cache files on all subsites for WP Multisite
* Update - Automatically add the CDN URL if WP_CONTENT_URL is set as a different domain
* Fixed the Change Paths for Logged Users issue

= 6.0.15 (21 Feb 2022)=
* Update - Added 7G Firewall option in Hide My WP > Change Paths > Firewall & Headers > Firewall Against Script Injection
* Update - Fixed the menu hidden issue when other security plugins are active
* Update - Compatibility with Login/Signup Popup plugin when Brute Force Google reCaptcha is activated
* Update - Compatibility with Buy Me A Cofee plugin
* Update - Automatically add the CDN URL if WP_CONTENT_URL is set as a different domain
* Fix - Change Paths for Logged Users issue when cache plugins are installed
* Fix - Library loading ID in HMWP Ghost

= 6.0.14 (07 Feb 2022)=
* Update - Security & Compatibility
* Update - Compatibility with Namecheap hosting
* Update - Compatibility with Ploi.io
* Fix - Removed the ignore option from Nginx notification
* Fix - The Security check on install.php and upgrade.php files
* Fix - The Restore to default to remove the rules from the config file

= 6.0.13 (03 Feb 2022)=
* Update - Added new option in Login Security: Hide the language switcher option on the login page
* Update - Compatibility with WordPress 5.9
* Update - Compatibility with Coming Soon & Maintenance Mode PRO
* Update - Compatibility with Advanced Access Manager (AAM) plugin
* Update - Compatibility with WPS Hide Login
* Update - Compatibility with JobCareer theme
* Fix - Popup issue when Safe Mode or Ghost Mode is selected and other plugins are modifying the bootstrap javascript
* Fix - 404 error on WordPress upgrade when access the file upgrade.php for logged users
* Fix - Brute Force blocking Wordfence Cron Job

= 6.0.12 (10 Ian 2022)=
* Update - Compatibility with Smush plugin
* Update - Compatibility with WordPress 5.8.3
* Update - Compatibility with Wordfence 2FA when reCaptcha is active
* Fix - Infinit loop when POST action on unknown paths

= 6.0.11 (08 Dec 2021)=
* Update - Added the Ctrl + Shift + C restriction when Inspect Element option is active
* Update - Added the features text for translation
* Update - Removed the WordPress title tag from login/register pages
* Update - Added the option to ignore the notifications and avoid repeating alerts
* Fix - Remove the login URL from the logo on the custom login page
* Fix - Set Filesystem to direct connection for file management

= 6.0.10 (20 Nov 2021)=
* Update - Added Permissions-Policy & Referrer-Policy default security headers
* Update - Added the option to disable Right-Click for logged users and user roles
* Update - Added the option to disable Inspect Element for logged users and user roles
* Update - Added the option to disable View Source for logged users and user roles
* Update - Added the option to disable Copy/Paste for logged users and user roles
* Update - Added the option to disable Drag/Drop for logged users and user roles
* Fix - Whitelist and Blacklist error messages in Brute Force when no IP was added
* Fix - Typos in HMWP Ghost plugin

= 6.0.09 (03 Nov 2021)=
* Fix - Remove Sitemap style from Yoast, Rank Math, XML Sitemap on Nginx servers when the option Change Paths in Sitemaps XML is active
* Update - Compatibility with Wordfence Security Scan when the wp-admin is hidden
* Update - Compatibility with the Temporary Login Without Password plugin to work with the passwordless connection on custom admin
* Update - Compatibility with the LoginPress plugin to work with the passwordless connection on custom admin
* Update - Compatibility with WordPress Sitemap, Rank Math SEO, SEOPress, XML Sitemaps to hide the paths and style on Nginx servers

= 6.0.08 (22 Oct 2021)=
* Update - Compatibility with Nitropack
* Update - Compatibility with OptimizePress Dashboard
* Update - Change the Plugin Name on update check success message
* Fix - Compact the frontend scripts for removing right click and keys
* Fix - Add links to the Change Paths page from Security Check

= 6.0.07 (18 Oct 2021)=
* Update - Select the WordPress common files you want to hide
* Update - Add the option to block comments that may lead to spam
* Update - Removed Plugins Section from Settings
* Update - Removed any affiliate links from the plugin
* Update - Compatibility with MainWP
* Update - Compatibility with Limit Login Attempts Reloaded
* Update - Compatibility with Loginizer
* Update - Compatibility with Shield Security
* Update - Compatibility with iThemes Security
* Fix - Login & Logout redirects for Woocommerce
* Fix - Don't show the rewrite alert messages if nothing was changed in HMWP 

= 6.0.06 (14 Oct 2021)=
* Update - Update the White Label options to remove plugin name, and author while the plugin is active
* Fix - Added handle when the plugin is not installed correctly
* Fix - Avoid changing the cache in the paths like plugins and themes and broke the website

= 6.0.05 (6 Oct 2021)=
* Update - Add the option to hide the wp-admin path for non-admin users
* Update - Advanced Text Mapping to work with Page Builders in admin
* Update - Changing the paths in sitemap.xml and robots.txt to work with all SEO plugins
* Update - Translate the plugin in more languages
* Update - Select the cache directory if there is a custom cache directory set in the cache plugin
* Update - Show the change in cache files option for more cache plugins
* Fix - Showing the old paths on unfound files
* Fix - Not load the Click Disable while editing with Page Builders

= 6.0.04 (1 Oct 2021)=
* Update - Use WordPress filesystem for all file actions
* Fix - Rewrite built on custom register and lostpassword path
* Fix - wp_ previx detection in Website Security Check
* Fix - plugin typos & translations

= 6.0.03 (28 Sept 2021)=
* Update - Added compatibility with JCH Optimize 3 plugin
* Update - Added compatibility with Oxygen 3.8 plugin
* Update - Added compatibility with WP Bakery plugin
* Update - Added compatibility with Bunny CDN plugin
* Update - Update compatibility with Manage WP plugin
* Update - Update compatibility with Autoptimize plugin
* Update - Update compatibility with Breeze plugin
* Update - Update compatibility with Cache Enabler plugin
* Update - Update compatibility with CDN Enabler plugin
* Update - Update compatibility with Comet Cache plugin
* Update - Update compatibility with Hummingbird plugin
* Update - Update compatibility with Hyper Cache plugin
* Update - Update compatibility with Litespeed Cache plugin
* Update - Update compatibility with Power Cache plugin
* Update - Update compatibility with W3 Total Cache plugin
* Update - Update compatibility with WP Fastest Cache plugin
* Update - Update compatibility with iThemes plugin
* Update - Added compatibility with Hummingbird Performance plugin
* Fix - Small Bugs

= 6.0.02 (21 Sept 2021)=
* Update - A new UI for Hide My WP Ghost
* Update - Added new features in the plugin
* Update - Compatibility with other plugins