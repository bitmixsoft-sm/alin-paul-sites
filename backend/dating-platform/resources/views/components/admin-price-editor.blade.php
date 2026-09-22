{{-- Admin/editor-only inline price-setting control (client's request, 2026-09-21/22, answer #1:
     both admin and editor can price a photo or album individually). Only rendered when
     Auth::user()->isAdmin() (covers both roles - see User::isAdmin()) at the call site, since
     regular users must never see or reach this. Two independent price fields - EUR (money mode)
     and a plain credits count (credits mode) - see ImageGet::effectivePrice() for why these are
     never converted from one another.

     Expects: $type ('image' or 'album'), $id, $price (EUR), $priceCredits. --}}
<div class="admin-price-editor" style="position:absolute; top:6px; right:6px; z-index:6;">
    <button type="button" class="au-btn" style="padding:0 8px; font-size:11px; height:24px; background:#28a745; color:#fff;" onclick="event.stopPropagation(); event.preventDefault(); adminPriceEditorToggle(this);">
        &euro; {{ l('Pret') }}
    </button>
    <div class="admin-price-editor-panel" style="display:none; position:absolute; top:26px; right:0; background:#fff; border:1px solid #ccc; border-radius:4px; padding:8px; width:170px; box-shadow:0 2px 8px rgba(0,0,0,.2); color:#333; font-size:12px;">
        <label style="display:block; margin-bottom:4px;">{{ l('Pret EUR') }}
            <input type="number" step="0.01" min="0" class="form-control form-control-sm price-eur-input" value="{{ number_format((float) $price, 2, '.', '') }}" style="width:100%;">
        </label>
        <label style="display:block; margin-bottom:6px;">{{ l('Pret credite') }}
            <input type="number" step="1" min="0" class="form-control form-control-sm price-credits-input" value="{{ (int) $priceCredits }}" style="width:100%;">
        </label>
        <button type="button" class="au-btn au-btn--blue" style="width:100%; font-size:11px; height:26px;" onclick="event.stopPropagation(); event.preventDefault(); adminPriceEditorSave(this, '{{ $type }}', {{ (int) $id }});">
            {{ l('Salveaza') }}
        </button>
    </div>
</div>
