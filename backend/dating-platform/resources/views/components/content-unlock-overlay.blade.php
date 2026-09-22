{{-- Priced photo/album paywall overlay - client's request, 2026-09-21/22 ("Poze si albume cu
     pret"). Renders nothing when $locked is false, so it's safe to drop into any existing
     photo-card markup unconditionally. The wrapping element around the <img> this sits next to
     MUST be `position: relative` (or have one already, as most theme photo-card wrappers do) -
     this overlay itself is `position: absolute; inset: 0`.

     Expects: $type ('image' or 'album'), $id, $price, $locked (bool). --}}
@if($locked)
    <div class="content-unlock-overlay" data-type="{{ $type }}" data-id="{{ $id }}" data-price="{{ $price }}"
         style="position:absolute; inset:0; z-index:5; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; background:rgba(0,0,0,.35); color:#fff; text-align:center; padding:10px;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 10V7a6 6 0 0 1 12 0v3"/><rect x="4" y="10" width="16" height="11" rx="2"/></svg>
        @php $isMoneyMode = \App\Settings::where('name', 'CONTENT_UNLOCK_PRICE_MODE')->value('value') === 'money'; @endphp
        <strong style="font-size:15px; text-shadow:0 1px 3px rgba(0,0,0,.6);">
            @if($isMoneyMode)
                {{ number_format((float) $price, 2) }} EUR
            @else
                {{ (int) $price }} {{ l('credite') }}
            @endif
        </strong>
        <button type="button" class="content-unlock-btn au-btn au-btn--blue" style="padding:0 16px; font-size:12px; height:30px;" onclick="event.stopPropagation(); event.preventDefault(); contentUnlockClick(this);">
            {{ l('Deblocheaza') }}
        </button>
    </div>
@endif
