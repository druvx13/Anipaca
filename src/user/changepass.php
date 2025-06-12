<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');

session_start();

$fetch = null;
$stmt = null;
$update_stmt = null;
$message = []; // Ensure message array is initialized

if(!isset($_COOKIE['userID'])){
  header('location:../user/login.php');
  exit; // Add exit after header redirect
};

if(isset($_COOKIE['userID'])){
  $user_id = $_COOKIE['userID'];
};

try {
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result && $result->num_rows > 0){
           $fetch = $result->fetch_assoc();
        }
    } else {
        // This case should ideally be caught by mysqli_report if enabled
        error_log("Error preparing statement for fetching user data in changepass: " . $conn->error);
        $message[] = "Could not load user data due to a server error. Please try again.";
    }
} catch (mysqli_sql_exception $e) {
    error_log("Error fetching user data in changepass: " . $e->getMessage());
    $message[] = "Could not load user data. Please try again.";
} finally {
    if ($stmt) {
        $stmt->close();
    }
}

if(isset($_POST['update_profile'])){
    if (!$fetch) {
        $message[] = "User data not available, cannot update password.";
    } else {
$current_pass = $_POST['update_pass'];
$new_pass = $_POST['new_pass'];
$confirm_pass = $_POST['confirm_pass'];

if(!empty($current_pass) || !empty($new_pass) || !empty($confirm_pass)){
   if(!password_verify($current_pass, $fetch['password'])){
      $message[] = 'Old Password Not Matched!';
   }elseif($new_pass != $confirm_pass){
      $message[] = 'Confirm Password Not Matched!';
   }else{
      try {
          $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
          $update_stmt = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
          if ($update_stmt) {
              $update_stmt->bind_param("si", $hashed_password, $user_id);

              if($update_stmt->execute()){
                 $message2[] = 'Password Updated Successfully!';
              } else {
                 // This else might not be reached if execute throws exception on failure
                 $message[] = 'Failed to update password!';
              }
          } else {
              error_log("Error preparing statement for password update in changepass: " . $conn->error);
              $message[] = "Failed to update password due to a server error.";
          }
      } catch (mysqli_sql_exception $e) {
          error_log("Error updating password: " . $e->getMessage());
          $message[] = "Failed to update password due to a database error.";
      } finally {
          if ($update_stmt) {
              $update_stmt->close();
          }
      }
   }
}
    } // End of else for if (!$fetch)
}
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
  <title>Change Password - <?=$websiteTitle?></title>
  
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

<body data-page="page_changepass">
  <div id="sidebar_menu_bg"></div>
  <div id="wrapper" data-page="page_home">
    <?php include 'src/component/header.php'; ?>
    <div class="clearfix"></div>

    <div id="main-wrapper" class="layout-page layout-profile">
      <div class="profile-header">
        <div class="profile-header-cover"
          style="background-image: url(<?php 
          if($fetch['image'] == ''){
            echo ''.$websiteUrl.'/files/avatar/default/default.jpeg';
         }else{
            echo ''.$websiteUrl.'/files/avatar/'.$fetch['image'].'';
         }?>);"></div>
        <div class="container">
        <div class="ph-title" style="font-size: 20px;">Uwaaah~! You forgot your password, baka?! (＞﹏＜)</div>
          <div class="ph-tabs">
            <div class="bah-tabs">
              <ul class="nav nav-tabs pre-tabs">
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/profile"><i
                      class="fas fa-user mr-2"></i>Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/continue-watching"><i class="fas fa-history mr-2"></i>Continue Watching
                      </a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $websiteUrl ?>/watchlist"><i class="fas fa-heart mr-2"></i>Watch
                    List</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= $websiteUrl ?>/changepass"><i class="fas fa-key mr-2"></i>Change
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
      <div class="profile-content">
        <div class="container">
          <div class="profile-box profile-box-account makeup">
            <h2 class="h2-heading mb-4"><i class="fas fa-key mr-3"></i>Change Password</h2>
            <div class="block_area-content">
              <div class="show-profile-avatar text-center mb-3">
                <div class="profile-avatar d-inline-block" data-toggle="modal" data-target="#modalavatars">
                  <?php
                  if (!empty($fetch['image'])): ?>
                      <img id="preview-avatar" src="<?= $fetch['image'] ?>" alt="Profile Picture" >
                  <?php else: ?>
                      <img id="preview-avatar" src="<?= $websiteUrl ?>/files/images/default_avatar.png" alt="Default Avatar" >
                  <?php endif; ?>
                </div>
              </div>
              <form class="preform" method="post" action=""  enctype="multipart/form-data">
              <input type="hidden" name="old_pass" value="<?php echo $fetch['password']; ?>">
                <div class="row">
                  <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="form-group">
                      <label class="prelabel" for="pro5-currentpass">Current Password</label>
                      <input type="password" class="form-control" id="pro5-currentpass" name="update_pass">
                    </div>
                    <div class="form-group">
                      <label class="prelabel" for="pro5-pass">New Password</label>
                      <input type="password" class="form-control" id="pro5-pass" name="new_pass">
                    </div>
                    <div class="form-group">
                      <label class="prelabel" for="pro5-confirm">Confirm New Password</label>
                      <input type="password" class="form-control" id="pro5-confirm" name="confirm_pass">
                    </div>
                  </div>
                  <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="form-group">
                      <div class="mt-4"></div>
                      <input type="submit" value="Save" name="update_profile" class="btn btn-block btn-primary">
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>
    <?php include 'src/component/footer.php'; ?>
    
    <div id="mask-overlay"></div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js?v=1.5"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js"></script>
   <!-- <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/comman.js"></script> -->
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/movie.js?v=1.5"></script>
    <link rel="stylesheet" href="<?= $websiteUrl ?>/src/assets/css/jquery-ui.css?v=1.5">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js?v=1.5"></script>
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/function.js?v=1.5"></script>
    <script type="text/javascript" src="<?= $websiteUrl ?>/src/assets/js/app.min.js?v=1.4"></script>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js?v=<?=$version?>"></script>
    <?php
      if(isset($message)){
         foreach($message as $msg){
            echo '<script type="text/javascript">swal({title: "Error!",text: "'.$msg.'",icon: "warning",button: "Close",})</script>;';
         }
      }
      ?>
      <?php
      if(isset($message2)){
         foreach($message2 as $msg2){
            echo '<script type="text/javascript">swal({title: "Success!",text: "'.$msg2.'",icon: "success",button: "Close",})</script>;';
         }
      }
      ?>
    <div style="display:none;">
    </div>
  </div>
 
  <script>
    function showToast(title, message, type) {
        swal({
            title: title,
            text: message,
            icon: type,
            button: "Close",
        });
    }
    </script>
</body>

</html>
