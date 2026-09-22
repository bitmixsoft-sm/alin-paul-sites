{{-- Shared admin/editor price-edit panel for components/admin-price-editor.blade.php - include
     this ONCE per page (not once per photo/album). Same hand-styled, body-relocated overlay
     pattern already proven to work around theme-CSS interference elsewhere in this app (AI
     Style Learning's help/detail overlays) - hidden by default, moved to be a direct child of
     <body> on load so no ancestor's positioning/stacking/sizing rules can reach it. --}}
<div id="admin-price-editor-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.6); z-index:999999; align-items:center; justify-content:center;" onclick="if(event.target===this){this.style.display='none';}">
    <div style="background:#fff; border-radius:6px; width:260px; padding:20px; color:#333; font-size:13px;">
        <h6 style="margin:0 0 14px; font-weight:700;">Pret continut</h6>

        <label style="display:block; margin-bottom:10px;">Pret EUR (mod bani)
            <input type="number" step="0.01" min="0" id="admin-price-eur-input" class="form-control" style="width:100%; margin-top:4px;">
        </label>
        <label style="display:block; margin-bottom:14px;">Pret credite (mod credite)
            <input type="number" step="1" min="0" id="admin-price-credits-input" class="form-control" style="width:100%; margin-top:4px;">
        </label>

        <div style="display:flex; gap:8px;">
            <button type="button" onclick="document.getElementById('admin-price-editor-overlay').style.display='none';" style="flex:1; border:1px solid #ccc; background:#fff; color:#333; border-radius:4px; padding:8px 0; cursor:pointer;">Anuleaza</button>
            <button type="button" onclick="adminPriceEditorSave();" style="flex:1; border:none; background:#0d6efd; color:#fff; border-radius:4px; padding:8px 0; cursor:pointer;">Salveaza</button>
        </div>
    </div>
</div>
