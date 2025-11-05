/**
 * Kashiwazaki SEO OGP Manager - Admin Scripts
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
        initMediaUploader();
        initPostTypeSelector();
        initRobotsDuplicateCheck();
    });

    /**
     * Initialize media uploader for settings page.
     */
    function initMediaUploader() {
        // Settings page - Default image upload
        $('.ksom-upload-image-button').on('click', function(e) {
            e.preventDefault();

            const button = $(this);
            const inputField = $('#ksom_default_image');
            const previewContainer = button.closest('.ksom-image-upload').find('.ksom-image-preview');

            // If the media uploader already exists, reopen it.
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Create the media uploader.
            mediaUploader = wp.media({
                title: ksomAdmin.selectImage || 'Select Image',
                button: {
                    text: ksomAdmin.useThisImage || 'Use This Image'
                },
                multiple: false
            });

            // When an image is selected, run a callback.
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();

                // Set the image URL to input field.
                inputField.val(attachment.url);

                // Update or create preview.
                if (previewContainer.length > 0) {
                    previewContainer.html('<img src="' + attachment.url + '" alt="Default OGP Image" style="max-width: 300px; height: auto; margin-top: 10px;" />');
                } else {
                    button.closest('.ksom-image-upload').append(
                        '<div class="ksom-image-preview"><img src="' + attachment.url + '" alt="Default OGP Image" style="max-width: 300px; height: auto; margin-top: 10px;" /></div>'
                    );
                }
            });

            // Open the media uploader.
            mediaUploader.open();
        });

        // Remove image button
        $('.ksom-remove-image-button').on('click', function(e) {
            e.preventDefault();

            const button = $(this);
            const inputField = $('#ksom_default_image');
            const previewContainer = button.closest('.ksom-image-upload').find('.ksom-image-preview');

            // Clear input field
            inputField.val('');

            // Remove preview
            previewContainer.remove();
        });
    }

    /**
     * Initialize post type selector buttons.
     */
    function initPostTypeSelector() {
        // Select all button
        $('.ksom-select-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.ksom-post-type-checkbox').prop('checked', true);
        });

        // Deselect all button
        $('.ksom-deselect-all-post-types').on('click', function(e) {
            e.preventDefault();
            $('.ksom-post-type-checkbox').prop('checked', false);
        });
    }

    /**
     * Initialize robots meta duplicate check.
     */
    function initRobotsDuplicateCheck() {
        $(document).on('click', '.ksom-check-robots-duplicate', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const button = $(this);
            const resultSpan = $('.ksom-robots-check-result');

            // Show loading
            resultSpan.html('<span style="color: #666;">チェック中...</span>');

            // Fetch homepage HTML
            $.ajax({
                url: ksomAdmin.homeUrl || '/',
                method: 'GET',
                dataType: 'html',
                success: function(html) {
                    // Check for robots meta tag with max-image-preview
                    const regex = /<meta\s+name=["']robots["']\s+content=["'][^"']*max-image-preview[^"']*["']/i;

                    if (regex.test(html)) {
                        resultSpan.html('<span style="color: #d63638; font-weight: bold;">⚠ 重複が検出されました</span>');
                    } else {
                        resultSpan.html('<span style="color: #00a32a; font-weight: bold;">✓ 重複なし</span>');
                    }
                },
                error: function() {
                    resultSpan.html('<span style="color: #d63638;">チェックに失敗しました</span>');
                }
            });

            return false;
        });
    }

})(jQuery);
