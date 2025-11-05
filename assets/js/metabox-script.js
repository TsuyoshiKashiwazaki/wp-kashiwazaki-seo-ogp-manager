/**
 * Kashiwazaki SEO OGP Manager - Metabox Scripts
 *
 * @package KashiwazakiSeoOgpManager
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Media uploader instance.
     */
    let mediaUploader;

    /**
     * Initialize when DOM is ready.
     */
    $(document).ready(function() {
        initMetaboxMediaUploader();
    });

    /**
     * Initialize media uploader for metabox.
     */
    function initMetaboxMediaUploader() {
        // Select image button
        $(document).on('click', '.ksom-select-image', function(e) {
            e.preventDefault();

            const button = $(this);
            const imageFieldId = ksomMetabox.imageFieldId || 'ksom_og_image';
            const inputField = $('#' + imageFieldId);
            const previewSelector = ksomMetabox.previewSelector || '.ksom-image-preview';
            const previewContainer = button.closest('.ksom-metabox-field').find(previewSelector);

            // If the media uploader already exists, reopen it.
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Create the media uploader.
            mediaUploader = wp.media({
                title: ksomMetabox.selectImage || 'Select OGP Image',
                button: {
                    text: ksomMetabox.useThisImage || 'Use This Image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When an image is selected, run a callback.
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();

                // Set the image URL to input field.
                inputField.val(attachment.url);

                // Update or create preview.
                const imgHtml = '<img src="' + attachment.url + '" alt="OGP Image Preview" />';

                if (previewContainer.length > 0) {
                    previewContainer.html(imgHtml);
                } else {
                    button.closest('.ksom-metabox-field').append(
                        '<div class="ksom-image-preview">' + imgHtml + '</div>'
                    );
                }
            });

            // Open the media uploader.
            mediaUploader.open();
        });

        // Remove image button
        $(document).on('click', '.ksom-remove-image', function(e) {
            e.preventDefault();

            const button = $(this);
            const imageFieldId = ksomMetabox.imageFieldId || 'ksom_og_image';
            const inputField = $('#' + imageFieldId);
            const previewSelector = ksomMetabox.previewSelector || '.ksom-image-preview';
            const previewContainer = button.closest('.ksom-metabox-field').find(previewSelector);

            // Clear input field
            inputField.val('');

            // Remove preview
            previewContainer.remove();
        });
    }

})(jQuery);
