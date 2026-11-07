@props(['href', 'type', 'label'])

@php
    $isPdf = strtolower($type) === 'pdf';
    $color = $isPdf ? '#e0443e' : '#389c61';
@endphp

<a {{ $attributes->merge(['href' => $href]) }} aria-label="{{ $label }}" title="{{ $label }}" class="export-link" style="display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;color:{{ $color }};box-shadow:0 3px 8px rgba(18,59,93,.1);text-decoration:none">
    @if($isPdf)
        <svg viewBox="0 0 48 48" width="36" height="36" fill="none" stroke="{{ $color }}" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M11 4h18l10 10v27a3 3 0 0 1-3 3H11a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Z"/>
            <path d="M29 4v11h10M15 21h18M15 27h18M15 33h12M24 32v11m-6-6 6 6 6-6"/>
        </svg>
    @else
        <svg viewBox="0 0 48 48" width="36" height="36" fill="none" stroke="{{ $color }}" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10 4h28v29M10 4v29m0 0h8m12 0h8"/>
            <path d="M14 10h6v6h-6zM22 10h6v6h-6zM30 10h4v6h-4zM14 19h6v6h-6zM22 19h6v6h-6zM30 19h4v6h-4z"/>
            <path d="M24 27v16m-6-6 6 6 6-6"/>
        </svg>
    @endif
</a>
