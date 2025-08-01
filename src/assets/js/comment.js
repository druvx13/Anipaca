class CommentHandler {
    constructor() {
        this.page = 1;
        this.loading = false;
        this.currentEpisode = '1';
        this.currentAnimeId = '';

        const commentBlock = document.getElementById('comment-block');
        this.userId = commentBlock ? commentBlock.dataset.userId : null;
        this.isLoggedIn = commentBlock ? commentBlock.dataset.isLoggedIn === '1' : false;

        this.initialize();
        this.initializeEventListeners();
        this.initializeEmojiPickers();
    }

    initialize() {
        const urlParams = new URLSearchParams(window.location.search);
        this.currentEpisode = urlParams.get('ep') || '1';
        // This is a bit brittle, might need a better way to get the anime ID
        this.currentAnimeId = window.location.pathname.split('/watch/')[1].split('?')[0];

        if (this.currentAnimeId) {
            this.loadComments(this.currentEpisode, this.currentAnimeId);
        }

        // Listen for custom episode change events from the watch page
        window.addEventListener('episodeChange', (e) => {
            if (e.detail && e.detail.episodeNumber) {
                this.currentEpisode = e.detail.episodeNumber;
                if (this.currentAnimeId) {
                    this.loadComments(this.currentEpisode, this.currentAnimeId);
                }
            }
        });

        // Re-initialize if URL changes (for SPA-like navigation)
        let lastUrl = location.href;
        new MutationObserver(() => {
            const url = location.href;
            if (url !== lastUrl) {
                lastUrl = url;
                this.initialize();
            }
        }).observe(document.body, { subtree: true, childList: true });
    }

    async loadComments(episodeId, animeId) {
        if (this.loading) return;
        this.loading = true;

        try {
            document.querySelectorAll('[data-current-episode]').forEach(input => input.value = episodeId);
            document.querySelectorAll('[data-current-anime]').forEach(input => input.value = animeId);

            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('episode_id', episodeId);
            formData.append('anime_id', animeId);

            const response = await fetch('/src/component/comment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                document.getElementById('comment-count').textContent = result.commentCount;
                const commentsList = document.getElementById('comments-list');
                commentsList.innerHTML = result.comments.map(comment => this.generateCommentHTML(comment, true)).join('');

                document.querySelectorAll('.current-episode').forEach(el => el.textContent = episodeId);
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        } finally {
            this.loading = false;
        }
    }

    initializeEmojiPickers() {
        // Emoji picker logic can be added here if needed
    }

    async handleReaction(button) {
        if (!this.isLoggedIn) {
            alert('Please login to react to comments');
            return;
        }

        const commentId = button.dataset.id;
        const type = button.dataset.type;
        const episodeId = document.querySelector('input[name="episode_id"]').value;
        const animeId = document.querySelector('input[name="anime_id"]').value;

        const formData = new FormData();
        formData.append('action', 'react');
        formData.append('comment_id', commentId);
        formData.append('type', type);
        formData.append('episode_id', episodeId);
        formData.append('anime_id', animeId);

        try {
            const response = await fetch('/src/component/comment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                const commentElement = document.querySelector(`#cm-${commentId}`);
                const likeBtn = commentElement.querySelector(`.cm-btn-vote[data-type="1"]`);
                const dislikeBtn = commentElement.querySelector(`.cm-btn-vote[data-type="0"]`);

                likeBtn.querySelector('.value').textContent = result.likes || '';
                dislikeBtn.querySelector('.value').textContent = result.dislikes || '';

                likeBtn.classList.toggle('active', result.userReaction === 1);
                dislikeBtn.classList.toggle('active', result.userReaction === 0);
            }
        } catch (error) {
            console.error('Error handling reaction:', error);
        }
    }

    initializeEventListeners() {
        const commentsList = document.getElementById('comments-list');
        if (!commentsList) return;

        // Comment form submission
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitComment(e.target);
            });
        }

        // Use a single delegated event listener for clicks inside the comments list
        commentsList.addEventListener('click', (e) => {
            const reactionBtn = e.target.closest('.cm-btn-vote');
            if (reactionBtn) {
                e.preventDefault();
                this.handleReaction(reactionBtn);
                return;
            }

            const replyBtn = e.target.closest('.ib-reply .btn');
            if (replyBtn) {
                e.preventDefault();
                const commentId = replyBtn.closest('.cw_l-line').id.split('-')[1];
                this.toggleReplyForm(commentId);
                return;
            }

            const closeReplyBtn = e.target.closest('.btn-close-reply');
            if (closeReplyBtn) {
                e.preventDefault();
                const commentId = closeReplyBtn.closest('.reply-block').id.split('-')[1];
                this.toggleReplyForm(commentId); // Toggles it off
                return;
            }
        });

        // Delegated event listener for reply form submissions
        commentsList.addEventListener('submit', (e) => {
            if (e.target.classList.contains('reply-form')) {
                e.preventDefault();
                this.submitReply(e.target);
            }
        });
    }

    toggleReplyForm(commentId) {
        const replyBlock = document.querySelector(`#reply-${commentId}`);
        if (replyBlock) {
            replyBlock.style.display = replyBlock.style.display === 'none' ? 'block' : 'none';
        } else {
            // Create and inject the reply form if it doesn't exist
            const commentElement = document.querySelector(`#cm-${commentId} .info`);
            const replyFormHTML = this.generateReplyFormHTML(commentId);
            commentElement.insertAdjacentHTML('beforeend', replyFormHTML);
        }
    }

    async submitComment(form) {
        const loadingSpinner = document.getElementById('comment-loading');
        try {
            loadingSpinner.style.display = 'block';
            const formData = new FormData(form);
            const response = await fetch('/src/component/comment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                form.querySelector('textarea').value = '';
                this.loadComments(this.currentEpisode, this.currentAnimeId);
            } else {
                alert(result.message || 'Error submitting comment');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to submit comment');
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }

    async submitReply(form) {
        try {
            const formData = new FormData(form);
            const response = await fetch('/src/component/comment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                // Instead of full reload, we can just append the new reply
                // but for simplicity, a reload is fine for now.
                this.loadComments(this.currentEpisode, this.currentAnimeId);
            } else {
                alert(result.message || 'Error submitting reply');
            }
        } catch (error) {
            console.error('Error submitting reply:', error);
            alert('Failed to submit reply');
        }
    }

    generateCommentHTML(comment, isTopLevel = false) {
        const repliesHTML = comment.replies && comment.replies.length > 0
            ? `<div class="replies">${comment.replies.map(reply => this.generateCommentHTML(reply, false)).join('')}</div>`
            : '';

        const replyButtonHTML = isTopLevel ? `
            <div class="ib-li ib-reply">
                <a class="btn" data-id="${comment.id}"><i class="fas fa-reply mr-1"></i>Reply</a>
            </div>
        ` : '';

        return `
            <div class="cw_l-line" id="cm-${comment.id}">
                <a href="/community/user/${comment.user_id}" target="_blank" class="user-avatar">
                    <img class="user-avatar-img" src="${comment.user_avatar}" alt="${comment.username}">
                </a>
                <div class="info">
                    <div class="ihead">
                        <a href="/community/user/${comment.user_id}" target="_blank" class="user-name">${comment.username}</a>
                        <div class="time">${comment.created_at}</div>
                    </div>
                    <div class="ibody">
                        <p>${comment.content}</p>
                    </div>
                    <div class="ibottom">
                        <div class="ib-li ib-like">
                            <a class="btn cm-btn-vote" data-id="${comment.id}" data-type="1">
                                <i class="far fa-thumbs-up mr-1"></i>
                                <span class="value">${comment.likes || ''}</span>
                            </a>
                        </div>
                        <div class="ib-li ib-dislike">
                            <a class="btn cm-btn-vote" data-id="${comment.id}" data-type="0">
                                <i class="far fa-thumbs-down mr-1"></i>
                                <span class="value">${comment.dislikes || ''}</span>
                            </a>
                        </div>
                        ${replyButtonHTML}
                    </div>
                    ${repliesHTML}
                </div>
            </div>
        `;
    }

    generateReplyFormHTML(parentId) {
        return `
            <div class="reply-block" id="reply-${parentId}" style="display: block;">
                <form class="reply-form preform preform-dark">
                    <input type="hidden" name="action" value="reply">
                    <input type="hidden" name="parent_id" value="${parentId}">
                     <input type="hidden" name="episode_id" value="${this.currentEpisode}" data-current-episode>
                    <input type="hidden" name="anime_id" value="${this.currentAnimeId}" data-current-anime>
                    <textarea class="form-control form-control-textarea" name="content" placeholder="Write a reply..."></textarea>
                    <div class="ci-buttons">
                        <button type="submit" class="btn btn-sm btn-primary">Submit Reply</button>
                        <button type="button" class="btn btn-sm btn-secondary btn-close-reply">Cancel</button>
                    </div>
                </form>
            </div>
        `;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Make sure this runs only on pages with the comment block
    if (document.getElementById('comment-block')) {
        new CommentHandler();
    }
});