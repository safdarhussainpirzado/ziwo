@extends('layouts.app')

@section('title', 'Audio Announcement Registry - NHMP 130')

@section('page-title', 'Audio Announcement Registry')

@section('content')


    <div class="py-12" x-data="{ playing: null }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Script Cards -->
                @forelse($scripts as $script)
                <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-100 p-8 hover:border-blue-500 transition-all group flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-bullhorn text-xl"></i>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest rounded-lg border border-slate-200">{{ $script->language }}</span>
                        </div>
                        <h3 class="text-xl font-extrabold text-navy-900 mb-2 truncate" title="{{ $script->title }}">{{ $script->title }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed line-clamp-3 mb-6 font-medium italic">"{{ $script->content }}"</p>
                    </div>

                    <div class="pt-6 border-t border-slate-50 flex items-center justify-between">
                        @if($script->audio_path)
                        <button @click="if(playing === '{{ $script->id }}') { $refs.audio{{ $script->id }}.pause(); playing = null; } else { if(playing) { document.getElementById('audio' + playing).pause(); } $refs.audio{{ $script->id }}.play(); playing = '{{ $script->id }}'; }" 
                                class="flex items-center gap-2 text-blue-600 font-black text-[10px] uppercase tracking-widest hover:text-blue-700 transition-colors">
                            <i class="fa-solid" :class="playing === '{{ $script->id }}' ? 'fa-pause' : 'fa-play'"></i>
                            <span x-text="playing === '{{ $script->id }}' ? 'Interrupting' : 'Audition'"></span>
                            <audio x-ref="audio{{ $script->id }}" id="audio{{ $script->id }}" @ended="playing = null" src="{{ Storage::url($script->audio_path) }}" class="hidden"></audio>
                        </button>
                        @else
                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest italic">Digital Only</span>
                        @endif

                        <form action="{{ route('admin.tts-scripts.destroy', $script) }}" method="POST" data-no-pjax onsubmit="return confirm('Purge script from sovereign registry?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-slate-300 hover:text-rose-500 transition-colors">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-32 text-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-6">
                        <i class="fa-solid fa-microphone-slash text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-400 uppercase tracking-widest">No Scripts Found</h3>
                    <p class="text-slate-400 font-bold mt-2">Initialize the registry to enable automated announcements.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal for adding script -->
    <x-modal name="add-tts-script" :show="false">
        <form action="{{ route('admin.tts-scripts.store') }}" method="POST" enctype="multipart/form-data" data-no-pjax class="p-10">
            @csrf
            <h3 class="text-2xl font-black text-navy-900 mb-8 uppercase tracking-tight italic scale-y-110">Registry Input</h3>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Script Title</label>
                    <input type="text" name="title" required class="w-full bg-slate-50 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-navy-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all" placeholder="e.g., Road Closure Alert - Swat">
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Language</label>
                        <select name="language" required class="w-full bg-slate-50 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-navy-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all">
                            <option value="Urdu">Urdu</option>
                            <option value="English">English</option>
                            <option value="Pashto">Pashto</option>
                            <option value="Punjabi">Punjabi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Audio Attachment</label>
                        <input type="file" name="audio" class="w-full bg-slate-50 border-slate-100 rounded-2xl px-5 py-3 text-xs font-bold text-navy-900 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[9px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Announcement Content (Synthesis String)</label>
                    <textarea name="content" required rows="4" class="w-full bg-slate-50 border-slate-100 rounded-2xl px-5 py-4 text-sm font-bold text-navy-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all leading-relaxed" placeholder="Type the announcement here..."></textarea>
                </div>
            </div>

            <div class="mt-10 flex justify-end gap-4">
                <button type="button" x-on:click="$dispatch('close')" class="px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all">Abort</button>
                <button type="submit" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-600/20">Commit to Registry</button>
            </div>
        </form>
    </x-modal>
@endsection
