@extends('errors.layout')

@section('code', '419')
@section('title', __('Session Expired'))
@section('message', __('Your session has expired for security reasons. Please refresh the page and sign in again.'))

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 7v5.2l3.2 2" />
    </svg>
@endsection
