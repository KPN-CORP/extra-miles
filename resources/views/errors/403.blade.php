@extends('errors.layout')

@section('code', '403')
@section('title', __('Access Denied'))
@section('message', __('The page you are looking for seems to be off limits. If you think you should have access, ask your administrator to review your permissions.'))

@if (isset($exception) && trim($exception->getMessage()) !== '')
    @section('detail', $exception->getMessage())
@endif

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        <circle cx="12" cy="16.5" r="1.2" fill="currentColor" stroke="none" />
    </svg>
@endsection
