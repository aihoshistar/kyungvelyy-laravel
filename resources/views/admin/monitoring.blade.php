@extends('layouts.admin')

@section('title', 'SYSTEM MONITORING')
@section('header_title', 'SYSTEM MONITORING')

@section('styles')
<style>
    .terminal-scroll::-webkit-scrollbar { width: 4px; }
    .terminal-scroll::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>
@endsection

@section('content')
<div class="flex-grow overflow-y-auto p-4 md:p-10 custom-scrollbar bg-[#050505]">
    <div class="max-w-6xl mx-auto pb-10">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-white text-xl font-bold tracking-tighter flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></span>
                    Real-time Application Logs
                </h2>
            </div>
            <div class="flex gap-3">
                <button onclick="window.location.reload()" class="px-4 py-2 bg-[#111] border border-white/10 rounded-lg text-xs font-medium hover:bg-white/5 transition text-white tracking-widest">
                    REFRESH LOGS
                </button>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="p-6 rounded-2xl bg-[#111] border border-white/10">
                <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">Server Status</span>
                <p class="text-white text-xl font-semibold mt-1">Healthy</p>
            </div>
            <div class="p-6 rounded-2xl bg-[#111] border border-white/10">
                <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">Environment</span>
                <p class="text-blue-400 text-xl font-semibold mt-1">{{ config('app.env') }}</p>
            </div>
            <div class="p-6 rounded-2xl bg-[#111] border border-white/10">
                <span class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">Total Logs</span>
                <p class="text-white text-xl font-semibold mt-1">{{ count($logs) }} Entries</p>
            </div>
        </div>

        <!-- Terminal View -->
        <div class="rounded-2xl bg-[#0a0a0a] border border-white/10 overflow-hidden shadow-2xl">
            <div class="bg-[#111] px-5 py-3 border-b border-white/10 flex items-center justify-between">
                <div class="flex space-x-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-500/20"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/20"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/20"></div>
                </div>
                <span class="text-[10px] font-mono text-gray-500 tracking-widest">laravel.log</span>
            </div>
            
            <div class="p-4 md:p-6 h-[600px] overflow-y-auto terminal-scroll font-mono text-sm leading-relaxed">
                @forelse($logs as $log)
                    <div class="mb-4 p-3 rounded-lg border {{ $log['class'] }}">
                        <div class="flex items-center mb-1">
                            <span class="font-bold mr-3">[{{ $log['level'] }}]</span>
                            <span class="text-[10px] opacity-60">{{ $log['timestamp'] }}</span>
                        </div>
                        <p class="break-all opacity-90">{{ $log['message'] }}</p>
                    </div>
                @empty
                    <div class="h-full flex items-center justify-center text-gray-600 italic">
                        No log entries found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection