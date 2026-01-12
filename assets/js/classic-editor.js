/**
 * Shifter Future Publish - Classic Editor Script
 *
 * Changes the "Schedule" button text to "Publish" for enabled post types
 * when a future date is selected in the Classic Editor.
 */
(function($) {
    'use strict';

    // Get plugin settings from localized data
    var settings = window.shifterFuturePublishSettings || {
        enabled: false,
        postTypes: [],
        currentPostType: 'post'
    };

    if (!settings.enabled) {
        return;
    }

    // Check if current post type is enabled
    if (settings.postTypes.indexOf(settings.currentPostType) === -1) {
        return;
    }

    /**
     * Check if the selected date is in the future
     */
    function isFutureDate() {
        var year = $('#aa').val();
        var month = $('#mm').val();
        var day = $('#jj').val();
        var hour = parseInt($('#hh').val(), 10) || 0;
        var minute = parseInt($('#mn').val(), 10) || 0;

        if (!year || !month || !day) {
            return false;
        }

        // Create date from form values (month is 1-indexed in the form)
        var postDate = new Date(
            parseInt(year, 10),
            parseInt(month, 10) - 1,
            parseInt(day, 10),
            hour,
            minute
        );
        var now = new Date();

        return postDate > now;
    }

    /**
     * Update the publish button text
     */
    function updatePublishButton() {
        var $publishButton = $('#publish');
        
        if (!$publishButton.length) {
            return;
        }

        var currentValue = $publishButton.val();
        
        if (isFutureDate()) {
            // Change "Schedule" / "予約投稿" to "Publish" / "公開"
            if (currentValue === '予約投稿' || currentValue === 'Schedule') {
                $publishButton.val('公開');
            }
        }
    }

    /**
     * Initialize event listeners
     */
    function init() {
        // Update on page load
        updatePublishButton();

        // Watch for changes in the date/time fields
        $('#aa, #mm, #jj, #hh, #mn').on('change', function() {
            // Delay to allow WordPress to update the button first
            setTimeout(updatePublishButton, 100);
        });

        // Watch for the timestamp edit link click
        $('.edit-timestamp').on('click', function() {
            // When timestamp edit is opened, watch for OK button click
            setTimeout(function() {
                $('.save-timestamp').off('click.shifterFuturePublish').on('click.shifterFuturePublish', function() {
                    setTimeout(updatePublishButton, 200);
                });
            }, 100);
        });

        // Also watch for any changes to the publish box
        $('#submitdiv').on('change', 'input, select', function() {
            setTimeout(updatePublishButton, 100);
        });

        // Use MutationObserver as a fallback
        var publishBox = document.getElementById('submitdiv');
        if (publishBox && window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                updatePublishButton();
            });

            observer.observe(publishBox, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['value']
            });
        }

        // Periodic check as ultimate fallback (3 seconds for better performance)
        setInterval(updatePublishButton, 3000);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Small delay to ensure WordPress has initialized
        setTimeout(init, 500);
    });

})(jQuery);
