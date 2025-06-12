<?php
class CommentSystem {
    private $conn;
    private $anime_id;
    private $episode_id;
    private $user_id;

    public function __construct($conn, $episode_id, $anime_id) {
        $this->conn = $conn;
        $this->episode_id = (int)$episode_id;
        $this->anime_id = $anime_id;
        $this->user_id = isset($_COOKIE['userID']) ? (int)$_COOKIE['userID'] : null;
        
        error_log("CommentSystem initialized with: " . json_encode([
            'episode_id' => $this->episode_id,
            'anime_id' => $this->anime_id,
            'user_id' => $this->user_id
        ]));
    }

    public function getComments($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT 
                c.*,
                (SELECT COUNT(*) FROM comment_reactions cr WHERE cr.comment_id = c.id AND cr.type = 1) as likes,
                (SELECT COUNT(*) FROM comment_reactions cr WHERE cr.comment_id = c.id AND cr.type = 0) as dislikes
            FROM comments c
            WHERE c.anime_id = ? 
            AND c.episode_id = ?
            AND c.parent_id IS NULL
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = null;
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::getComments: " . $this->conn->error);
                return [];
            }
            $stmt->bind_param("siii",
                $this->anime_id,
                $this->episode_id,
                $limit,
                $offset
            );

            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::getComments: " . $e->getMessage());
            return [];
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    private function getReplies($parent_id) {
        $query = "
            SELECT 
                c.*,
                u.username,
                COALESCE(u.image, u.avatar_url) as user_avatar,
                (SELECT COUNT(*) FROM comment_reactions cr WHERE cr.comment_id = c.id AND cr.type = 1) as likes,
                (SELECT COUNT(*) FROM comment_reactions cr WHERE cr.comment_id = c.id AND cr.type = 0) as dislikes
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.parent_id = ?
            ORDER BY c.created_at ASC
        ";

        $stmt = null;
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::getReplies: " . $this->conn->error);
                return [];
            }
            $stmt->bind_param("i", $parent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $replies = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

            if ($replies) {
                foreach ($replies as &$reply) {
                    $reply['userReaction'] = $this->getUserReaction($reply['id']);
                }
            }
            return $replies;
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::getReplies: " . $e->getMessage());
            return [];
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function addComment($content, $username, $avatar_url) {
        $stmt = null;
        try {
            if (empty($this->anime_id)) {
                error_log("Error: anime_id is empty in addComment");
                return ['success' => false, 'message' => 'Invalid anime ID'];
            }

            $user_id = isset($_COOKIE['userID']) ? (int)$_COOKIE['userID'] : 0;
            
            $stmt = $this->conn->prepare("
                INSERT INTO comments 
                (content, username, user_avatar, episode_id, anime_id, user_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::addComment: " . $this->conn->error);
                return ['success' => false, 'message' => 'Failed to prepare statement'];
            }
            
            $stmt->bind_param("sssisi", 
                $content, 
                $username, 
                $avatar_url, 
                $this->episode_id, 
                $this->anime_id,
                $user_id
            );

            if ($stmt->execute()) {
                $comment_id = $this->conn->insert_id;
                return [
                    'success' => true,
                    'message' => 'Comment added successfully',
                    'comment' => [
                        'id' => $comment_id,
                        'content' => $content,
                        'username' => $username,
                        'user_avatar' => $avatar_url,
                        'episode_id' => $this->episode_id,
                        'anime_id' => $this->anime_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'likes' => 0,
                        'dislikes' => 0
                    ]
                ];
            } else {
                error_log("MySQL Execute Error in CommentSystem::addComment: " . $stmt->error);
                return ['success' => false, 'message' => 'Failed to add comment'];
            }
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::addComment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing comment due to database issue.'];
        } catch (Exception $e) { // Catch other non-mysqli exceptions
            error_log("Non-DB Exception in CommentSystem::addComment: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing comment.'];
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    private function getCommentById($comment_id) {
        $query = "
            SELECT 
                c.*,
                u.username,
                COALESCE(u.image, u.avatar_url) as user_avatar
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ";

        $stmt = null;
        try {
            $stmt = $this->conn->prepare($query);
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::getCommentById: " . $this->conn->error);
                return null;
            }
            $stmt->bind_param("i", $comment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_assoc() : null;
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::getCommentById: " . $e->getMessage());
            return null;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    public function addReaction($comment_id, $type) {
        if (!$this->user_id) {
            return ['success' => false, 'message' => 'User not logged in'];
        }

        $stmt = null;
        try {
            $this->conn->begin_transaction();

            // Check if user already reacted
            $stmt_check = $this->conn->prepare("
                SELECT type FROM comment_reactions 
                WHERE comment_id = ? AND user_id = ?
            ");
            if (!$stmt_check) throw new mysqli_sql_exception("Prepare failed for check: " . $this->conn->error);
            $stmt_check->bind_param("ii", $comment_id, $this->user_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            $existing = $result->fetch_assoc();
            $stmt_check->close(); // Close this statement

            if ($existing) {
                if ($existing['type'] == $type) {
                    // Remove reaction if clicking the same button
                    $stmt = $this->conn->prepare("
                        DELETE FROM comment_reactions 
                        WHERE comment_id = ? AND user_id = ?
                    ");
                    if (!$stmt) throw new mysqli_sql_exception("Prepare failed for delete: " . $this->conn->error);
                    $stmt->bind_param("ii", $comment_id, $this->user_id);
                    $stmt->execute();
                } else {
                    // Update reaction if different type
                    $stmt = $this->conn->prepare("
                        UPDATE comment_reactions 
                        SET type = ? 
                        WHERE comment_id = ? AND user_id = ?
                    ");
                    if (!$stmt) throw new mysqli_sql_exception("Prepare failed for update: " . $this->conn->error);
                    $stmt->bind_param("iii", $type, $comment_id, $this->user_id);
                    $stmt->execute();
                }
            } else {
                // Add new reaction
                $stmt = $this->conn->prepare("
                    INSERT INTO comment_reactions (comment_id, user_id, type) 
                    VALUES (?, ?, ?)
                ");
                if (!$stmt) throw new mysqli_sql_exception("Prepare failed for insert: " . $this->conn->error);
                $stmt->bind_param("iii", $comment_id, $this->user_id, $type);
                $stmt->execute();
            }
            if ($stmt) $stmt->close(); // Close the last used statement

            $this->conn->commit();

            // Get updated counts
            $likes = $this->getReactionCount($comment_id, 1);
            $dislikes = $this->getReactionCount($comment_id, 0);
            $userReaction = $this->getUserReaction($comment_id);

            return [
                'success' => true,
                'likes' => $likes,
                'dislikes' => $dislikes,
                'userReaction' => $userReaction
            ];
        } catch (mysqli_sql_exception $e) {
            $this->conn->rollback();
            error_log("Database error in CommentSystem::addReaction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update reaction due to database error.'];
        } catch (Exception $e) { // Catch other non-mysqli exceptions
            $this->conn->rollback();
            error_log("Non-DB error in CommentSystem::addReaction: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update reaction.'];
        } finally {
            // Ensure any $stmt that might have been assigned in try is closed if an exception occurred before its explicit close
            if ($stmt && $stmt->errno) { // Check if $stmt is an object and has an error number (might not be prepared)
                 // $stmt->close(); // This might error if $stmt is not a valid statement object
            }
        }
    }

    private function getReactionCount($comment_id, $type) {
        $stmt = null;
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count
                FROM comment_reactions
                WHERE comment_id = ? AND type = ?
            ");
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::getReactionCount: " . $this->conn->error);
                return 0;
            }
            $stmt->bind_param("ii", $comment_id, $type);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::getReactionCount: " . $e->getMessage());
            return 0;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }

    private function getUserReaction($comment_id) {
        if (!$this->user_id) return null;
        $stmt = null;
        try {
            $stmt = $this->conn->prepare("
                SELECT type
                FROM comment_reactions
                WHERE comment_id = ? AND user_id = ?
            ");
            if (!$stmt) {
                error_log("MySQL Prepare Error in CommentSystem::getUserReaction: " . $this->conn->error);
                return null;
            }
            $stmt->bind_param("ii", $comment_id, $this->user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result && $result->num_rows > 0 ? $result->fetch_assoc()['type'] : null;
        } catch (mysqli_sql_exception $e) {
            error_log("Database error in CommentSystem::getUserReaction: " . $e->getMessage());
            return null;
        } finally {
            if ($stmt) {
                $stmt->close();
            }
        }
    }
}
?> 