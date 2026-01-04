# PLAYGO - Web IPTV Player

[![PHP](https://img.shields.io/badge/PHP-7.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery-3.5+-0769AD?style=flat-square&logo=jquery&logoColor=white)](https://jquery.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4+-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/License-CC%20BY--NC%204.0-EF9421?style=flat-square&logo=creative-commons&logoColor=white)](https://creativecommons.org/licenses/by-nc/4.0/)
[![Xtream UI](https://img.shields.io/badge/Xtream%20UI-Compatible-00A86B?style=flat-square&logo=stream&logoColor=white)](#)
[![Visitor Counter](https://visitor-badge.laobi.icu/badge?page_id=PLAYGO-Web-IPTV-Player&left_color=blue&right_color=green)](https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player)

**PLAYGO** is a lightweight, modular web-based IPTV player that connects to Xtream UI-compatible IPTV services. It does not redistribute or host any content; instead, it allows users to stream IPTV by configuring the provider's IP and port. PLAYGO is ideal for users who want a centralized and personalized IPTV experience.

![Preview Screenshot](assets/image/Screenshot_1.png)

![Preview Screenshot](assets/image/Screenshot_2.png)

![Preview Screenshot](assets/image/Screenshot_3.png)

---

## 🚀 Features

- 📺 Stream live TV, movies, and series via Xtream UI.
- 🧩 Modular file structure for easy maintenance and scalability.
- 🎨 Multiple visual themes (Aqua, Blue, Orange, Pink).
- 📂 No MySQL required — uses flat files and JSON.
- 🔒 Local caching of TMDB media metadata and artwork.
- ⭐ Favorites, recommendations, history, and sagas.
- 🔍 Advanced filtering system for movies (genre, rating, year, sorting).
- 📅 Smart sorting by year with month consideration.
- 🎯 Clean URL structure for filters.
- 💾 Local fonts (no external CDN dependencies).
- 📱 Fully responsive design for desktop and mobile devices.

---

## 📁 Folder Structure

```

PLAYGO/
├── index.php              # Redirects to login.php
├── login.php              # Login page
├── Xtream_api.php         # Xtream UI API integration
├── channels.php           # Channel listing page
├── channel.php            # Channel details and player
├── movies.php             # Movie listing with filters
├── movie.php              # Movie details and player
├── series.php             # Series listing
├── serie.php              # Series details and episodes
├── home.php               # Home dashboard
├── profile.php            # User profile and settings
├── favorites.php          # Favorites management
├── collection.php         # Collections/sagas
├── populares.php          # Popular content
├── sagas.php              # Movie sagas
├── .gitignore             # Git ignore rules
├── LICENSE
├── README.md
├── assets/                # Assets organized by type
│   ├── channels/          # Channel logos (user-generated, ignored by git)
│   ├── fonts/            # Local font files
│   ├── icon/             # Icons (favicon, buttons)
│   ├── image/            # Images (wallpaper, screenshots)
│   └── logo/             # Logo files
├── styles/                # CSS organized by feature
│   ├── core/             # Core styles (main.css, fonts)
│   ├── channels/         # Channel page styles
│   ├── channel/          # Channel detail styles
│   ├── movies/           # Movie page styles
│   ├── search/           # Search modal styles
│   └── login/            # Login page styles
├── scripts/               # JavaScript organized by feature
│   ├── core/             # Core utilities
│   ├── channels/         # Channel functionality
│   ├── channel/          # Channel player
│   ├── movies/           # Movie filters and modals
│   ├── vendors/          # Third-party libraries
│   └── login/            # Login page scripts
├── db/                    # Cached data in JSON (data.json ignored by git)
├── libs/                  # Libraries and utilities
│   ├── controllers/      # MVC Controllers
│   ├── services/         # Business logic services
│   ├── endpoints/        # API endpoints
│   ├── views/            # View templates
│   ├── config.php        # Configuration (use config.example.php)
│   └── lib.php           # General utilities
├── tmdb_cache/           # Cached images from TMDB
└── vendor/               # External dependencies (Composer, PHPMailer)

````

---

## 🛠️ Requirements

- Web server: Apache or Nginx
- PHP 7.2+ with extensions:
  - `cURL`, `json`, `mbstring`, etc. (see `php7.2-ext.sh`)
- No MySQL required
- Xtream UI service credentials (IP, port, username, password)
- Optional: [Composer](https://getcomposer.org/) for PHP dependencies
- Write permissions for:
  - `db/`, `tmdb_cache/`, `collection/`


---

## 📖 Usage Guide

### Initial Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player.git
   cd PLAYGO-Web-IPTV-Player
   ```

2. Copy the configuration example:
   ```bash
   cp libs/config.example.php libs/config.php
   ```

3. Edit `libs/config.php` with your settings:
   - Set your Xtream UI server IP/URL
   - Configure SMTP settings (if using trial accounts)
   - Set TMDB API key (for movie/series metadata)

4. Set write permissions:
   ```bash
   chmod -R 755 db/ tmdb_cache/ assets/channels/
   ```

### Using the Player

* Access the web interface and log in with your Xtream credentials.
* Use the navigation menu to browse:
  * **Live channels** - Browse and watch live TV channels
  * **Movies** - Filter by genre, rating (1-10), year, and sort options
  * **Series** - Browse series and watch episodes
* Select an item to begin streaming.
* Use features like Favorites, History, and Recommendations to customize your experience.

### Movie Filtering

The movie section includes advanced filtering:
- **Genre** - Filter by movie genre
- **Rating** - Filter by rating (1-10 scale, integer values)
- **Year** - Filter by specific year or year range
- **Sorting** - Sort by name, year (with month consideration), rating, or date added

---

## 👨‍💻 Developer Notes & Contributions

* Modular architecture with clearly separated logic and presentation.
* This project does **not** redistribute content — it only plays streams configured by the user.
* Contributions welcome!

  * Fork the repo
  * Create a feature branch
  * Submit a pull request with a detailed description

### Code Style

* Follows PHP standards with modular organization.
* MVC pattern: Controllers in `/libs/controllers`, Services in `/libs/services`.
* Assets organized by type: `/assets/icon`, `/assets/image`, `/assets/logo`, `/assets/fonts`.
* Styles organized by feature: `/styles/core`, `/styles/channels`, `/styles/movies`, etc.
* JavaScript organized by feature: `/scripts/core`, `/scripts/channels`, `/scripts/movies`, etc.
* No comments in code (as per project standards).
* Clean URLs for filters (no empty parameters).

### Recent Improvements

* ✅ Removed unused files (`assinatura.php`, `libs/idioma.php`)
* ✅ Cleaned up configuration files
* ✅ Improved movie filtering system (integer ratings, clean URLs)
* ✅ Enhanced year sorting (includes month consideration)
* ✅ Local fonts (no external CDN dependencies)
* ✅ Responsive design improvements for mobile devices
* ✅ Code cleanup and optimization

---

## 📄 License

This project is licensed under the **Creative Commons Attribution-NonCommercial (CC BY-NC)** license.

See the [LICENSE](LICENSE) file for details.

---

## 🙏 Disclaimer

PLAYGO does not host, store, or provide any IPTV content. It is a tool for connecting to legally obtained IPTV services. You are solely responsible for the content you access.

---

## ⚙️ Configuration

### Important Files

- `libs/config.php` - Main configuration file (not tracked in git)
- `libs/config.example.php` - Configuration template
- `.gitignore` - Files ignored by git:
  - `libs/config.php` (contains sensitive data)
  - `db/data.json` (user data)
  - `assets/channels/` (user-generated channel logos)

### Configuration Variables

Key settings in `libs/config.php`:
- `IP` - Your Xtream UI server URL
- `TMDB_API_KEY` - The Movie Database API key
- `XTREAM_URL`, `XTREAM_USER`, `XTREAM_PWD` - Xtream CMS credentials
- `SMTP_*` - Email configuration for trial accounts
- `$customChannelLogos` - Array for custom channel logo mappings

---

## 📬 Contact

For issues, suggestions, or contributions, please open an [Issue](https://github.com/Jeremias0618/PLAYGO/issues) or [Pull Request](https://github.com/Jeremias0618/PLAYGO/pulls).

---

