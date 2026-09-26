<?php
/**
 * Welcome Screen settings.
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

namespace WelcomeScreen\Admin;

use WelcomeScreen\PostTypes\WelcomeScreenPostType;
use WP_Post;

/**
 * Registers and renders Welcome Screen settings.
 */
final class Settings {
	public const OPTION_NAME = 'welcome_screen_options';
	public const ENABLED = '1';
	public const DISABLED = '0';
	public const DISPLAY_HOME = 'home';
	public const DISPLAY_ALL = 'all';
	public const FREQUENCY_ONCE = 'once';
	public const FREQUENCY_SESSION = 'session';

	private const MENU_SLUG = 'edit.php?post_type=' . WelcomeScreenPostType::KEY;
	private const SETTINGS_SLUG = 'welcome-screen-settings';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('admin_menu', array($this, 'register_admin_menu'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register the plugin-owned admin menu.
	 */
	public function register_admin_menu(): void {
		add_menu_page(
			__('Welcome Screen', 'welcome-screen'),
			__('Welcome Screen', 'welcome-screen'),
			'edit_posts',
			self::MENU_SLUG,
			'',
			'dashicons-welcome-view-site',
			27
		);

		remove_submenu_page(self::MENU_SLUG, self::MENU_SLUG);

		add_submenu_page(
			self::MENU_SLUG,
			__('All Welcome Screens', 'welcome-screen'),
			__('All Welcome Screens', 'welcome-screen'),
			'edit_posts',
			self::MENU_SLUG
		);

		add_submenu_page(
			self::MENU_SLUG,
			__('Welcome Screen Settings', 'welcome-screen'),
			__('Settings', 'welcome-screen'),
			'manage_options',
			self::SETTINGS_SLUG,
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Register the Welcome Screen option.
	 */
	public function register_settings(): void {
		register_setting(
			'welcome_screen',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array($this, 'sanitize_options'),
				'default'           => $this->get_defaults(),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		if (! current_user_can('manage_options')) {
			return;
		}

		$options = $this->get_options();
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Welcome Screen', 'welcome-screen'); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields('welcome_screen'); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e('Enabled', 'welcome-screen'); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[enabled]" value="<?php echo esc_attr(self::ENABLED); ?>" <?php checked($options['enabled'], self::ENABLED); ?>>
									<?php esc_html_e('Yes', 'welcome-screen'); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[enabled]" value="<?php echo esc_attr(self::DISABLED); ?>" <?php checked($options['enabled'], self::DISABLED); ?>>
									<?php esc_html_e('No', 'welcome-screen'); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="welcome-screen-id"><?php esc_html_e('Welcome Content', 'welcome-screen'); ?></label></th>
						<td>
							<?php $this->render_content_select($options['welcome_screen_id']); ?>
							<p class="description"><?php esc_html_e('Select the base Welcome Screen. Polylang translations are resolved automatically when available.', 'welcome-screen'); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('Display On', 'welcome-screen'); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[display]" value="<?php echo esc_attr(self::DISPLAY_HOME); ?>" <?php checked($options['display'], self::DISPLAY_HOME); ?>>
									<?php esc_html_e('Home page only', 'welcome-screen'); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[display]" value="<?php echo esc_attr(self::DISPLAY_ALL); ?>" <?php checked($options['display'], self::DISPLAY_ALL); ?>>
									<?php esc_html_e('All frontend pages', 'welcome-screen'); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e('Frequency', 'welcome-screen'); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[frequency]" value="<?php echo esc_attr(self::FREQUENCY_ONCE); ?>" <?php checked($options['frequency'], self::FREQUENCY_ONCE); ?>>
									<?php esc_html_e('Once ever', 'welcome-screen'); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[frequency]" value="<?php echo esc_attr(self::FREQUENCY_SESSION); ?>" <?php checked($options['frequency'], self::FREQUENCY_SESSION); ?>>
									<?php esc_html_e('Every visit', 'welcome-screen'); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get sanitized options merged with defaults.
	 *
	 * @return array{enabled: string, welcome_screen_id: int, display: string, frequency: string}
	 */
	public function get_options(): array {
		$options = get_option(self::OPTION_NAME, array());
		$options = is_array($options) ? $options : array();

		return $this->sanitize_options(array_merge($this->get_defaults(), $options));
	}

	/**
	 * Get default option values.
	 *
	 * @return array{enabled: string, welcome_screen_id: int, display: string, frequency: string}
	 */
	public function get_defaults(): array {
		return array(
			'enabled'           => self::DISABLED,
			'welcome_screen_id' => 0,
			'display'           => self::DISPLAY_HOME,
			'frequency'         => self::FREQUENCY_ONCE,
		);
	}

	/**
	 * Sanitize Welcome Screen options.
	 *
	 * @param mixed $raw_options Raw options.
	 * @return array{enabled: string, welcome_screen_id: int, display: string, frequency: string}
	 */
	public function sanitize_options($raw_options): array {
		$raw_options = is_array($raw_options) ? $raw_options : array();
		$defaults = $this->get_defaults();

		$enabled = isset($raw_options['enabled']) ? sanitize_key((string) $raw_options['enabled']) : $defaults['enabled'];
		$display = isset($raw_options['display']) ? sanitize_key((string) $raw_options['display']) : $defaults['display'];
		$frequency = isset($raw_options['frequency']) ? sanitize_key((string) $raw_options['frequency']) : $defaults['frequency'];
		$welcome_screen_id = isset($raw_options['welcome_screen_id']) ? absint($raw_options['welcome_screen_id']) : 0;

		if (! in_array($enabled, array(self::ENABLED, self::DISABLED), true)) {
			$enabled = $defaults['enabled'];
		}

		if (! in_array($display, array(self::DISPLAY_HOME, self::DISPLAY_ALL), true)) {
			$display = $defaults['display'];
		}

		if (! in_array($frequency, array(self::FREQUENCY_ONCE, self::FREQUENCY_SESSION), true)) {
			$frequency = $defaults['frequency'];
		}

		if (0 !== $welcome_screen_id && ! $this->is_valid_content_id($welcome_screen_id)) {
			$welcome_screen_id = 0;
		}

		return array(
			'enabled'           => $enabled,
			'welcome_screen_id' => $welcome_screen_id,
			'display'           => $display,
			'frequency'         => $frequency,
		);
	}

	/**
	 * Render the Welcome Screen post selector.
	 *
	 * @param int $selected_id Selected Welcome Screen ID.
	 */
	private function render_content_select(int $selected_id): void {
		$posts = get_posts(
			array(
				'post_type'      => WelcomeScreenPostType::KEY,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<select id="welcome-screen-id" name="<?php echo esc_attr(self::OPTION_NAME); ?>[welcome_screen_id]">
			<option value="0"><?php esc_html_e('Select Welcome Screen', 'welcome-screen'); ?></option>
			<?php foreach ($posts as $post) : ?>
				<?php if ($post instanceof WP_Post) : ?>
					<option value="<?php echo esc_attr((string) $post->ID); ?>" <?php selected($selected_id, $post->ID); ?>><?php echo esc_html(get_the_title($post)); ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Check whether a Welcome Screen content ID is valid.
	 *
	 * @param int $post_id Post ID.
	 */
	private function is_valid_content_id(int $post_id): bool {
		$post = get_post($post_id);

		return $post instanceof WP_Post && WelcomeScreenPostType::KEY === $post->post_type && 'publish' === $post->post_status;
	}
}
