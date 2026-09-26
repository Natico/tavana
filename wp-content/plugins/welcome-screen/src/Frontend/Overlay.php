<?php
/**
 * Frontend Welcome Screen overlay.
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

namespace WelcomeScreen\Frontend;

use WelcomeScreen\Admin\Settings;
use WelcomeScreen\PostTypes\WelcomeScreenPostType;
use WP_Post;

/**
 * Renders the Welcome Screen overlay on eligible frontend requests.
 */
final class Overlay {
	/**
	 * Settings service.
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 */
	public function __construct(Settings $settings) {
		$this->settings = $settings;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('wp_footer', array($this, 'render_overlay'));
		add_action('site_mode_standalone_head', array($this, 'render_standalone_head'));
		add_action('site_mode_standalone_footer', array($this, 'render_standalone_footer'));
	}

	/**
	 * Enqueue assets for normal theme-rendered frontend requests.
	 */
	public function enqueue_assets(): void {
		if (null === $this->get_context()) {
			return;
		}

		wp_enqueue_style(
			'welcome-screen',
			WELCOME_SCREEN_URL . 'assets/welcome-screen.css',
			array(),
			WELCOME_SCREEN_VERSION
		);

		wp_enqueue_script(
			'welcome-screen',
			WELCOME_SCREEN_URL . 'assets/welcome-screen.js',
			array(),
			WELCOME_SCREEN_VERSION,
			true
		);
	}

	/**
	 * Render assets needed by standalone documents such as Site Mode.
	 */
	public function render_standalone_head(): void {
		if (null === $this->get_context()) {
			return;
		}

		?>
		<link rel="stylesheet" href="<?php echo esc_url(WELCOME_SCREEN_URL . 'assets/welcome-screen.css?ver=' . rawurlencode(WELCOME_SCREEN_VERSION)); ?>">
		<?php
	}

	/**
	 * Render the standalone overlay and script.
	 */
	public function render_standalone_footer(): void {
		if (null === $this->get_context()) {
			return;
		}

		$this->render_overlay();
		?>
		<script src="<?php echo esc_url(WELCOME_SCREEN_URL . 'assets/welcome-screen.js?ver=' . rawurlencode(WELCOME_SCREEN_VERSION)); ?>"></script>
		<?php
	}

	/**
	 * Render the Welcome Screen overlay.
	 */
	public function render_overlay(): void {
		$context = $this->get_context();

		if (null === $context) {
			return;
		}

		$content = apply_filters('the_content', $context['post']->post_content);
		$content = is_string($content) ? $content : '';
		$direction = is_rtl() ? 'rtl' : 'ltr';
		$direction_class = 'rtl' === $direction ? 'is-rtl' : 'is-ltr';
		?>
		<div
			class="welcome-screen <?php echo esc_attr($direction_class); ?>"
			dir="<?php echo esc_attr($direction); ?>"
			role="dialog"
			aria-modal="true"
			aria-label="<?php echo esc_attr__('Welcome', 'welcome-screen'); ?>"
			data-welcome-screen
			data-welcome-screen-frequency="<?php echo esc_attr($context['frequency']); ?>"
			data-welcome-screen-storage-key="<?php echo esc_attr($context['storage_key']); ?>"
			hidden
		>
			<div class="welcome-screen__panel">
				<div class="welcome-screen__content">
					<?php echo wp_kses_post($content); ?>
				</div>
				<button class="welcome-screen__enter" type="button" data-welcome-screen-enter><?php esc_html_e('ENTER', 'welcome-screen'); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Build frontend context when the request is eligible.
	 *
	 * @return array{post: WP_Post, frequency: string, storage_key: string}|null
	 */
	private function get_context(): ?array {
		if (! $this->is_frontend_request()) {
			return null;
		}

		$options = $this->settings->get_options();

		if (Settings::ENABLED !== $options['enabled']) {
			return null;
		}

		if (Settings::DISPLAY_HOME === $options['display'] && ! is_front_page()) {
			return null;
		}

		$post = $this->resolve_post((int) $options['welcome_screen_id']);

		if (! $post instanceof WP_Post) {
			return null;
		}

		return array(
			'post'        => $post,
			'frequency'   => $options['frequency'],
			'storage_key' => 'welcomeScreen:' . (string) $options['welcome_screen_id'],
		);
	}

	/**
	 * Check whether this is a normal frontend request for Welcome Screen purposes.
	 */
	private function is_frontend_request(): bool {
		if (is_admin() || wp_doing_ajax() || wp_doing_cron() || is_feed()) {
			return false;
		}

		if ((defined('REST_REQUEST') && REST_REQUEST) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
			return false;
		}

		if (isset($GLOBALS['pagenow']) && 'wp-login.php' === $GLOBALS['pagenow']) {
			return false;
		}

		return true;
	}

	/**
	 * Resolve the Welcome Screen post for the current Polylang language.
	 *
	 * @param int $base_post_id Base Welcome Screen post ID.
	 */
	private function resolve_post(int $base_post_id): ?WP_Post {
		$base_post = $this->get_valid_post($base_post_id);

		if (! $base_post instanceof WP_Post) {
			return null;
		}

		if (! function_exists('pll_current_language') || ! function_exists('pll_get_post')) {
			return $base_post;
		}

		$language = pll_current_language('slug');

		if (! is_string($language) || '' === $language) {
			return $base_post;
		}

		$translated_post_id = pll_get_post($base_post->ID, $language);
		$translated_post = is_numeric($translated_post_id) ? $this->get_valid_post(absint($translated_post_id)) : null;

		return $translated_post instanceof WP_Post ? $translated_post : $base_post;
	}

	/**
	 * Get a valid published Welcome Screen post.
	 *
	 * @param int $post_id Post ID.
	 */
	private function get_valid_post(int $post_id): ?WP_Post {
		if (0 === $post_id) {
			return null;
		}

		$post = get_post($post_id);

		if (! $post instanceof WP_Post || WelcomeScreenPostType::KEY !== $post->post_type || 'publish' !== $post->post_status) {
			return null;
		}

		return $post;
	}
}
