@if(auth()->check() && auth()->user()->utype === 'USR')
<style>
  .support-chat { position: fixed; right: 22px; bottom: 24px; z-index: 1050; font-family: inherit; }
  .support-chat__toggle { width: 58px; height: 58px; border: 0; border-radius: 50%; background: #111827; color: #fff; box-shadow: 0 12px 30px rgba(17, 24, 39, .25); font-size: 24px; }
  .support-chat__panel { display: none; width: min(360px, calc(100vw - 32px)); height: 500px; max-height: calc(100vh - 110px); background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 18px 50px rgba(15, 23, 42, .18); overflow: hidden; }
  .support-chat.is-open .support-chat__panel { display: flex; flex-direction: column; }
  .support-chat.is-open .support-chat__toggle { display: none; }
  .support-chat__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; background: #111827; color: #fff; }
  .support-chat__title { font-weight: 600; line-height: 1.2; }
  .support-chat__subtitle { display: block; margin-top: 2px; font-size: 12px; color: #cbd5e1; }
  .support-chat__close { border: 0; background: transparent; color: #fff; font-size: 24px; line-height: 1; }
  .support-chat__notice { padding: 10px 14px; background: #fff7ed; color: #9a3412; font-size: 13px; border-bottom: 1px solid #fed7aa; }
  .support-chat__messages { flex: 1; overflow-y: auto; padding: 14px; background: #f8fafc; }
  .support-chat__message { display: flex; flex-direction: column; max-width: 82%; margin-bottom: 10px; }
  .support-chat__message.is-mine { margin-left: auto; align-items: flex-end; }
  .support-chat__bubble { padding: 9px 12px; border-radius: 8px; background: #fff; color: #111827; border: 1px solid #e5e7eb; font-size: 14px; line-height: 1.45; word-break: break-word; }
  .support-chat__message.is-mine .support-chat__bubble { background: #111827; color: #fff; border-color: #111827; }
  .support-chat__message.is-system { max-width: 100%; align-items: center; margin: 8px 0; }
  .support-chat__message.is-system .support-chat__bubble { background: transparent; border: 0; color: #64748b; font-size: 12px; text-align: center; }
  .support-chat__meta { margin-top: 3px; color: #94a3b8; font-size: 11px; }
  .support-chat__form { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; background: #fff; }
  .support-chat__input { flex: 1; min-width: 0; height: 42px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; }
  .support-chat__send, .support-chat__end { border: 0; border-radius: 6px; height: 42px; padding: 0 14px; background: #111827; color: #fff; font-weight: 600; }
  .support-chat__end { background: #e5e7eb; color: #111827; }
  .support-chat__actions { display: flex; gap: 8px; padding: 0 12px 12px; background: #fff; }
</style>

<div class="support-chat" id="supportChat"
  data-state-url="{{ route('support.customer.state') }}"
  data-start-url="{{ route('support.customer.start') }}"
  data-message-url="{{ route('support.customer.message') }}"
  data-close-url="{{ route('support.customer.close') }}">
  <button class="support-chat__toggle" type="button" aria-label="Chat hỗ trợ">
    <i class="fa fa-comments-o"></i>
  </button>
  <section class="support-chat__panel" aria-live="polite">
    <div class="support-chat__header">
      <div>
        <div class="support-chat__title">Hỗ trợ khách hàng</div>
        <span class="support-chat__subtitle" data-support-status>Nhấn để bắt đầu tư vấn</span>
      </div>
      <button class="support-chat__close" type="button" aria-label="Đóng">&times;</button>
    </div>
    <div class="support-chat__notice" data-support-notice style="display:none;"></div>
    <div class="support-chat__messages" data-support-messages></div>
    <form class="support-chat__form" data-support-form>
      <input class="support-chat__input" data-support-input type="text" maxlength="1000" placeholder="Nhập tin nhắn..." autocomplete="off">
      <button class="support-chat__send" type="submit">Gửi</button>
    </form>
    <div class="support-chat__actions">
      <button class="support-chat__end" data-support-end type="button">Kết thúc tư vấn</button>
    </div>
  </section>
</div>

<script>
  (function () {
    var root = document.getElementById('supportChat');
    if (!root) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var messagesEl = root.querySelector('[data-support-messages]');
    var noticeEl = root.querySelector('[data-support-notice]');
    var statusEl = root.querySelector('[data-support-status]');
    var inputEl = root.querySelector('[data-support-input]');
    var conversation = null;
    var lastMessageCount = 0;

    function escapeHtml(value) {
      return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
      });
    }

    function post(url, data) {
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data || {})
      }).then(function (response) { return response.json().then(function (json) { return { ok: response.ok, json: json }; }); });
    }

    function get(url) {
      return fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (response) { return response.json(); });
    }

    function render(payload) {
      conversation = payload.conversation;
      var messages = payload.messages || [];
      var notice = payload.notice || (conversation && conversation.notice);

      noticeEl.style.display = notice ? 'block' : 'none';
      noticeEl.textContent = notice || '';
      statusEl.textContent = conversation && conversation.staff_name ? 'Đang tư vấn với ' + conversation.staff_name : 'Tư vấn trực tiếp';

      messagesEl.innerHTML = messages.map(function (item) {
        var classes = ['support-chat__message'];
        if (item.mine) classes.push('is-mine');
        if (item.sender_type === 'system') classes.push('is-system');
        return '<div class="' + classes.join(' ') + '">' +
          '<div class="support-chat__bubble">' + escapeHtml(item.message) + '</div>' +
          (item.sender_type === 'system' ? '' : '<span class="support-chat__meta">' + escapeHtml(item.sender_name) + ' · ' + escapeHtml(item.time) + '</span>') +
        '</div>';
      }).join('');

      if (messages.length !== lastMessageCount) {
        messagesEl.scrollTop = messagesEl.scrollHeight;
        lastMessageCount = messages.length;
      }

      inputEl.disabled = !!conversation && conversation.status === 'closed';
    }

    function refresh() {
      get(root.dataset.stateUrl).then(render).catch(function () {});
    }

    function start(message) {
      post(root.dataset.startUrl, { message: message || '' }).then(function (result) {
        render(result.json);
      });
    }

    root.querySelector('.support-chat__toggle').addEventListener('click', function () {
      root.classList.add('is-open');
      start('');
    });

    root.querySelector('.support-chat__close').addEventListener('click', function () {
      root.classList.remove('is-open');
    });

    root.querySelector('[data-support-form]').addEventListener('submit', function (event) {
      event.preventDefault();
      var message = inputEl.value.trim();
      if (!message) return;
      inputEl.value = '';

      if (!conversation) {
        start(message);
        return;
      }

      post(root.dataset.messageUrl, { message: message }).then(function (result) {
        if (result.ok) render(result.json);
        if (!result.ok && result.json.message) {
          noticeEl.style.display = 'block';
          noticeEl.textContent = result.json.message;
        }
      });
    });

    root.querySelector('[data-support-end]').addEventListener('click', function () {
      if (!conversation || conversation.status === 'closed') return;
      post(root.dataset.closeUrl, {}).then(function (result) { render(result.json); });
    });

    refresh();
    setInterval(refresh, 3000);
  })();
</script>
@endif
