# Misr-Child: store layer (child theme of Egstore) for عطور.ستور

This theme holds only what is specific to عطور.ستور. Everything else comes
from the Egstore theme and the egstore-commerce plugin, which update without
touching this folder. Built for Egstore theme and egstore-commerce **1.3.0**.

| File | Purpose |
|---|---|
| `config/pages/home.php` | Homepage: which theme sections, in which order, with this store's copy. Replaces the theme's `config/pages/home.php`. |
| `assets/store.css` | Brand layer: header menu style, section tweaks and the homepage's dark hero and features band (the rest of the homepage uses the theme's light palette). Values only, no structure. In the dark sections the border colour is frost at the Customizer's border opacity. |
| `assets/store.js` | Presentation only: the header menu's sliding underline and the looping categories band (`#catalogue`, as in the demo: no arrows, full width, stops while pointed at). Both are candidates to become Egstore theme options if another store wants them. |
| `inc/import-settings.php` | First activation only: builds Misr-Child's Customizer settings from the parent theme's settings on that site plus this store's defaults (see below). |
| `functions.php` | Loads `assets/store.css`, `assets/store.js` and the first-activation import. Nothing else belongs here. |

If something cannot be expressed with sections, settings or tokens, add the
capability to the Egstore theme (or plugin) instead of copying templates here.

## Settings that live in the database (not in code)

WordPress keeps Customizer settings per theme, so a child theme starts
empty. On its **first activation** Misr-Child fills its settings itself
(`inc/import-settings.php`):

1. whatever Misr-Child already has (a re-activation never overwrites);
2. the parent Egstore theme's settings on that site (colours, menu
   locations, logo: what the store already set);
3. the store defaults below (header, announcement bar, role colours), only
   where nothing above sets them.

The parent's Additional CSS is not carried over. It runs once (option
`misr_child_settings_imported`); earlier Misr-Child settings, if any, are
kept in `misr_child_settings_before_import`. Delete the marker option to run
it again on the next activation. After that, check these (Appearance →
Customize / Menus):

- **Colours** (تصميم Egstore → الألوان): primary `#007f5f`, primary hover
  `#55a630`, text `#192c27`, muted `#365951`, border `#e6f0ee`, inverse
  `#f2f7f6`, prices `#007f5f`, card button hover `#55a630`, card button hover
  icon `#192c27`. Since 1.3.0: border opacity ("شفافية الحدود") 30% (the
  default; it also scales the dark homepage's borders) and rating stars
  ("نجوم التقييم") empty for the theme's default yellow-orange.
- **Header**: header menu on, sidebar menu button off, account button off.
  On phones (Egstore 1.3.0) the header shows the logo only and the bottom
  bar carries search, home, shop and the cart (no menu item while the
  sidebar menu is off; the account link is in the footer).
- **Announcement bar**: on, homepage only, four messages:
  شحن سريع لكل المحافظات · عروض مختارة تتجدد باستمرار ·
  منتجات أصلية من مصادر موثوقة · اختيارات عطرية تناسب كل حضور.
- **Menus**: `new-header` → Primary Navigation; "لكي أنتي" (and "كل العطور")
  marked "عنصر مميز". **Manual on every site**: the "عنصر مميز" box is on
  the menu item itself (Appearance → Menus), not a Customizer setting.
- **Homepage**: a static front page (the theme's front page renders the
  composition; no page template needs to be selected).
- **Content**: products for the best-sellers shelf go in the
  "الأكثر مبيعا" category (the section is hidden while it is empty); the
  hero image is media library attachment 489.
