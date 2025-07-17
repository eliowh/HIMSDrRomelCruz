@php
    $adminName = auth()->user()->name ?? 'Admin';
@endphp
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Approval</title>
        <link rel="stylesheet" href="{{url('css/admin.css')}}">
</head>
<div class="sidebar">
    <div class="logo">HIMS Admin</div>
    <nav>
        <ul>
            <li>
                <a href="{{ url('/admin/home') }}"
                   class="sidebar-btn{{ request()->is('admin/home') ? ' active' : '' }}">
                    <span class="icon">🏠</span> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ url('/admin/userapproval') }}"
                   class="sidebar-btn{{ request()->is('admin/userapproval') ? ' active' : '' }}">
                    <span class="icon">👥</span> User Approval
                </a>
            </li>
        </ul>
        <form action="{{ url('/logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="sidebar-btn"><span class="icon">🚪</span> Log Out</button>
        </form>
    </nav>
</div>