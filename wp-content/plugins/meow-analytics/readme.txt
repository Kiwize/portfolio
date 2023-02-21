=== Meow Analytics (Google Analytics) ===
Contributors: TigrouMeow
Tags: google, analytics, dashboard, google analytics, meowapps
Donate link: https://meowapps.com/donation/
Requires at least: 5.2
Tested up to: 6.0.1
Requires PHP: 7.0
Stable tag: 1.1.2

Adds the Google Analytics (GA4 included) code to your website and a little dashboard with realtime and historical data. 

== Description ==

Adds the Google Analytics (GA4 included) code to your website and a little dashboard with realtime and historical data. It's discrete, light, and... just works! 🥰 Please let me know your issues. I will improve it based on your feedback.

== Why another analytics plugin? ==

Mainly two reasons:

If like me, you are tired of all those Google Analytics plugins with too many useless features, too much code, and coded like if we were still in the neolithic era, you will love this plugin! 💫

Did another plugin asked you for the permission to access your data through its own third-party service? Personally, I don't like sharing my data with third-parties, and I don't want to be dependent on them neither. Please check your [Google Permissions](https://myaccount.google.com/permissions)! You might want to remove some access your granted. Meow Analytics works differently, and connects you directly to Google Analytics.

=== Dashboard ===

You can access to some of your analytics data directly in your WordPress dashboard (realtime and historical data). If you wish to do this, [follow this tutorial](https://meowapps.com/plugin/meow-analytics/).

=== Usage ===

Visit the Settings under Meow Apps > Analytics, enter your Google Analytics code, and that's it. For more, please check the official page of the plugin: [Meow Analytics](https://meowapps.com/plugin/meow-analytics/).

Languages: English.

== Installation ==

1. Upload `meow-analytics` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

Replace all the files. Nothing else to do.

== Changelog ==

= 1.1.2 (2022/08/31) =
* Add: Extra IDs (for Google Ads, for example).

= 1.1.1 (2022/08/21) =
* Add: Possibility to track logged-user without administrator and editors.

= 1.1.0 (2022/08/15) =
* Update: Textual clarifications.

= 1.0.9 (2022/07/27) =
* Update: Compatibility with WP 6.
* Update: UI enhancements.
* Note: The plugin has been perfectly stable for a while now :) Feel free to use it on all your websites (I already do!). And if you are happy with Meow Analytics, thanks a lot for [writing a little review for it](https://wordpress.org/support/plugin/meow-analytics/reviews/?rate=5#new-post).

= 1.0.8 (2022/02/17) =
* Update: Admin and compatibility WP 3.9.

= 1.0.7 (2021/09/23) =
* Update: Admin and common files updated.
* Fix: Analytics was not being displayed on the dashboard for new installs.

= 1.0.1 =
* Update: Brand new admin, cleaner and more dynamic.

= 0.1.2 =
* Update: Seems like replacing Lodash was killing the Dashboard (more exactly, chartjs), but that was only happening when another lodash was used somewhere else. Weird issue, but hopefully that is fixed.

= 0.1.1 =
* Update: Support for new WP.
* Update: Little code cleaning.

= 0.1.0 =
* Add: Today's historical data per hour.

= 0.0.8 =
* Fix: Better management of errors for the dashboard.
* Update: Perform less requests per day to avoid to reach the maximum quota.

= 0.0.7 =
* Fix: Now renew the token successfully and re-use it in realtime in the dashboard.
* Update: Many improvements in the code.
* Add: Option to track the logged-in users.

= 0.0.5 =
* Update: Much better success/error checks, with messages.
* Fix: Long token doesn't work on the JS side, so I am using short token now, which are renewed automatically when needed.
* Info: Sorry for the regular updates; I am working on making the base of this plugin really strong, with good error checks. Please contact me if you have any issue or feedback.
* Note: If you are happy with Meow Analytics and its direction, and would like to give me more motivation, please [write a little review for Meow Analytics](https://wordpress.org/support/plugin/meow-analytics/reviews/?rate=5#new-post). Thank you so much.

= 0.0.3 =
* Update: Simplified and enhanced the process of linking the website with Google Analytics.
* Info: Any issue, please let me know. I would like to make this perfect and easy for everyone :)

= 0.0.2 =
* Add: Realtime/Historical tabs with dynamic charts.
* Update: Much better flow for auth, with error management.
* Update: Optimized the JS bundles to use React from WordPress, and many optimizations.

= 0.0.1 =
* First release.

== Screenshots ==

1. Settings.
2. Realtime data.
3. Historical chart.
