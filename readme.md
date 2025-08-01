# <p align="center"><img src="public/logo/logo.png" alt="Anipaca Logo" width="400"></p>

<p align="center">
  <strong>Anipaca is a feature-rich, open-source anime streaming website.</strong>
  <br>
  <br>
  <a href="https://discord.gg/aVvqx77RGs">
    <img src="https://img.shields.io/discord/1012901585896087652?label=&logo=discord&color=5460e6&style=flat-square&labelColor=2b2f35" alt="Discord">
  </a>
  <a href="https://github.com/PacaHat/Anipaca/stargazers">
    <img src="https://img.shields.io/github/stars/PacaHat/Anipaca" alt="GitHub Stars">
  </a>
  <a href="https://github.com/PacaHat/Anipaca/forks">
    <img src="https://img.shields.io/github/forks/PacaHat/Anipaca" alt="GitHub Forks">
  </a>
  <a href="https://github.com/PacaHat/Anipaca/issues">
    <img src="https://img.shields.io/github/issues/PacaHat/Anipaca" alt="GitHub Issues">
  </a>
</p>

---

## About The Project

<p align="center">
  <img src="https://raw.githubusercontent.com/PacaHat/Anipaca/refs/heads/main/public/images/banner.png" alt="Anipaca Screenshot" width="80%">
</p>

Anipaca is a high-quality anime streaming website built with PHP. It's designed for anime enthusiasts who want a clean, ad-free experience with a rich set of features. This project demonstrates how to build a modern, scalable anime streaming platform.

> [!IMPORTANT]
> This project is for **educational purposes only**. The content provided by the external API is not hosted on this server and belongs to its respective owners. Do not use this project for commercial purposes.

### Key Features

*   **High-Quality Streaming**: Watch anime in 1080p, 720p, 480p, and 360p.
*   **Ad-Free**: A clean viewing experience without video ads.
*   **User Accounts**: Register and log in to track your watch history and lists.
*   **Watchlist**: Keep track of anime you're watching, plan to watch, have on hold, or have completed.
*   **Continue Watching**: Easily resume watching from where you left off.
*   **Interactive Comment Section**: Engage in discussions with a nested reply system.
*   **Admin Panel**: A secure dashboard for site administrators to monitor stats and manage users.
*   **Anime Downloads**: A feature to download episodes for offline viewing.
*   **Responsive Design**: Works seamlessly on desktops, tablets, and mobile devices.
*   **Automatic Migrations**: The database schema is automatically updated on startup.

### Tech Stack

*   **Backend**: PHP
*   **Database**: MySQL
*   **Frontend**: HTML, CSS, JavaScript, Bootstrap
*   **Video Player**: ArtPlayer

---

## Getting Started

Follow these instructions to get a local copy of Anipaca up and running on your machine.

### Prerequisites

You will need a local web server environment with PHP and MySQL. We recommend using one of the following:
*   [XAMPP](https://www.apachefriends.org/index.html) (for Windows, macOS, and Linux)
*   [WAMP](https://www.wampserver.com/en/) (for Windows)
*   [MAMP](https://www.mamp.info/en/mamp/) (for macOS)

### Installation

1.  **Clone the repository**:
    ```bash
    git clone https://github.com/PacaHat/Anipaca.git
    cd Anipaca
    ```

2.  **Set up the database**:
    *   Open your MySQL database management tool (e.g., phpMyAdmin).
    *   Create a new database (e.g., `anipaca`).
    *   Import the `database.sql` file into your new database. This will create the necessary tables.

3.  **Configure the application**:
    *   Rename `_config.php.example` to `_config.php` (if it exists) or edit `_config.php` directly.
    *   Update the database connection details in `_config.php`:
        ```php
        $conn = new mysqli("YOUR_HOSTNAME", "YOUR_USERNAME", "YOUR_PASSWORD", "YOUR_DATABASE");
        ```
        For a standard local setup, this will likely be:
        ```php
        $conn = new mysqli("localhost", "root", "", "anipaca");
        ```

4.  **Configure the API**:
    *   Anipaca relies on an external API for fetching anime data and video streams. You need to deploy your own instance of the [zen-api](https://github.com/PacaHat/zen-api).
    *   Once deployed, update the `$zpi` variable in `_config.php` with your API's URL:
        ```php
        $zpi = "https://your-hosted-api.com/api";
        ```

5.  **Run the application**:
    *   Place the project directory in your web server's root folder (e.g., `htdocs` in XAMPP).
    *   Open your web browser and navigate to `http://localhost/Anipaca` (or the appropriate URL for your setup).
    *   The application will automatically run the necessary database migrations on the first load.

---

## Configuration Details

The `_config.php` file contains all the main configuration options for the site:

*   `$conn`: Database connection details.
*   `$websiteTitle`: The title of your website.
*   `$websiteUrl`: The base URL of your site. This is usually detected automatically.
*   `$zpi`: The URL of your deployed `zen-api` instance.
*   `$proxy`: The URL of your proxy for fetching video streams. You can use the built-in proxy or deploy a separate one for better performance.

---

## Roadmap

Here are some of the planned features for the future:

- [x] Comment section with replies
- [x] Admin panel to manage and monitor the site
- [x] Anime download feature
- [ ] User profile enhancements
- [ ] Advanced search and filtering options
- [ ] Integration with services like AniList or MyAnimeList

Have a feature request? [Join our Discord server](https://discord.gg/aVvqx77RGs) and let us know!

---

## Contributing

Contributions are welcome and greatly appreciated! If you'd like to contribute, please follow these steps:

1.  Fork the Project.
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push to the Branch (`git push origin feature/AmazingFeature`).
5.  Open a Pull Request.

Please read our [Contribution Guide](https://github.com/PacaHat/Anipaca/blob/main/contribution/GUIDE.md) for more details.

---

## License

This project is licensed under the **MIT License**. See the `LICENSE` file for more information.

---

## Disclaimer

This project is provided "as is" without any warranty of any kind. The author is not responsible for how this project is used. It was created for educational purposes to demonstrate web development skills.
