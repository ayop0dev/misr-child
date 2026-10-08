# Misr-Child Changelog

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
