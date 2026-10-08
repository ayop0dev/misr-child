<?php
/**
 * Otour homepage composition (replaces the Egstore theme's home.php).
 *
 * Sections, data and behaviour come from the Egstore theme and the commerce
 * plugin; this file only chooses them, in order, with this store's copy.
 * Images are media library attachment IDs; category cards and banners use
 * each category's own thumbnail and link.
 *
 * @package MisrChild
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$shopUrl = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$categoryUrl = static function (string $slug) use ($shopUrl): string {
	$url = get_term_link($slug, 'product_cat');

	return is_string($url) ? $url : $shopUrl;
};

return [
	[
		'section' => 'hero',
		'config'  => [
			'eyebrow'             => 'عطور.ستور',
			'title'               => 'أكمل أناقتك بعطرك الفريد',
			'description'         => 'مختارات استثنائية من العطور العربية تمنحك بصمة فريدة',
			'mobile_align'        => 'center',
			'image'               => 489,
			'search'              => true,
			'search_placeholder'  => 'ابحث عن عطرك أو النوتة المفضلة...',
			'search_button_label' => 'استكشف العطور',
			'suggestions_label'   => 'نوتات رائجة:',
			'suggestions'         => ['عود', 'عنبر', 'مسك', 'زعفران', 'ورد'],
		],
	],
	[
		'section' => 'category-showcase',
		'config'  => [
			'id'             => 'catalogue',
			'title'          => 'عوالم متنوعة لعطورك من عطور',
			'description'    => 'من العطور الشخصية والمسكات إلى أجواء تعطير المنزل والسيارة.',
			'view_all_label' => 'عرض الكل',
			'card_style'     => 'overlay',
			'categories'     => [
				'incense',
				'raw-essential-oil',
				'original',
				'for-kid',
				'for-him',
				'for-her',
				'fragrant',
				'burners',
				'musk',
				'bodysplash',
				'hair-mist',
				'air-freshener',
				'home-air-freshener',
				'car-air-fresheners',
			],
			'copy'           => [
				'incense'            => ['label' => 'طيوب التراث', 'text' => 'رقائق وبخور عربي لنفحات الضيافة الأصيلة.'],
				'raw-essential-oil'  => ['label' => 'خلاصات نقية', 'text' => 'زيوت وأدهان عطرية مركزة بثبات ممتد.'],
				'original'           => ['label' => 'نيش أصيل', 'text' => 'عطور أصلية مختارة من دور موثوقة.'],
				'for-kid'            => ['label' => 'عناية رقيقة', 'text' => 'نفحات لطيفة مصممة للصغار.'],
				'for-him'            => ['label' => 'طابع رجالي', 'text' => 'توليفات خشبية وعطرية لحضور واثق.'],
				'for-her'            => ['label' => 'أنوثة فاتنة', 'text' => 'نفحات زهرية ومخملية مميزة.'],
				'fragrant'           => ['label' => 'أجواء معمارية', 'text' => 'فواحات تنشر العطر بهدوء في المكان.'],
				'burners'            => ['label' => 'تحف الاقتناء', 'text' => 'مباخر تضيف حضورا للمجلس والمنزل.'],
				'musk'               => ['label' => 'نقاء مطلق', 'text' => 'مختارات المسك للنقاء والثبات.'],
				'bodysplash'         => ['label' => 'عناية يومية', 'text' => 'رذاذات منعشة للجسم طوال اليوم.'],
				'hair-mist'          => ['label' => 'لمعان وثبات', 'text' => 'معطرات شعر خفيفة وعملية.'],
				'air-freshener'      => ['label' => 'انتعاش فندقي', 'text' => 'رذاذات جو لنفحات واضحة ونظيفة.'],
				'home-air-freshener' => ['label' => 'سكينة الأرجاء', 'text' => 'عطور للمفارش ومساحات المنزل.'],
				'car-air-fresheners' => ['label' => 'مقصورة فارهة', 'text' => 'معطرات ترافقك في كل رحلة.'],
			],
		],
	],
	[
		'section' => 'features',
		'config'  => [
			'id'    => 'charter',
			'items' => [
				['icon' => 'circle-check', 'title' => 'أصالة موثوقة', 'text' => 'منتجات منتقاة من مصادر موثوقة.'],
				['icon' => 'truck', 'title' => 'شحن لكل المحافظات', 'text' => 'توصيل منظم ومتابعة واضحة للطلب.'],
				['icon' => 'zap', 'title' => 'عروض متجددة', 'text' => 'اختيارات وعروض مناسبة طوال الوقت.'],
				['icon' => 'message-circle', 'title' => 'دعم مستمر', 'text' => 'نساعدك في اختيار المنتج المناسب.'],
			],
		],
	],
	[
		// Products the merchant puts in the "الأكثر مبيعا" category; the
		// section stays hidden while the category is empty.
		'section' => 'product-collection',
		'config'  => [
			'id'           => 'bestsellers',
			'title'        => 'الأكثر مبيعا',
			'description'  => 'مختارات رائجة من العطور والنفحات التي يفضلها عملاؤنا.',
			'action_label' => 'عرض الكل',
			'action_url'   => $categoryUrl('الأكثر-مبيعا'),
			'show_rating'  => true,
			'source'       => ['type' => 'latest', 'limit' => 8, 'category' => 'الأكثر-مبيعا'],
		],
	],
	[
		'section' => 'product-collection',
		'config'  => [
			'id'           => 'body-sprays',
			'title'        => 'سبلاشات من لطافة',
			'description'  => 'مختارات لطافة الأصلية لمعطرات الجسم للإستخدام اليومي',
			'action_label' => 'عرض الكل',
			'action_url'   => $categoryUrl('bodysplash'),
			'layout'       => 'split',
			'show_rating'  => true,
			'source'       => ['type' => 'latest', 'limit' => 6, 'category' => 'bodysplash'],
			'promo'        => [
				'title'        => 'انتعاش يرافقك',
				'text'         => 'معطرات جسم لنفحات خفيفة ومتجددة طوال اليوم.',
				'action_label' => 'تصفح المجموعة',
				'category'     => 'bodysplash',
			],
		],
	],
	[
		'section' => 'product-collection',
		'config'  => [
			'id'           => 'for-her',
			'title'        => 'عطور نسائية',
			'description'  => 'توليفات أنثوية منتقاة تجمع بين الرقة والحضور والثبات.',
			'action_label' => 'عرض الكل',
			'action_url'   => $categoryUrl('for-her'),
			'show_rating'  => true,
			'source'       => ['type' => 'latest', 'limit' => 8, 'category' => 'for-her'],
		],
	],
	[
		'section' => 'product-collection',
		'config'  => [
			'id'           => 'for-him',
			'title'        => 'عطور رجالي',
			'description'  => 'عطور رجالية مختارة بطابع واثق ونفحات تدوم.',
			'action_label' => 'عرض الكل',
			'action_url'   => $categoryUrl('for-him'),
			'show_rating'  => true,
			'source'       => ['type' => 'latest', 'limit' => 8, 'category' => 'for-him'],
		],
	],
	[
		'section' => 'product-collection',
		'config'  => [
			'id'           => 'new-arrivals',
			'title'        => 'العطور الأصلية والإصدارات المميزة',
			'description'  => 'توليفات منتقاة لحضور واضح وثبات يدوم.',
			'action_label' => 'عرض الكل',
			'action_url'   => $shopUrl,
			'show_rating'  => true,
			'source'       => ['type' => 'latest', 'limit' => 8],
		],
	],
	[
		'section' => 'promo-banners',
		'config'  => [
			'id'          => 'sanctuaries',
			'title'       => 'سكينة المنزل وفخامة الطريق',
			'description' => 'حلول عطرية تنشر الراحة والانتعاش في مساحتك ومقصورة سيارتك.',
			'items'       => [
				[
					'label'        => 'أجواء المنزل',
					'title'        => 'نفحات تمنح مساحتك طابعا خاصا',
					'action_label' => 'استكشف المجموعة',
					'category'     => 'home-air-freshener',
				],
				[
					'label'        => 'مقصورة السيارة',
					'title'        => 'أناقة ترافقك في كل رحلة',
					'action_label' => 'استكشف المجموعة',
					'category'     => 'car-air-fresheners',
				],
			],
		],
	],
];
