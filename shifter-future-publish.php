<?php
/**
 * Plugin Name: Shifter Future Publish
 * Plugin URI: https://github.com/digitalcube/shifter-future-publish
 * Description: Allows publishing posts with future dates immediately. Useful for Shifter static site generation to include future-dated content in artifacts.
 * Version: 2.1.0
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

define('SHIFTER_FUTURE_PUBLISH_VERSION', '2.1.0');
define('SHIFTER_FUTURE_PUBLISH_PLUGIN_URL', plugin_dir_url(__FILE__));

final class Shifter_Future_Publish {

    private static ?self $instance = null;

    /** @var array{enabled: bool, post_types: array<string>} */
    private array $settings;

    public static function get_instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        $this->settings = wp_parse_args(
            get_option('shifter_future_publish_settings', []),
            ['enabled' => true, 'post_types' => ['post']]
        );
        $this->init_hooks();
    }

    private function init_hooks(): void {
        // Admin hooks (always register)
        add_action('admin_menu', $this->add_admin_menu(...));
        add_action('admin_init', $this->register_settings(...));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), $this->add_settings_link(...));

        if (!$this->settings['enabled']) {
            return;
        }

        // Core: Intercept post save to force publish status
        add_filter('wp_insert_post_data', $this->force_publish_status(...), 10, 2);

        // Editor scripts for button text change
        add_action('enqueue_block_editor_assets', $this->enqueue_editor_assets(...));
        add_action('admin_enqueue_scripts', $this->enqueue_classic_editor_assets(...));
    }

    /**
     * Force publish status for future-dated posts.
     *
     * @param array<string, mixed> $data Post data array.
     * @param array<string, mixed> $postarr Raw post data array.
     * @return array<string, mixed> Modified post data.
     */
    public function force_publish_status(array $data, array $postarr): array {
        if (!in_array($data['post_type'] ?? '', $this->settings['post_types'], true)) {
            return $data;
        }

        if ($data['post_status'] === 'future') {
            $data['post_status'] = 'publish';
        }

        return $data;
    }

    /**
     * Enqueue block editor assets.
     */
    public function enqueue_editor_assets(): void {
        $screen = get_current_screen();
        if (!$screen?->is_block_editor()) {
            return;
        }

        wp_enqueue_script(
            'shifter-future-publish-editor',
            SHIFTER_FUTURE_PUBLISH_PLUGIN_URL . 'assets/js/editor.js',
            ['wp-data', 'wp-editor', 'wp-i18n'],
            SHIFTER_FUTURE_PUBLISH_VERSION,
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
        if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen || $screen->is_block_editor()) {
            return;
        }

        wp_enqueue_script(
            'shifter-future-publish-classic-editor',
            SHIFTER_FUTURE_PUBLISH_PLUGIN_URL . 'assets/js/classic-editor.js',
            ['jquery'],
            SHIFTER_FUTURE_PUBLISH_VERSION,
            true
        );

        wp_localize_script(
            'shifter-future-publish-classic-editor',
            'shifterFuturePublishSettings',
            [
                'enabled' => $this->settings['enabled'],
                'postTypes' => $this->settings['post_types'],
                'currentPostType' => $screen->post_type ?? 'post',
            ]
        );
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
            fn() => null,
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
        $valid_post_types = array_keys(get_post_types(['public' => true], 'names'));
        $post_types = isset($input['post_types']) && is_array($input['post_types'])
            ? array_values(array_intersect($input['post_types'], $valid_post_types))
            : [];

        return [
            'enabled' => !empty($input['enabled']),
            'post_types' => $post_types,
        ];
    }

    /**
     * Render enabled checkbox field.
     */
    public function render_enabled_field(): void {
        printf(
            '<label><input type="checkbox" name="shifter_future_publish_settings[enabled]" value="1" %s> %s</label>',
            $this->settings['enabled'] ? 'checked' : '',
            esc_html__('Enable future date publishing', 'shifter-future-publish')
        );
    }

    /**
     * Render post types checkboxes field.
     */
    public function render_post_types_field(): void {
        foreach (get_post_types(['public' => true], 'objects') as $post_type) {
            printf(
                '<label style="display:block;margin-bottom:5px"><input type="checkbox" name="shifter_future_publish_settings[post_types][]" value="%s" %s> %s <code>(%s)</code></label>',
                esc_attr($post_type->name),
                in_array($post_type->name, $this->settings['post_types'], true) ? 'checked' : '',
                esc_html($post_type->label),
                esc_html($post_type->name)
            );
        }
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
            <p><?php esc_html_e('This plugin allows publishing posts with future dates immediately for Shifter static site generation.', 'shifter-future-publish'); ?></p>
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
        array_unshift($links, sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=shifter-future-publish'),
            __('Settings', 'shifter-future-publish')
        ));
        return $links;
    }
}

add_action('plugins_loaded', static fn() => Shifter_Future_Publish::get_instance());

register_activation_hook(__FILE__, static function(): void {
    if (!get_option('shifter_future_publish_settings')) {
        add_option('shifter_future_publish_settings', ['enabled' => true, 'post_types' => ['post']]);
    }
});
