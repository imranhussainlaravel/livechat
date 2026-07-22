<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Nexon Live Chat</title>
    <link rel="icon" type="image/webp" href="https://images.nexonpackaging.com/logo.webp">
    {{-- Theme (dark/light/system) — set before paint to avoid a flash --}}
    <script>
        window.applyTheme = function () {
            var t = localStorage.getItem('theme') || 'system';
            var dark = t === 'dark' || (t === 'system' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
            var r = document.documentElement;
            r.setAttribute('data-theme', dark ? 'dark' : 'light');
            r.classList.toggle('dark', dark);
        };
        window.applyTheme();
        try {
            matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                if ((localStorage.getItem('theme') || 'system') === 'system') window.applyTheme();
            });
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="/css/app.css?v={{ @filemtime(public_path('css/app.css')) }}">
    <script src="/js/app.js"></script>

    <!-- DOMContentLoaded compat shim: must load FIRST so per-page scripts work after Turbo navigation -->
    <script>
    (function() {
        var domReady = false;
        document.addEventListener('DOMContentLoaded', function() { domReady = true; });
        var _orig = EventTarget.prototype.addEventListener;
        EventTarget.prototype.addEventListener = function(type, fn, opts) {
            if (this === document && type === 'DOMContentLoaded' && domReady) {
                setTimeout(fn, 0); // fire immediately on Turbo nav
                return;
            }
            return _orig.apply(this, arguments);
        };
    })();
    </script>

    <!-- Turbo: SPA-like navigation (no full reloads) -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-umd.js"></script>

    <!-- Tailwind: precompiled stylesheet (/css/app.css) is loaded above; the in-browser CDN was removed for speed. -->
    <script src="https://unpkg.com/alpinejs" defer></script>

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #6366F1;
            --brand-primary-soft: rgba(99, 102, 241, 0.1);
        }
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Global Toast styles */
        #toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .toast {
            min-width: 200px;
            padding: 0.75rem 1rem;
            background: #1f2937;
            color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(1rem);
            transition: all 0.3s ease;
        }
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        .toast-success { border-left: 4px solid #10b981; }
        .toast-error { border-left: 4px solid #ef4444; }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 font-sans antialiased h-screen flex overflow-hidden relative">
    <!-- Background Decor (Matches Login Page) -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[60%] h-[60%] bg-[#6366F1]/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[60%] h-[60%] bg-indigo-500/5 blur-[150px] rounded-full"></div>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden"></div>

    <!-- Sidebar Component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Header Component -->
        <x-header />

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 w-full">
            @yield('content')
        </main>
    </div>

    <div id="toast-container"></div>
    <div id="direct-alert-permission" class="hidden fixed bottom-6 left-6 z-[9999] max-w-sm rounded-lg border border-indigo-500/30 bg-slate-900/95 p-4 shadow-2xl shadow-black/40">
        <p class="text-sm font-semibold text-slate-100">Live chat alerts are off</p>
        <p class="mt-1 text-xs text-slate-400">Enable browser alerts and sound so new chats notify you directly.</p>
        <button type="button" id="enable-direct-alerts" class="mt-3 inline-flex items-center rounded-md bg-[#6366F1] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#4F46E5]">
            Enable alerts
        </button>
    </div>

    <script>
        // ---- GLOBAL TOAST FEEDBACK ----
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                ${type === 'success' ? '<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' : '<svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'}
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ---- DIRECT LIVE CHAT ALERTS ----
        // NOTE: Turbo re-executes this whole <script> block on every page
        // navigation (it doesn't do a real reload), so plain `var` state
        // here would reset itself on every nav. We cache the live
        // AudioContext on `window` and persist the "unlocked" flag in
        // localStorage so "Enable alerts" only ever needs to be clicked once.
        var alertAudioContext = window._alertAudioContext || null;
        var alertSoundUnlocked = localStorage.getItem('chat_alerts_unlocked') === '1';

        function updateDirectAlertPermissionUI() {
            var panel = document.getElementById('direct-alert-permission');
            if (!panel) return;

            // Keep prompting until alerts are FULLY set up: sound unlocked AND
            // OS notification permission granted (so alerts pop even when you're
            // on another tab / minimized). Exceptions where the button can't help:
            //  - Notifications unsupported by the browser
            //  - permission already 'denied' (browser won't re-prompt; user must
            //    change it in site settings) — don't nag once sound is unlocked.
            var perm = ('Notification' in window) ? Notification.permission : 'granted';
            var fullyReady = alertSoundUnlocked && perm === 'granted';
            var deniedDeadEnd = alertSoundUnlocked && perm === 'denied';
            panel.classList.toggle('hidden', fullyReady || deniedDeadEnd);
        }

        function unlockAlertSound() {
            if (!('AudioContext' in window) && !('webkitAudioContext' in window)) {
                alertSoundUnlocked = true;
                localStorage.setItem('chat_alerts_unlocked', '1');
                updateDirectAlertPermissionUI();
                return Promise.resolve();
            }

            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            alertAudioContext = alertAudioContext || new AudioContextClass();
            window._alertAudioContext = alertAudioContext;

            return alertAudioContext.resume().then(function() {
                alertSoundUnlocked = true;
                localStorage.setItem('chat_alerts_unlocked', '1');
                playAlertSound(true);
                updateDirectAlertPermissionUI();
            }).catch(function() {
                updateDirectAlertPermissionUI();
            });
        }

        function playAlertSound(isTest) {
            if (!alertAudioContext || alertAudioContext.state !== 'running') return;

            var now = alertAudioContext.currentTime;
            var volume = alertAudioContext.createGain();
            volume.gain.setValueAtTime(isTest ? 0.04 : 0.09, now);
            volume.gain.exponentialRampToValueAtTime(0.001, now + 0.75);
            volume.connect(alertAudioContext.destination);

            [880, 1175].forEach(function(frequency, index) {
                var oscillator = alertAudioContext.createOscillator();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(frequency, now + (index * 0.16));
                oscillator.connect(volume);
                oscillator.start(now + (index * 0.16));
                oscillator.stop(now + 0.5 + (index * 0.16));
            });
        }

        function requestDirectAlertPermission() {
            var permissionRequest = Promise.resolve();
            if ('Notification' in window && Notification.permission === 'default') {
                permissionRequest = Notification.requestPermission();
            }

            return permissionRequest.then(function() {
                return unlockAlertSound();
            }).then(function() {
                showToast('Live chat alerts enabled.');
                updateDirectAlertPermissionUI();
            });
        }

        function sendDirectAlert(title, body, url) {
            playAlertSound(false);

            if (!('Notification' in window) || Notification.permission !== 'granted') return;

            var notification = new Notification(title, {
                body: body || 'Open live chat to respond.',
                icon: '/favicon.ico',
                tag: url || 'livechat-alert'
            });

            notification.onclick = function() {
                window.focus();
                if (url) window.location.href = url;
                notification.close();
            };
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('#enable-direct-alerts')) {
                requestDirectAlertPermission();
            }
        });

        document.addEventListener('DOMContentLoaded', updateDirectAlertPermissionUI);
        document.addEventListener('turbo:load', updateDirectAlertPermissionUI);

        // ---- GLOBAL AJAX FORM HANDLER ----
        document.addEventListener('submit', function(e) {
            const form = e.target.closest('[data-ajax-form]');
            if (!form) return;
            
            e.preventDefault();
            
            const url = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            const formData = new FormData(form);
            
            // Handle Laravel @method('PATCH') etc.
            let fetchMethod = method;
            if (formData.has('_method')) {
                fetchMethod = formData.get('_method');
            }

            // Convert FormData to JSON for controllers that expect JSON
            const data = {};
            formData.forEach((value, key) => {
                if (!key.startsWith('_')) data[key] = value;
            });

            const btn = e.submitter || form.querySelector('button[type="submit"]');
            const originalBtnText = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin h-4 w-4 mx-auto" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            }

            fetch(url, {
                method: fetchMethod,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(data)
            })
            .then(async response => {
                const res = await response.json();
                if (!response.ok) {
                    throw res;
                }
                return res;
            })
            .then(res => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
                
                if (res.message) {
                    showToast(res.message);
                }
                
                // Special handling: visitor-note
                if (form.getAttribute('action').includes('visitor-note')) {
                    // We keep text in textarea for single note logic
                }
                
                // If response contains a redirect
                if (res.redirect) {
                    window.location.href = res.redirect;
                } else if (fetchMethod.toUpperCase() === 'DELETE') {
                    // Deletions (e.g. removing a stale pending chat) — drop the row instead of a full reload
                    const row = form.closest('[data-chat-id]');
                    if (row) {
                        row.style.transition = 'opacity 0.25s ease';
                        row.style.opacity = '0';
                        setTimeout(function() { row.remove(); }, 250);
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
                
                let errorMsg = 'Something went wrong. Please try again.';
                if (err.error) errorMsg = err.error;
                else if (err.message) errorMsg = err.message;
                if (err.errors) {
                    const firstError = Object.values(err.errors).flat()[0];
                    if (firstError) errorMsg = firstError;
                }

                showToast(errorMsg, 'error');

                // A queue "Join" that failed means another agent already took the
                // chat — drop the row so this agent's queue stays accurate.
                var failAction = form.getAttribute('action') || '';
                if (failAction.indexOf('/queue/') !== -1 && failAction.indexOf('/join') !== -1) {
                    var takenRow = form.closest('[data-chat-id]');
                    if (takenRow) {
                        takenRow.style.transition = 'opacity 0.25s ease';
                        takenRow.style.opacity = '0';
                        setTimeout(function () { takenRow.remove(); }, 250);
                    }
                }
                console.error('AJAX Error:', err);
            });
        });
        // ---- UNREAD NOTIFICATIONS LOGIC ----
        window.unreadChats = JSON.parse(localStorage.getItem('unreadChats') || '[]');

        function updateUnreadUI() {
            // Always re-read from localStorage so cross-page changes are picked up
            window.unreadChats = JSON.parse(localStorage.getItem('unreadChats') || '[]');
            var count = window.unreadChats.length;

            // My Chats badge (querySelectorAll handles admin + agent sidebar duplicates)
            document.querySelectorAll('#unread-chat-counter').forEach(function(badge) {
                badge.textContent = count > 0 ? count : '';
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.style.transition = 'transform 0.3s ease';
                    badge.style.transform = 'scale(1.3)';
                    setTimeout(function() { badge.style.transform = ''; }, 350);
                } else {
                    badge.classList.add('hidden');
                }
            });

            // Monitor badge
            document.querySelectorAll('#monitor-unread-counter').forEach(function(badge) {
                badge.textContent = count > 0 ? count : '';
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.style.transition = 'transform 0.3s ease';
                    badge.style.transform = 'scale(1.3)';
                    setTimeout(function() { badge.style.transform = ''; }, 350);
                } else {
                    badge.classList.add('hidden');
                }
            });

            // Per-chat unread dots — hide all, then show only unread ones
            document.querySelectorAll('[class*="unread-dot-"]').forEach(function(el) {
                el.classList.add('hidden');
            });
            window.unreadChats.forEach(function(chatId) {
                document.querySelectorAll('.unread-dot-' + chatId).forEach(function(dot) {
                    dot.classList.remove('hidden');
                });
            });
        }

        // ---- REAL-TIME QUEUE COUNT ----
        function updateQueueCount(count) {
            document.querySelectorAll('#sidebar-queue-count').forEach(function(badge) {
                badge.textContent = count > 0 ? count : '';
                if (count > 0) {
                    badge.classList.remove('hidden');
                    badge.style.transition = 'transform 0.3s ease';
                    badge.style.transform = 'scale(1.3)';
                    setTimeout(function() { badge.style.transform = ''; }, 350);
                } else {
                    badge.classList.add('hidden');
                }
            });
        }

        // Auto-clear unread when viewing a specific chat page
        function clearUnreadForCurrentChat() {
            var match = window.location.pathname.match(/\/chats\/(\d+)/);
            if (match) {
                var openChatId = parseInt(match[1], 10);
                var stored = JSON.parse(localStorage.getItem('unreadChats') || '[]');
                var idx = stored.indexOf(openChatId);
                if (idx !== -1) {
                    stored.splice(idx, 1);
                    localStorage.setItem('unreadChats', JSON.stringify(stored));
                }
                if (window.unreadChats) {
                    var memIdx = window.unreadChats.indexOf(openChatId);
                    if (memIdx !== -1) window.unreadChats.splice(memIdx, 1);
                }
            }
        }

        // Call immediately to render any initial unread state
        clearUnreadForCurrentChat();
        updateUnreadUI();

        // Re-sync badges after every Turbo navigation (including cached page restores)
        document.addEventListener('turbo:load', function() { clearUnreadForCurrentChat(); updateUnreadUI(); });
        document.addEventListener('turbo:render', function() { clearUnreadForCurrentChat(); updateUnreadUI(); });

        // ---- FLASH A CHAT ROW (Chats & Monitor pages) ----
        function flashChatRow(chatId) {
            var row = document.querySelector('[data-chat-id="' + chatId + '"]');
            if (!row) return;
            row.style.transition = 'background-color 0.4s ease';
            row.style.backgroundColor = 'rgba(99,102,241,0.15)';
            setTimeout(function() { row.style.backgroundColor = ''; }, 2500);
        }

        // ---- INJECT NEW PENDING CHAT ROW (Pending Queue page) ----
        function injectPendingChatRow(data) {
            var isQueuePage = /\/queue/.test(window.location.pathname);
            if (!isQueuePage) return;

            var list = document.querySelector('.divide-y.divide-gray-800');
            if (!list) return;

            // Remove the "empty queue" state if it's there
            var empty = list.querySelector('.px-6.py-20');
            if (empty) list.innerHTML = '';

            // Avoid duplicate injection
            if (list.querySelector('[data-chat-id="' + data.chat_id + '"]')) return;

            var csrf = (document.querySelector('input[name="_token"]') || {}).value || '{{ csrf_token() }}';
            var initial = (data.visitor_name || 'V').charAt(0).toUpperCase();

            var row = document.createElement('div');
            row.setAttribute('data-chat-id', data.chat_id);
            row.className = 'flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-5 transition relative';
            row.style.backgroundColor = 'rgba(99,102,241,0.15)';
            row.innerHTML =
                '<div class="flex-1 min-w-0 flex items-center gap-4">' +
                    '<div class="w-12 h-12 rounded-full bg-[#6366F1]/20 flex items-center justify-center text-lg font-bold text-[#6366F1] shrink-0">' + initial + '</div>' +
                    '<div>' +
                        '<div class="flex items-center gap-2 mb-1">' +
                            '<p class="text-sm font-semibold text-gray-100">' + (data.visitor_name || 'Visitor') + '</p>' +
                            '<span class="text-xs text-gray-500">#' + data.chat_id + '</span>' +
                            '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 animate-pulse">New</span>' +
                        '</div>' +
                        '<p class="text-sm text-gray-400">Just connected</p>' +
                        '<span class="inline-flex items-center text-xs text-gray-400 mt-1">' +
                            '<svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' +
                            'Just now' +
                        '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="flex items-center gap-3 shrink-0">' +
                    '<form method="POST" action="/agent/queue/' + data.chat_id + '/join" data-ajax-form>' +
                        '<input type="hidden" name="_token" value="' + csrf + '">' +
                        '<button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all active:scale-95">' +
                            '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>' +
                            'Join Conversation' +
                        '</button>' +
                    '</form>' +
                '</div>';

            list.insertBefore(row, list.firstChild);
            // Fade background highlight out after 2 seconds
            setTimeout(function() {
                row.style.transition = 'background-color 0.6s ease';
                row.style.backgroundColor = '';
            }, 2000);
        }

        // ---- SHARED MOBILE DRAWER REGISTRY ----
        // Lets independent drawers (main nav sidebar, chat page's visitor-info
        // panel, ...) close each other so only one is ever open at a time.
        window._drawers = window._drawers || {};
        window.registerDrawer = function(name, closeFn) { window._drawers[name] = closeFn; };
        window.closeOtherDrawers = function(exceptName) {
            Object.keys(window._drawers).forEach(function(name) {
                if (name !== exceptName) window._drawers[name]();
            });
        };

        // ---- MOBILE SIDEBAR DRAWER ----
        function closeSidebar() {
            var sidebar = document.getElementById('app-sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) { sidebar.classList.add('-translate-x-full'); sidebar.classList.remove('translate-x-0'); }
            if (backdrop) backdrop.classList.add('hidden');
        }
        function openSidebar() {
            window.closeOtherDrawers('sidebar');
            var sidebar = document.getElementById('app-sidebar');
            var backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar) { sidebar.classList.remove('-translate-x-full'); sidebar.classList.add('translate-x-0'); }
            if (backdrop) backdrop.classList.remove('hidden');
        }
        window.registerDrawer('sidebar', closeSidebar);
        window.toggleSidebar = function() {
            var sidebar = document.getElementById('app-sidebar');
            if (!sidebar) return;
            if (sidebar.classList.contains('-translate-x-full')) openSidebar(); else closeSidebar();
        };
        document.addEventListener('click', function(e) {
            if (e.target.closest('#sidebar-backdrop') || e.target.closest('#sidebar-close-btn')) closeSidebar();
        });

        // ---- TURBO HOOKS ----
        // Re-initialize Alpine after each Turbo navigation
        document.addEventListener('turbo:load', function() {
            updateUnreadUI();
            closeSidebar(); // always start collapsed on mobile after navigating
            if (window.Alpine) window.Alpine.initTree(document.body);
        });

        // Destroy Chart.js before page is cached to prevent "canvas in use" errors on back navigation
        document.addEventListener('turbo:before-cache', function() {
            if (window.Chart) {
                document.querySelectorAll('canvas').forEach(function(c) {
                    var ch = window.Chart.getChart(c);
                    if (ch) ch.destroy();
                });
            }
        });

        // Clean up per-chat Echo subscriptions when leaving a chat page
        document.addEventListener('turbo:before-render', function() {
            if (window._chatChannelId != null && window.Echo) {
                try { window.Echo.leave('chat.' + window._chatChannelId); } catch(e) {}
                try { window.Echo.leave('chat-room.' + window._chatChannelId); } catch(e) {}
                window._chatChannelId = null;
            }
        });

        // Initialize Pusher Globally if Authenticated
        @if(auth()->check() && (auth()->user()->isAgent() || auth()->user()->isAdmin()))
            const loadScript = (src) => new Promise(resolve => {
                if(document.querySelector(`script[src="${src}"]`)) return resolve();
                const s = document.createElement('script'); s.src = src; s.onload = resolve; document.head.appendChild(s);
            });

            Promise.all([
                loadScript('https://unpkg.com/pusher-js@8.3.0/dist/web/pusher.min.js'),
                loadScript('https://unpkg.com/laravel-echo@1.15.3/dist/echo.iife.js')
            ]).then(() => {
                // CRITICAL: If Echo is already initialized with a working Pusher connection,
                // do NOT disconnect/recreate it. Turbo re-runs this script on every
                // navigation — we must preserve the existing connection + channel subscriptions.
                if (window._globalEchoInitDone) {
                    console.log('[Echo] Already initialized, skipping.');
                    return;
                }

                console.log('[Echo] First-time initialization...');

                // The bundled app.js creates window.Echo with a broken reverb config
                // (key: undefined, wsHost: undefined). We MUST overwrite it.
                var EchoConstructor = null;
                if (window.Echo) {
                    EchoConstructor = window.Echo.constructor;
                    try { window.Echo.disconnect(); } catch(e) {}
                }
                if (!EchoConstructor || EchoConstructor === Object || EchoConstructor === Function) {
                    EchoConstructor = window.Echo;
                }

                window.Echo = new EchoConstructor({
                    broadcaster: 'pusher',
                    key: '54ff5280f5ead0e4ec9f',
                    cluster: 'mt1',
                    forceTLS: true,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                || document.querySelector('input[name="_token"]')?.value
                                || '{{ csrf_token() }}'
                        }
                    }
                });

                console.log('[Echo] Echo created. Pusher OK:', !!window.Echo?.connector?.pusher);

                window._globalEchoInitDone = true;

                var currentUserId = {{ auth()->id() }};
                var isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
                var channelName = isAdmin ? 'admin' : 'agent.' + currentUserId;

                console.log('[Echo] Subscribing — user:', currentUserId, 'isAdmin:', isAdmin, 'channel:', channelName);

                // Personal channel — new visitor messages
                window.Echo.private(channelName)
                    .listen('.message.new', function(e) {
                        console.log('[Echo] .message.new received:', e);
                        if (e.sender_type === 'visitor') {
                            var isCurrentChatPage = window.location.pathname.includes('/chats/' + e.chat_id);
                            if (!isCurrentChatPage) {
                                if (!window.unreadChats.includes(e.chat_id)) {
                                    window.unreadChats.push(e.chat_id);
                                    localStorage.setItem('unreadChats', JSON.stringify(window.unreadChats));
                                }
                                updateUnreadUI();
                                flashChatRow(e.chat_id);
                                showToast('New message from visitor #' + e.chat_id);
                                sendDirectAlert(
                                    'New live chat message',
                                    e.message,
                                    '/agent/chats/' + e.chat_id
                                );
                            }
                        }
                    })
                    .error(function(err) {
                        console.error('[Echo] Channel error on ' + channelName + ':', err);
                    });

                // agents channel — new chat started (pending queue)
                window.Echo.private('agents')
                    .listen('.chat.started', function(e) {
                        console.log('[Echo] .chat.started received:', e);
                        updateQueueCount(e.pending_count);
                        showToast('New chat from ' + (e.visitor_name || 'Visitor'));
                        injectPendingChatRow(e);
                        sendDirectAlert(
                            'New live chat started',
                            (e.visitor_name || 'Visitor') + ' is waiting in the queue.',
                            '/agent/queue'
                        );
                    });

                // agent.queue channel — queue count changed (accept, close, transfer)
                window.Echo.private('agent.queue')
                    .listen('.queue.updated', function(e) {
                        console.log('[Echo] .queue.updated received:', e);
                        updateQueueCount(e.pending_count);
                    });

                console.log('[Echo] All channel subscriptions registered.');
            }).catch(function(err) {
                console.error('[Echo] Failed to load CDN scripts:', err);
            });
        @endif
    </script>

    {{-- Sidebar scroll persistence across Turbo navigations --}}
    <script>
        (function () {
            var SB_KEY = 'sidebarScrollTop';
            function saveSidebarScroll() {
                var nav = document.getElementById('sidebar-nav');
                if (nav) sessionStorage.setItem(SB_KEY, nav.scrollTop);
            }
            function restoreSidebarScroll() {
                var nav = document.getElementById('sidebar-nav');
                if (!nav) return;
                var v = sessionStorage.getItem(SB_KEY);
                if (v !== null) nav.scrollTop = parseInt(v, 10) || 0;
            }
            // scroll doesn't bubble → listen in the capture phase to remember
            // the menu position continuously as the user scrolls it.
            document.addEventListener('scroll', function (e) {
                if (e.target && e.target.id === 'sidebar-nav') {
                    sessionStorage.setItem(SB_KEY, e.target.scrollTop);
                }
            }, true);
            document.addEventListener('turbo:before-cache', saveSidebarScroll);
            document.addEventListener('turbo:before-visit', saveSidebarScroll);
            document.addEventListener('DOMContentLoaded', restoreSidebarScroll);
            document.addEventListener('turbo:load', restoreSidebarScroll);
            document.addEventListener('turbo:render', restoreSidebarScroll);
        })();
    </script>

    {{-- Global team-message (internal DM) notifications — works on any page --}}
    @if(auth()->check() && (auth()->user()->canLiveChat() || auth()->user()->isAdmin()))
    <script>
        (function () {
            var TEAM_URL = @json(route('agent.agents.unreadSummary'));
            var LAST_KEY = 'lastTeamMsgId';

            function setBadge(el, count) {
                if (!el) return;
                el.textContent = count > 0 ? count : '';
                el.classList.toggle('hidden', !(count > 0));
            }

            function pollTeamMessages() {
                fetch(TEAM_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) return;
                        setBadge(document.getElementById('team-unread-badge'), data.count);
                        setBadge(document.getElementById('header-unread-badge'), data.count);
                        if (!data.latest) return;

                        var lastSeen = parseInt(localStorage.getItem(LAST_KEY) || '0', 10);
                        // First run in this browser → set a baseline silently so we
                        // don't alert for messages already sitting unread.
                        if (!lastSeen) { localStorage.setItem(LAST_KEY, data.latest.id); return; }

                        if (data.latest.id > lastSeen) {
                            localStorage.setItem(LAST_KEY, data.latest.id);
                            // Don't double-toast while already on the Team Chat page.
                            if (!/\/other-agents/.test(window.location.pathname)) {
                                if (window.showToast) showToast('New message from ' + data.latest.sender_name);
                                if (window.sendDirectAlert) {
                                    sendDirectAlert('Team message from ' + data.latest.sender_name, data.latest.preview, data.latest.url);
                                }
                            }
                        }
                    })
                    .catch(function () {});
            }

            // Guard so Turbo's per-navigation re-run doesn't stack intervals.
            if (!window._teamPollStarted) {
                window._teamPollStarted = true;
                pollTeamMessages();
                setInterval(pollTeamMessages, 12000);
            }
        })();
    </script>

    {{-- Live-chat notifications (poll-based) — new pending chats + new messages in my chats --}}
    <script>
        (function () {
            var LC_URL = @json(route('agent.alerts.poll'));
            var Q_KEY = 'lastQueueChatId';
            var M_KEY = 'lastMyMsgId';

            // When Reverb (Echo) is connected it delivers alerts in real-time,
            // so the poll should NOT also fire them (avoid double notifications).
            // It still keeps badges + queue rows in sync as a safety net.
            function echoLive() {
                try {
                    return !!(window.Echo && window.Echo.connector && window.Echo.connector.pusher
                        && window.Echo.connector.pusher.connection
                        && window.Echo.connector.pusher.connection.state === 'connected');
                } catch (e) { return false; }
            }

            function pollLiveChat() {
                fetch(LC_URL, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) return;

                        // Keep the sidebar queue badge in sync (reuses the layout helper).
                        if (typeof updateQueueCount === 'function') updateQueueCount(data.pending_count);

                        // On the Pending Queue page, drop rows for chats another
                        // agent has already joined (or that were removed/closed).
                        if (Array.isArray(data.pending_ids) && /\/queue/.test(window.location.pathname)) {
                            var stillPending = {};
                            data.pending_ids.forEach(function (id) { stillPending[id] = true; });
                            document.querySelectorAll('[data-chat-id]').forEach(function (row) {
                                var rid = parseInt(row.getAttribute('data-chat-id'), 10);
                                if (rid && !stillPending[rid]) {
                                    row.style.transition = 'opacity 0.25s ease';
                                    row.style.opacity = '0';
                                    setTimeout(function () { row.remove(); }, 250);
                                }
                            });
                        }

                        // New chat waiting in the queue
                        if (data.latest_pending) {
                            var lastQ = parseInt(localStorage.getItem(Q_KEY) || '0', 10);
                            if (!lastQ) {
                                localStorage.setItem(Q_KEY, data.latest_pending.id);
                            } else if (data.latest_pending.id > lastQ) {
                                localStorage.setItem(Q_KEY, data.latest_pending.id);
                                if (!echoLive()) {
                                    if (window.showToast) showToast('New chat from ' + data.latest_pending.visitor_name);
                                    if (window.sendDirectAlert) sendDirectAlert('New live chat', data.latest_pending.visitor_name + ' is waiting in the queue.', '/agent/queue');
                                }
                            }
                        }

                        // New visitor message in one of my active chats
                        if (data.latest_message) {
                            var lastM = parseInt(localStorage.getItem(M_KEY) || '0', 10);
                            if (!lastM) {
                                localStorage.setItem(M_KEY, data.latest_message.id);
                            } else if (data.latest_message.id > lastM) {
                                localStorage.setItem(M_KEY, data.latest_message.id);
                                var cid = data.latest_message.chat_id;
                                var onThatChat = new RegExp('/chats/' + cid + '(?:\\D|$)').test(window.location.pathname);
                                if (!onThatChat && !echoLive()) {
                                    // Reuse the unread-badge system so "My Chats" updates too.
                                    try {
                                        window.unreadChats = JSON.parse(localStorage.getItem('unreadChats') || '[]');
                                        if (window.unreadChats.indexOf(cid) === -1) {
                                            window.unreadChats.push(cid);
                                            localStorage.setItem('unreadChats', JSON.stringify(window.unreadChats));
                                        }
                                        if (typeof updateUnreadUI === 'function') updateUnreadUI();
                                    } catch (e) {}
                                    if (window.showToast) showToast('New message from ' + data.latest_message.visitor_name);
                                    if (window.sendDirectAlert) sendDirectAlert('New message from ' + data.latest_message.visitor_name, data.latest_message.preview, '/agent/chats/' + cid);
                                }
                            }
                        }
                    })
                    .catch(function () {});
            }

            if (!window._liveChatPollStarted) {
                window._liveChatPollStarted = true;
                pollLiveChat();
                setInterval(pollLiveChat, 10000);
            }
        })();
    </script>
    @endif

    {{-- Flash feedback for standard (non-AJAX) form redirects, e.g. CRM screens. Reuses showToast(). --}}
    @if(session('success') || session('error') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.showToast) return;
            @if(session('success')) showToast(@json(session('success')), 'success'); @endif
            @if(session('error')) showToast(@json(session('error')), 'error'); @endif
            @if($errors->any()) showToast(@json($errors->first()), 'error'); @endif
        });
    </script>
    @endif
    @stack('scripts')
</body>

</html>
