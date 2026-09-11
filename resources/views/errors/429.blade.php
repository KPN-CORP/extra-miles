@extends('errors.layout')

@section('code', '429')
@section('title', __('Too Many Requests'))
@section('message', __('You have made too many requests in a short time. Please wait a moment and try again.'))

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <path d="M12 3a9 9 0 1 1-9 9" />
        <path d="M3 3v5h5" />
        <path d="M12 8v4.2l2.8 1.8" />
    </svg>
@endsection
