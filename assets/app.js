(function () {
  function csrf() {
    var el = document.querySelector('meta[name="csrf-token"]');
    if (el) return el.getAttribute('content');
    var input = document.querySelector('input[name="_csrf"]');
    return input ? input.value : '';
  }
  function postJson(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf() },
      body: JSON.stringify(body || {}),
      credentials: 'same-origin',
    }).then(function (r) {
      return r.json().then(function (data) {
        if (!r.ok) throw data;
        return data;
      });
    });
  }
  document.addEventListener('click', function (e) {
    var likeBtn = e.target.closest('[data-like]');
    if (likeBtn) {
      e.preventDefault();
      postJson('api/like.php', { video_id: parseInt(likeBtn.getAttribute('data-like'), 10) })
        .then(function (data) {
          likeBtn.classList.toggle('active', data.liked);
          var count = likeBtn.querySelector('[data-count]');
          if (count) count.textContent = data.likes;
        })
        .catch(function (err) {
          if (err && err.error === 'login') window.location.href = 'login.php';
        });
      return;
    }
    var subBtn = e.target.closest('[data-subscribe]');
    if (subBtn) {
      e.preventDefault();
      postJson('api/subscribe.php', { channel_id: parseInt(subBtn.getAttribute('data-subscribe'), 10) })
        .then(function (data) {
          subBtn.textContent = data.subscribed ? 'Subscribed' : 'Subscribe';
        })
        .catch(function (err) {
          if (err && err.error === 'login') window.location.href = 'login.php';
        });
    }
  });
  var commentForm = document.getElementById('comment-form');
  if (commentForm) {
    commentForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var videoId = commentForm.getAttribute('data-video');
      var textarea = commentForm.querySelector('textarea');
      var body = (textarea.value || '').trim();
      if (!body) return;
      var btn = commentForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      postJson('api/comment.php', { video_id: parseInt(videoId, 10), body: body })
        .then(function (data) {
          textarea.value = '';
          if (data.comment) {
            var list = document.getElementById('comments-list');
            if (list) {
              var c = data.comment;
              var div = document.createElement('div');
              div.className = 'comment';
              div.innerHTML =
                '<img src="' + escapeAttr(c.avatar_url) + '" alt="">' +
                '<div><div class="meta"><strong>' + escapeHtml(c.display_name) + '</strong> · ' +
                escapeHtml(c.created_ago) + '</div><div class="body">' + escapeHtml(c.body) + '</div></div>';
              list.insertBefore(div, list.firstChild);
            }
            var cnt = document.querySelector('[data-comments-count]');
            if (cnt) cnt.textContent = data.comments_count;
          }
        })
        .catch(function (err) {
          if (err && err.error === 'login') window.location.href = 'login.php';
        })
        .finally(function () { btn.disabled = false; });
    });
  }
  function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  function escapeAttr(s) { return escapeHtml(s).replace(/'/g, '&#39;'); }
})();
