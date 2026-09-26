<?php
/**
 * Site Mode settings.
 *
 * @package SiteMode
 */

declare(strict_types=1);

namespace SiteMode;

/**
 * Registers and renders Site Mode settings.
 */
final class Settings {
	public const OPTION_NAME = 'site_mode_options';
	public const MODE_LIVE = 'live';
	public const MODE_COMING_SOON = 'coming_soon';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('admin_menu', array($this, 'register_settings_page'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	/**
	 * Register the Settings -> Site Mode page.
	 */
	public function register_settings_page(): void {
		add_options_page(
			__('Site Mode', 'site-mode'),
			__('Site Mode', 'site-mode'),
			'manage_options',
			'site-mode',
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Register the Site Mode option.
	 */
	public function register_settings(): void {
		register_setting(
			'site_mode',
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
			<h1><?php esc_html_e('Site Mode', 'site-mode'); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields('site_mode'); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e('Site Mode', 'site-mode'); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[mode]" value="<?php echo esc_attr(self::MODE_LIVE); ?>" <?php checked($options['mode'], self::MODE_LIVE); ?>>
									<?php esc_html_e('Live', 'site-mode'); ?>
								</label>
								<br>
								<label>
									<input type="radio" name="<?php echo esc_attr(self::OPTION_NAME); ?>[mode]" value="<?php echo esc_attr(self::MODE_COMING_SOON); ?>" <?php checked($options['mode'], self::MODE_COMING_SOON); ?>>
									<?php esc_html_e('Coming Soon', 'site-mode'); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="site-mode-coming-soon-page-id"><?php esc_html_e('Coming Soon Page', 'site-mode'); ?></label></th>
						<td>
							<?php
							wp_dropdown_pages(
								array(
									'name'              => self::OPTION_NAME . '[coming_soon_page_id]',
									'id'                => 'site-mode-coming-soon-page-id',
									'echo'              => 1,
									'show_option_none'  => __('Select a page', 'site-mode'),
									'option_none_value' => '0',
									'selected'          => $options['coming_soon_page_id'],
									'post_status'       => array('publish', 'private', 'draft'),
								)
							);
							?>
							<p class="description"><?php esc_html_e('Select the WordPress Page to render through the standalone Coming Soon template.', 'site-mode'); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Get Site Mode options merged with defaults.
	 *
	 * @return array{mode: string, coming_soon_page_id: int}
	 */
	public function get_options(): array {
		$options = get_option(self::OPTION_NAME, array());
		$options = is_array($options) ? $options : array();

		return $this->sanitize_options(array_merge($this->get_defaults(), $options));
	}

	/**
	 * Get default option values.
	 *
	 * @return array{mode: string, coming_soon_page_id: int}
	 */
	public function get_defaults(): array {
		return array(
			'mode'                => self::MODE_LIVE,
			'coming_soon_page_id' => 0,
		);
	}

	/**
	 * Sanitize Site Mode options.
	 *
	 * @param mixed $raw_options Raw options.
	 * @return array{mode: string, coming_soon_page_id: int}
	 */
	public function sanitize_options($raw_options): array {
		$raw_options = is_array($raw_options) ? $raw_options : array();
		$defaults = $this->get_defaults();
		$mode = isset($raw_options['mode']) ? sanitize_key((string) $raw_options['mode']) : $defaults['mode'];

		if (! in_array($mode, array(self::MODE_LIVE, self::MODE_COMING_SOON), true)) {
			$mode = $defaults['mode'];
		}

		$page_id = isset($raw_options['coming_soon_page_id']) ? absint($raw_options['coming_soon_page_id']) : 0;

		if (0 !== $page_id && ! $this->is_valid_page_id($page_id)) {
			$page_id = 0;
		}

		return array(
			'mode'                => $mode,
			'coming_soon_page_id' => $page_id,
		);
	}

	/**
	 * Check whether a page ID points to an existing non-trash Page.
	 *
	 * @param int $page_id Page ID.
	 */
	private function is_valid_page_id(int $page_id): bool {
		$page = get_post($page_id);

		return $page instanceof \WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status;
	}
}
