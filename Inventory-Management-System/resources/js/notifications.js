document.addEventListener('DOMContentLoaded', function () {
  const notifCountEl = document.getElementById('notif-count');
  const notifItems = document.getElementById('notif-items');

  function csrf() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
  }

  async function loadNotifs() {
    try {
      const res = await fetch('/notifications', {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
      if (!res.ok) throw res;
      const data = await res.json();
      notifCountEl.textContent = data.unread_count || 0;
      const items = data.notifications || [];
      if (items.length === 0) {
        notifItems.innerHTML = '<div class="text-muted small">No new notifications</div>';
        return;
      }
      notifItems.innerHTML = '';
      items.forEach(n => {
        // n is a Notification model: n.data contains your array
        const payload = n.data || {};
        const li = document.createElement('div');
        li.className = 'dropdown-item d-flex justify-content-between align-items-start';
        const left = document.createElement('div');
        left.innerHTML = `<div><strong>${payload.product_name || payload.title || 'Item'}</strong></div><div class="small text-muted">${payload.message || ''}</div><div class="small text-muted">${new Date(n.created_at).toLocaleString()}</div>`;
        const right = document.createElement('div');
        right.innerHTML = `<button class="btn btn-sm btn-link mark-read" data-id="${n.id}">Mark</button>`;
        li.appendChild(left);
        li.appendChild(right);
        notifItems.appendChild(li);
      });
    } catch (err) {
      console.error(err);
    }
  }

  async function markAsRead(id) {
    try {
      await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      await loadNotifs();
    } catch (err) {
      console.error(err);
    }
  }

  async function markAll() {
    try {
      await fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      });
      await loadNotifs();
    } catch (err) {
      console.error(err);
    }
  }

  document.addEventListener('click', function (e) {
    if (e.target && e.target.matches('.mark-read')) {
      const id = e.target.dataset.id;
      markAsRead(id);
    }
    if (e.target && e.target.id === 'markAllReadBtn') {
      markAll();
    }
  });

  loadNotifs();
  setInterval(loadNotifs, 60000);
});
