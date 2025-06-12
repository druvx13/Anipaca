<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
session_start();

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_COOKIE['userID'])) {
    header('location:/login');
    exit();
}

// Get user data
$user_id = $_COOKIE['userID'];
$user = null;
$recent_watch_result = null;
$total_items = 0;
$latest_updates_result = null;
$stmt = null;
$count_stmt = null;

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
    } else {
        error_log("Error preparing user fetch statement in continue-watching: " . $conn->error);
        exit("Error loading user data. Please try again later.");
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching user in continue-watching: " . $e->getMessage());
    exit("Error loading user data. Please try again later.");
} finally {
    if ($stmt) {
        $stmt->close();
        $stmt = null; // Reset stmt for next query
    }
}

if (!$user) {
    // This case might be redundant if exit() is called above, but as a safeguard:
    error_log("User data not loaded in continue-watching for user_id: " . $user_id);
    exit("User data could not be loaded. Please ensure you are logged in.");
}

// Pagination setup
$items_per_page = 12; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Fetch most recently watched anime (Continue Watching) with pagination
$recent_watch_sql = "SELECT DISTINCT anime_id, anime_name, episode_number, poster, sub_count, dub_count 
                     FROM watch_history 
                     WHERE user_id = ? 
                     GROUP BY anime_id 
                     ORDER BY MAX(updated_at) DESC 
                     LIMIT ? OFFSET ?";
