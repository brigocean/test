(function () {
    function send(postId, vote) {
        var body = new URLSearchParams();
        body.append('action', 'article_like');
        body.append('nonce', LikesData.nonce);
        body.append('post_id', postId);
        body.append('vote', vote);
        body.append('page_url', window.location.href);

        return fetch(LikesData.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).then(function (res) {
            return res.json();
        });
    }

    function render(box, data) {
        box.querySelector('[data-count="up"]').textContent = data.up;
        box.querySelector('[data-count="down"]').textContent = data.down;

        var up = box.querySelector('.likes__btn--up');
        var down = box.querySelector('.likes__btn--down');

        up.classList.toggle('is-active', data.vote === 1);
        down.classList.toggle('is-active', data.vote === -1);
    }

    document.querySelectorAll('.likes').forEach(function (box) {
        var postId = box.getAttribute('data-post');

        box.querySelectorAll('.likes__btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (box.classList.contains('is-loading')) {
                    return;
                }

                var vote = parseInt(btn.getAttribute('data-vote'), 10);
                box.classList.add('is-loading');

                send(postId, vote).then(function (response) {
                    if (response && response.success) {
                        render(box, response.data);
                    }
                }).finally(function () {
                    box.classList.remove('is-loading');
                });
            });
        });
    });
})();
