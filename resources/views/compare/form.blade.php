@extends('layouts.app')
@section('title', 'Compare Accounts')

@section('content')
<x-page-header num="CMP" title="Compare" :meta="'Side-by-side public account data'" />

@if(session('error'))
    <div class="mb-8"><x-error-state label="Comparison unavailable" :message="session('error')" /></div>
@endif

<form method="POST" action="{{ route('compare') }}" class="max-w-4xl">
    @csrf
    <div class="grid gap-5 md:grid-cols-2">
        @foreach(['a' => 'Account A', 'b' => 'Account B'] as $name => $label)
            <label class="grid gap-2 text-sm font-medium text-paper/70">
                {{ $label }}
                <input type="text" name="{{ $name }}" value="{{ old($name) }}" required maxlength="50"
                       placeholder="Username or user ID"
                       class="min-h-12 w-full rounded-md border border-white/15 bg-white px-4 text-base text-ink outline-none focus:border-acid">
                @error($name)<span class="text-xs text-signal">{{ $message }}</span>@enderror
            </label>
        @endforeach
    </div>
    <button type="submit" class="mt-6 min-h-12 rounded-md bg-acid px-6 font-semibold text-white hover:opacity-90">
        Compare accounts
    </button>
</form>
@endsection