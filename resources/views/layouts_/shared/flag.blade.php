{{-- Inline flag icon for the language switcher.

     Drawn inline rather than loaded from public/images/flags because the theme
     only ships a us.jpg and no Indonesian flag, and the admin asset paths go
     through the storage symlink -- which is not set up in every environment.
     Inlining keeps the icon working locally and on the cPanel host with no
     extra file to deploy.

     Expects: $code ('en' | 'id') and $uid (unique per render -- the Union Jack
     needs a clipPath id, and the switcher draws each flag more than once). --}}

@php
    $uid = $uid ?? $code;
@endphp

@if ($code === 'id')
    {{-- Indonesia: red over white, 3:2. --}}
    <svg viewBox="0 0 60 40" width="18" height="12" class="rounded-1 align-middle"
        style="box-shadow: 0 0 0 1px rgba(0,0,0,.12);" role="img" aria-hidden="true">
        <rect width="60" height="20" fill="#CE1126" />
        <rect y="20" width="60" height="20" fill="#FFFFFF" />
    </svg>
@else
    {{-- United Kingdom, the usual marker for the English option. --}}
    <svg viewBox="0 0 60 40" width="18" height="12" class="rounded-1 align-middle"
        style="box-shadow: 0 0 0 1px rgba(0,0,0,.12);" role="img" aria-hidden="true">
        <clipPath id="flag-uj-{{ $uid }}">
            <path d="M30,20 h30 v20 z v20 h-30 z h-30 v-20 z v-20 h30 z" />
        </clipPath>
        <rect width="60" height="40" fill="#012169" />
        <path d="M0,0 L60,40 M60,0 L0,40" stroke="#FFFFFF" stroke-width="8" />
        <path d="M0,0 L60,40 M60,0 L0,40" stroke="#C8102E" stroke-width="5"
            clip-path="url(#flag-uj-{{ $uid }})" />
        <path d="M30,0 v40 M0,20 h60" stroke="#FFFFFF" stroke-width="13" />
        <path d="M30,0 v40 M0,20 h60" stroke="#C8102E" stroke-width="8" />
    </svg>
@endif
