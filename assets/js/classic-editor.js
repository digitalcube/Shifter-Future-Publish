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
        var y = $('#aa').val(), m = $('#mm').val(), d = $('#jj').val();
        if (!y || !m || !d) return false;
        return new Date(parseInt(y, 10), parseInt(m, 10) - 1, parseInt(d, 10), parseInt($('#hh').val(), 10) || 0, parseInt($('#mn').val(), 10) || 0) > new Date();
    }

    function updateButton() {
        var $btn = $('#publish'), val = $btn.val();
        if (isFutureDate() && (val === '予約投稿' || val === 'Schedule')) {
            $btn.val('公開');
        }
    }

    $(function() {
        updateButton();
        $('#aa, #mm, #jj, #hh, #mn').on('change', function() { setTimeout(updateButton, 100); });
        $('.save-timestamp').on('click', function() { setTimeout(updateButton, 200); });
    });

})(jQuery);
