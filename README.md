```markdown
ClipMaster — 2025 Revival (updated project)
===========================================

Overview
--------
This is an updated ClipMaster demo: a nostalgic video-sharing scaffold modernized with:
- PDO-based storage (default: SQLite) with a graceful fallback to the original file-based storage.
- Secure password hashing (password_hash where available), CSRF tokens, prepared statements.
- Improved upload handling: MIME checks, extension whitelist, upload size limit and optional ffmpeg thumbnail generation.
- Comments, search, pagination, and a simple but compatible UI.
- Backwards compatible with older browsers via simple HTML/CSS and object/embed fallbacks.

Files of interest
-----------------
- config.example.php — copy to config.php and edit if needed.
- lib.php — main library with DB + file fallback, auth, uploads, CSRF.
- index.php, signup.php, login.php, upload.php, video.php — main app pages.
- style.css — stylesheet.
- data/ — serialized files (fallback) or SQLite DB file.
- uploads/ — uploaded videos; thumbs in uploads/thumbs/.
- assets/placeholder.png — add your placeholder image.

Quick setup
-----------
1. Copy config.example.php -> config.php and adjust settings (if you want MySQL fill pdo_dsn/user/pass).
2. Ensure writable directories:
   mkdir data uploads uploads/thumbs
   chmod 755 data uploads uploads/thumbs
3. If using SQLite, the DB file will be created automatically inside data/.
4. For thumbnails, install ffmpeg on the server (optional).
5. Open index.php in your browser.

Security & Notes
----------------
- This is still a demo scaffold — do not run exposed to the public internet without hardening.
- Use HTTPS in production.
- Consider migration to a robust DB and add rate limiting, anti-spam (CAPTCHA), email verification, and stronger file scanning.

Next steps (ideas already prepared)
-----------------------------------
- Add an installer script to guide config.php creation and set filesystem permissions.
- Add background transcoding (ffmpeg) to create web-optimized mp4/webm copies.
- Add account management (password reset via email), admin moderation, and user avatars.

Enjoy the ClipMaster revival!
```