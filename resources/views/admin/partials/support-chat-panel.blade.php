@php
  $supportUser = auth()->user();
  $isSuperAdminChat = $supportUser
      && $supportUser->utype === 'ADMM'
      && strtolower($supportUser->email) === 'admint@lvtn.vn';
  $isSupportAdvisor = $supportUser
      && $supportUser->utype === 'ADM';
@endphp

@if($isSuperAdminChat || $isSupportAdvisor)
<style>
  .support-notification-bell { position: relative; width: 42px; height: 42px; border: 1px solid #dbe3ef; border-radius: 8px; background: #fff; color: #111827; display: inline-flex; align-items: center; justify-content: center; margin-right: 12px; }
  .support-notification-bell__count { position: absolute; top: -7px; right: -7px; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: #dc2626; color: #fff; font-size: 11px; line-height: 20px; text-align: center; font-weight: 700; }
  .operator-chat { position: fixed; right: 24px; bottom: 24px; z-index: 1200; width: min(760px, calc(100vw - 32px)); font-family: inherit; }
  .operator-chat__panel { display: none; height: 560px; max-height: calc(100vh - 96px); background: #fff; border: 1px solid #dbe3ef; border-radius: 8px; box-shadow: 0 18px 48px rgba(15, 23, 42, .2); overflow: hidden; }
  .operator-chat.is-open .operator-chat__panel { display: grid; grid-template-columns: 260px 1fr; }
  .operator-chat__sidebar { border-right: 1px solid #e5e7eb; background: #f8fafc; min-width: 0; display: flex; flex-direction: column; }
  .operator-chat__head { padding: 14px 16px; background: #111827; color: #fff; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .operator-chat__title { font-weight: 700; line-height: 1.2; }
  .operator-chat__close { border: 0; background: transparent; color: #fff; font-size: 24px; line-height: 1; }
  .operator-chat__tabs { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid #e5e7eb; }
  .operator-chat__tab { border: 0; background: #fff; color: #334155; min-height: 40px; font-size: 12px; font-weight: 700; }
  .operator-chat__tab.is-active { background: #111827; color: #fff; }
  .operator-chat__list { overflow-y: auto; padding: 8px; flex: 1; }
  .operator-chat__item { width: 100%; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; padding: 10px; margin-bottom: 8px; text-align: left; color: #111827; }
  .operator-chat__item:hover { border-color: #111827; }
  .operator-chat__item-title { display: flex; justify-content: space-between; gap: 8px; font-weight: 700; font-size: 13px; }
  .operator-chat__item-text { margin-top: 4px; color: #64748b; font-size: 12px; line-height: 1.35; }
  .operator-chat__badge { min-width: 18px; height: 18px; border-radius: 999px; background: #dc2626; color: #fff; font-size: 11px; line-height: 18px; text-align: center; }
  .operator-chat__main { display: flex; flex-direction: column; min-width: 0; }
  .operator-chat__conversation-head { padding: 14px 16px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
  .operator-chat__name { font-weight: 700; color: #111827; }
  .operator-chat__sub { color: #64748b; font-size: 12px; margin-top: 2px; }
  .operator-chat__messages { flex: 1; overflow-y: auto; padding: 14px; background: #f8fafc; }
  .operator-chat__message { max-width: 84%; margin-bottom: 10px; }
  .operator-chat__message.is-mine { margin-left: auto; text-align: right; }
  .operator-chat__message.is-system { max-width: 100%; text-align: center; }
  .operator-chat__bubble { display: inline-block; padding: 9px 12px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #111827; font-size: 14px; line-height: 1.45; text-align: left; word-break: break-word; }
  .operator-chat__message.is-mine .operator-chat__bubble { background: #111827; color: #fff; border-color: #111827; }
  .operator-chat__message.is-system .operator-chat__bubble { background: transparent; border: 0; color: #64748b; font-size: 12px; }
  .operator-chat__meta { display: block; margin-top: 3px; font-size: 11px; color: #94a3b8; }
  .operator-chat__form { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; }
  .operator-chat__input { flex: 1; min-width: 0; height: 40px; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 12px; }
  .operator-chat__btn { border: 0; border-radius: 6px; min-height: 38px; padding: 0 13px; background: #111827; color: #fff; font-weight: 600; }
  .operator-chat__btn.secondary { background: #e5e7eb; color: #111827; }
  .operator-chat__btn.danger { background: #dc2626; }
  .operator-chat__actions { display: flex; gap: 8px; }
  .operator-chat__empty { color: #64748b; font-size: 13px; padding: 16px; text-align: center; }
  @media (max-width: 720px) {
    .operator-chat { left: 12px; right: 12px; width: auto; }
    .operator-chat.is-open .operator-chat__panel { grid-template-columns: 1fr; height: calc(100vh - 90px); }
    .operator-chat__sidebar { max-height: 230px; border-right: 0; border-bottom: 1px solid #e5e7eb; }
  }
</style>

<div class="operator-chat" id="operatorChat"
  data-state-url="{{ route('support.staff.state') }}"
  data-open-url="{{ url('/admin/support-chat/__ID__/open') }}"
  data-accept-url="{{ url('/admin/support-chat/__ID__/accept') }}"
  data-decline-url="{{ url('/admin/support-chat/__ID__/decline') }}"
  data-message-url="{{ url('/admin/support-chat/__ID__/message') }}"
  data-close-url="{{ url('/admin/support-chat/__ID__/close') }}"
  data-internal-open-url="{{ url('/admin/internal-chat/__STAFF__') }}"
  data-internal-message-url="{{ url('/admin/internal-chat/__STAFF__') }}">
  <section class="operator-chat__panel">
    <aside class="operator-chat__sidebar">
      <div class="operator-chat__head">
        <div class="operator-chat__title">Trung tâm chat</div>
        <button class="operator-chat__close" data-operator-close type="button">&times;</button>
      </div>
      <div class="operator-chat__tabs">
        <button class="operator-chat__tab is-active" data-operator-tab="requests" type="button">Yêu cầu</button>
        <button class="operator-chat__tab" data-operator-tab="history" type="button">Lịch sử</button>
        <button class="operator-chat__tab" data-operator-tab="internal" type="button">Nội bộ</button>
      </div>
      <div class="operator-chat__list" data-operator-list></div>
    </aside>
    <main class="operator-chat__main">
      <div class="operator-chat__conversation-head">
        <div>
          <div class="operator-chat__name" data-operator-title>Chọn cuộc trò chuyện</div>
          <div class="operator-chat__sub" data-operator-sub>Yêu cầu tư vấn và tin nhắn nội bộ sẽ hiện ở đây.</div>
        </div>
        <div class="operator-chat__actions" data-operator-actions></div>
      </div>
      <div class="operator-chat__messages" data-operator-messages>
        <div class="operator-chat__empty">Chưa chọn cuộc trò chuyện.</div>
      </div>
      <form class="operator-chat__form" data-operator-form>
        <input class="operator-chat__input" data-operator-input type="text" maxlength="1000" placeholder="Nhập tin nhắn..." autocomplete="off">
        <button class="operator-chat__btn" type="submit">Gửi</button>
      </form>
    </main>
  </section>
</div>

<script>
  (function () {
    var root = document.getElementById('operatorChat');
    var bell = document.getElementById('supportNotificationBell');
    var badge = document.getElementById('supportNotificationCount');
    if (!root || !bell) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var listEl = root.querySelector('[data-operator-list]');
    var messagesEl = root.querySelector('[data-operator-messages]');
    var titleEl = root.querySelector('[data-operator-title]');
    var subEl = root.querySelector('[data-operator-sub]');
    var actionsEl = root.querySelector('[data-operator-actions]');
    var inputEl = root.querySelector('[data-operator-input]');
    var state = null;
    var selected = { type: null, id: null, staffId: null, status: null };
    var activeTab = 'requests';
    var lastNotificationCount = 0;

    function escapeHtml(value) {
      return String(value || '').replace(/[&<>"']/g, function (char) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
      });
    }

    function url(template, id, staffId) {
      return template.replace('__ID__', id || '').replace('__STAFF__', staffId || '');
    }

    function getJson(target) {
      return fetch(target, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (response) { return response.json(); });
    }

    function postJson(target, data) {
      return fetch(target, {
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

    function setBadge(count) {
      badge.style.display = count > 0 ? 'block' : 'none';
      badge.textContent = count > 99 ? '99+' : String(count);
      if (count > lastNotificationCount && window.swal) {
        swal('Có thông báo chat mới', 'Bấm biểu tượng chuông để xem yêu cầu hoặc tin nhắn.', 'info');
      }
      lastNotificationCount = count;
    }

    function refresh() {
      getJson(root.dataset.stateUrl).then(function (payload) {
        state = payload;
        setBadge(payload.notification_count || 0);
        renderList();
        if (selected.type === 'support' && payload.conversation && selected.id === payload.conversation.id) {
          renderSupport(payload);
        }
      }).catch(function () {});
    }

    function renderList() {
      if (!state) return;
      root.querySelectorAll('[data-operator-tab]').forEach(function (tab) {
        tab.classList.toggle('is-active', tab.dataset.operatorTab === activeTab);
      });

      var items = [];
      if (activeTab === 'requests') {
        items = state.requests || [];
        if (state.conversation && state.conversation.status === 'active') {
          items = [state.conversation].concat(items.filter(function (item) { return item.id !== state.conversation.id; }));
        }
      } else if (activeTab === 'history') {
        items = state.history || [];
      } else {
        items = state.internal_threads || [];
      }

      if (!items.length) {
        listEl.innerHTML = '<div class="operator-chat__empty">Không có dữ liệu.</div>';
        return;
      }

      listEl.innerHTML = items.map(function (item) {
        if (activeTab === 'internal') {
          return '<button class="operator-chat__item" data-open-internal="' + item.staff_id + '" type="button">' +
            '<span class="operator-chat__item-title"><span>' + escapeHtml(item.other_name) + '</span>' +
            (item.unread_count ? '<span class="operator-chat__badge">' + item.unread_count + '</span>' : '') + '</span>' +
            '<span class="operator-chat__item-text">' + escapeHtml(item.latest_message || 'Chưa có tin nhắn.') + '</span>' +
          '</button>';
        }

        return '<button class="operator-chat__item" data-open-support="' + item.id + '" data-status="' + item.status + '" type="button">' +
          '<span class="operator-chat__item-title"><span>' + escapeHtml(item.customer_name || 'Khách hàng') + '</span>' +
          (item.unread_count ? '<span class="operator-chat__badge">' + item.unread_count + '</span>' : '<span>' + escapeHtml(item.status) + '</span>') + '</span>' +
          '<span class="operator-chat__item-text">Nhân viên: ' + escapeHtml(item.staff_name || 'Chưa phân bổ') + '<br>' + escapeHtml(item.time || '') + '</span>' +
        '</button>';
      }).join('');
    }

    function renderMessages(messages, systemAware) {
      messagesEl.innerHTML = (messages || []).map(function (item) {
        var classes = ['operator-chat__message'];
        if (item.mine) classes.push('is-mine');
        if (systemAware && item.sender_type === 'system') classes.push('is-system');
        return '<div class="' + classes.join(' ') + '">' +
          '<span class="operator-chat__bubble">' + escapeHtml(item.message) + '</span>' +
          ((systemAware && item.sender_type === 'system') ? '' : '<span class="operator-chat__meta">' + escapeHtml(item.sender_name) + ' · ' + escapeHtml(item.time) + '</span>') +
        '</div>';
      }).join('') || '<div class="operator-chat__empty">Chưa có tin nhắn.</div>';
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function renderSupport(payload) {
      var conversation = payload.conversation;
      selected = { type: 'support', id: conversation.id, status: conversation.status };
      titleEl.textContent = 'Khách hàng: ' + (conversation.customer_name || '');
      subEl.textContent = conversation.notice || ('Trạng thái: ' + conversation.status);
      inputEl.disabled = conversation.status !== 'active';
      actionsEl.innerHTML = '';

      if (conversation.status === 'assigned' || conversation.status === 'pending') {
        actionsEl.innerHTML = '<button class="operator-chat__btn" data-action="accept" type="button">Nhận tư vấn</button>' +
          (conversation.status === 'assigned' ? '<button class="operator-chat__btn danger" data-action="decline" type="button">Từ chối</button>' : '');
      } else if (conversation.status === 'active') {
        actionsEl.innerHTML = '<button class="operator-chat__btn secondary" data-action="close" type="button">Kết thúc</button>';
      }

      renderMessages(payload.messages || [], true);
    }

    function openSupport(id) {
      getJson(url(root.dataset.openUrl, id)).then(function (payload) {
        renderSupport(payload);
        refresh();
      });
    }

    function openInternal(staffId) {
      getJson(url(root.dataset.internalOpenUrl, null, staffId)).then(function (payload) {
        selected = { type: 'internal', staffId: staffId };
        titleEl.textContent = payload.thread.other_name || 'Tin nhắn nội bộ';
        subEl.textContent = 'Trao đổi nội bộ';
        inputEl.disabled = false;
        actionsEl.innerHTML = '';
        renderMessages(payload.messages || [], false);
        refresh();
      });
    }

    bell.addEventListener('click', function () {
      root.classList.toggle('is-open');
      refresh();
    });

    root.querySelector('[data-operator-close]').addEventListener('click', function () {
      root.classList.remove('is-open');
    });

    root.querySelectorAll('[data-operator-tab]').forEach(function (tab) {
      tab.addEventListener('click', function () {
        activeTab = tab.dataset.operatorTab;
        renderList();
      });
    });

    listEl.addEventListener('click', function (event) {
      var supportButton = event.target.closest('[data-open-support]');
      var internalButton = event.target.closest('[data-open-internal]');
      if (supportButton) openSupport(supportButton.dataset.openSupport);
      if (internalButton) openInternal(internalButton.dataset.openInternal);
    });

    actionsEl.addEventListener('click', function (event) {
      var action = event.target.dataset.action;
      if (!action || selected.type !== 'support') return;

      if (action === 'accept') {
        postJson(url(root.dataset.acceptUrl, selected.id), {}).then(function (result) { renderSupport(result.json); refresh(); });
      }
      if (action === 'decline') {
        postJson(url(root.dataset.declineUrl, selected.id), {}).then(function () { selected = { type: null }; refresh(); });
      }
      if (action === 'close') {
        postJson(url(root.dataset.closeUrl, selected.id), {}).then(function (result) { renderSupport(result.json); refresh(); });
      }
    });

    root.querySelector('[data-operator-form]').addEventListener('submit', function (event) {
      event.preventDefault();
      var message = inputEl.value.trim();
      if (!message || !selected.type) return;
      inputEl.value = '';

      if (selected.type === 'support') {
        postJson(url(root.dataset.messageUrl, selected.id), { message: message }).then(function (result) {
          if (result.ok) renderSupport(result.json);
        });
      } else {
        postJson(url(root.dataset.internalMessageUrl, null, selected.staffId), { message: message }).then(function (result) {
          if (result.ok) {
            renderMessages(result.json.messages || [], false);
            refresh();
          }
        });
      }
    });

    refresh();
    setInterval(refresh, 3500);
  })();
</script>
@endif
