@props(['locale'])

@if ($locale === 'en')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 14" width="20" height="14" class="inline-block rounded-[2px] align-middle overflow-hidden shadow-sm">
    <rect width="20" height="14" fill="#012169"/>
    {{-- St Andrew (white diagonals) --}}
    <path d="M0 0 L20 14 M20 0 L0 14" stroke="white" stroke-width="4"/>
    {{-- St Patrick (red diagonals, simplified) --}}
    <path d="M0 0 L20 14 M20 0 L0 14" stroke="#C8102E" stroke-width="2"/>
    {{-- St George (white cross) --}}
    <rect x="8" y="0" width="4" height="14" fill="white"/>
    <rect x="0" y="5" width="20" height="4" fill="white"/>
    {{-- St George (red cross) --}}
    <rect x="8.5" y="0" width="3" height="14" fill="#C8102E"/>
    <rect x="0" y="5.5" width="20" height="3" fill="#C8102E"/>
</svg>
@elseif ($locale === 'de')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 14" width="20" height="14" class="inline-block rounded-[2px] align-middle overflow-hidden shadow-sm">
    <rect y="0"    width="20" height="4.67" fill="#1a1a1a"/>
    <rect y="4.67" width="20" height="4.67" fill="#CC0000"/>
    <rect y="9.33" width="20" height="4.67" fill="#FFCE00"/>
</svg>
@endif
