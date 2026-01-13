/**
 * Shifter Future Publish - Block Editor Script
 *
 * Changes the "Schedule" button text to "Publish" for enabled post types
 * when a future date is selected.
 */
(function() {
    'use strict';

    const { subscribe, select } = wp.data;
    const { __ } = wp.i18n;

    const settings = window.shifterFuturePublishSettings || { enabled: false, postTypes: [] };
    if (!settings.enabled) return;

    const publishText = __('Publish', 'shifter-future-publish') || '公開';
    const scheduleTexts = ['Schedule', '予約投稿'];

    function isEnabledPostType() {
        return settings.postTypes.includes(select('core/editor').getCurrentPostType());
    }

    function hasFutureDate() {
        const date = select('core/editor').getEditedPostAttribute('date');
        return date ? new Date(date) > new Date() : false;
    }

    function updateButtonText(button) {
        const walker = document.createTreeWalker(button, NodeFilter.SHOW_TEXT, null, false);
        let node;
        while ((node = walker.nextNode()) !== null) {
            if (scheduleTexts.includes(node.textContent.trim())) {
                node.textContent = publishText;
            }
        }
    }

    function updatePublishButton() {
        if (!isEnabledPostType() || !hasFutureDate()) return;

        document.querySelectorAll('.editor-post-publish-button, .editor-post-publish-panel__toggle').forEach(button => {
            if (scheduleTexts.some(text => button.textContent.includes(text))) {
                updateButtonText(button);
            }
            if (scheduleTexts.includes(button.getAttribute('aria-label'))) {
                button.setAttribute('aria-label', publishText);
            }
        });
    }

    let previousDate = null;
    subscribe(() => {
        const currentDate = select('core/editor').getEditedPostAttribute('date');
        if (currentDate !== previousDate) {
            previousDate = currentDate;
            setTimeout(updatePublishButton, 100);
        }
    });
})();
