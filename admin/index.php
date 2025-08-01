<?php
require_once('check_admin.php');

// Get stats from the database
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
$total_watchlist = $conn->query("SELECT COUNT(*) as count FROM watchlist")->fetch_assoc()['count'];

include('header.php');
?>

<h1 class="h2">Dashboard</h1>
<p>Welcome to the admin panel. Here are some stats about your site:</p>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text"><?php echo $total_users; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Comments</h5>
                <p class="card-text"><?php echo $total_comments; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Total Watchlist Items</h5>
                <p class="card-text"><?php echo $total_watchlist; ?></p>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
