<?php
/**
 * First activation: carry the store's Customizer settings over to Misr-Child.
 *
 * WordPress keeps Customizer settings per theme (theme_mods_{slug}), so a
 * child theme starts empty and the store would lose its colours, menus and
 * header settings. The first time Misr-Child is active, its settings are
 * built from, in order of precedence:
 *   1. anything Misr-Child already has (a re-activation never overwrites);
 *   2. the parent Egstore theme's settings on this site (what the store set);
 *   3. this store's defaults below (عطور.ستور's header, announcement bar and
 *      role colours), only where nothing above sets them.
 * The parent's Additional CSS is not carried over (old hand-made fixes stay
 * behind). The previous Misr-Child settings, if any, are kept as a backup
 * option, and a marker makes this run once.
 *
 * @package MisrChild
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

const MISR_CHILD_IMPORT_MARKER = 'misr_child_settings_imported';
const MISR_CHILD_IMPORT_BACKUP = 'misr_child_settings_before_import';

/** @return array<string, mixed> عطور.ستور's settings (see README.md). */
function misr_child_store_defaults(): array
{
	return [
		'egstore_header_show_menu'             => true,
		'egstore_header_show_drawer'           => false,
		'egstore_header_show_account'          => false,
		'egstore_announcement_enabled'         => true,
		'egstore_announcement_placement'       => 'home',
		'egstore_announcement_1'               => 'شحن سريع لكل المحافظات',
		'egstore_announcement_2'               => 'عروض مختارة تتجدد باستمرار',
		'egstore_announcement_3'               => 'منتجات أصلية من مصادر موثوقة',
		'egstore_announcement_4'               => 'اختيارات عطرية تناسب كل حضور',
		'egstore_color_primary'                => '#007f5f',
		'egstore_color_primary_hover'          => '#55a630',
		'egstore_color_text'                   => '#192c27',
		'egstore_color_muted'                  => '#365951',
		'egstore_color_border'                 => '#e6f0ee',
		'egstore_color_inverse'                => '#f2f7f6',
		'egstore_color_price'                  => '#007f5f',
		'egstore_color_card_action_hover'      => '#55a630',
		'egstore_color_card_action_hover_text' => '#192c27',
	];
}

/**
 * The settings Misr-Child gets on its first activation.
 *
 * @param array<string, mixed> $child  Misr-Child's current settings.
 * @param array<string, mixed> $parent The parent theme's settings.
 * @return array<string, mixed>
 */
function misr_child_merge_settings(array $child, array $parent): array
{
	unset($parent['custom_css_post_id']);

	return array_replace(misr_child_store_defaults(), $parent, $child);
}

function misr_child_import_settings(): void
{
	if (get_option(MISR_CHILD_IMPORT_MARKER)) {
		return;
	}

	$stylesheet = get_stylesheet();
	$child = get_option('theme_mods_' . $stylesheet);
	$child = is_array($child) ? $child : [];
	$parent = get_option('theme_mods_' . get_template());
	$parent = is_array($parent) ? $parent : [];

	if ($child !== []) {
		update_option(MISR_CHILD_IMPORT_BACKUP, $child, false);
	}

	update_option('theme_mods_' . $stylesheet, misr_child_merge_settings($child, $parent));
	update_option(MISR_CHILD_IMPORT_MARKER, gmdate('c'), false);
}

// After WordPress's own after_switch_theme handlers: _wp_menus_changed()
// writes the new theme's settings from a copy it read earlier, and would
// overwrite an import made before it.
add_action('after_switch_theme', 'misr_child_import_settings', 100);
