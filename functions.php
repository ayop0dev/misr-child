<?php
/**
 * Otour store layer.
 *
 * Only this store's identity lives here: the homepage composition
 * (config/pages/home.php replaces the parent's) and brand styling through the
 * parent theme's tokens. Behaviour belongs in the Egstore theme or the
 * egstore-commerce plugin; settings (colours, menus, announcement bar) live
 * in the Customizer.
 *
 * @package EgstoreOtour
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$path = get_stylesheet_directory() . '/assets/store.css';

		wp_enqueue_style(
			'egstore-otour',
			get_stylesheet_directory_uri() . '/assets/store.css',
			['egstore-core'],
			(string) filemtime($path)
		);

		// Presentation only: the header menu's sliding underline.
		$menuScript = get_stylesheet_directory() . '/assets/menu.js';
		wp_enqueue_script(
			'egstore-otour-menu',
			get_stylesheet_directory_uri() . '/assets/menu.js',
			[],
			(string) filemtime($menuScript),
			['in_footer' => true, 'strategy' => 'defer']
		);
	},
	20
);
