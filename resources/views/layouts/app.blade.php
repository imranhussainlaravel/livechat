<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'LiveChat') }}</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
    <!-- Tailwind CSS (temporary CDN for prototyping if Vite isn't setup fully for UI yet, otherwise use Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>

    <style>
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

<body class="bg-gray-50 text-gray-900 font-sans antialiased h-screen flex overflow-hidden">

    <!-- Sidebar Component -->
    <x-sidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Header Component -->
        <x-header />

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 w-full">
            @yield('content')
        </main>
    </div>

    <div id="toast-container"></div>

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
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
                
                let errorMsg = 'Something went wrong. Please try again.';
                if (err.message) errorMsg = err.message;
                if (err.errors) {
                    const firstError = Object.values(err.errors).flat()[0];
                    if (firstError) errorMsg = firstError;
                }
                
                showToast(errorMsg, 'error');
                console.error('AJAX Error:', err);
            });
        });
    </script>
    @stack('scripts')
</body>

</html>