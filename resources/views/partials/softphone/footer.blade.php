{{--
    Softphone footer. Tiny status bar — connection indicator + gateway label.
--}}
<footer
    x-show="phoneAuthenticated"
    class="shrink-0 h-7 px-3 border-t border-slate-800 bg-[#0B1220] flex items-center justify-between text-[8px] uppercase tracking-wider font-bold text-slate-500"
>
    <div class="flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>Connected · nayatel</span>
    </div>
    <span class="text-slate-600">v2.0</span>
</footer>