try {
    $stmt = $conn->prepare($recent_watch_sql);
    if ($stmt) {
        $stmt->bind_param("iii", $user_id, $items_per_page, $offset);
        $stmt->execute();
        $recent_watch_result = $stmt->get_result();
    } else {
        error_log("Error preparing recent_watch_sql statement in continue-watching: " . $conn->error);
        $recent_watch_result = null;
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching recent watch history: " . $e->getMessage());
    $recent_watch_result = null;
} finally {
    if ($stmt) {
        $stmt->close();
        $stmt = null;
    }
}

// Get total count for pagination
$count_sql = "SELECT COUNT(DISTINCT anime_id) as total FROM watch_history WHERE user_id = ?";
try {
    $count_stmt = $conn->prepare($count_sql);
    if ($count_stmt) {
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        if ($count_result) {
            $total_row = $count_result->fetch_assoc();
            if ($total_row) {
                $total_items = $total_row['total'];
            }
        }
    } else {
        error_log("Error preparing count_sql statement in continue-watching: " . $conn->error);
        $total_items = 0;
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching total watch history count: " . $e->getMessage());
    $total_items = 0;
} finally {
    if ($count_stmt) {
        $count_stmt->close();
        $count_stmt = null;
    }
}
$total_pages = ($items_per_page > 0) ? ceil($total_items / $items_per_page) : 0;

// Fetch latest updated episodes with pagination
$latest_updates_sql = "SELECT wh.* 
                      FROM watch_history wh
                      INNER JOIN (
                          SELECT anime_id, MAX(episode_number) as max_ep
                          FROM watch_history
                          GROUP BY anime_id
                      ) latest ON wh.anime_id = latest.anime_id 
                      AND wh.episode_number = latest.max_ep
                      WHERE wh.user_id = ?
                      ORDER BY wh.updated_at DESC 
                      LIMIT ? OFFSET ?";
try {
    $stmt = $conn->prepare($latest_updates_sql);
    if ($stmt) {
        $stmt->bind_param("iii", $user_id, $items_per_page, $offset);
        $stmt->execute();
        $latest_updates_result = $stmt->get_result();
    } else {
        error_log("Error preparing latest_updates_sql statement in continue-watching: " . $conn->error);
        $latest_updates_result = null;
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching latest updated episodes: " . $e->getMessage());
    $latest_updates_result = null;
} finally {
    if ($stmt) {
        $stmt->close();
        $stmt = null;
    }
}
?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<title>Your watch history on <?= $websiteTitle ?> </title>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="title"
        content="<?= $websiteTitle ?> #1 Watch High Quality Anime Online Without Ads" />
    <meta name="description"
        content="<?= $websiteTitle ?> #1 Watch High Quality Anime Online Without Ads. You can watch anime online free in HD without Ads. Best place for free find and one-click anime." />
    <meta name="keywords"
        content="<?= $websiteTitle ?>, watch anime online, free anime, anime stream, anime hd, english sub, kissanime, gogoanime, animeultima, 9anime, 123animes, vidstreaming, gogo-stream, animekisa, zoro.to, gogoanime.run, animefrenzy, animekisa" />
    <meta name="charset" content="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <meta name="robots" content="index, follow" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta http-equiv="Content-Language" content="en" />
    <meta property="og:title"
        content="<?= $websiteTitle ?> #1 Watch High Quality Anime Online Without Ads">
    <meta property="og:description"
        content="<?= $websiteTitle ?> #1 Watch High Quality Anime Online Without Ads. You can watch anime online free in HD without Ads. Best place for free find and one-click anime.">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $websiteTitle ?>">
    <meta property="og:url" content="<?= $websiteUrl ?>/home">
    <meta itemprop="image" content="<?= $banner ?>">
    <meta property="og:image" content="<?= $banner ?>">
    <meta property="og:image:secure_url" content="<?= $banner ?>">
    <meta property="og:image:width" content="650">
    <meta property="og:image:height" content="350">
    <meta name="apple-mobile-web-app-status-bar" content="#202125">
    <meta name="theme-color" content="#202125">
        <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/styles.min.css?v=<?= $version ?>">
    <link rel="apple-touch-icon" href="<?= $websiteUrl ?>/public/logo/favicon.png?v=<?= $version ?>" />
    <link rel="shortcut icon" href="<?= $websiteUrl ?>/public/logo/favicon.png?v=<?= $version ?>" type="image/x-icon" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $websiteUrl ?>/public/logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $websiteUrl ?>/public/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $websiteUrl ?>/public/logo/favicon-16x16.png">
    <link rel="mask-icon" href="<?= $websiteUrl ?>/public/logo/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="icon" sizes="192x192" href="<?= $websiteUrl ?>/public/logo/touch-icon-192x192.png?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/new.css?v=<?= $version ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/search.css">
    <script src="<?= $websiteUrl ?>/src/assets/js/search.js"></script>

    <noscript>
        <link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css>
        <link rel=stylesheet href=https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css>
    </noscript>
    <script>const cssFiles = ["https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css"], firstLink = document.getElementsByTagName("link")[0]; cssFiles.forEach((s => { const t = document.createElement("link"); t.rel = "stylesheet", t.href = `${s}?v=<?= $version ?>`, t.type = "text/css", firstLink.parentNode.insertBefore(t, firstLink) }))</script>
    <link rel=stylesheet href=https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css>
    <link rel=stylesheet href=https://use.fontawesome.com/releases/v5.3.1/css/all.css>
    <link rel=stylesheet href="https://fonts.googleapis.com/icon?family=Material+Icons">
</head>

<body data-page="page_anime">
  <div id="sidebar_menu_bg"></div>
  <div id="wrapper" data-page="page_home">
    <?php include 'src/component/header.php'; ?>
    <div class="clearfix"></div>

    <div id="main-wrapper" class="layout-page page-az">


    <div class="profile-header">
        <div class="profile-header-cover"
          style="background-image: url(<?= htmlspecialchars($user['image']) ?>);"></div>
        <div class="container">
          <div class="ph-title">Hi, <?= htmlspecialchars($user['username']) ?></div>
          <div class="ph-tabs">
            <div class="bah-tabs">
              <ul class="nav nav-tabs pre-tabs">
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/profile"><i
                      class="fas fa-user mr-2"></i>Profile</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= $websiteUrl ?>/continue-watching"><i class="fas fa-history mr-2"></i>Continue Watching
                      </a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/watchlist"><i class="fas fa-heart mr-2"></i>Watch
                    List</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/changepass"><i class="fas fa-key mr-2"></i>Change
                    Password</a></li>
                    <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showToast('(・`ω´・)', 'Do you really need settings too, baka?', 'info')">
                       <i class="fas fa-cog mr-2"></i>Settings 
                    </a>
                </li>
              </ul>
            </div>
          </div>
          <div class="clearfix"></div>
        </div>
      </div>


            <div class="container">
                <div class="prebreadcrumb">
          <h2 class="h2-heading mb-4"><i class="fas fa-clock mr-3"></i>Continue Watching</h2>
        </div>
        <div class="block_area-content block_area-list film_list film_list-grid">
          <div class="film_list-wrap">
            <?php if ($recent_watch_result && $recent_watch_result->num_rows > 0): ?>
              <?php while($anime = $recent_watch_result->fetch_assoc()): ?>
                <div class="flw-item">
                  <div class="film-poster">
                    <div class="tick ltr">
                      <?php if (!empty($anime['sub_count'])): ?>
                        <div class="tick-item tick-sub"><i class="fas fa-closed-captioning mr-1"></i><?php echo htmlspecialchars($anime['sub_count']); ?></div>
                      <?php endif; ?>
                      <?php if (!empty($anime['dub_count'])): ?>
                        <div class="tick-item tick-dub"><i class="fas fa-microphone mr-1"></i><?php echo htmlspecialchars($anime['dub_count']); ?></div>
                      <?php endif; ?>
                     
                    </div>
                    <img class="film-poster-img" 
                         src="<?php echo htmlspecialchars($anime['poster'] ?? '/path/to/default-image.jpg'); ?>"
                         alt="<?php echo htmlspecialchars($anime['anime_name'] ?? 'Unknown Title'); ?>">
                    <a class="film-poster-ahref" 
                       href="<?php echo $websiteUrl; ?>/watch/<?php echo htmlspecialchars($anime['anime_id'] ?? ''); ?>?ep=<?php echo htmlspecialchars($anime['episode_number'] ?? '1'); ?>"
                       title="<?php echo htmlspecialchars($anime['anime_name'] ?? 'Unknown Title'); ?>">
                       <i class="fas fa-play"></i>
                    </a>
                  </div>
                  <div class="film-detail">
                    <h3 class="film-name">
                      <a href="<?php echo $websiteUrl; ?>/details/<?php echo htmlspecialchars($anime['anime_id'] ?? ''); ?>"
                         title="<?php echo htmlspecialchars($anime['anime_name'] ?? 'Unknown Title'); ?>">
                         <?php echo htmlspecialchars($anime['anime_name'] ?? 'Unknown Title'); ?>
                      </a>
                    </h3>
                    <div class="fd-infor">
                      <span class="fdi-item">
                        <i class="fas fa-play-circle"></i> EP <?= $anime['episode_number'] ?></span>                                      
                    </div>             
                  </div>
                  <div class="clearfix"></div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="alert alert-warning">No recently watched anime!</div>
            <?php endif; ?>
          </div>
          <div class="pre-pagination mt-5 mb-5">
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-lg justify-content-center">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" title="Previous">Previous</a>
                  </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <?php if ($i <= 3 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                      <a class="page-link" href="?page=<?php echo $i; ?>" title="Page <?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                  <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" title="Next">Next</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>
          </div>
        </div>
      </div>

    
      </div>
    </div>
    <div class="clearfix"></div>
  </div>
</body>
    <?php include 'src/component/footer.php' ?>
  
    <div id="mask-overlay"></div>
    
<script type="text/javascript">
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
<div id="mask-overlay"></div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
  
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/comma.js?v=1.5"></script>
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/movie.js?v=1.5"></script>
    <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/jquery-ui.css?v=1.5">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js?v=1.5"></script>
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/function.js?v=1.5"></script>
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/app.min.js?v=1.4"></script>
    


    </div>
  </div>

</body>

</html>
