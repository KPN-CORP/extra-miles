@extends('errors.layout')

@section('code', '500')
@section('title', __('Something Went Wrong'))
@section('message', __('An unexpected error occurred on our side. The team has been notified — please try again in a few moments.'))

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <path d="M10.3 3.9 2.5 17.2A2 2 0 0 0 4.2 20.2h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
        <path d="M12 9v4" />
        <circle cx="12" cy="16.6" r="1.1" fill="currentColor" stroke="none" />
    </svg>
@endsection
