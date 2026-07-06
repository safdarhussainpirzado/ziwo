<?php

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\SystemSetting::get($key, $default);
    }
}

if (!function_exists('format_duration')) {
    function format_duration(?int $seconds): string
    {
        if (!$seconds) return '--';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        if ($h > 0) return "{$h}h {$m}m";
        if ($m > 0) return "{$m}m {$s}s";
        return "{$s}s";
    }
}

if (!function_exists('call_status_badge')) {
    function call_status_badge(string $status): string
    {
        $status = strtolower($status);
        $badges = [
            'pending' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-slate-100 text-slate-500 border border-slate-200/50 shadow-sm shadow-slate-200/20">Pending</span>',
            'in_progress' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-amber-50 text-amber-600 border border-amber-200/50 shadow-sm shadow-amber-500/10">In Progress</span>',
            'completed' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200/50 shadow-sm shadow-emerald-500/10">Completed</span>',
            'cancelled' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-rose-50 text-rose-600 border border-rose-200/50 shadow-sm shadow-rose-500/10">Cancelled</span>',
            'junk' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-slate-200 text-slate-400 border border-slate-300 shadow-sm italic">Junk</span>',
            'forwarded' => '<span class="px-3 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-full bg-blue-50 text-blue-600 border border-blue-200/50 shadow-sm shadow-blue-500/10">Forwarded</span>',
        ];
        return $badges[$status] ?? "<span class='px-3 py-1 text-[9px] font-black uppercase rounded-full bg-slate-100'>{$status}</span>";
    }
}

if (!function_exists('priority_badge')) {
    function priority_badge(int $priority): string
    {
        $badges = [
            1 => '<span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg bg-rose-600/10 text-rose-600 border border-rose-600/20 shadow-sm shadow-rose-500/10 flex items-center gap-1.5 w-fit"><span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span> P1 — Critical</span>',
            2 => '<span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg bg-amber-500/10 text-amber-600 border border-amber-500/20 shadow-sm shadow-amber-500/10 flex items-center gap-1.5 w-fit"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> P2 — High</span>',
            3 => '<span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg bg-blue-500/10 text-blue-600 border border-blue-500/20 shadow-sm shadow-blue-500/10 flex items-center gap-1.5 w-fit"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> P3 — Medium</span>',
            4 => '<span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 shadow-sm shadow-emerald-500/10 flex items-center gap-1.5 w-fit"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> P4 — Low</span>',
            5 => '<span class="px-2.5 py-1 text-[8px] font-black uppercase tracking-widest rounded-lg bg-slate-400/10 text-slate-500 border border-slate-400/20 shadow-sm flex items-center gap-1.5 w-fit"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> P5 — Junk</span>',
        ];
        return $badges[$priority] ?? "<span class='px-2.5 py-1 text-[8px] font-black uppercase rounded-lg bg-slate-100'>P{$priority}</span>";
    }
}
