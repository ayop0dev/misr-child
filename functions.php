<?php
/**
 * Misr-Child: the store layer for عطور.ستور (otour.store).
 *
 * Only this store's identity lives here: the homepage composition
 * (config/pages/home.php replaces the parent's) and brand styling through the
 * parent theme's tokens. Behaviour belongs in the Egstore theme or the
 * egstore-commerce plugin; settings (colours, menus, announcement bar) live
 * in the Customizer.
 *
 * @package MisrChild
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// First activation: the store's Customizer settings move over to Misr-Child.
require_once __DIR__ . '/inc/import-settings.php';

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$path = get_stylesheet_directory() . '/assets/store.css';

		wp_enqueue_style(
			'misr-child',
			get_stylesheet_directory_uri() . '/assets/store.css',
			['egstore-core'],
			(string) filemtime($path)
		);

		// Presentation only: the header menu's sliding underline and the
		// looping categories band.
		$storeScript = get_stylesheet_directory() . '/assets/store.js';
		wp_enqueue_script(
			'misr-child',
			get_stylesheet_directory_uri() . '/assets/store.js',
			[],
			(string) filemtime($storeScript),
			['in_footer' => true, 'strategy' => 'defer']
		);
	},
	20
);
