# Misr-Child Changelog

## 1.3.4

- Homepage section headings with a button opposite (product sections' and
  the categories' "عرض الكل"): the button in the theme's button colours
  (Customizer primary and its hover), and a divider under the heading,
  before the products, in the theme's border colour and opacity.

## 1.3.3

- Hero on desktops (64rem and up): the title stays on one line; its room
  widens to 64rem while the description, search and suggestions keep the
  theme's width.
- Features band on phones: two a row instead of one, titles at 16px so each
  stays on one line.

## 1.3.2

Needs Egstore theme 1.3.2.

- Homepage copy without tashkeel. Hero: eyebrow "عطور.ستور", title "أكمل
  أناقتك بعطرك الفريد", new description; centred on phones
  (`mobile_align`), with an even overlay behind it there.
- Categories: "عوالم متنوعة لعطورك من عطور" with a new description; overlay
  cards (3:4) and "عرض الكل", which turns the band into the theme's grid
  (the loop stops and its copies step aside).
- Body splashes: "سبلاشات من لطافة" with a new description.
- Order: the features band follows the categories; women's and men's
  perfumes come before the original perfumes.

## 1.3.1

- Categories band (#catalogue): the loop is a CSS animation instead of a
  script moving the band every frame, and it stands still while off screen.
  store.js only measures one set of cards (on load and resize). Same speed
  and direction, still stops while pointed at or focused. Lighthouse
  (mobile): store.js main-thread time from 6-14 s to under 0.2 s.

## 1.3.0

- Private updates: Misr-Child now updates from WordPress → Updates through
  the Egstore update gateway, like the parent theme and the plugin.
  `style.css` declares `Update URI` and `Egstore Update Key` (the Ed25519
  public key its releases are signed with). Needs egstore-commerce 1.3.1 or
  newer, which reads these headers.
- Releases are built by `scripts/release/build.php` (tag `v{version}`,
  signed manifest, checksums), the same tool as the Egstore theme.

## 1.2.0

- First activation carries the store's settings over
  (`inc/import-settings.php`).
