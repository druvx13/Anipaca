<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/_config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/src/ajax/cm-up.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($commentData)) {
    error_log('Comment Data Received: ' . print_r($commentData, true));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_COOKIE['userID']) && $_POST['action'] !== 'get') {
        echo json_encode(['success' => false, 'message' => 'Please login to comment']);
        exit;
    }

    if (!isset($_POST['anime_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing anime ID']);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'get':
            if (!isset($_POST['episode_id'])) {
                echo json_encode(['success' => false, 'message' => 'Missing episode ID']);
                exit;
            }
            
            $commentSystem = new CommentSystem($conn, $_POST['episode_id'], $_POST['anime_id']);
            $comments = $commentSystem->getComments();
            
            echo json_encode([
                'success' => true, 
                'comments' => $comments,
                'commentCount' => count($comments)
            ]);
            break;
            
        case 'add':
            if (!isset($_POST['content']) || empty(trim($_POST['content']))) {
                echo json_encode(['success' => false, 'message' => 'Comment content cannot be empty']);
                exit;
            }

            $commentSystem = new CommentSystem(
                $conn,
                $_POST['episode_id'],
                $_POST['anime_id']
            );

            $result = $commentSystem->addComment($_POST['content']);

            echo json_encode($result);
            break;

        case 'reply':
            if (!isset($_POST['content']) || empty(trim($_POST['content']))) {
                echo json_encode(['success' => false, 'message' => 'Reply content cannot be empty']);
                exit;
            }

            if (!isset($_POST['parent_id']) || empty($_POST['parent_id'])) {
                echo json_encode(['success' => false, 'message' => 'Missing parent comment ID']);
                exit;
            }

            $commentSystem = new CommentSystem(
                $conn, 
                $_POST['episode_id'], 
                $_POST['anime_id'] 
            );

            $result = $commentSystem->addComment($_POST['content'], $_POST['parent_id']);
            
            echo json_encode($result);
            break;
            
        case 'react':
            if (!isset($_POST['comment_id']) || !isset($_POST['type'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            $commentSystem = new CommentSystem($conn, $_POST['episode_id'], $_POST['anime_id']);
            $result = $commentSystem->addReaction($_POST['comment_id'], $_POST['type']);
            
            echo json_encode($result);
            break;
    }
    exit;
}

$user_id = isset($_COOKIE['userID']) ? $_COOKIE['userID'] : null;
$username = '';

if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $username = $user['username'] ?? '';
}
?>

<style>
</style>

<section class="block_area block_area-comment" id="comment-block" data-user-id="<?php echo $user_id ?? 'null'; ?>" data-is-logged-in="<?php echo $user_id ? '1' : '0'; ?>">
    <div class="block_area-header block_area-header-tabs">
        <div class="float-left bah-heading mr-4">
            <h2 class="cat-heading">Comments</h2>
        </div>
        <div class="clearfix"></div>
    </div>
    
    <div class="show-comments">
        <div id="content-comments" class="comments-wrap">
            <div class="sc-header">
                <div class="sc-h-from">
                    <a class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true" name="Ep_Number" aria-expanded="false">
                        Episode <span class="current-episode">1</span>
                        <i class="fas fa-angle-down ml-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                        <a class="dropdown-item cm-by active" data-value="episode" href="javascript:;">Episode <span class="current-episode">1</span> <i class="fas fa-check mt-2"></i></a>
                    </div>
                </div>

                <div class="sc-h-title">
                    <i class="far fa-comment-alt mr-2"></i>
                    <span id="comment-count">0</span> Comments
                </div>

                <div class="sc-h-sort">
                    <a class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Sort by<i class="fas fa-sort ml-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-model dropdown-menu-normal" aria-labelledby="ssc-list">
                        <a class="dropdown-item cm-sort active" data-value="newest" href="javascript:;">Newest <i class="fas fa-check mt-2"></i></a>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="comment-input">
                <?php if ($user_id): ?>
                    <div class="user-avatar">
                        <img class="user-avatar-img" src="<?= htmlspecialchars($user['image']) ?>" alt="<?= htmlspecialchars($username) ?>">
                    </div>
                    <div class="ci-form">
                        <div class="user-name">
                            Comment as <span class="link-highlight ml-1"><?= htmlspecialchars($username) ?></span>
                        </div>
                        <form id="comment-form" class="preform preform-dark comment-form">
                            <div class="loading-absolute bg-white" id="comment-loading" style="display: none;">
                                <div class="loading">
                                    <div class="span1"></div>
                                    <div class="span2"></div>
                                    <div class="span3"></div>
                                </div>
                            </div>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="episode_id" value="<?= htmlspecialchars($commentData['episode_id']) ?>" data-current-episode>
                            <input type="hidden" name="anime_id" value="<?= htmlspecialchars($commentData['anime_id']) ?>" data-current-anime>
                            <textarea class="form-control form-control-textarea comment-subject cm-input-base" 
                                    id="df-cm-content" 
                                    name="content" 
                                    placeholder="Leave a comment..."></textarea>
                            
                            <div class="ci-buttons" id="df-cm-buttons">
                                <div class="ci-b-right">
                                    <div class="cb-li">
                                        <a class="btn btn-sm btn-secondary" id="df-cm-close">Close</a>
                                    </div>
                                    <div class="cb-li">
                                        <button type="submit" class="btn btn-sm btn-primary ml-2">Comment</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Please <a href="/login">login</a> to comment</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="list-comment">
                <div class="cw_list" id="comments-list">
                    <!-- Comments will be loaded here by comment.js -->
                </div>
            </div>
        </div>
    </div>
</section>
