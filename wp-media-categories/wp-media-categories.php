<?php
/**
 * Plugin Name:       Media Categories Filter
 * Plugin URI:        https://example.com/plugins/media-categories-filter/
 * Description:       Adds a custom taxonomy to categorize media items and provides filters in the media library and modal.
 * Version:           1.0.1
 * Author:            Your Name or Company
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       media-categories-filter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Define constants
 */
define( 'MEDIA_CATEGORIES_VERSION', '1.0.1' );
define( 'MEDIA_CATEGORIES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MEDIA_CATEGORIES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the taxonomy class
require_once MEDIA_CATEGORIES_PLUGIN_DIR . 'includes/class-media-taxonomy.php';

// Include the admin list filter class
require_once MEDIA_CATEGORIES_PLUGIN_DIR . 'includes/class-admin-list-filter.php';

// Include the media modal filter class
require_once MEDIA_CATEGORIES_PLUGIN_DIR . 'includes/class-media-modal-filter.php';

/**
 * Enqueue scripts and styles.
 */
function media_categories_enqueue_assets() {
    // Enqueue the main stylesheet for the plugin (for admin area)
    if ( is_admin() ) {
        wp_enqueue_style(
            'media-categories-filter-admin-css',
            MEDIA_CATEGORIES_PLUGIN_URL . 'assets/css/media-filters.css',
            array(),
            MEDIA_CATEGORIES_VERSION
        );
    }
    // Note: The media modal specific JS is enqueued in class-media-modal-filter.php
    // If that class also needed CSS, it could enqueue it there or we could do it here.
}
add_action( 'admin_enqueue_scripts', 'media_categories_enqueue_assets' );

/**
 * Plugin activation hook.
 * Flushes rewrite rules to ensure taxonomy archives work correctly.
 */
function media_categories_activate() {
    // Ensure our taxonomy is registered before flushing
    // This might require including the taxonomy class if not already loaded,
    // but given our current structure, it should be by the time 'init' runs.
    // For activation, it's safer to explicitly register if needed, or ensure loading.
    // However, Media_Categories_Taxonomy::init() hooks into 'init',
    // and activation hooks run *after* plugins are loaded but *before* 'init' for the current request.
    // A common practice is to just call the registration function directly.

    Media_Categories_Taxonomy::register_taxonomy(); // Call directly for activation context
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'media_categories_activate' );

/**
 * Plugin deactivation hook.
 * (Optional: Add any cleanup needed on deactivation)
 */
function media_categories_deactivate() {
    // Placeholder for any deactivation logic
    // For example, if we were storing plugin options and wanted to remove them.
    // For this plugin, flushing rewrite rules on deactivation isn't strictly necessary
    // as WordPress will rebuild them eventually.
    // flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'media_categories_deactivate' );

?>
