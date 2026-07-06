<?php
/**
 * Uninstall handler for Shifter Future Publish.
 *
 * Removes all plugin data when the plugin is deleted from the WordPress admin.
 *
 * @package Shifter_Future_Publish
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'shifter_future_publish_settings' );
