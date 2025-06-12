<p align="center">
  <a href="https://github.com/PacaHat/Anipaca">
    <img 
      src="https://raw.githubusercontent.com/PacaHat/Anipaca/refs/heads/main/public/logo/Untitled255_20241231223556.png" 
      alt="Anipaca Mascot"
      width="200"
      height="200"
      decoding="async"
      fetchpriority="high"
    /> 
  </a>
</p> 

# <p align="center"> <img src="public/logo/logo.png?v=0.2" alt="Anipaca Logo" width="400"></p>

<p align="center">
  <div align="center">
    <h3>Anipaca - High-Quality Anime Streaming Platform</h3>
    <a href="https://discord.gg/aVvqx77RGs">
      <img src="https://img.shields.io/discord/1012901585896087652?label=&logo=discord&color=5460e6&style=flat-square&labelColor=2b2f35" alt="Discord">
    </a>
    <a href="https://github.com/PacaHat/Anipaca/graphs/contributors">
      <img src="https://img.shields.io/github/contributors/PacaHat/Anipaca" alt="Contributors">
    </a>
    <a href="https://github.com/PacaHat/Anipaca/forks">
      <img src="https://img.shields.io/github/forks/PacaHat/Anipaca" alt="Forks">
    </a>
    <a href="https://github.com/PacaHat/Anipaca/stargazers">
      <img src="https://img.shields.io/github/stars/PacaHat/Anipaca" alt="Stars">
    </a>
    <a href="https://github.com/PacaHat/Anipaca/issues">
      <img src="https://img.shields.io/github/issues/PacaHat/Anipaca" alt="Issues">
    </a>
  </div>
  <hr />
</p>

> [!IMPORTANT]
>
> 1.  This project is an independent development and is not affiliated with any other streaming sites. It utilizes publicly available APIs for its content.
> 2.  The content provided through this platform is sourced from third-party APIs and is not hosted by the Anipaca project or its maintainers. Ownership and responsibility for the content lie with the respective API providers and original content owners.
> 3.  This project is intended strictly for educational and demonstrative purposes, showcasing web development techniques.
> 4.  Commercial use of this project, including but not limited to deploying it with advertisements for profit, is strictly prohibited. The project maintainers reserve the right to take action, including DMCA complaints, against unauthorized commercial deployments.

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about-the-project">About The Project</a></li>
    <li><a href="#anipaca-v2---key-improvements--features">Anipaca V2 - Key Improvements & Features</a></li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#api-setup">API Setup</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#disclaimer">Disclaimer</a></li>
  </ol>
</details>

<!-- ABOUT THE PROJECT -->
## About The Project

