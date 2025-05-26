/* global wp, _, mediaCategoryFilterData */
(function($) {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        // WordPress media scripts not loaded
        return;
    }

    $(document).ready(function() {
        // Ensure mediaCategoryFilterData is available
        if (typeof mediaCategoryFilterData === 'undefined') {
            // console.error('Media Category Filter: Data object not found.');
            return;
        }

        var originalAttachmentsBrowser = wp.media.view.AttachmentsBrowser;
        if (!originalAttachmentsBrowser) {
            // console.error('Media Category Filter: wp.media.view.AttachmentsBrowser not found.');
            return;
        }

        wp.media.view.AttachmentsBrowser = originalAttachmentsBrowser.extend({
            createToolbar: function() {
                // Call the original createToolbar
                originalAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);

                // Add our custom filter dropdown
                var filters = this.toolbar.get('filters');
                if (filters && mediaCategoryFilterData.terms.length > 0) {
                    var taxonomyFilter = new wp.media.view.AttachmentFilters.Select({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -75, // Negative numbers for left, positive for right. Adjust as needed.
                        label: mediaCategoryFilterData.select_label || 'Filter by Category',
                        property: mediaCategoryFilterData.taxonomy_slug, // This will be the query var
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
                }
            }
        });

        // Additionally, if you want the filter to appear in the "Insert Media" screen's left sidebar
        // (where "Uploaded to this post", "Images", "Video" etc. appear)
        // This is a bit more involved as it modifies the router if not present.

        var originalMediaFrame = wp.media.view.MediaFrame.Post;
        if (originalMediaFrame) {
            wp.media.view.MediaFrame.Post = originalMediaFrame.extend({
                initialize: function() {
                    originalMediaFrame.prototype.initialize.apply(this, arguments);

                    if (mediaCategoryFilterData.terms.length > 0) {
                        var self = this;
                        // Ensure states are present
                        if (!this.states) {
                            return;
                        }
                        
                        this.states.each(function(state) {
                            // We are interested in the 'library' state, which handles attachments browsing
                            if (state.id === 'library' || (state.props && state.props.get('id') === 'library')) {
                                var originalProps = state.props.toJSON();
                                originalProps[mediaCategoryFilterData.taxonomy_slug] = 'all'; // Default to 'all'
                                state.props.set(originalProps);

                                // This is a bit of a hack to make sure the query args update
                                // when the filter is changed from the toolbar.
                                // The AttachmentsBrowser toolbar filter directly modifies `this.collection.props`.
                                // The state needs to be aware of this for subsequent queries (e.g., search).
                                if (state.get('library') && state.get('library').props) {
                                   state.get('library').props.on('change:' + mediaCategoryFilterData.taxonomy_slug, function(model, value) {
                                       // When our custom filter changes, update the state's library props.
                                       // This helps ensure that if other actions (like search) reset parts of the query,
                                       // our taxonomy filter selection is maintained.
                                       var newProps = {};
                                       newProps[mediaCategoryFilterData.taxonomy_slug] = value;
                                       state.props.set(newProps);

                                       // Trigger a refresh of the content
                                       // This might not be strictly necessary if the toolbar filter already handles it well.
                                       // self.content.get().collection.props.set(mediaCategoryFilterData.taxonomy_slug, value);
                                       // self.content.get().collection.more(); // This might fetch more without resetting, adjust as needed
                                   });
                                }
                            }
                        });
                    }
                }
            });
        }
    });

})(jQuery);
