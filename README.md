ClipMaster (2007 remake / 2025 revival) — simple PHP + CSS demo
==============================================================

What this is
-------------
A small, self-contained demo of a classic video-sharing site ("ClipMaster") with features:
- Sign Up / Log In (simple file-backed user store)
- Upload Videos (store files under uploads/)
- Browse Categories, view Video pages
- Older-browser-friendly HTML 4.01 transitional layout and object/embed fallbacks

Files
-----
- index.php — home page with featured videos
- header.php / footer.php — templates
- lib.php — small helper library (data storage, auth, video functions)
- signup.php, login.php, logout.php — authentication flows
- upload.php — upload a video file and metadata
- video.php — view a single video with HTML5 + legacy fallbacks
- categories.php — list categories and videos by category
- style.css — stylesheet with older-browser compatibility
- data/ — writable directory where users.dat and videos.dat will be stored
- uploads/ — where uploaded videos are stored
- assets/placeholder.png — simple placeholder thumbnail (add yourself)

Quick setup
-----------
1. Place this project in a PHP-enabled webroot (Apache, Nginx + PHP-FPM, etc).
2. Ensure PHP can write to data/ and uploads/:
   mkdir data uploads
   chmod 755 data uploads
3. For older PHP installs, the code falls back to md5-based password hashing (not secure).
   For modern installs, PHP's password_hash() is used.
4. Open the site in your browser (e.g., http://localhost/clipmaster/)

Notes & Warnings
----------------
- This is a demo scaffold intended for local testing and nostalgia. It is NOT secure for production.
- Password hashing fallback (md5 + short salt) is insecure — upgrade to password_hash() and a proper DB for real use.
- File upload handling is minimal; real sites must validate and sanitize thoroughly and configure server-side upload limits.
- If you want to use a real database, replace the file-based functions in lib.php with PDO/MySQL calls.

Ideas for next steps
--------------------
- Migrate data storage to MySQL/Postgres and use PDO with prepared statements.
- Add real thumbnail generation (ffmpeg) and transcoding for multi-bitrate playback.
- Improve security: password reset, email verification, CSRF tokens, input sanitization.
- Add pagination, comments, view counters, and search.

License
-------
MIT-style demo. Use and modify at your own risk.
```
