@extends('errors.layout')

{{-- Rendered by SsoController when Darwinbox cannot vouch for the employee.
     It must not link to "/", which is the employee SPA and only shows
     "Mobile Only" on a desktop browser. --}}

@section('code', '403')
@section('title', __('Login Failed'))
@section('message', __('Login Failed, Please Contact Administrator'))

@section('actions')
    <a href="https://kpncorporation.darwinbox.com/" class="btn btn-primary">{{ __('Back to Darwinbox') }}</a>
@endsection

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        <circle cx="12" cy="16.5" r="1.2" fill="currentColor" stroke="none" />
    </svg>
@endsection
