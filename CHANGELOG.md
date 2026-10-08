# Misr-Child Changelog

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
