/**
 * Shifter Future Publish - Block Editor Script
 *
 * Changes the "Schedule" button text to "Publish" for enabled post types
 * when a future date is selected.
 */
(function() {
    'use strict';

    var settings = window.shifterFuturePublishSettings || { enabled: false, postTypes: [] };
    if (!settings.enabled) return;

    var scheduleTexts = ['Schedule', '予約投稿'];
    var publishText = '公開';

    function isEnabledPostType() {
        return settings.postTypes.includes(wp.data.select('core/editor').getCurrentPostType());
    }

    function hasFutureDate() {
        var date = wp.data.select('core/editor').getEditedPostAttribute('date');
        return date && new Date(date) > new Date();
    }

    function updateButtonText(el) {
        scheduleTexts.forEach(function(text) {
            if (el.textContent.includes(text)) {
                el.textContent = el.textContent.replace(text, publishText);
            }
            if (el.getAttribute('aria-label') === text) {
                el.setAttribute('aria-label', publishText);
            }
        });
    }

    function updateButtons() {
        if (!isEnabledPostType() || !hasFutureDate()) return;
        document.querySelectorAll('.editor-post-publish-button, .editor-post-publish-panel__toggle').forEach(updateButtonText);
    }

    var prevDate = null;
    wp.data.subscribe(function() {
        var date = wp.data.select('core/editor').getEditedPostAttribute('date');
        if (date !== prevDate) {
            prevDate = date;
            setTimeout(updateButtons, 100);
        }
    });
})();
