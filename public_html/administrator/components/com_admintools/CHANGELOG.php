<?php die() ?>
Admin Tools 4.0.1
================================================================================
! YOU MUST UPDATE TO THIS RELEASE MANUALLY. The Download ID was not registered with Joomla, making updates from 4.0.0.b1, 4.0.0.b2, 4.0.0.rc1 and 4.0.0 impossible.
# [MEDIUM] Master password does not work with passwords containing special characters
# [MEDIUM] Configure Permissions does not work with folder names containing special characters
# [MEDIUM] Administrator Password Protection does not work with passwords containing special characters
# [MEDIUM] PHP File Scanner's front-end scanning feature does not work with secret keys containing special characters
# [LOW] If you were living under a rock and hadn't updated to Admin Tools 3.6 or later the last 2 years you may get a fatal error after update to 4.0
# [LOW] Layout / Javascript issues with IE11 on the Configure WAF page

Admin Tools 4.0.0
================================================================================
+ Improved performance while importing settings with thousands of IP addresses
+ Added option to display hidden files in Permissions Configuration page
# [LOW] Missing language key for user signup notes

Admin Tools 4.0.0.rc1
================================================================================
~ Changing the #__akeeba_common table schema
~ Exceptions from WAF are not available on broken servers which don't set the HTTP_HOST environment variable
# [HIGH] The PHP File Change Scanner front-end scheduling feature was broken
# [HIGH] The Professional package was missing the CLI scripts
# [MEDIUM] The Quick Setup Wizard would claim to have already run even on fresh installations (thanks @brianteeman)
# [LOW] Missing language strings
# [LOW] PHP File Change Scanner emails (from CLI) displayed the HTML markup instead of formatted text
# [LOW] The “Reload update information” button in the control panel page resulted in an error
# [LOW] Incorrect language strings, thank you @brianteeman
# [LOW] The warnings about the IP white/blacklist being disabled were displayed under the wrong conditions
# [LOW] PHP notices on installation from missing method arguments

Admin Tools 4.0.0.b1
================================================================================
+ Rewritten using FOF 3.0
+ Improved detection and removal of duplicate update sites
+ You can export/import WAF Blacklist and Exceptions (always forbidden and always allowed component/view/task access)
+ Highlight the suspicious and malicious matches on the file source in the PHP File Change Scanner results
+ IP whitelist will warn you when the feature is not enabled
+ IP blacklist will warn you when the feature is not enabled
+ .htaccess / nginx.conf / web.config maker: added .well-known to the default list of allowed access folders for non-PHP files
+ Enabling HSTS in .htaccess Maker will now also avoid unsafe (HTTP) redirections wherever possible and not send the HSTS header over plain HTTP. Does not apply to NginX Conf and web.config Makers. PLEASE READ THE DOCUMENTATION!
+ URL Redirection is now available in the free of charge Core release
~ Extremely conservative .htaccess Maker settings applied by the Quick Setup Wizard because people don't bother reading the big, fat warning above the apply button
~ Joomla! 3.6 has moved the logs folder inside /administrator. Our software is now adjusted for this change.
~ Warn about eAccelerator
~ Warn about end of life PHP versions
~ Remove obsolete FOF 2.x update site if it exists
# [LOW] Joomla bug would cause URL redirections to issue HTTP 303 See other (temporary) redirection instead of 301 Moved (permanent) redirection
# [HIGH] WAF Blacklist with RegEx matching would block all requests all the time
# [HIGH] Joomla! "Conservative" cache bug: you could not enter the Download ID when prompted
# [HIGH] Joomla! "Conservative" cache bug: you could not apply the proposed Secret Word when prompted
# [HIGH] Joomla! "Conservative" cache bug: component Options (e.g. Download ID, Secret Word, front-end file scanner feature) would be forgotten on the next page load
