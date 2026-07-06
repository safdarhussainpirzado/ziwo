@extends('layouts.app')

@section('title', 'Authorized User Profile - NHMP 130')

@section('page-title', 'Authorized User Profile')

@section('content')
<div x-data="profileManager(@js($user))" class="py-12" x-cloak>
    <div class="max-w-[1700px] mx-auto sm:px-6 lg:px-8 space-y-10">
        
        <!-- Header Info -->
        <div class="bg-gradient-to-br from-blue-900 to-indigo-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-center gap-6">
                    <div class="w-24 h-24 rounded-[2rem] bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-4xl font-black shadow-inner">
                        {{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-3xl font-black tracking-tight uppercase">{{ $user->full_name ?? 'System Operative' }}</h2>
                        <p class="text-blue-200 font-bold uppercase tracking-[0.2em] text-xs mt-1">{{ $user->getRoleTitle() }}</p>
                        <div class="flex items-center gap-4 mt-4">
                            <span class="px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-[10px] font-black uppercase tracking-widest text-emerald-300">Identity Verified</span>
                            <span class="text-white/40 text-[10px] font-bold uppercase tracking-widest">UID: #{{ $user->id }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    @can('profile.password_change')
                        <button @click="showPasswordModal = true" class="px-6 py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all backdrop-blur-sm flex items-center gap-3">
                            <i class="fa-solid fa-key"></i> Rotate Security Key
                        </button>
                    @endcan

                    @can('profile.self_manage')
                        <button @click="editMode = !editMode" :class="editMode ? 'bg-amber-500 border-amber-600' : 'bg-blue-600 border-blue-700'" class="px-8 py-4 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl transition-all border flex items-center gap-3">
                            <i class="fa-solid" :class="editMode ? 'fa-times' : 'fa-sliders'"></i>
                            <span x-text="editMode ? 'Abort Update' : 'Update Parameters'"></span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-10">
            <!-- Profile Info -->
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-10 overflow-hidden relative group">
                <div class="absolute -right-10 -top-10 opacity-5 group-hover:scale-110 transition-transform duration-700">
                    <i class="fa-solid fa-user-shield text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shadow-sm">
                                <i class="fa-solid fa-fingerprint text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-extrabold text-navy-900 uppercase">Operational Identity</h3>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System registration parameters</p>
                            </div>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <div x-show="!editMode" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Operational Name</label>
                                <p class="text-sm font-black text-navy-900" x-text="user.full_name"></p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Identity Handle</label>
                                <p class="text-sm font-black text-blue-600" x-text="user.username"></p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Electronic Mail</label>
                                <p class="text-sm font-black text-navy-900" x-text="user.email"></p>
                            </div>
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-1">Mobile Access</label>
                                <p class="text-sm font-black text-navy-900" x-text="user.mobile_no || 'N/A'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div x-show="editMode" x-transition>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <!-- Access Topology -->
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-10 overflow-hidden relative group">
                <div class="absolute -right-10 -top-10 opacity-5 text-indigo-500 group-hover:scale-110 transition-transform duration-700">
                    <i class="fa-solid fa-network-wired text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 shadow-sm">
                            <i class="fa-solid fa-shield-halved text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-navy-900 uppercase">Access Topology</h3>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active permission scope</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Designation</span>
                            <span class="text-xs font-black text-navy-900">{{ $user->designation?->name ?? 'Personnel' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">National CNIC</span>
                            <span class="text-xs font-black text-navy-900">{{ $user->cnic ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Assigned Scopes</span>
                            <span class="text-xs font-black text-blue-600">{{ $user->activeScopes->count() }} Active Units</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Rotation Modal -->
    <template x-if="showPasswordModal">
        <div class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6">
            <div class="flex items-center justify-center min-h-screen">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showPasswordModal = false"></div>
                
                <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md z-[110] overflow-hidden relative border border-slate-100" x-transition.scale>
                    <div class="bg-gradient-to-br from-rose-600 to-rose-700 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center border border-white/20">
                                    <i class="fa-solid fa-key text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold uppercase">Key Rotation</h3>
                                    <p class="text-white/70 text-[9px] font-black uppercase tracking-widest">Update authorization keys</p>
                                </div>
                            </div>
                            <button @click="showPasswordModal = false" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function profileManager(user) {
    return {
        user: user,
        editMode: false,
        showPasswordModal: false,
        init() {
            // Check for success session from PHP
            if ("{{ session('status') }}" === 'password-updated') {
                showSuccess("Security Key Rotated Successfully");
            }
        }
    }
}
</script>
@endsection
