
<?php 

// Production Error Reporting (recommended for shared hosting)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
// Optional: Set a specific error log path if you know it and have write permissions.
// ini_set('error_log', '/path/to/your/php_error.log');
// Shared hosting usually has a default error log path configured,
// so explicitly setting it might not be necessary or might be restricted.

// Set error reporting level (E_ALL & ~E_DEPRECATED & ~E_STRICT is common for production)
// This ensures notices and deprecation warnings during development don't break pages
// but all other errors are logged.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli("HOSTNAME", "USERNAME", "PASSWORD", "DATABASE"); //just like $conn = new mysqli("localhost", "root", "", "anipaca");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later or contact support.");
}

$websiteTitle = "AniPaca";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$websiteUrl = "{$protocol}://{$_SERVER['SERVER_NAME']}";
$websiteLogo = $websiteUrl . "/public/logo/logo.png";
$contactEmail = "raisulentertainment@gmail.com";

$version = "1.0.2";

$discord = "https://dcd.gg/anipaca";
$github = "https://github.com/PacaHat";
$telegram = "https://t.me/anipaca";
$instagram = "https://www.instagram.com/pxr15_"; 

// all the api you need
$zpi = "https://your-hosted-api.com/api"; //https://github.com/PacaHat/zen-api
$proxy = $websiteUrl . "/src/ajax/proxy.php?url=";

//If you want faster loading speed just put // before the first proxy and remove slashes from this one 
//$proxy = "https://your-hosted-proxy.com/proxy?url="; //https://github.com/PacaHat/shrina-proxy


$banner = $websiteUrl . "/public/images/banner.png";

    