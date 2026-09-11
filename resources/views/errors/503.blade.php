@extends('errors.layout')

@section('code', '503')
@section('title', __('Under Maintenance'))
@section('message', __('Extra Miles is briefly offline while we ship an update. Please check back shortly.'))

@section('icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
        stroke-linejoin="round">
        <path d="M14.6 5.6a3.8 3.8 0 0 0 5 5L15 15.2l-4.2-4.2 3.8-5.4Z" />
        <path d="m10.8 11-6 6a2 2 0 1 0 2.8 2.8l6-6" />
    </svg>
@endsection
