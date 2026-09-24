<?php
/**
 * Public contact form shortcode.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Frontend;

use ContactWorkflow\Admin\DepartmentFields;
use ContactWorkflow\PostTypes\DepartmentPostType;
use WP_Post;

/**
 * Renders the Contact Workflow public form.
 */
final class FormShortcode {
	/**
	 * Form handler.
	 */
	private FormHandler $handler;

	/**
	 * Constructor.
	 *
	 * @param FormHandler $handler Form handler.
	 */
	public function __construct(FormHandler $handler) {
		$this->handler = $handler;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_shortcode('contact_workflow_form', array($this, 'render'));
	}

	/**
	 * Render the shortcode output.
	 *
	 * @param mixed $atts Shortcode attributes.
	 */
	public function render($atts = array()): string {
		unset($atts);

		$departments = $this->get_active_departments();
		$values = $this->handler->get_values();
		$errors = $this->handler->get_errors();
		$has_success = isset($_GET[FormHandler::SUCCESS_QUERY_ARG]) && '1' === (string) wp_unslash($_GET[FormHandler::SUCCESS_QUERY_ARG]);
		$return_post_id = get_queried_object_id();

		ob_start();
		?>
		<div class="contact-workflow-form">
			<?php if ($has_success && array() === $errors) : ?>
				<div class="contact-workflow-form__notice contact-workflow-form__notice--success" role="status">
					<?php esc_html_e('Your message has been sent successfully.', 'contact-workflow'); ?>
				</div>
			<?php endif; ?>

			<?php if (array() !== $errors) : ?>
				<div class="contact-workflow-form__notice contact-workflow-form__notice--error" role="alert">
					<?php echo esc_html($errors['general'] ?? __('Please review the highlighted fields and try again.', 'contact-workflow')); ?>
				</div>
			<?php endif; ?>

			<?php if (array() === $departments) : ?>
				<div class="contact-workflow-form__notice contact-workflow-form__notice--error" role="status">
					<?php esc_html_e('Contact submission is currently unavailable. Please try again later.', 'contact-workflow'); ?>
				</div>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url($this->get_form_action_url()); ?>" class="contact-workflow-form__form" novalidate>
					<?php wp_nonce_field(FormHandler::NONCE_ACTION, FormHandler::NONCE_FIELD); ?>
					<input type="hidden" name="<?php echo esc_attr(FormHandler::ACTION_FIELD); ?>" value="<?php echo esc_attr(FormHandler::ACTION_VALUE); ?>">
					<?php if ($return_post_id > 0) : ?>
						<input type="hidden" name="<?php echo esc_attr(FormHandler::RETURN_POST_ID_FIELD); ?>" value="<?php echo esc_attr((string) $return_post_id); ?>">
					<?php endif; ?>

					<?php $this->render_text_field(FormHandler::FIELD_NAME, 'name', __('Name', 'contact-workflow'), $values, $errors, true, 'text'); ?>
					<?php $this->render_text_field(FormHandler::FIELD_EMAIL, 'email', __('Email', 'contact-workflow'), $values, $errors, true, 'email'); ?>
					<?php $this->render_text_field(FormHandler::FIELD_PHONE, 'phone', __('Phone', 'contact-workflow'), $values, $errors, false, 'tel'); ?>

					<div class="contact-workflow-form__field contact-workflow-form__field--department">
						<label for="contact-workflow-department"><?php esc_html_e('Department', 'contact-workflow'); ?> <span aria-hidden="true">*</span></label>
						<select id="contact-workflow-department" name="<?php echo esc_attr(FormHandler::FIELD_DEPARTMENT); ?>" required aria-invalid="<?php echo isset($errors['department']) ? 'true' : 'false'; ?>">
							<option value=""><?php esc_html_e('Select a department', 'contact-workflow'); ?></option>
							<?php foreach ($departments as $department) : ?>
								<option value="<?php echo esc_attr((string) $department->ID); ?>" <?php selected($values['department'] ?? '', (string) $department->ID); ?>>
									<?php echo esc_html(get_the_title($department)); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<?php $this->render_field_error('department', $errors); ?>
					</div>

					<?php $this->render_text_field(FormHandler::FIELD_SUBJECT, 'subject', __('Subject', 'contact-workflow'), $values, $errors, true, 'text'); ?>

					<div class="contact-workflow-form__field contact-workflow-form__field--message">
						<label for="contact-workflow-message"><?php esc_html_e('Message', 'contact-workflow'); ?> <span aria-hidden="true">*</span></label>
						<textarea id="contact-workflow-message" name="<?php echo esc_attr(FormHandler::FIELD_MESSAGE); ?>" rows="6" required aria-invalid="<?php echo isset($errors['message']) ? 'true' : 'false'; ?>"><?php echo esc_textarea($values['message'] ?? ''); ?></textarea>
						<?php $this->render_field_error('message', $errors); ?>
					</div>

					<p class="contact-workflow-form__actions">
						<button type="submit" class="contact-workflow-form__submit"><?php esc_html_e('Submit', 'contact-workflow'); ?></button>
					</p>
				</form>
			<?php endif; ?>
		</div>
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Render a text-like field.
	 *
	 * @param string                $input_name Input name.
	 * @param string                $value_key Sanitized value key.
	 * @param string                $label Field label.
	 * @param array<string, string> $values Sanitized values.
	 * @param array<string, string> $errors Validation errors.
	 * @param bool                  $required Whether the field is required.
	 * @param string                $type Input type.
	 */
	private function render_text_field(string $input_name, string $value_key, string $label, array $values, array $errors, bool $required, string $type): void {
		$field_id = 'contact-workflow-' . str_replace('_', '-', $value_key);
		?>
		<div class="contact-workflow-form__field contact-workflow-form__field--<?php echo esc_attr($value_key); ?>">
			<label for="<?php echo esc_attr($field_id); ?>">
				<?php echo esc_html($label); ?><?php if ($required) : ?> <span aria-hidden="true">*</span><?php endif; ?>
			</label>
			<input id="<?php echo esc_attr($field_id); ?>" type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($values[$value_key] ?? ''); ?>" <?php echo $required ? 'required' : ''; ?> aria-invalid="<?php echo isset($errors[$value_key]) ? 'true' : 'false'; ?>">
			<?php $this->render_field_error($value_key, $errors); ?>
		</div>
		<?php
	}

	/**
	 * Render a field-specific validation error.
	 *
	 * @param string                $field Field key.
	 * @param array<string, string> $errors Validation errors.
	 */
	private function render_field_error(string $field, array $errors): void {
		if (! isset($errors[$field])) {
			return;
		}
		?>
		<p class="contact-workflow-form__error"><?php echo esc_html($errors[$field]); ?></p>
		<?php
	}

	/**
	 * Get active Departments for the select field.
	 *
	 * @return WP_Post[]
	 */
	private function get_active_departments(): array {
		$departments = get_posts(
			array(
				'post_type'      => DepartmentPostType::KEY,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$departments = array_values(
			array_filter(
				$departments,
				static function ($department): bool {
					if (! $department instanceof WP_Post) {
						return false;
					}

					$value = get_post_meta($department->ID, DepartmentFields::META_ACTIVE, true);

					return '' === $value || filter_var($value, FILTER_VALIDATE_BOOLEAN);
				}
			)
		);

		usort(
			$departments,
			static function (WP_Post $first, WP_Post $second): int {
				$first_order = get_post_meta($first->ID, DepartmentFields::META_ORDER, true);
				$second_order = get_post_meta($second->ID, DepartmentFields::META_ORDER, true);
				$order_compare = (is_numeric($first_order) ? (int) $first_order : 0) <=> (is_numeric($second_order) ? (int) $second_order : 0);

				if (0 !== $order_compare) {
					return $order_compare;
				}

				return strcasecmp($first->post_title, $second->post_title);
			}
		);

		return $departments;
	}

	/**
	 * Get the form action URL.
	 */
	private function get_form_action_url(): string {
		$request_uri = isset($_SERVER['REQUEST_URI']) && is_scalar($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
		$url = home_url($request_uri);

		return remove_query_arg(FormHandler::SUCCESS_QUERY_ARG, $url);
	}
}
