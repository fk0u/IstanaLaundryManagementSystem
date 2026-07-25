<header class="flex justify-between items-center h-14 md:h-16 px-4 md:px-6 lg:px-8 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-b border-outline-variant dark:border-slate-800 z-40 sticky top-0 transition-colors duration-200">
    <div class="flex items-center gap-2 md:gap-4 min-w-0">
        <!-- Hamburger Menu for Mobile -->
        <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 md:hidden cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>

        <!-- Sidebar Toggle Icon for Desktop/Tablet -->
        <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)" 
                class="hidden md:flex p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 cursor-pointer rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shrink-0 items-center justify-center"
                :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Perluas Sidebar'">
            <span class="material-symbols-outlined text-[22px] transition-transform duration-300"
                  :class="{ 'rotate-180': !desktopSidebarOpen }">
                menu_open
            </span>
        </button>

        <!-- Brand Name - Desktop -->
        <h2 class="text-base md:text-lg font-black text-primary dark:text-orange-400 tracking-tight hidden md:block whitespace-nowrap">Istana Laundry Samarinda</h2>
        
        <!-- Mobile Brand -->
        <h2 class="text-sm font-extrabold text-primary dark:text-orange-400 tracking-tight md:hidden truncate">Istana Laundry</h2>

        <div class="flex gap-2 md:gap-4 items-center shrink-0">
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <form action="{{ route('switch-branch') }}" method="POST" class="inline-flex items-center">
                    @csrf
                    <label class="text-2xs font-bold text-slate-400 dark:text-slate-500 mr-1.5 uppercase tracking-wider hidden lg:inline">Scope:</label>
                    <select name="branch_id" onchange="this.form.submit()"
                            class="bg-slate-50 dark:bg-slate-950 border border-outline-variant dark:border-slate-800 rounded-lg text-2xs md:text-[10px] font-bold px-2 py-1.5 md:py-1 outline-none focus:border-primary text-slate-700 dark:text-slate-300 cursor-pointer max-w-[120px] md:max-w-none">
                        <option value="">Global</option>
                        @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                            <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>
                                {{ $br->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <span class="text-2xs md:text-[10px] font-bold text-primary bg-primary-container/10 px-2 py-1 rounded-lg border border-primary/20 whitespace-nowrap">
                    {{ auth()->user()->branch?->name ?? 'N/A' }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 md:gap-3 shrink-0">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors" title="Toggle Dark Mode">
            <span class="material-symbols-outlined text-xl" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-xl" x-show="darkMode" x-cloak>light_mode</span>
        </button>

        <!-- Notification bell -->
        <div class="relative group">
            <button class="p-2 text-slate-500 dark:text-slate-400 hover:text-primary rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-xl">notifications</span>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-primary rounded-full"></span>
            </button>
        </div>

        <!-- Quick Action button - Desktop only -->
        <a href="/pos" class="px-3.5 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-[0.98] hidden lg:flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base text-primary">add_circle</span>
            Quick Action
        </a>

        <!-- User Profile Avatar Image -->
        <div class="w-8 h-8 md:w-9 md:h-9 rounded-full overflow-hidden border border-outline-variant dark:border-slate-800 shrink-0">
            <img class="w-full h-full object-cover" 
                 alt="Profile Picture" 
                 src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9Yo8eZudS1XZIORWPZfki2FC4WzJDnIC-GyJ7h-hOvAeMwQCevFfCprrGYz9o_P748aVm3W0-hXbeTH4YCNNYsTQyIRs2lc-jvfmR6kACE9WFglgCX7-7qnDMnMIYcHGJuIi6JavXqVc4Oj1Sg8xDgHMWNeN7bSooQeYSdLCtTRGE7mXPtWfBk2CX1YpEwiwIMDRNKfRr017XvUUSWyrPN1NXN0_2mxQMmjQ__RgIgccXO3YoXp83qNrf1ZO2sebtlUu8NXWylI4"/>
        </div>
    </div>
</header>
