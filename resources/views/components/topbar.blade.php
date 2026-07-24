<header class="flex justify-between items-center h-16 px-8 md:px-12 w-full bg-white dark:bg-slate-900 border-b border-outline-variant dark:border-slate-800 z-40 sticky top-0 transition-colors duration-200">
    <div class="flex items-center gap-6">
        <!-- Hamburger Menu for Mobile -->
        <button @click="sidebarOpen = !sidebarOpen" class="p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 md:hidden cursor-pointer">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>

        <h2 class="text-lg font-black text-primary dark:text-orange-400 tracking-tight hidden sm:block">Istana Laundry Samarinda</h2>
        <div class="flex gap-4 items-center">
            <span class="text-xs font-bold text-primary bg-primary-container/10 px-3 py-1 rounded-lg border border-primary/20">
                Branch: 
                @if(session('scoped_branch_id'))
                    {{ \App\Models\Branch::find(session('scoped_branch_id'))?->name ?? 'Unknown Branch' }}
                @elseif(auth()->user()->branch_id)
                    {{ auth()->user()->branch?->name ?? 'Unknown Branch' }}
                @else
                    Semua Cabang (Global)
                @endif
            </span>
            <span class="text-xs text-slate-500 dark:text-slate-400 hover:text-primary cursor-pointer transition-colors font-semibold hidden md:inline-block">Notifications</span>
            <span class="text-xs text-slate-500 dark:text-slate-400 hover:text-primary cursor-pointer transition-colors font-semibold hidden md:inline-block">Support</span>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors" title="Toggle Dark Mode">
            <span class="material-symbols-outlined text-xl" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-xl" x-show="darkMode" x-cloak>light_mode</span>
        </button>

        <!-- Notification bell -->
        <div class="relative group">
            <span class="material-symbols-outlined text-slate-500 dark:text-slate-450 cursor-pointer group-hover:text-primary p-2 transition-all">notifications</span>
            <div class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full"></div>
        </div>

        <!-- Quick Action button -->
        <a href="/pos" class="px-5 py-2 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all active:scale-[0.98] hidden sm:block">
            Quick Action
        </a>

        <!-- User Profile Avatar Image -->
        <div class="w-9 h-9 rounded-full overflow-hidden border border-outline-variant dark:border-slate-850 shrink-0">
            <img class="w-full h-full object-cover" 
                 alt="Profile Picture" 
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9Yo8eZudS1XZIORWPZfki2FC4WzJDnIC-GyJ7h-hOvAeMwQCevFfCprrGYz9o_P748aVm3W0-hXbeTH4YCNNYsTQyIRs2lc-jvfmR6kACE9WFglgCX7-7qnDMnMIYcHGJuIi6JavXqVc4Oj1Sg8xDgHMWNeN7bSooQeYSdLCtTRGE7mXPtWfBk2CX1YpEwiwIMDRNKfRr017XvUUSWyrPN1NXN0_2mxQMmjQ__RgIgccXO3YoXp83qNrf1ZO2sebtlUu8NXWylI4"/>
        </div>
    </div>
</header>
