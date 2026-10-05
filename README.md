# Otour store layer (child theme of Egstore)

This theme holds only what is specific to عطور.ستور. Everything else comes
from the Egstore theme and the egstore-commerce plugin, which update without
touching this folder.

| File | Purpose |
|---|---|
| `config/pages/home.php` | Homepage: which theme sections, in which order, with this store's copy. Replaces the theme's `config/pages/home.php`. |
| `assets/store.css` | Brand layer: the dark homepage and section tokens. Values only, no structure. |
| `functions.php` | Loads `assets/store.css`. Nothing else belongs here. |

If something cannot be expressed with sections, settings or tokens, add the
capability to the Egstore theme (or plugin) instead of copying templates here.

## Settings that live in the database (not in code)

Set these on every environment (Appearance → Customize / Menus). WordPress
keeps Customizer settings per theme, so after activating this child theme for
the first time, copy the parent's settings (`theme_mods_Egstore-theme` →
`theme_mods_egstore-otour`) or re-enter them.

- **Colours** (تصميم Egstore → الألوان): primary `#007f5f`, primary hover
  `#55a630`, text `#192c27`, muted `#365951`, border `#e6f0ee`, inverse
  `#f2f7f6`, prices `#007f5f`, card button hover `#55a630`, card button hover
  icon `#192c27`.
- **Header**: header menu on, sidebar menu button off, account button off.
- **Announcement bar**: on, homepage only, four messages:
  شحن سريع لكل المحافظات · عروض مختارة تتجدد باستمرار ·
  منتجات أصلية من مصادر موثوقة · اختيارات عطرية تناسب كل حضور.
- **Menus**: `new-header` → Primary Navigation; "لكي أنتي" marked
  "عنصر مميز".
- **Homepage**: a static front page (the theme's front page renders the
  composition; no page template needs to be selected).
- **Content**: products for the best-sellers shelf go in the
  "الأكثر مبيعا" category (the section is hidden while it is empty); the
  hero image is media library attachment 489.
