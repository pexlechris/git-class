<?php
/**
 * Handles the registration of the custom taxonomy for media categories.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Media_Categories_Taxonomy {

    /**
     * The slug for the custom taxonomy.
     * @var string
     */
    const TAXONOMY_SLUG = 'media_category';

    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomy' ), 0 );
    }

    /**
     * Register the custom taxonomy.
     */
    public static function register_taxonomy() {
        $labels = array(
            'name'                       => _x( 'Media Categories', 'Taxonomy General Name', 'media-categories-filter' ),
            'singular_name'              => _x( 'Media Category', 'Taxonomy Singular Name', 'media-categories-filter' ),
            'menu_name'                  => __( 'Media Categories', 'media-categories-filter' ),
            'all_items'                  => __( 'All Media Categories', 'media-categories-filter' ),
            'parent_item'                => __( 'Parent Media Category', 'media-categories-filter' ),
            'parent_item_colon'          => __( 'Parent Media Category:', 'media-categories-filter' ),
            'new_item_name'              => __( 'New Media Category Name', 'media-categories-filter' ),
            'add_new_item'               => __( 'Add New Media Category', 'media-categories-filter' ),
            'edit_item'                  => __( 'Edit Media Category', 'media-categories-filter' ),
            'update_item'                => __( 'Update Media Category', 'media-categories-filter' ),
            'view_item'                  => __( 'View Media Category', 'media-categories-filter' ),
            'separate_items_with_commas' => __( 'Separate categories with commas', 'media-categories-filter' ),
            'add_or_remove_items'        => __( 'Add or remove categories', 'media-categories-filter' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'media-categories-filter' ),
            'popular_items'              => __( 'Popular Media Categories', 'media-categories-filter' ),
            'search_items'               => __( 'Search Media Categories', 'media-categories-filter' ),
            'not_found'                  => __( 'No media categories found.', 'media-categories-filter' ),
            'no_terms'                   => __( 'No media categories', 'media-categories-filter' ),
            'items_list'                 => __( 'Media categories list', 'media-categories-filter' ),
            'items_list_navigation'      => __( 'Media categories list navigation', 'media-categories-filter' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true, // Make it public to appear in REST API, etc.
            'show_ui'                    => true,
            'show_admin_column'          => true, // Show in the media library list view column
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false, // Usually not needed for this type of taxonomy
            'show_in_rest'               => true, // Important for Gutenberg and modern WP interfaces
            'query_var'                  => true,
        );
        register_taxonomy( self::TAXONOMY_SLUG, array( 'attachment' ), $args );
    }
}

Media_Categories_Taxonomy::init();

?>
