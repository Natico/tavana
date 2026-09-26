<?php
/**
 * Coming Soon standalone template.
 *
 * @package SiteMode
 */

defined('ABSPATH') || exit;
?>
<!doctype html>
<html lang="<?php echo esc_attr($document_language); ?>" dir="<?php echo $is_rtl ? 'rtl' : 'ltr'; ?>">
<head>
	<meta charset="<?php echo esc_attr(get_bloginfo('charset')); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html($document_title); ?></title>
	<link rel="stylesheet" href="<?php echo esc_url($stylesheet_url); ?>">
	<?php do_action('site_mode_standalone_head'); ?>
</head>
<body class="site-mode-coming-soon <?php echo $is_rtl ? 'site-mode-coming-soon--rtl' : 'site-mode-coming-soon--ltr'; ?>">
	<main class="site-mode-coming-soon__page">
		<article class="site-mode-coming-soon__content">
			<div class="site-mode-coming-soon__body">
				<?php echo wp_kses_post($content); ?>
			</div>
		</article>
	</main>
	<?php do_action('site_mode_standalone_footer'); ?>
</body>
</html>
