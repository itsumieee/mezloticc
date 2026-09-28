@extends('layouts.app')
@section('title', 'Roblox Account Checker')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[80vh] gap-8">

    <h1 class="text-4xl font-bold bg-gradient-to-r from-cyan-400 to-purple-500 bg-clip-text text-transparent">
        Roblox Account Checker
    </h1>

    <p class="text-slate-400 text-center max-w-md">
        Enter a Roblox username to view public account information.
        No password or login required.
    </p>

    @if(session('error'))
        <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-2 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-500/20 border border-red-500 text-red-300 px-4 py-2 rounded-lg" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('search') }}" id="searchForm" class="w-full max-w-md flex gap-2">
        @csrf
        <input type="text" name="username" id="usernameInput" placeholder="Enter Roblox Username"
               value="{{ old('username') }}"
               class="flex-1 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700
                      focus:outline-none focus:ring-2 focus:ring-cyan-500 text-slate-100 transition-all duration-300" required>
        <button type="submit" id="searchBtn"
                class="px-6 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-500
                       transition font-semibold text-white shadow-lg disabled:opacity-50 disabled:cursor-wait">
            <span id="btnText">CHECK</span>
        </button>
    </form>

    <div id="loadingState" class="hidden w-full max-w-md" role="status" aria-live="polite">
        <div class="flex items-center gap-3 text-cyan-400">
            <div class="w-5 h-5 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin"></div>
            <span class="text-sm">Looking up account...</span>
        </div>
    </div>

    <p class="text-xs text-slate-500 max-w-md text-center">
        This tool only uses publicly available Roblox APIs.
        It never asks for passwords, cookies, or security tokens.
    </p>
    <a href="{{ route('history') }}" class="text-xs text-slate-500 hover:text-cyan-400 underline">
        View search history
    </a>
</div>

<script>
    document.getElementById('searchForm').addEventListener('submit', function () {
        document.getElementById('btnText').textContent = '...';
        document.getElementById('searchBtn').disabled = true;
        document.getElementById('loadingState').classList.remove('hidden');
    });
</script>
@endsection