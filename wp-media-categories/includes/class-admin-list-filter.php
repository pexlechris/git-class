<?php
/**
 * Adds filtering capabilities to the Media Library list view (upload.php)
 * based on the custom media category.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Media_Categories_Admin_List_Filter {

    /**
     * Initialize hooks.
     */
    public static function init() {
        // Add the filter dropdown to the media library
        add_action( 'restrict_manage_posts', array( __CLASS__, 'add_media_category_filter_dropdown' ) );
        // Modify the query to filter by the selected category
        add_action( 'parse_query', array( __CLASS__, 'filter_media_by_category_query' ) );
    }

    /**
     * Display category filter dropdown on media library page.
     *
     * @param string $post_type The current post type.
     */
    public static function add_media_category_filter_dropdown( $post_type ) {
        // Only apply to the 'attachment' post type (Media Library)
        if ( 'attachment' !== $post_type ) {
            return;
        }

        $taxonomy_slug = Media_Categories_Taxonomy::TAXONOMY_SLUG;
        $selected_category = isset( $_GET[$taxonomy_slug] ) ? sanitize_text_field( $_GET[$taxonomy_slug] ) : '';

        wp_dropdown_categories( array(
            'show_option_all' => __( 'All Media Categories', 'media-categories-filter' ),
            'taxonomy'        => $taxonomy_slug,
            'name'            => $taxonomy_slug,
            'orderby'         => 'name',
            'selected'        => $selected_category,
            'hierarchical'    => true,
            'show_count'      => true,
            'hide_empty'      => false, // Show categories even if they have no media yet
        ) );
    }

    /**
     * Modify the main query to filter attachments by the selected media category.
     *
     * @param WP_Query $query The WP_Query instance.
     */
    public static function filter_media_by_category_query( $query ) {
        global $pagenow;
        $taxonomy_slug = Media_Categories_Taxonomy::TAXONOMY_SLUG;

        // Check if we are on the upload.php page and it's the main query
        if ( $query->is_main_query() && 'upload.php' === $pagenow ) {
            $selected_category = isset( $_GET[$taxonomy_slug] ) ? sanitize_text_field( $_GET[$taxonomy_slug] ) : '';

            if ( ! empty( $selected_category ) ) {
                $query->set( 'tax_query', array(
                    array(
                        'taxonomy' => $taxonomy_slug,
                        'field'    => 'slug', // or 'term_id' if you store term_id in selected
                        'terms'    => $selected_category,
                    ),
                ) );
            }
        }
    }
}

Media_Categories_Admin_List_Filter::init();

?>
