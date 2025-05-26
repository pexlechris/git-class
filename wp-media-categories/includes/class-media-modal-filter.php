<?php
/**
 * Handles adding the media category filter to the WordPress Media Modal.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Media_Categories_Modal_Filter {

    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'wp_enqueue_media', array( __CLASS__, 'enqueue_modal_scripts' ) );
        add_filter( 'ajax_query_attachments_args', array( __CLASS__, 'filter_ajax_query_attachments_args' ), 10, 1 );
    }

    /**
     * Enqueue JavaScript and localize data for the media modal.
     */
    public static function enqueue_modal_scripts() {
        // Only enqueue if we are actually going to display the media modal
        // wp_enqueue_media is a good hook for this.

        $taxonomy_slug = Media_Categories_Taxonomy::TAXONOMY_SLUG;
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy_slug,
            'hide_empty' => false,
        ) );

        $media_categories = array();
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $media_categories[] = array(
                    'slug' => $term->slug,
                    'name' => $term->name,
                );
            }
        }

        // Register the script
        wp_register_script(
            'media-modal-category-filter-js',
            MEDIA_CATEGORIES_PLUGIN_URL . 'assets/js/media-modal-filter.js',
            array( 'media-views' ), // Dependency: media-views (Backbone for media)
            MEDIA_CATEGORIES_VERSION,
            true // Load in footer
        );

        // Localize data to be used by the script
        wp_localize_script(
            'media-modal-category-filter-js',
            'mediaCategoryFilterData', // Object name in JavaScript
            array(
                'terms'           => $media_categories,
                'taxonomy_slug'   => $taxonomy_slug,
                'select_label'    => __( 'Filter by Media Category', 'media-categories-filter' ),
                'all_categories_label' => __( 'All Media Categories', 'media-categories-filter' ),
            )
        );

        // Enqueue the script
        wp_enqueue_script( 'media-modal-category-filter-js' );

        // Optionally, enqueue a dedicated CSS file for the modal filter here if needed later
        // wp_enqueue_style('media-modal-category-filter-css', MEDIA_CATEGORIES_PLUGIN_URL . 'assets/css/media-modal-filter.css', array(), MEDIA_CATEGORIES_VERSION);
    }

    /**
     * Modify the AJAX query arguments for attachments in the media modal.
     * If our custom taxonomy filter is set to 'all', remove it from tax_query.
     *
     * @param array $query The query variables for attachments.
     * @return array Modified query variables.
     */
    public static function filter_ajax_query_attachments_args( $query ) {
        $taxonomy_slug = Media_Categories_Taxonomy::TAXONOMY_SLUG;

        if ( isset( $query[$taxonomy_slug] ) && $query[$taxonomy_slug] === 'all' ) {
            // Remove our specific taxonomy query if 'all' is selected,
            // to prevent WP_Query from searching for a term with slug 'all'.
            if ( isset( $query['tax_query'] ) ) {
                $new_tax_query = array();
                foreach ( $query['tax_query'] as $tax_condition ) {
                    if ( isset( $tax_condition['taxonomy'] ) && $tax_condition['taxonomy'] === $taxonomy_slug ) {
                        // Skip this condition
                        continue;
                    }
                    $new_tax_query[] = $tax_condition;
                }
                if (empty($new_tax_query)) {
                    unset($query['tax_query']);
                } else {
                    $query['tax_query'] = $new_tax_query;
                }
            }
            // Also unset the direct query var if it exists
            unset( $query[$taxonomy_slug] );
        }
        return $query;
    }
}

Media_Categories_Modal_Filter::init();

?>
