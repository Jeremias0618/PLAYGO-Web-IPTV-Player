# PLAYGO - Web IPTV Player

[![PHP](https://img.shields.io/badge/PHP-7.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery-3.5+-0769AD?style=flat-square&logo=jquery&logoColor=white)](https://jquery.com/)
[![Apache](https://img.shields.io/badge/Apache-2.4+-D22128?style=flat-square&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License](https://img.shields.io/badge/License-CC%20BY--NC%204.0-EF9421?style=flat-square&logo=creative-commons&logoColor=white)](https://creativecommons.org/licenses/by-nc/4.0/)
[![Xtream UI](https://img.shields.io/badge/Xtream%20UI-Compatible-00A86B?style=flat-square&logo=stream&logoColor=white)](#)
[![Visitor Counter](https://visitor-badge.laobi.icu/badge?page_id=PLAYGO-Web-IPTV-Player&left_color=blue&right_color=green)](https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player)

**PLAYGO** is a production-ready, modular web-based IPTV player that connects to Xtream UI-compatible IPTV services. Built with a clean MVC architecture, it provides a comprehensive streaming platform with advanced features including user profiles, custom playlists, saga management, and real-time progress tracking. The system uses JSON-based storage for scalability and performance, eliminating the need for traditional databases.

![Preview Screenshot](assets/image/Screenshot_1.png)
![Preview Screenshot](assets/image/Screenshot_2.png)
![Preview Screenshot](assets/image/Screenshot_3.png)

---

## 🏗️ Architecture Overview

PLAYGO follows a **Model-View-Controller (MVC)** architecture pattern with clear separation of concerns:

- **Controllers** (`libs/controllers/`): Handle HTTP requests, orchestrate business logic, and prepare data for views
- **Services** (`libs/services/`): Contain core business logic, API integrations, and data processing
- **Endpoints** (`libs/endpoints/`): RESTful API endpoints for AJAX requests and data operations
- **Views**: PHP templates that render the user interface
- **Storage**: JSON-based file system for user data, playlists, history, and configuration

### Key Design Patterns

- **Dependency Injection**: Services are injected into controllers for testability
- **Repository Pattern**: Data access is abstracted through service layers
- **Factory Pattern**: Used for creating API clients and service instances
- **Observer Pattern**: Event-driven architecture for user actions and data updates

---

## 🚀 Core Features

### Streaming & Content Management
- **Live TV Streaming**: Real-time channel streaming with EPG support
- **VOD Library**: Complete movie and series catalog with metadata
- **Series Management**: Multi-season series with episode tracking
- **Content Search**: Full-text search across all content types
- **Content Recommendations**: AI-powered recommendations based on viewing history

### User Management & Personalization
- **User Profiles**: Comprehensive user profiles with statistics and account information
- **Authentication System**: Secure login with Xtream UI credential validation
- **Session Management**: Cookie-based session handling with expiration tracking
- **User Data Storage**: Per-user JSON storage for isolation and scalability
- **Progress Tracking**: Real-time viewing progress with resume functionality
- **Watch History**: Complete viewing history with date tracking and filtering

### Content Organization
- **Custom Playlists**: User-created playlists with drag-and-drop reordering
- **Saga Management**: Create and manage custom movie/series sagas
- **Favorites System**: Quick access to favorite content
- **Content Collections**: Pre-defined collections and curated content

### Technical Features
- **Parallel API Processing**: `curl_multi` for concurrent Xtream UI API calls
- **Response Caching**: Intelligent caching of API responses and metadata
- **Image Optimization**: Lazy loading, WebP support, and fallback mechanisms
- **Responsive Design**: Mobile-first design with Android optimization
- **Performance Monitoring**: Built-in performance tracking and optimization

---

## 📁 Project Structure

```
PLAYGO/
├── index.php                      # Application entry point
├── login.php                      # Authentication interface
├── home.php                       # Dashboard/homepage
├── profile.php                    # User profile management
├── playlist.php                   # Playlist viewer
├── collection.php                 # Saga/collection viewer
├── sagas.php                      # Saga listing
├── sagas_admin.php                # Saga administration panel
│
├── channels.php / channel.php     # Live TV channels
├── movies.php / movie.php          # Movie catalog
├── series.php / serie.php         # Series catalog
├── episode.php                    # Episode player
│
├── libs/                          # Core application logic
│   ├── controllers/               # MVC Controllers
│   │   ├── Authentication.php    # Login/logout handling
│   │   ├── Profile.php           # User profile data
│   │   ├── Collection.php        # Saga/collection rendering
│   │   ├── Movie.php             # Movie detail controller
│   │   ├── SeriePageController.php # Series detail controller
│   │   ├── EpisodePageController.php # Episode controller
│   │   ├── Search.php            # Search functionality
│   │   └── SagasAdminController.php # Saga admin operations
│   │
│   ├── services/                  # Business logic layer
│   │   ├── auth.php              # Authentication service
│   │   ├── XtreamApi.php         # Xtream UI API client
│   │   ├── collection.php        # Collection processing
│   │   ├── movies.php            # Movie data processing
│   │   ├── series.php            # Series data processing
│   │   ├── series-episodes.php   # Episode management
│   │   ├── live.php              # Live TV processing
│   │   └── content.php           # Content aggregation
│   │
│   ├── endpoints/                 # REST API endpoints
│   │   ├── UserData.php          # User data operations
│   │   ├── SagasAdmin.php        # Saga CRUD operations
│   │   ├── XtreamApi.php         # Xtream API proxy
│   │   ├── ApiContent.php        # Content API
│   │   └── MovieRecommended.php  # Recommendations API
│   │
│   ├── config.php                 # Application configuration
│   ├── config.example.php        # Configuration template
│   ├── lib.php                    # Utility functions
│   └── connection.php             # Database abstraction (future)
│
├── storage/                       # JSON-based data storage
│   ├── sagas.json                 # Saga definitions
│   ├── users/                     # Per-user data
│   │   └── [username]/
│   │       ├── user_data.json     # User session history
│   │       ├── playlists.json     # User playlists
│   │       ├── favorites.json     # Favorite content
│   │       ├── history.json       # Viewing history
│   │       └── progress.json      # Viewing progress
│
├── assets/                        # Static assets
│   ├── icon/                      # Icon files (SVG, PNG)
│   ├── image/                     # Images (wallpapers, screenshots)
│   └── logo/                      # Branding assets
│
├── styles/                        # CSS stylesheets (organized by page)
│   ├── profile/                   # Profile page styles
│   │   ├── layout.css            # Desktop layout
│   │   └── mobile.css            # Mobile/Android optimization
│   ├── collection/                # Collection page styles
│   ├── movie/                     # Movie page styles
│   ├── serie/                     # Series page styles
│   ├── playlist/                  # Playlist page styles
│   └── [page]/                    # Page-specific styles
│
├── scripts/                       # JavaScript modules
│   ├── profile/                   # Profile page scripts
│   │   └── init.js               # Carousel initialization
│   ├── collection/                # Collection page scripts
│   ├── movie/                     # Movie page scripts
│   ├── serie/                     # Series page scripts
│   └── [page]/                    # Page-specific scripts
│
├── vendor/                        # Third-party dependencies
│   └── phpmailer/                 # PHPMailer library
│
└── tmdb_cache/                    # TMDB metadata cache
```

---

## 🛠️ Technical Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.2 or higher (PHP 8.0+ recommended)
- **PHP Extensions**:
  - `curl` - For API communication
  - `json` - For JSON data processing
  - `mbstring` - For string manipulation
  - `openssl` - For secure connections
  - `fileinfo` - For file type detection
  - `gd` or `imagick` - For image processing (optional)

### System Requirements
- **Storage**: Minimum 100MB free space (for cache and user data)
- **Memory**: PHP memory_limit ≥ 128MB recommended
- **Permissions**: Write access required for:
  - `storage/` directory and subdirectories
  - `tmdb_cache/` directory
  - `db/` directory (if used)

### External Services
- **Xtream UI**: Compatible IPTV service with API access
- **TMDB API** (Optional): For enhanced metadata and artwork
- **SMTP Server** (Optional): For email notifications

---

## ⚙️ Configuration

### Initial Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player.git
   cd PLAYGO-Web-IPTV-Player
   ```

2. **Configure the application**:
   ```bash
   cp libs/config.example.php libs/config.php
   nano libs/config.php
   ```

3. **Set required configuration**:
   ```php
   define('XTREAM_URL', 'https://your-xtream-server.com/');
   define('XTREAM_USER', 'your_username');
   define('XTREAM_PWD', 'your_password');
   define('TMDB_API_KEY', 'your_tmdb_api_key'); // Optional
   ```

4. **Set directory permissions**:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 tmdb_cache/
   chmod -R 755 db/
   ```

5. **Configure web server** (Apache example):
   - Enable `mod_rewrite`
   - Set `DocumentRoot` to project directory
   - Configure virtual host (see `.htaccess.example`)

### Configuration Options

| Option | Description | Required |
|--------|-------------|----------|
| `XTREAM_URL` | Xtream UI server URL | Yes |
| `XTREAM_USER` | Xtream UI username | Yes |
| `XTREAM_PWD` | Xtream UI password | Yes |
| `TMDB_API_KEY` | TMDB API key for metadata | No |
| `LANGUAGE` | Default language (es-ES, en-US, etc.) | No |
| `SAGAS_ADMIN_ENABLED` | Enable saga admin panel | No |
| `SAGAS_ADMIN_USER` | Username for saga admin access | No |
| `SMTP_*` | SMTP configuration for emails | No |

---

## 🔧 API Integration

### Xtream UI API

PLAYGO integrates with Xtream UI API for content delivery:

```php
// Example: Fetching movie information
$api = new XtreamApiService();
$movieInfo = $api->getVodInfo($movieId);
```

**Supported Endpoints**:
- `get_live_categories` - Live TV categories
- `get_live_streams` - Live TV streams
- `get_vod_categories` - VOD categories
- `get_vod_info` - Movie information
- `get_series_categories` - Series categories
- `get_series_info` - Series information
- `get_series_episodes` - Episode listing
- `get_account_info` - User account details

### Internal API Endpoints

PLAYGO provides RESTful endpoints for frontend operations:

- `POST /libs/endpoints/UserData.php` - User data operations
  - `action=playlist_create` - Create playlist
  - `action=playlist_delete` - Delete playlist
  - `action=playlist_add` - Add item to playlist
  - `action=playlist_remove` - Remove item from playlist
  - `action=favorite_add` - Add to favorites
  - `action=favorite_remove` - Remove from favorites
  - `action=history_add` - Add to history
  - `action=progress_update` - Update viewing progress

- `POST /libs/endpoints/SagasAdmin.php` - Saga management
  - `action=create` - Create new saga
  - `action=update` - Update existing saga
  - `action=delete` - Delete saga
  - `action=list` - List all sagas

---

## 📊 Data Storage Architecture

### JSON Storage Structure

PLAYGO uses a hierarchical JSON storage system:

```json
storage/
├── sagas.json                    # Global saga definitions
└── users/
    └── [username]/
        ├── user_data.json        # Session history
        │   [
        │     {"id": 1, "user": "user", "date": "2025-01-16 10:00:00"},
        │     ...
        │   ]
        ├── playlists.json        # User playlists
        │   {
        │     "Playlist Name": [
        │       {"id": "123", "type": "movie", "name": "...", ...},
        │       ...
        │     ]
        │   }
        ├── favorites.json        # Favorite content
        │   [
        │     {"id": "123", "type": "movie", "name": "...", ...},
        │     ...
        │   ]
        ├── history.json          # Viewing history
        │   [
        │     {"id": "123", "type": "movie", "name": "...", "date": "...", ...},
        │     ...
        │   ]
        └── progress.json         # Viewing progress
            {
              "123": {"position": 3600, "duration": 7200, "last_updated": "..."},
              ...
            }
```

### Saga Structure

```json
{
  "id": 1,
  "title": "Saga Name",
  "image": "path/to/image.jpg",
  "items": [
    {
      "id": "123",
      "type": "movie",
      "name": "Movie Title",
      "order": 1
    },
    ...
  ]
}
```

---

## 🎨 Frontend Architecture

### CSS Organization

Styles are organized by page/module with mobile-first approach:

- **Layout CSS**: Desktop-specific styles
- **Mobile CSS**: Responsive styles for mobile devices (max-width: 600px)
- **Component CSS**: Reusable component styles

### JavaScript Modules

JavaScript follows modular architecture:

- **Page-specific modules**: Each page has its own script directory
- **Shared utilities**: Common functions in `libs/lib.php` (PHP) or shared JS files
- **Event-driven**: Uses jQuery for DOM manipulation and event handling
- **Carousel integration**: Owl Carousel for content carousels

### Responsive Design

- **Breakpoints**: 
  - Mobile: `max-width: 600px`
  - Tablet: `601px - 1000px`
  - Desktop: `> 1000px`
- **Touch optimization**: Swipe gestures, touch-friendly buttons
- **Performance**: Lazy loading, image optimization, code splitting

---

## 🔐 Security Features

- **Input Validation**: All user inputs are sanitized and validated
- **XSS Protection**: Output escaping using `htmlspecialchars()`
- **CSRF Protection**: Token-based request validation
- **Session Security**: Secure cookie handling with expiration
- **File Upload Validation**: Type and size restrictions
- **Path Traversal Protection**: Sanitized file paths
- **SQL Injection Prevention**: JSON storage eliminates SQL injection risks

---

## 📈 Performance Optimization

### Caching Strategy
- **API Response Caching**: Reduces redundant Xtream UI API calls
- **Image Caching**: Local storage of TMDB images
- **Metadata Caching**: Cached content metadata for faster page loads

### Code Optimization
- **Parallel Processing**: `curl_multi` for concurrent API requests
- **Lazy Loading**: Images and content loaded on demand
- **Code Splitting**: Modular JavaScript and CSS loading
- **Minification**: Production-ready minified assets (when applicable)

### Database Optimization
- **JSON Indexing**: Efficient JSON structure for quick lookups
- **File-based Storage**: No database overhead
- **Incremental Updates**: Only modified data is written

---

## 🧪 Development

### Code Style Guidelines

- **PHP**: PSR-12 coding standard
- **JavaScript**: ES6+ with jQuery compatibility
- **CSS**: BEM-like naming convention
- **File Naming**: kebab-case for files, PascalCase for classes

### Testing

```bash
# Test API connectivity
php -r "require 'libs/services/XtreamApi.php'; ..."

# Validate JSON structure
php -r "json_decode(file_get_contents('storage/sagas.json'));"
```

### Debugging

Enable error reporting in development:
```php
// In libs/config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📖 Usage Examples

### Creating a Custom Saga

```php
// Via admin panel: sagas_admin.php
// Or programmatically:
$sagaData = [
    'id' => 1,
    'title' => 'Marvel Cinematic Universe',
    'image' => 'path/to/image.jpg',
    'items' => [
        ['id' => '123', 'type' => 'movie', 'name' => 'Iron Man', 'order' => 1],
        // ...
    ]
];
```

### Adding to Playlist via API

```javascript
fetch('libs/endpoints/UserData.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=playlist_add&playlist_name=My%20List&item_id=123&item_type=movie'
});
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Contribution Guidelines
- Follow existing code style and architecture patterns
- Add comments for complex logic
- Update documentation for new features
- Test on multiple devices and browsers
- Ensure backward compatibility

---

## 📄 License

This project is licensed under the **Creative Commons Attribution-NonCommercial (CC BY-NC 4.0)** license.

See the [LICENSE](LICENSE) file for details.

**Commercial Use**: For commercial licensing, please contact the project maintainers.

---

## 🙏 Disclaimer

PLAYGO does not host, store, or provide any IPTV content. It is a tool for connecting to legally obtained IPTV services. Users are solely responsible for ensuring they have proper licensing and authorization to access the content they stream through this application.

The developers and contributors of PLAYGO are not responsible for:
- Content accessed through the application
- Legal issues arising from content access
- Service availability or quality from IPTV providers
- Any misuse of the application

---

## 📬 Support & Contact

- **Issues**: [GitHub Issues](https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player/issues)
- **Pull Requests**: [GitHub Pull Requests](https://github.com/Jeremias0618/PLAYGO-Web-IPTV-Player/pulls)
- **Documentation**: See inline code comments and this README

---

## 🗺️ Roadmap

- [ ] Database migration support (MySQL/PostgreSQL)
- [ ] Multi-language interface
- [ ] Advanced analytics dashboard
- [ ] RESTful API for mobile apps
- [ ] WebSocket support for real-time updates
- [ ] Advanced recommendation engine
- [ ] Social features (sharing, reviews)
- [ ] Chromecast/Apple TV support

---

**Built with ❤️ by the PLAYGO development team**
