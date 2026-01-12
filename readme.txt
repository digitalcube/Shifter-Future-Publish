=== Shifter Future Publish ===
Contributors: digitalcube
Tags: shifter, future, publish, schedule, static site
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 2.0.5
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows publishing posts with future dates immediately. Useful for Shifter static site generation to include future-dated content in artifacts.

== Description ==

Shifter Future Publish is a WordPress plugin that allows you to publish posts with future dates immediately, rather than scheduling them for later publication.

This plugin is particularly useful for Shifter static site generation, where you want future-dated content to be included in the generated artifacts. By default, WordPress sets posts with future dates to "future" status, which means they won't be included in Shifter's static site generation. This plugin changes that behavior by setting the status to "publish" instead.

= Features =

* Publish posts with future dates immediately
* Configure which post types should allow future date publishing
* Simple admin settings page
* Compatible with Shifter static site generation
* Multiple layers of protection to ensure future posts are treated as published

= How It Works =

This plugin uses multiple layers to ensure future-dated posts are treated as published:

1. **Post Save Interception** - When saving a post with a future date, the status is changed from "future" to "publish" before saving to the database.
2. **Future Post Hooks** - Post-type-specific hooks ensure any posts that slip through are immediately published.
3. **Status Filter** - The get_post_status filter ensures future posts appear as published in all contexts.
4. **Query Modification** - SQL queries are modified to include future posts in archive and listing pages.
5. **404 Prevention** - Single post pages for future posts will not return 404 errors.

= Use Cases =

* Event websites where you want to display upcoming events with future dates
* News sites that want to prepare content with future publication dates but make it visible immediately
* Any site using Shifter that needs future-dated content in static artifacts

== Installation ==

1. Upload the `shifter-future-publish` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Shifter Future Publish to configure the plugin
4. Select which post types should allow future date publishing

== Frequently Asked Questions ==

= How does this plugin work? =

When you publish a post with a future date, WordPress normally sets the post status to "future" and schedules it for publication at the specified date. This plugin intercepts that process using multiple layers (post save interception, future post hooks, status filters, query modification, and 404 prevention) to ensure the post is treated as "publish" status, making it immediately visible while keeping the future date.

= Will this affect my existing scheduled posts? =

No, this plugin only affects posts that are saved after the plugin is activated. Existing scheduled posts will remain scheduled.

= Can I choose which post types this applies to? =

Yes, you can configure which post types should allow future date publishing in the plugin settings page (Settings > Shifter Future Publish).

= Is this plugin compatible with Shifter? =

Yes, this plugin is specifically designed to work with Shifter static site generation. Posts with future dates will be included in the generated artifacts.

= What happens if I disable the plugin? =

When the plugin is disabled, WordPress will revert to its default behavior. New posts with future dates will be scheduled as usual. Existing posts that were published with future dates will remain published.

== Screenshots ==

1. Plugin settings page

== Changelog ==

= 2.0.5 =
* Fixed: While loop assignment syntax for better code clarity
* Fixed: Prevent multiple event listener registrations
* Added: i18n support for button text strings
* Improved: Changed setInterval from 1s to 3s for better performance
* Fixed: Date comparison logic with proper parseInt parsing
* Fixed: Removed unnecessary conditional in PHP enqueue function
* Fixed: Removed unused wp-element dependency
* Added: Error logging for publish_future_post_now function

= 2.0.4 =
* Added: Classic Editor support - "Schedule" button now changes to "Publish" for future-dated posts
* Improved: Both Gutenberg and Classic Editor now have consistent button text behavior

= 2.0.3 =
* Added: Block editor script to change "Schedule" button to "Publish" for future-dated posts
* Improved: Better user experience in Gutenberg editor for enabled post types

= 2.0.2 =
* Fixed: Changed SELECT * to explicit column list for security
* Fixed: Changed is_single property access to is_single() method call
* Fixed: Clone WP_Post objects instead of direct modification
* Fixed: Changed preg_replace error handling from ?? to ?: operator
* Improved: Convert stdClass to WP_Post for proper type handling

= 2.0.1 =
* Fixed: Removed named arguments from WordPress API calls (not supported by WordPress core functions)

= 2.0.0 =
* PHP 8.0+ required - complete rewrite using PHP 8 features
* Added strict types declaration
* Using typed properties and return types throughout
* Using first-class callable syntax for hook callbacks
* Using match expressions for cleaner conditionals
* Using union types for better type safety
* Using null coalescing assignment operator
* Using named arguments for WordPress API calls
* Using arrow functions for simple callbacks
* Class marked as final for better encapsulation
* Improved PHPDoc annotations with generics

= 1.2.0 =
* Fixed bug in show_future_posts() that was re-executing queries without including future posts
* Fixed get_post_status filter to skip admin area for proper admin behavior
* Improved SQL WHERE clause modification to handle more patterns
* Added proper constant definition checks to prevent redefinition errors
* Improved post object type checking in filter_post_status()
* Code review and bug fixes

= 1.1.0 =
* Added multiple layers of protection for future post handling
* Added post-type-specific future hooks (future_{post_type}) for better compatibility
* Added get_post_status filter to ensure future posts appear as published
* Added the_posts filter to prevent 404 errors on single future post pages
* Added posts_where filter to include future posts in archive queries
* Improved settings page with "How it works" documentation
* Code refactoring and optimization

= 1.0.0 =
* Initial release
* Core functionality to publish posts with future dates
* Admin settings page to configure post types
* Compatible with WordPress 6.7

== Upgrade Notice ==

= 2.0.0 =
Major update requiring PHP 8.0+. Complete rewrite using modern PHP 8 features for better performance and type safety. Required for Shifter environments running PHP 8.

= 1.2.0 =
Bug fixes and improvements based on code review. Recommended upgrade for better stability.

= 1.1.0 =
Enhanced version with multiple layers of protection for future post handling. Recommended upgrade for better compatibility.

= 1.0.0 =
Initial release of Shifter Future Publish.
