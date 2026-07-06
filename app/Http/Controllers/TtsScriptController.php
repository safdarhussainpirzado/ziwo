<?php

namespace App\Http\Controllers;

use App\Models\TtsScript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TtsScriptController extends Controller
{
    public function index()
    {
        $this->authorize('system.tts_scripts.manage');
        $scripts = TtsScript::orderBy('title')->get();
        return view('admin.settings.tts.index', compact('scripts'));
    }

    public function store(Request $request)
    {
        $this->authorize('system.tts_scripts.manage');
        $validated = $request->validate([
            'title' => 'required|string|max:100|unique:tts_scripts',
            'language' => 'required|string|max:30',
            'content' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav|max:10240',
        ]);

        if ($request->hasFile('audio')) {
            $validated['audio_path'] = $request->file('audio')->store('tts', 'public');
        }
        
        $validated['updated_by'] = auth()->id();
        unset($validated['audio']);

        TtsScript::create($validated);

        return redirect()->back()->with('success', 'TTS script synchronized successfully.');
    }

    public function destroy(TtsScript $ttsScript)
    {
        $this->authorize('system.tts_scripts.manage');
        if ($ttsScript->audio_path) {
            Storage::disk('public')->delete($ttsScript->audio_path);
        }
        $ttsScript->delete();
        return redirect()->back()->with('success', 'TTS script purged from registry.');
    }
}
