{{-- Admin/editor-only price trigger (client's request, 2026-09-21/22, answer #1). Only rendered
     when Auth::user()->isAdmin() at the call site - regular users must never see this.

     Deliberately a single small button with only inline styles and no theme classes (no
     `.au-btn` - that class turned out to have zero CSS on the public site, only in the admin
     panel, so the button rendered as a blank, textless rectangle) and no per-card dropdown
     panel (the earlier version's panel inherited unpredictable sizing from this page's own
     `.photo-item`/`.photo-album-wrapper` CSS and rendered oversized, overlapping neighboring
     photos). The actual edit form is ONE shared modal, relocated to <body> - see
     components/admin-price-editor-modal.blade.php (include it once per page) and
     adminPriceEditorOpen() in dating.js - the same pattern already proven immune to this kind
     of theme-CSS interference elsewhere in this app (AI Style Learning's help/detail overlays).

     Expects: $type ('image' or 'album'), $id, $price (EUR), $priceCredits. --}}
<button type="button"
    class="admin-price-trigger"
    data-type="{{ $type }}"
    data-id="{{ $id }}"
    data-price="{{ number_format((float) $price, 2, '.', '') }}"
    data-price-credits="{{ (int) $priceCredits }}"
    onclick="event.stopPropagation(); event.preventDefault(); adminPriceEditorOpen(this);"
    style="position:absolute; top:6px; right:6px; z-index:6; border:none; border-radius:4px; background:#28a745; color:#ffffff; font-size:12px; line-height:1; padding:6px 10px; cursor:pointer; font-family:inherit;">
    &euro; Pret
</button>
