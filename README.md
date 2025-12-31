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

---

## 📁 Folder Structure

```

PLAYGO/
├── index.php              # Redirects to login.php
├── login.php               # Login page
├── Xtream\_api.php        # Xtream UI API integration
├── channels.php / canal.php # Channel listing and details
├── filmes.php / filme.php # Movie listing and details
├── series.php / serie.php # Series listing and episode view
├── assinatura.php         
├── home.php             
├── install.sh             # Initial setup script
├── php7.2-ext.sh          # PHP extensions installer
├── connection.php         
├── LICENSE
├── README.md
├── assets/                # Assets organized by type
│   ├── icon/              # Icons (favicon, buttons)
│   ├── image/             # Images (wallpaper, screenshots)
│   └── logo/              # Logo files
├── collection/            # Media files (audio, images)
├── styles/                # Additional styles
│   └── login/             # Login page styles
├── db/                    # Cached data in JSON
├── inc/                   # Reusable PHP includes
├── scripts/               # Main JavaScript files
│   └── login/             # Login page scripts
├── libs/                  # Libraries and utilities
│   ├── controllers/       # Controllers (login, etc.)
│   ├── services/          # Services (authentication, etc.)
│   ├── config.php         # Configuration
│   ├── idioma.php         # Language files
│   └── lib.php            # General utilities
├── tmdb\_cache/           # Cached images from TMDB
└── vendor/                # External dependencies (Composer, PHPMailer)

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

* Access the web interface and log in with your Xtream credentials.
* Use the navigation menu to browse:

  * Live channels
  * Movies
  * Series and episodes
* Select an item to begin streaming.
* Use features like Favorites, History, and Recommendations to customize your experience.

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
* Reusable components in `/inc`, configuration in `/libs`.
* MVC pattern: Controllers in `/libs/controllers`, Services in `/libs/services`.
* Assets organized: `/assets/icon`, `/assets/image`, `/assets/logo`.

---

## 📄 License

This project is licensed under the **Creative Commons Attribution-NonCommercial (CC BY-NC)** license.

See the [LICENSE](LICENSE) file for details.

---

## 🙏 Disclaimer

PLAYGO does not host, store, or provide any IPTV content. It is a tool for connecting to legally obtained IPTV services. You are solely responsible for the content you access.

---

## 📬 Contact

For issues, suggestions, or contributions, please open an [Issue](https://github.com/Jeremias0618/PLAYGO/issues) or [Pull Request](https://github.com/Jeremias0618/PLAYGO/pulls).

---

