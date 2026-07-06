=== Shifter Future Publish ===
Contributors: digitalcube
Tags: shifter, future, publish, schedule, static site
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 2.1.4
Requires PHP: 8.1
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
* Simple and efficient architecture

= How It Works =

This plugin uses a simple 2-layer architecture to ensure future-dated posts are treated as published:

1. **Post Save Interception (Primary)** - Uses the `wp_insert_post_data` filter to change the status from "future" to "publish" before saving to the database.
2. **Future Post Hooks (Fallback)** - Post-type-specific hooks (`future_{post_type}`) handle edge cases.

= Important: Content Exposure =

When enabled, posts of the selected post types saved with a future date get real "publish" status and are visible everywhere on the front end. This includes not only archive and single pages, but also RSS/Atom feeds and the public REST API (e.g. `/wp-json/wp/v2/posts`), where the content becomes visible to anonymous visitors and search engines immediately. Make sure your content is ready to be public before saving it with a future date.

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

When you publish a post with a future date, WordPress normally sets the post status to "future" and schedules it for publication at the specified date. This plugin intercepts that process using the `wp_insert_post_data` filter to change the status to "publish" before saving to the database, making it immediately visible while keeping the future date.

= Will this affect my existing scheduled posts? =

No, this plugin only affects posts that are saved after the plugin is activated. Existing scheduled posts will remain scheduled.

= Can I choose which post types this applies to? =

Yes, you can configure which post types should allow future date publishing in the plugin settings page (Settings > Shifter Future Publish).

= Is this plugin compatible with Shifter? =

Yes, this plugin is specifically designed to work with Shifter static site generation. Posts with future dates will be included in the generated artifacts.

= Are future posts exposed in feeds and the REST API? =

Yes. Future-dated posts of the enabled post types are published immediately, so they are included in RSS/Atom feeds and in public REST API responses (e.g. `/wp-json/wp/v2/posts`), just like any other published post. Do not use future dates for content that must stay private until its date.

= What happens if I disable the plugin? =

When the plugin is disabled, WordPress will revert to its default behavior. New posts with future dates will be scheduled as usual. Existing posts that were published with future dates will remain published.

== Screenshots ==

1. Plugin settings page

== Changelog ==

= 2.1.4 =
## What's Changed
* Fix fatal error and no-op hook removal, harden security and safety by @plastikdreams in https://github.com/digitalcube/Shifter-Future-Publish/pull/20
* chore: Bump version to 2.1.4 by @plastikdreams in https://github.com/digitalcube/Shifter-Future-Publish/pull/21


**Full Changelog**: https://github.com/digitalcube/Shifter-Future-Publish/compare/v2.1.3...v2.1.4
= 2.1.4 =
* Fixed: `remove_action()` for `_future_post_hook` did not specify the priority core registers it with (5), so the removal was a no-op and left stale `publish_future_post` cron events
* Fixed: `setup_future_hooks()` now runs at `init` priority 999 so custom post types registered on `init` are covered
* Fixed: `SHIFTER_FUTURE_PUBLISH_VERSION` constant was stuck at 2.0.5 regardless of the plugin version, so browsers/CDNs could not detect changes to the enqueued JS assets
* Added: `uninstall.php` to delete plugin settings on uninstall
* Improved: Escaped the settings page link URL with `esc_url()`
* Docs: Clarified that future-dated posts of enabled post types are exposed in RSS/Atom feeds and the public REST API

= 2.1.3 =
* refactor: Delete root Composer files and PHPStan config, and update the CI workflow to execute Composer and static analysis within the `_tests` directory. by @tekapo in https://github.com/digitalcube/Shifter-Future-Publish/pull/19

**Full Changelog**: https://github.com/digitalcube/Shifter-Future-Publish/compare/v2.1.2...v2.1.3
= 2.1.2 =
## What's Changed
* feat: Auto-update changelog from GitHub Release Notes by @devin-ai-integration[bot] in https://github.com/digitalcube/Shifter-Future-Publish/pull/17


**Full Changelog**: https://github.com/digitalcube/Shifter-Future-Publish/compare/v2.1.1...v2.1.2
= 2.1.0 =
* Major: Simplified codebase architecture (5 layers to 2 layers)
* Removed: Redundant fallback mechanisms (get_post_status, the_posts, posts_where filters)
* Improved: JavaScript files significantly reduced (editor.js: 76%, classic-editor.js: 75%)
* Added: PHPStan (level max) compliance for better type safety
* Added: PHPCS (WordPress Coding Standards) compliance
* Added: PHPDoc comments for all classes and methods
* Fixed: `_future_post_hook()` call to pass correct parameters per WordPress API

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

= 2.1.4 =
Recommended bug-fix release. Fixes a cache-busting bug (version constant was stuck at 2.0.5) and a no-op hook removal that left stale cron events. No breaking changes.

= 2.1.0 =
Major simplification of the codebase. Removed redundant fallback mechanisms for a cleaner, more maintainable architecture. No breaking changes - all existing functionality preserved.

= 2.0.0 =
Major update requiring PHP 8.0+. Complete rewrite using modern PHP 8 features for better performance and type safety. Required for Shifter environments running PHP 8.

= 1.2.0 =
Bug fixes and improvements based on code review. Recommended upgrade for better stability.

= 1.1.0 =
Enhanced version with multiple layers of protection for future post handling. Recommended upgrade for better compatibility.

= 1.0.0 =
Initial release of Shifter Future Publish.
