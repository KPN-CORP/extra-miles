@extends('errors.layout')

@section('code', '404')
@section('title', __('Page Not Found'))
@section('message', __('We could not find the page you were looking for. It may have been moved, renamed, or removed.'))

@if (isset($exception) && trim($exception->getMessage()) !== '')
    @section('detail', $exception->getMessage())
@endif

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <circle cx="11" cy="11" r="7" />
        <path d="m20 20-4.2-4.2" />
        <path d="M8.5 8.5 13.5 13.5M13.5 8.5 8.5 13.5" />
    </svg>
@endsection
