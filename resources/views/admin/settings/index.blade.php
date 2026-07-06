@extends('layouts.app')

@section('title', 'System Configuration - NHMP 130')

@section('page-title', 'System Configuration')

@section('content')


    <div class="py-12" x-data="{ activeTab: 'general' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[3rem] shadow-2xl border border-slate-100 overflow-hidden relative">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-[0.05]"></div>
                
                <div class="bg-gradient-to-r from-blue-900 via-slate-800 to-blue-900 p-8 text-white relative flex justify-between items-end">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight uppercase italic scale-y-110">Configuration Gateway</h3>
                        <p class="text-blue-600 text-[10px] font-black uppercase tracking-[0.3em] mt-2 opacity-60">Global Environment & Intelligence Tuning</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-white text-blue-900' : 'bg-white/10 text-white hover:bg-white/20'" class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">General</button>
                        <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-white text-blue-900' : 'bg-white/10 text-white hover:bg-white/20'" class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">Security</button>
                        <button @click="activeTab = 'sla'" :class="activeTab === 'sla' ? 'bg-white text-blue-900' : 'bg-white/10 text-white hover:bg-white/20'" class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">SLA Targets</button>
                    </div>
                </div>

                <div class="p-12">
                    <!-- General Settings -->
                    <div x-show="activeTab === 'general'" x-cloak>
                        <form action="{{ route('admin.settings.update') }}" method="POST" data-no-pjax class="space-y-8">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="group" value="general">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Helpline Official Identifier</label>
                                    <input type="text" name="app_name" value="{{ setting('app_name', 'NHMP 130 CRM') }}" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Broadcast Frequency (Sec)</label>
                                    <input type="number" name="broadcast_interval" value="{{ setting('broadcast_interval', 30) }}" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                </div>
                            </div>

                            <div class="pt-8 border-t border-slate-50 flex justify-end">
                                <button type="submit" class="px-12 py-4 bg-blue-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:-translate-y-1 transition-all">Apply Parameters</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div x-show="activeTab === 'security'" x-cloak>
                        <form action="{{ route('admin.settings.update') }}" method="POST" data-no-pjax class="space-y-8">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="group" value="security">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">2FA Enforcement Level</label>
                                    <select name="2fa_level" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                        <option value="none" {{ setting('2fa_level') === 'none' ? 'selected' : '' }}>None</option>
                                        <option value="supervisor" {{ setting('2fa_level') === 'supervisor' ? 'selected' : '' }}>Supervisors Only</option>
                                        <option value="all" {{ setting('2fa_level') === 'all' ? 'selected' : '' }}>All Active Nodes</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Audit Logging Depth</label>
                                    <select name="audit_depth" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                        <option value="standard" {{ setting('audit_depth') === 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="forensic" {{ setting('audit_depth') === 'forensic' ? 'selected' : '' }}>Full Forensic</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-8 border-t border-slate-50 flex justify-end">
                                <button type="submit" class="px-12 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:-translate-y-1 transition-all">Secure Protocol</button>
                            </div>
                        </form>
                    </div>

                    <!-- SLA Settings -->
                    <div x-show="activeTab === 'sla'" x-cloak>
                        <form action="{{ route('admin.settings.update') }}" method="POST" data-no-pjax class="space-y-8">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="group" value="sla">
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">P1 Resolution (Min)</label>
                                    <input type="number" name="sla_p1" value="{{ setting('sla_p1', 10) }}" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">P2 Resolution (Min)</label>
                                    <input type="number" name="sla_p2" value="{{ setting('sla_p2', 20) }}" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">P3 Resolution (Min)</label>
                                    <input type="number" name="sla_p3" value="{{ setting('sla_p3', 60) }}" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-blue-900">
                                </div>
                            </div>

                            <div class="pt-8 border-t border-slate-50 flex justify-end">
                                <button type="submit" class="px-12 py-4 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:-translate-y-1 transition-all">Update SLA Metrics</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
