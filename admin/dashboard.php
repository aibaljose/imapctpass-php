<?php
// Admin SPA shell (loads fragments into #admin-main)
include_once '../api/apimethods.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// We'll use fragments for content; dashboard shell keeps minimal PHP
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex h-screen">
    <!-- Custom Admin Top Nav -->
    <div class="fixed top-0 left-0 right-0 bg-white shadow z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded hover:bg-gray-100" aria-label="Toggle menu">
                    <!-- menu icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="text-2xl font-extrabold text-blue-700">ImpactPass Admin</div>
                <nav class="hidden md:flex space-x-2 ml-6">
                    <a href="dashboard.php" data-nav="dashboard" class="py-2 px-3 rounded hover:bg-gray-100 text-gray-700">Overview</a>
                    <a href="manage_events.php" data-nav="events" class="py-2 px-3 rounded hover:bg-gray-100 text-gray-700">Events</a>
                    <a href="manage_bookings.php" data-nav="bookings" class="py-2 px-3 rounded hover:bg-gray-100 text-gray-700">Bookings</a>
                    <a href="manage_users.php" data-nav="users" class="py-2 px-3 rounded hover:bg-gray-100 text-gray-700">Users</a>
                </nav>
            </div>
            <div class="flex items-center space-x-3">
                <a href="../index.php" class="hidden sm:inline-flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 20l-6-6 1.5-1.5L10 17l4.5-4.5L16 14l-6 6z"/></svg>
                    <span>View site</span>
                </a>
                <a href="../logout.php" class="inline-flex items-center px-3 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 text-sm">Logout</a>
            </div>
        </div>
    </div>

    <!-- Sidebar for wide screens -->
    <aside id="admin-sidebar" class="w-64 bg-white shadow hidden md:block pt-16 fixed inset-y-0 left-0 transform transition-transform duration-200">
        <div class="p-4">
            <div class="text-sm text-gray-500 mb-4">Menu</div>
            <nav class="space-y-2">
                <a href="dashboard.php" data-nav="dashboard" class="flex items-center gap-3 py-2 px-3 rounded hover:bg-gray-100 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3h4v4H3V3zM3 13h4v4H3v-4zM13 3h4v4h-4V3zM13 13h4v4h-4v-4z"/></svg><span>Overview</span></a>
                <a href="manage_events.php" data-nav="events" class="flex items-center gap-3 py-2 px-3 rounded hover:bg-gray-100 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/></svg><span>Events</span></a>
                <a href="manage_bookings.php" data-nav="bookings" class="flex items-center gap-3 py-2 px-3 rounded hover:bg-gray-100 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg><span>Bookings</span></a>
                <a href="manage_users.php" data-nav="users" class="flex items-center gap-3 py-2 px-3 rounded hover:bg-gray-100 text-gray-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor"><path d="M10 10a4 4 0 100-8 4 4 0 000 8zM2 18a8 8 0 0116 0H2z"/></svg><span>Users</span></a>
            </nav>
        </div>
    </aside>

    <!-- Main area -->
    <div class="flex-1 md:ml-64 pt-16 overflow-auto">
        <div id="admin-main" class="p-6 max-w-7xl mx-auto">
            <!-- content loaded via JS -->
            <div class="text-center py-20 text-gray-500">Loading...</div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="admin-loading" class="fixed inset-0 bg-black bg-opacity-30 hidden items-center justify-center z-50">
        <div class="bg-white p-4 rounded shadow flex items-center gap-3">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z"></path></svg>
            <div>Loading...</div>
        </div>
    </div>

    <!-- Confirm modal -->
    <div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-60">
        <div class="bg-white rounded shadow-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-2">Please confirm</h3>
            <p id="confirmText" class="text-gray-600 mb-4"></p>
            <div class="flex justify-end gap-3">
                <button id="confirmCancel" class="px-4 py-2 rounded border">Cancel</button>
                <button id="confirmOk" class="px-4 py-2 rounded bg-red-600 text-white">Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="admin-toast" class="fixed bottom-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow hidden"></div>
</div>

<script>
// SPA loader: fetch fragment pages and inject into #admin-main
async function loadFragment(url, push=true) {
    const fragUrl = url.includes('?') ? url + '&fragment=1' : url + '?fragment=1';
    showLoading(true);
    try {
        const res = await fetch(fragUrl, { credentials: 'same-origin' });
        const html = await res.text();
        // fade transition
        const container = document.getElementById('admin-main');
        container.style.opacity = 0;
        setTimeout(() => { container.innerHTML = html; container.style.opacity = 1; }, 150);
    } finally {
        showLoading(false);
    }
    if (push) history.pushState({url: url}, '', url);
    // bind fragment links and ajax forms
    bindFragmentLinks();
    bindAjaxForms();
}

function bindFragmentLinks(){
    document.querySelectorAll('.fragment-link').forEach(a => {
        a.addEventListener('click', function(e){
            e.preventDefault();
            const confirmText = this.getAttribute('data-confirm');
            if (confirmText) {
                showConfirm(confirmText, () => loadFragment(this.getAttribute('href')));
            } else {
                loadFragment(this.getAttribute('href'));
            }
        });
    });
    // nav links
    document.querySelectorAll('[data-nav]').forEach(a => {
        a.addEventListener('click', function(e){
            e.preventDefault();
            loadFragment(this.getAttribute('href'));
        });
    });
}

function bindAjaxForms(){
    document.querySelectorAll('form[data-ajax="true"]').forEach(form => {
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            const fd = new FormData(this);
            const action = this.getAttribute('action') || window.location.pathname;
            showLoading(true);
            try {
                const res = await fetch(action, { method: 'POST', body: fd, credentials: 'same-origin' });
                const html = await res.text();
                document.getElementById('admin-main').innerHTML = html;
                bindFragmentLinks(); bindAjaxForms();
            } finally { showLoading(false); }
        });
    });
}

window.addEventListener('popstate', function(e){
    if (e.state && e.state.url) loadFragment(e.state.url, false);
});

// initial load: dashboard
document.addEventListener('DOMContentLoaded', function(){
    const start = window.location.pathname.endsWith('dashboard.php') || window.location.pathname.endsWith('/admin/') ? 'dashboard.php' : window.location.pathname.split('/').pop();
    loadFragment(start, false);
});
</script>

<script>
// UI helpers: loading overlay, confirm modal, side toggle, toasts
function showLoading(show){
    const el = document.getElementById('admin-loading');
    if (!el) return;
    el.classList.toggle('hidden', !show);
}

function showToast(msg, timeout=3000){
    const t = document.getElementById('admin-toast');
    t.textContent = msg; t.classList.remove('hidden');
    setTimeout(()=> t.classList.add('hidden'), timeout);
}

function showConfirm(text, cb){
    const modal = document.getElementById('confirmModal');
    document.getElementById('confirmText').textContent = text;
    modal.classList.remove('hidden'); modal.classList.add('flex');
    const ok = document.getElementById('confirmOk');
    const cancel = document.getElementById('confirmCancel');
    const cleanup = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); ok.onclick = null; cancel.onclick=null; };
    ok.onclick = () => { cleanup(); cb(); showToast('Deleted'); };
    cancel.onclick = () => { cleanup(); };
}

// mobile menu toggle
document.getElementById('mobileMenuBtn').addEventListener('click', function(){
    const sb = document.getElementById('admin-sidebar');
    sb.classList.toggle('-translate-x-full');
});

</script>

</body>
</html>
</body>
</html>