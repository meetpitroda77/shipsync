@php
  $role = Auth::user()->role;
@endphp
<style>
  .notification-message {
    white-space: normal;
    word-wrap: break-word;
    word-break: break-word;
    max-width: 100%;
  }
</style>
<nav class="app-header navbar navbar-expand bg-body">
  <div class="container-fluid">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>
      </li>

    </ul>

    <ul class="navbar-nav ms-auto me-5">

      @if (in_array($role, ["agent", "staff", "admin"]))
        <li class="nav-item dropdown">
          <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <i class="bi bi-bell-fill"></i>

            <span class="navbar-badge badge text-bg-warning" id="notification-count">
              {{ auth()->user()->unreadNotifications->count() }}
            </span>
          </a>

          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end text-center" id="notification-list">

            <span class="dropdown-item dropdown-header" id="notification-header">
              {{ auth()->user()->unreadNotifications->count() }} Notifications
            </span>

            <div class="dropdown-divider"></div>

            @if(auth()->user()->unreadNotifications->count() > 0)

              @foreach(auth()->user()->unreadNotifications as $notification)
                <a href="javascript:void(0);" class="dropdown-item"
                  onclick="markAsReadAndRedirect('{{ $notification->id }}', '{{ $notification->data['tracking_id'] }}')">

                  <span class="notification-message">
                    {{ $notification->data['message'] }}
                  </span>

                </a>

                <div class="dropdown-divider"></div>
              @endforeach

            @else

              <div id="no-notification" class="p-3 text-center">


                <p class="text-muted mb-0">No new notifications</p>
              </div>

            @endif

          </div>
        </li>
      @endif


      <li class="nav-item">
        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
          <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
          <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
        </a>
      </li>








      <li class="nav-item dropdown">



        <a class="nav-link" data-bs-toggle="dropdown" href="#">
          <i class="bi bi-person-circle"></i>
        </a>

        <div class="dropdown-menu dropdown-menu-end">

          <span class="dropdown-item-text">{{ $role }}</span>

          <div class="dropdown-divider"></div>

          <a href="{{ route("{$role}.profile.editProfile", ['user' => Auth::id()]) }}" class="dropdown-item">
            <i class="fas fa-user me-2"></i> Profile
          </a>

          <form action="{{ route("{$role}.logout") }}" method="post">
            @csrf
            <button type="submit" class="dropdown-item">
              <i class="fas fa-sign-out-alt me-2"></i> Logout
            </button>
          </form>


        </div>

      </li>





    </ul>
  </div>
</nav>

{{-- @if (in_array($role, ["agent", "staff", "admin"]))
<script>
  function markAsReadAndRedirect(notificationId, trackingId) {
    let role = "{{ $role }}";

    $.ajax({
      url: '/{{ $role }}/notifications/' + notificationId + '/read',
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
      },
      success: function (response) {
        let notificationCount = parseInt($('#notification-count').text());
        if (notificationCount > 0) {
          $('#notification-count').text(notificationCount - 1);
        }

        window.location.href =
          "/" + role + "/shipments?search=" + trackingId;
      },
      error: function (error) {
        console.error('Error marking notification as read:', error);
      }
    });
  }
</script>
@endif --}}