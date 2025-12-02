<div id="notification-dropdown" class="dropdown">
  <button id="notifToggle" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
    Notifications <span id="notif-count" class="badge bg-danger">0</span>
  </button>
  <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:320px;">
    <li class="d-flex justify-content-between align-items-center mb-1">
      <strong>Notifications</strong>
      <button id="markAllReadBtn" class="btn btn-sm btn-link">Mark all read</button>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li id="notif-items">
      <div class="text-muted small">Loading…</div>
    </li>
  </ul>
</div>

<script src="{{ mix('js/notifications.js') }}"></script>
