@extends('layouts.app')

@section('title', 'Secure 2FA Setup - NHMP 130')

@section('page-title', 'Secure 2FA Setup')

@section('content')


    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[3rem] shadow-2xl border border-slate-100 overflow-hidden relative">
                <div class="absolute -right-20 -top-20 w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-[0.05]"></div>
                
                <div class="bg-gradient-to-r from-navy-900 via-slate-800 to-navy-900 p-12 text-white relative">
                    <h3 class="text-2xl font-black tracking-tight uppercase italic scale-y-110">Protocol Initialization</h3>
                    <p class="text-blue-200 text-[10px] font-black uppercase tracking-[0.3em] mt-2 opacity-60">Synchronize your biometric device with the central command vault</p>
                </div>

                <div class="p-16 grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <!-- QR Code Section -->
                    <div class="flex flex-col items-center text-center space-y-6">
                        <div class="p-4 bg-white rounded-[2rem] shadow-[0_20px_40px_rgba(0,0,0,0.05)] border border-slate-100 inline-block group hover:scale-105 transition-transform duration-500">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrCodeUrl) }}" 
                                 alt="QR Code" 
                                 class="w-48 h-48 rounded-xl">
                        </div>
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Manual Override Key</span>
                            <code class="px-4 py-2 bg-slate-50 rounded-lg font-mono text-sm font-bold text-navy-900 border border-slate-100 inline-block tracking-widest select-all">
                                {{ $secret }}
                            </code>
                        </div>
                    </div>

                    <!-- Instructions Section -->
                    <div class="space-y-8">
                        <div class="flex gap-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex-shrink-0 flex items-center justify-center font-black">1</div>
                            <div>
                                <h4 class="text-sm font-black text-navy-900 uppercase mb-2">Open Authenticator</h4>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed">Launch Google Authenticator, Authy, or Microsoft Authenticator on your mobile terminal.</p>
                            </div>
                        </div>

                        <div class="flex gap-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex-shrink-0 flex items-center justify-center font-black">2</div>
                            <div>
                                <h4 class="text-sm font-black text-navy-900 uppercase mb-2">Scan Matrix</h4>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed">Select 'Add Account' and scan the QR matrix displayed on the left of this console.</p>
                            </div>
                        </div>

                        <div class="flex gap-6">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex-shrink-0 flex items-center justify-center font-black">3</div>
                            <div>
                                <h4 class="text-sm font-black text-navy-900 uppercase mb-2">Finalize Link</h4>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed">Once scanned, navigate back to the dashboard to ensure the protocol is active.</p>
                            </div>
                        </div>

                        <div class="pt-6">
                            <a href="{{ route('dashboard') }}" class="inline-flex px-10 py-4 bg-navy-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl hover:-translate-y-1 transition-all">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
