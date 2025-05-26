/* global wp, _, mediaCategoryFilterData */
(function($) {
    'use strict';

    function extendMediaViews() {
        // console.log('Media Category Filter: Attempting to extend media views...');
        if (typeof wp === 'undefined' || 
            typeof wp.media === 'undefined' ||
            typeof wp.media.view === 'undefined' ||
            typeof wp.media.view.AttachmentsBrowser === 'undefined' ||
            typeof wp.media.view.AttachmentFilters === 'undefined' ||
            typeof wp.media.view.AttachmentFilters.Select === 'undefined') {
            // console.warn('Media Category Filter: Necessary WP Media objects not yet available for extension.');
            return;
        }

        // console.log('Media Category Filter: All necessary WP Media objects found.');

        if (typeof mediaCategoryFilterData === 'undefined') {
            // console.error('Media Category Filter: mediaCategoryFilterData object not found.');
            return;
        }
        // console.log('Media Category Filter: mediaCategoryFilterData found:', mediaCategoryFilterData);


        var originalAttachmentsBrowser = wp.media.view.AttachmentsBrowser;
        // console.log('Media Category Filter: Extending wp.media.view.AttachmentsBrowser...');
        wp.media.view.AttachmentsBrowser = originalAttachmentsBrowser.extend({
            createToolbar: function() {
                // console.log('Media Category Filter: AttachmentsBrowser.createToolbar called.');
                originalAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);

                var filters = this.toolbar.get('filters');
                if (filters && mediaCategoryFilterData.terms.length > 0) {
                    // console.log('Media Category Filter: Adding taxonomy filter to toolbar.');
                    if (typeof wp.media.view.AttachmentFilters.Select === 'undefined') {
                        // console.error('Media Category Filter: CRITICAL - AttachmentFilters.Select undefined just before instantiation!');
                        return;
                    }
                    var taxonomyFilter = new wp.media.view.AttachmentFilters.Select({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -75,
                        label: mediaCategoryFilterData.select_label || 'Filter by Category',
                        property: mediaCategoryFilterData.taxonomy_slug,
                        options: (function() {
                            var options = {};
                            options['all'] = mediaCategoryFilterData.all_categories_label || 'All Categories';
                            _.each(mediaCategoryFilterData.terms, function(term) {
                                options[term.slug] = term.name;
                            });
                            return options;
                        })()
                    });
                    this.toolbar.set(mediaCategoryFilterData.taxonomy_slug + '-filter', taxonomyFilter);
                    // console.log('Media Category Filter: Taxonomy filter added to toolbar.');
                } else {
                    // console.log('Media Category Filter: No terms or no filters object, not adding taxonomy filter.');
                }
            }
        });

        var originalMediaFramePost = wp.media.view.MediaFrame.Post;
        if (originalMediaFramePost) {
            // console.log('Media Category Filter: Extending wp.media.view.MediaFrame.Post...');
            wp.media.view.MediaFrame.Post = originalMediaFramePost.extend({
                initialize: function() {
                    // console.log('Media Category Filter: MediaFrame.Post.initialize called.');
                    originalMediaFramePost.prototype.initialize.apply(this, arguments);

                    if (mediaCategoryFilterData.terms.length > 0) {
                        var self = this;
                        if (!this.states) {
                            // console.warn('Media Category Filter: MediaFrame.Post - this.states not found.');
                            return;
                        }
                        this.states.each(function(state) {
                            if (state.id === 'library' || (state.props && state.props.get('id') === 'library')) {
                                // console.log('Media Category Filter: MediaFrame.Post - Modifying library state props for taxonomy:', mediaCategoryFilterData.taxonomy_slug);
                                var originalProps = state.props.toJSON();
                                originalProps[mediaCategoryFilterData.taxonomy_slug] = 'all';
                                state.props.set(originalProps);

                                if (state.get('library') && state.get('library').props) {
                                   state.get('library').props.on('change:' + mediaCategoryFilterData.taxonomy_slug, function(model, value) {
                                       // console.log('Media Category Filter: MediaFrame.Post - Detected change in taxonomy filter value:', value);
                                       var newProps = {};
                                       newProps[mediaCategoryFilterData.taxonomy_slug] = value;
                                       state.props.set(newProps);
                                   });
                                }
                            }
                        });
                    }
                }
            });
        } else {
            // console.warn('Media Category Filter: wp.media.view.MediaFrame.Post not found, not extending.');
        }
        // console.log('Media Category Filter: WP Media views extension process completed.');
    }

    $(document).ready(function() {
        // console.log('Media Category Filter: Document ready.');
        if (typeof wp !== 'undefined' && typeof wp.media !== 'undefined' && wp.media.frame) {
            // console.log('Media Category Filter: wp.media.frame found on document ready. Extending views directly.');
            extendMediaViews();
        } else {
            // console.log('Media Category Filter: wp.media.frame not found on document ready. Starting polling mechanism.');
            var counter = 0;
            var interval = setInterval(function() {
                // console.log('Media Category Filter: Polling attempt #', counter + 1);
                if (typeof wp !== 'undefined' && 
                    typeof wp.media !== 'undefined' && 
                    typeof wp.media.view !== 'undefined' && 
                    typeof wp.media.view.AttachmentsBrowser !== 'undefined' &&
                    typeof wp.media.view.AttachmentFilters !== 'undefined' &&
                    typeof wp.media.view.AttachmentFilters.Select !== 'undefined') {
                    clearInterval(interval);
                    // console.log('Media Category Filter: Necessary WP Media objects found after polling. Extending views.');
                    extendMediaViews();
                } else if (counter >= 29) { // Try for 30 attempts (3 seconds total: 0-29 attempts)
                    clearInterval(interval); // Give up
                    // console.error('Media Category Filter: Could not initialize extensions after 3 seconds, WP Media objects still not found.');
                }
                counter++;
            }, 100); // Check every 100ms
        }
    });

})(jQuery);
