/**
 * Shifter Future Publish - Classic Editor Script
 *
 * Changes the "Schedule" button text to "Publish" for enabled post types
 * when a future date is selected in the Classic Editor.
 */
(function($) {
    'use strict';

    var settings = window.shifterFuturePublishSettings || { enabled: false, postTypes: [], currentPostType: 'post' };
    if (!settings.enabled || settings.postTypes.indexOf(settings.currentPostType) === -1) return;

    function isFutureDate() {
        var year = parseInt($('#aa').val(), 10);
        var month = parseInt($('#mm').val(), 10) - 1;
        var day = parseInt($('#jj').val(), 10);
        var hour = parseInt($('#hh').val(), 10) || 0;
        var minute = parseInt($('#mn').val(), 10) || 0;

        if (!year || isNaN(month) || !day) return false;
        return new Date(year, month, day, hour, minute) > new Date();
    }

    function updatePublishButton() {
        var $btn = $('#publish');
        if ($btn.length && isFutureDate() && ['予約投稿', 'Schedule'].indexOf($btn.val()) !== -1) {
            $btn.val('公開');
        }
    }

    $(function() {
        updatePublishButton();
        $('#aa, #mm, #jj, #hh, #mn').on('change', function() {
            setTimeout(updatePublishButton, 100);
        });
        $('.save-timestamp').on('click', function() {
            setTimeout(updatePublishButton, 200);
        });
    });
})(jQuery);
