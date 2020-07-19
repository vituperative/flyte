# FLYTE
I2P BitTorrent Tracker

Forked from the original ByteMonsoon tracker.

Requirements: I2P/i2pd, Web server with php support, MySQL or compatible database server.
* Jetty + <a href="http://php-java-bridge.sourceforge.net/pjb/">phpjavabridge</a> is supported.

# Todo:
- [ ] Code cleanup
- [ ] Support of list of trackers
- [ ] Built-in torrent search engine
- [ ] Multi-page install script with support for initial db creation, categories, admin creation etc
- [ ] Optional support of flatfiles (instead of mysql)
- [x] Jetty support (using phpbridge)
- [x] Refactor html markup and streamline css
- [ ] Implement torrent syncing with torrent.ano
- [ ] Optional automatic torrent upload to torrent.ano
- [ ] Configuration panel for administrator
- [ ] More configuration options during installation (categories, encryption type used for passwords etc)
- [ ] Optional hiding of torrent uploader on torrents page (enabled per-torrent or globally on profile page)
- [ ] Support avatar icons for users with upload and default fall-back icon
- [ ] Add option to delete account when logged in (with optional removal of all uploaded torrents and comments)
- [ ] Add sub-categories with language indicators
- [ ] Admin UI with support for bulk delete, db export, bulk torrent import, user admin, category add/delete
- [ ] Admin option to toggle opentracker capabilities
- [ ] Optional automatic import of torrents from other trackers via RSS
- [x] Fix upload error when torrent contains no trackers
