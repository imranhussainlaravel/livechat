<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Nexon Live Chat</title>
    <link rel="icon" type="image/webp" href="https://images.nexonpackaging.com/logo.webp">
    <script>
        (function () {
            var t = localStorage.getItem('theme') || 'system';
            var dark = t === 'dark' || (t === 'system' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    <link rel="stylesheet" href="/css/app.css?v={{ @filemtime(public_path('css/app.css')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; overflow: hidden; }
        .brand-shadow { box-shadow: 0 10px 30px -10px rgba(240, 100, 75, 0.4); }
    </style>
</head>

<body class="bg-slate-950 min-h-screen flex items-center justify-center p-4 antialiased overflow-hidden">
    <!-- Background Decor -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[40%] h-[40%] bg-[#6366F1]/10 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-500/5 blur-[120px] rounded-full"></div>
    </div>

    <div class="w-full max-w-[380px]">
        {{-- Compact Logo & Title --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-6">
                <img src="https://images.nexonpackaging.com/logo.webp" alt="Nexon" class="h-12 w-auto object-contain">
                <div class="h-10 w-px bg-slate-700"></div>
                <div class="flex flex-col items-start">
                    <span class="text-[14px] font-black text-[#6366F1] uppercase tracking-[0.3em] leading-none mb-1.5">Nexon</span>
                    <span class="text-xl font-bold text-white uppercase tracking-wider leading-none">Live Chat</span>
                </div>
            </div>
        </div>

        {{-- Compact Login Card --}}
        <div class="bg-slate-900/80 backdrop-blur-2xl border border-slate-800 rounded-[2rem] p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-[#6366F1]/50 to-transparent"></div>
            
            @if($errors->any())
            <div class="mb-5 p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-[11px] text-rose-400 font-medium">
                @foreach($errors->all() as $error)
                <p class="flex items-center gap-2">{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#6366F1] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl pl-11 pr-4 py-2 text-xs text-slate-200
                                      placeholder-slate-600 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] transition-all outline-none"
                            placeholder="admin@nexon.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 group-focus-within:text-[#6366F1] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-xl pl-11 pr-4 py-2 text-xs text-slate-200
                                      placeholder-slate-600 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] transition-all outline-none"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-[#6366F1] hover:bg-[#4F46E5] text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 shadow-lg brand-shadow active:scale-[0.98] uppercase tracking-widest text-[10px]">
                    Sign In
                </button>
            </form>
        </div>

        {{-- Integrated Footer --}}
        <div class="text-center mt-6">
            <p class="text-slate-500 text-[9px] font-bold uppercase tracking-[0.2em] mb-1">powered by</p>
            <p class="text-slate-400 text-xs font-bold tracking-wider">nexonpackaging</p>
        </div>
    </div>

</body>

</html>
