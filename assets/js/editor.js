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

    // Get plugin settings from localized data
    const settings = window.shifterFuturePublishSettings || {
        enabled: false,
        postTypes: []
    };

    if (!settings.enabled) {
        return;
    }

    // i18n text strings with fallbacks
    const publishText = __('Publish', 'shifter-future-publish') || '公開';
    const scheduleTextEn = 'Schedule';
    const scheduleTextJa = '予約投稿';

    // Flag to prevent multiple initializations
    let hasInitialized = false;

    /**
     * Check if current post type is enabled for future publishing
     */
    function isEnabledPostType() {
        const postType = select('core/editor').getCurrentPostType();
        return settings.postTypes.includes(postType);
    }

    /**
     * Check if the post has a future date
     */
    function hasFutureDate() {
        const editedDate = select('core/editor').getEditedPostAttribute('date');
        if (!editedDate) {
            return false;
        }
        const postDate = new Date(editedDate);
        const now = new Date();
        return postDate > now;
    }

    /**
     * Update the publish button text
     */
    function updatePublishButton() {
        if (!isEnabledPostType()) {
            return;
        }

        // Target the publish button in the editor header
        const publishButtons = document.querySelectorAll(
            '.editor-post-publish-button, ' +
            '.editor-post-publish-panel__toggle, ' +
            '[aria-label="' + scheduleTextJa + '"], ' +
            '[aria-label="' + scheduleTextEn + '"]'
        );

        publishButtons.forEach(function(button) {
            // Check if button text contains "Schedule" or Japanese equivalent
            const buttonText = button.textContent || button.innerText;
            
            if (hasFutureDate()) {
                // Change "Schedule" / "予約投稿" to "Publish" / "公開"
                if (buttonText.includes(scheduleTextJa) || buttonText.includes(scheduleTextEn)) {
                    // Find the text node and update it
                    updateButtonText(button, publishText);
                }
                // Update aria-label as well
                if (button.getAttribute('aria-label') === scheduleTextJa || 
                    button.getAttribute('aria-label') === scheduleTextEn) {
                    button.setAttribute('aria-label', publishText);
                }
            }
        });

        // Also target buttons in the publish sidebar
        const sidebarButtons = document.querySelectorAll(
            '.editor-post-publish-button'
        );

        sidebarButtons.forEach(function(button) {
            const buttonText = button.textContent || button.innerText;
            if (hasFutureDate() && (buttonText.includes(scheduleTextJa) || buttonText.includes(scheduleTextEn))) {
                updateButtonText(button, publishText);
            }
        });
    }

    /**
     * Update button text while preserving child elements
     */
    function updateButtonText(button, newText) {
        // Walk through child nodes to find text nodes
        const walker = document.createTreeWalker(
            button,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        let node;
        while ((node = walker.nextNode()) !== null) {
            if (node.textContent.trim() === scheduleTextJa || 
                node.textContent.trim() === scheduleTextEn) {
                node.textContent = newText;
            }
        }

        // If no text node was found, check for span elements
        const spans = button.querySelectorAll('span');
        spans.forEach(function(span) {
            if (span.textContent.trim() === scheduleTextJa || 
                span.textContent.trim() === scheduleTextEn) {
                span.textContent = newText;
            }
        });
    }

    /**
     * Initialize the observer and subscription
     */
    function init() {
        // Use MutationObserver to watch for DOM changes
        const observer = new MutationObserver(function(mutations) {
            updatePublishButton();
        });

        // Start observing when the editor is ready
        const startObserving = function() {
            // Prevent multiple initializations
            if (hasInitialized) {
                return;
            }

            const editorWrapper = document.querySelector('.editor-header') || 
                                  document.querySelector('.edit-post-header') ||
                                  document.querySelector('#editor');
            
            if (editorWrapper) {
                hasInitialized = true;
                observer.observe(editorWrapper, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                updatePublishButton();
            }
        };

        // Subscribe to editor state changes
        let previousDate = null;
        subscribe(function() {
            const currentDate = select('core/editor').getEditedPostAttribute('date');
            
            if (currentDate !== previousDate) {
                previousDate = currentDate;
                // Delay to allow DOM to update
                setTimeout(updatePublishButton, 100);
            }
        });

        // Initial setup with delay to ensure editor is loaded
        if (document.readyState === 'complete') {
            setTimeout(startObserving, 500);
        } else {
            window.addEventListener('load', function() {
                setTimeout(startObserving, 500);
            });
        }

        // Also run on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(startObserving, 500);
        });

        // Periodic check as fallback (3 seconds interval for better performance)
        setInterval(updatePublishButton, 3000);
    }

    // Wait for wp.data to be available
    if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
        init();
    } else {
        // Fallback: wait for wp to be ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
                init();
            }
        });
    }
})();