![Anipaca Screenshot](https://raw.githubusercontent.com/PacaHat/Anipaca/refs/heads/main/public/images/banner.png "Anipaca Screenshot")

**Anipaca** is an open-source PHP-based application designed to provide a high-quality anime streaming experience. It leverages external APIs to deliver a wide range of anime content, offering features tailored for anime enthusiasts. The platform is built with a focus on user experience, offering multiple resolution options and broad device compatibility.

Key original strengths include:
*   **Ad-Free Potential:** Designed to allow streaming without disruptive video ads (dependent on deployment choices).
*   **Multiple Resolutions:** Supports streaming in 1080p, 720p, 480p, and 360p.
*   **Broad Compatibility:** Accessible on PCs, laptops, tablets, mobile devices, and smart TVs.
*   **User-Friendly Navigation:** Features for browsing by genre, season, and other criteria.

## Anipaca V2 - Key Improvements & Features

Anipaca V2 introduces significant updates, modernizing the codebase and enhancing functionality:

*   **PHP 8.x Modernization:**
    *   The entire codebase has been updated for compatibility with PHP 8.x (specifically targeting PHP 8.3).
    *   This includes adopting modern PHP syntax, robust error handling using exceptions (e.g., `mysqli_sql_exception`), and leveraging performance improvements inherent in newer PHP versions.
    *   Deprecated features and patterns from older PHP versions have been addressed or removed.

*   **M3U8 Download/Stream Link Feature:**
    *   Users can now access M3U8 playlist links directly on the watch page for each available stream (SUB/DUB).
    *   These links can be used with external media players like VLC or with third-party download managers and tools (e.g., JDownloader, `yt-dlp`) for offline viewing.
    *   Anipaca acts as a bridge to these M3U8 links from the API; it does not host or convert any media files itself.

*   **Code Refinements & Robustness:**
    *   Key areas of the application, including `details.php`, various AJAX handlers (e.g., comment system, watch history, watchlist updates), and user management scripts (`login.php`, `register.php`, `profile.php`, `logout.php`), have undergone significant refinement.
    *   Improvements focus on enhanced error handling (both server-side and for AJAX responses), consistent HTML escaping to prevent XSS vulnerabilities, input sanitization, and overall code clarity for better maintainability.
    *   Production-friendly error reporting settings have been integrated into `_config.php` to suppress direct error output and enable logging.

*   **Watchlist Functionality Note:**
    *   The watchlist update mechanism (`src/ajax/wl-up.php`) has had its error handling logic drafted and tested. However, due to persistent issues with the automated deployment tools for this specific file, its final refined version is pending manual code application during the V2 deployment. The core functionality remains operational.

<!-- GETTING STARTED -->
## Getting Started

To set up Anipaca on your own server (e.g., cPanel hosting), follow these steps:

### API Setup

Anipaca relies on external APIs. You will need to deploy or have access to instances of the following:

| API Name     | Deploy Link (Example)                                                                                              | Notes                                     |
|--------------|--------------------------------------------------------------------------------------------------------------------|-------------------------------------------|
| Main API     | [![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/PacaHat/zen-api)          | Provides core anime data, search, etc.    |
| M3U8 Proxy   | [![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/PacaHat/shrina-proxy) | Proxies M3U8 stream links if needed.      |

Update the API endpoints in your `_config.php` file accordingly.

### Installation

1.  **Clone or Download:**
    Obtain the Anipaca V2 source code:
    ```bash
    git clone https://github.com/PacaHat/Anipaca.git
    cd Anipaca
    ```
    Alternatively, download the ZIP archive from the repository.

2.  **Database Setup:**
    *   Import the `database.sql` file (located in the root directory) into your MySQL database. This will create the necessary tables.
    *   Update your database connection details (hostname, username, password, database name) in the `_config.php` file.

3.  **Configuration (`_config.php`):**
    Ensure your `_config.php` file is correctly set up. Below is an example structure:
    ```php
    <?php
    // Production Error Reporting
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli("YOUR_HOSTNAME", "YOUR_USERNAME", "YOUR_PASSWORD", "YOUR_DATABASE");
    } catch (mysqli_sql_exception $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later or contact support.");
    }

    $websiteTitle = "Anipaca"; // Your Website Title
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $websiteUrl = "{$protocol}://{$_SERVER['SERVER_NAME']}"; // Your website URL
    $websiteLogo = $websiteUrl . "/public/logo/logo.png";
    $contactEmail = "your_email@example.com";

    $version = "2.0.0"; // Anipaca Version

    // Social Links (Optional)
    $discord = "https://discord.gg/your_server";
    $github = "https://github.com/your_profile";
    // ... other links

    // API Endpoints
    $zpi = "https://your-main-api-deployment.com/api"; // Main API (zen-api)
    $proxy = $websiteUrl . "/src/ajax/proxy.php?url="; // Proxy for M3U8 links (if using local proxy)
    // Alternative: $proxy = "https://your-m3u8proxy-deployment.com/proxy?url=";

    $banner = $websiteUrl . "/public/images/banner.png"; // Default banner
    ?>
    ```

4.  **File Permissions:**
    Ensure that directories like `cache/` (if used for search or other caching) are writable by the web server.

5.  **Web Server Configuration:**
    *   A `.htaccess` file is included for Apache web servers to enable user-friendly URLs. Ensure `mod_rewrite` is enabled.
    *   For Nginx, you will need to configure URL rewriting manually. Example (basic):
        ```nginx
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        ```

<!-- ROADMAP -->
## Roadmap

Key features and future plans:

- [x] Comment System
- [x] PHP Routing (User-friendly URLs)
- [x] Multiple Video Sources/Servers
- [x] PHP 8.x Modernization & Error Handling
- [✔] Anime Download/Stream Links (M3U8 links provided)
- [ ] Enhanced Comment Section (e.g., replies, reactions - partially implemented, needs UI polish)
- [ ] Admin Panel for site management and monitoring
- [ ] Anilist/MAL Integration for tracking
- [ ] User Profile Enhancements

Have more feature requests? Join our [Discord server](https://discord.gg/aVvqx77RGs) to share your ideas!

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTRIBUTING -->
## Contributing

Contributions make the open-source community an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

Please refer to the [Contribution Guide](https://github.com/PacaHat/Anipaca/blob/main/contribution/GUIDE.md) for details on how to contribute to Anipaca.

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- DISCLAIMER -->
## Disclaimer

**Educational Purpose Only**

This Anipaca project and its source code are provided solely for **educational and demonstrative purposes**. The aim is to illustrate principles of web application development and API integration.

**Important Considerations:**

*   **Content Sourcing:** Anipaca interfaces with third-party APIs to fetch anime information and stream links. The project itself does not host, distribute, or own any of the media content that may be accessible through these APIs. All content-related rights and responsibilities belong to the respective API providers and original copyright holders.
*   **Legal and Ethical Use:** Users and developers experimenting with this codebase are expected to do so in a lawful and ethical manner, respecting copyright laws and the terms of service of any APIs utilized. The maintainers of Anipaca do not endorse or encourage any form of copyright infringement or illegal activity.
*   **No Commercial Use:** This project is not intended for commercial deployment, especially if such deployment involves monetization through advertising or subscriptions based on content accessed via third-party APIs. The maintainers reserve the right to take appropriate action against any unauthorized or unethical commercial use.
*   **No Warranty:** This software is provided "as is," without warranty of any kind, express or implied.

---

*This project is privately maintained and intended primarily for the developer's personal educational growth and portfolio.*

**Date:** July 2024
**Author:** Raisul Rahat
