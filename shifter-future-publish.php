<?php
/**
 * Plugin Name: Shifter Future Publish
 * Plugin URI: https://github.com/digitalcube/shifter-future-publish
 * Description: Allows publishing posts with future dates immediately. Useful for Shifter static site generation to include future-dated content in artifacts.
 * Version: 2.0.5
 * Author: DigitalCube
 * Author URI: https://developer.getshifter.io/
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shifter-future-publish
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.7
 * Requires PHP: 8.0
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('SHIFTER_FUTURE_PUBLISH_VERSION')) {
    define('SHIFTER_FUTURE_PUBLISH_VERSION', '2.0.5');
}
if (!defined('SHIFTER_FUTURE_PUBLISH_PLUGIN_DIR')) {
    define('SHIFTER_FUTURE_PUBLISH_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('SHIFTER_FUTURE_PUBLISH_PLUGIN_URL')) {
    define('SHIFTER_FUTURE_PUBLISH_PLUGIN_URL', plugin_dir_url(__FILE__));
}

final class Shifter_Future_Publish {

    private static ?self $instance = null;

    /** @var array{enabled: bool, post_types: array<string>} */
    private array $settings;

    public static function get_instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        $this->load_settings();
        $this->init_hooks();
    }

    private function load_settings(): void {
        $defaults = [
            'enabled' => true,
            'post_types' => ['post'],
        ];
        $saved = get_option('shifter_future_publish_settings', []);
        $saved_array = is_array($saved) ? $saved : [];
        $parsed = wp_parse_args($saved_array, $defaults);
        $this->settings = [
            'enabled' => (bool) ($parsed['enabled'] ?? true),
            'post_types' => is_array($parsed['post_types'] ?? null) ? array_values(array_filter($parsed['post_types'], 'is_string')) : ['post'],
        ];
    }

    private function init_hooks(): void {
        $this->register_admin_hooks();

        if (!$this->settings['enabled']) {
            return;
        }

        // Core: Intercept post save to force publish status
        add_filter('wp_insert_post_data', $this->force_publish_status(...), 10, 2);

        // Fallback: Handle future_{post_type} hooks for edge cases
        add_action('init', $this->setup_future_hooks(...));
    }

    private function register_admin_hooks(): void {
        add_action('admin_menu', $this->add_admin_menu(...));
        add_action('admin_init', $this->register_settings(...));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), $this->add_settings_link(...));
        add_action('enqueue_block_editor_assets', $this->enqueue_editor_assets(...));
        add_action('admin_enqueue_scripts', $this->enqueue_classic_editor_assets(...));
    }

    /**
     * Enqueue block editor assets.
     */
    public function enqueue_editor_assets(): void {
        $screen = get_current_screen();
        if (!$screen || !$screen->is_block_editor()) {
            return;
        }

        // Skip if plugin is disabled
        if (!$this->settings['enabled']) {
            return;
        }

        // Always enqueue and let JS handle post type check
        /** @var string $plugin_url */
        $plugin_url = SHIFTER_FUTURE_PUBLISH_PLUGIN_URL;
        /** @var string $version */
        $version = SHIFTER_FUTURE_PUBLISH_VERSION;
        wp_enqueue_script(
            'shifter-future-publish-editor',
            $plugin_url . 'assets/js/editor.js',
            ['wp-data', 'wp-editor', 'wp-i18n'],
            $version,
            true
        );

        wp_localize_script(
            'shifter-future-publish-editor',
            'shifterFuturePublishSettings',
            [
                'enabled' => $this->settings['enabled'],
                'postTypes' => $this->settings['post_types'],
            ]
        );
    }

    /**
     * Enqueue classic editor assets.
     */
    public function enqueue_classic_editor_assets(string $hook): void {
        // Only load on post edit screens
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Skip if block editor is active
        if ($screen->is_block_editor()) {
            return;
        }

        $post_type = $screen->post_type;

        /** @var string $plugin_url */
        $plugin_url = SHIFTER_FUTURE_PUBLISH_PLUGIN_URL;
        /** @var string $version */
        $version = SHIFTER_FUTURE_PUBLISH_VERSION;
        wp_enqueue_script(
            'shifter-future-publish-classic-editor',
            $plugin_url . 'assets/js/classic-editor.js',
            ['jquery'],
            $version,
            true
        );

        wp_localize_script(
            'shifter-future-publish-classic-editor',
            'shifterFuturePublishSettings',
            [
                'enabled' => $this->settings['enabled'],
                'postTypes' => $this->settings['post_types'],
                'currentPostType' => $post_type,
            ]
        );
    }

    /**
     * Setup future post hooks for each enabled post type.
     */
    public function setup_future_hooks(): void {
        remove_action('future_post', '_future_post_hook');

        foreach ($this->settings['post_types'] as $post_type) {
            remove_action("future_{$post_type}", '_future_post_hook');
            add_action("future_{$post_type}", $this->publish_future_post_now(...));
        }

        add_action('future_post', $this->handle_future_post(...));
    }

    /**
     * Handle future_post action for non-enabled post types.
     */
    public function handle_future_post(int $post_id): void {
        $post = get_post($post_id);
        if (!$post instanceof \WP_Post) {
            return;
        }

        if ($this->is_enabled_post_type($post->post_type)) {
            return;
        }

        _future_post_hook($post_id, $post);
    }

    /**
     * Publish future post immediately.
     */
    public function publish_future_post_now(int $post_id): void {
        wp_publish_post($post_id);
    }

    /**
     * Force publish status for future-dated posts of enabled post types.
     *
     * @param array<string, mixed> $data    Post data array.
     * @param array<string, mixed> $postarr Raw post data array.
     * @return array<string, mixed> Modified post data.
     */
    public function force_publish_status(array $data, array $postarr): array {
        $post_type = isset($data['post_type']) && is_string($data['post_type']) ? $data['post_type'] : '';
        if (!$this->is_enabled_post_type($post_type)) {
            return $data;
        }

        if ($data['post_status'] === 'future') {
            $data['post_status'] = 'publish';
        }

        return $data;
    }

    /**
     * Check if a post type is enabled for future publishing.
     */
    private function is_enabled_post_type(string $post_type): bool {
        return in_array($post_type, $this->settings['post_types'], true);
    }

    /**
     * Add admin menu page.
     */
    public function add_admin_menu(): void {
        add_options_page(
            __('Shifter Future Publish', 'shifter-future-publish'),
            __('Shifter Future Publish', 'shifter-future-publish'),
            'manage_options',
            'shifter-future-publish',
            $this->render_settings_page(...)
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings(): void {
        register_setting(
            'shifter_future_publish_settings_group',
            'shifter_future_publish_settings',
            ['sanitize_callback' => $this->sanitize_settings(...)]
        );

        add_settings_section(
            'shifter_future_publish_main_section',
            __('General Settings', 'shifter-future-publish'),
            $this->render_section_description(...),
            'shifter-future-publish'
        );

        add_settings_field(
            'enabled',
            __('Enable Plugin', 'shifter-future-publish'),
            $this->render_enabled_field(...),
            'shifter-future-publish',
            'shifter_future_publish_main_section'
        );

        add_settings_field(
            'post_types',
            __('Post Types', 'shifter-future-publish'),
            $this->render_post_types_field(...),
            'shifter-future-publish',
            'shifter_future_publish_main_section'
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array<string, mixed>|null $input Raw input data.
     * @return array{enabled: bool, post_types: array<string>} Sanitized data.
     */
    public function sanitize_settings(?array $input): array {
        $input ??= [];

        /** @var array<string, \WP_Post_Type> $all_post_types */
        $all_post_types = get_post_types(['public' => true], 'names');
        $valid_post_types = array_keys($all_post_types);
        $input_post_types = isset($input['post_types']) && is_array($input['post_types']) ? $input['post_types'] : [];
        /** @var array<string> $post_types */
        $post_types = array_values(array_filter(
            array_intersect($input_post_types, $valid_post_types),
            'is_string'
        ));

        return [
            'enabled' => !empty($input['enabled']),
            'post_types' => $post_types,
        ];
    }

    /**
     * Render section description.
     */
    public function render_section_description(): void {
        echo '<p>' . esc_html__('Configure which post types should allow publishing with future dates. When enabled, posts with future dates will be set to "publish" status instead of "future" status, allowing them to be included in Shifter artifacts.', 'shifter-future-publish') . '</p>';
    }

    /**
     * Render enabled checkbox field.
     */
    public function render_enabled_field(): void {
        $checked = $this->settings['enabled'] ? 'checked' : '';
        printf(
            '<label><input type="checkbox" name="shifter_future_publish_settings[enabled]" value="1" %s> %s</label>',
            $checked,
            esc_html__('Enable future date publishing', 'shifter-future-publish')
        );
        printf(
            '<p class="description">%s</p>',
            esc_html__('When enabled, posts with future dates will be published immediately instead of being scheduled.', 'shifter-future-publish')
        );
    }

    /**
     * Render post types checkboxes field.
     */
    public function render_post_types_field(): void {
        $post_types = get_post_types(['public' => true], 'objects');

        foreach ($post_types as $post_type) {
            $checked = $this->is_enabled_post_type($post_type->name) ? 'checked' : '';
            printf(
                '<label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="shifter_future_publish_settings[post_types][]" value="%s" %s> %s <code>(%s)</code></label>',
                esc_attr($post_type->name),
                $checked,
                esc_html($post_type->label),
                esc_html($post_type->name)
            );
        }
        printf(
            '<p class="description">%s</p>',
            esc_html__('Select which post types should allow publishing with future dates.', 'shifter-future-publish')
        );
    }

    /**
     * Render settings page.
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e('About this plugin:', 'shifter-future-publish'); ?></strong>
                    <?php esc_html_e('This plugin allows you to publish posts with future dates immediately. This is useful for Shifter static site generation, where you want future-dated content to be included in the generated artifacts.', 'shifter-future-publish'); ?>
                </p>
            </div>

            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('Important:', 'shifter-future-publish'); ?></strong>
                    <?php esc_html_e('When enabled, posts with future dates will be visible to all visitors immediately. Make sure your content is ready before publishing.', 'shifter-future-publish'); ?>
                </p>
            </div>

            <form action="options.php" method="post">
                <?php
                settings_fields('shifter_future_publish_settings_group');
                do_settings_sections('shifter-future-publish');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Add settings link to plugins page.
     *
     * @param array<string> $links Existing links.
     * @return array<string> Modified links.
     */
    public function add_settings_link(array $links): array {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=shifter-future-publish'),
            __('Settings', 'shifter-future-publish')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Get current settings.
     *
     * @return array{enabled: bool, post_types: array<string>} Current settings.
     */
    public function get_settings(): array {
        return $this->settings;
    }
}

// Initialize the plugin
add_action('plugins_loaded', static function(): void {
    Shifter_Future_Publish::get_instance();
});

// Activation hook
register_activation_hook(__FILE__, static function(): void {
    $default_settings = [
        'enabled' => true,
        'post_types' => ['post'],
    ];

    if (!get_option('shifter_future_publish_settings')) {
        add_option('shifter_future_publish_settings', $default_settings);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, static function(): void {
    // Optionally clean up settings on deactivation
    // delete_option('shifter_future_publish_settings');
});
