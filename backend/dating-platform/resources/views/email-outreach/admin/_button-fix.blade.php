{{-- Found live, 2026-10-20: the public site's storage/app/assets/css/style.css (loaded on
     admin pages too - see resources/views/admin/components/layout.blade.php) overrides every
     .btn's hover state with a generic rule that isn't scoped to this theme's own buttons,
     turning solid Bootstrap buttons' background white on hover while the text stays white too
     (invisible white-on-white) - same root cause as the earlier outline-button fix
     (campaign.blade.php's "Preia manual" / index.blade.php's "Editeaza"), just the opposite
     direction. Fixing this here, scoped to just these pages, instead of touching that shared
     stylesheet (used by the whole public site and every other admin page) to avoid any risk of
     regressing something else. Pins each button class back to its own normal Bootstrap color on
     hover, rather than guessing a darker shade, since staying visible matters more here than an
     exact hover effect. --}}
<style>
.btn-primary:hover { background-color: #007bff !important; border-color: #007bff !important; color: #fff !important; }
.btn-secondary:hover { background-color: #6c757d !important; border-color: #6c757d !important; color: #fff !important; }
.btn-success:hover { background-color: #28a745 !important; border-color: #28a745 !important; color: #fff !important; }
.btn-info:hover { background-color: #17a2b8 !important; border-color: #17a2b8 !important; color: #fff !important; }
.btn-danger:hover { background-color: #dc3545 !important; border-color: #dc3545 !important; color: #fff !important; }

{{-- Found live, 2026-10-20: a plain <input type="checkbox"> renders invisible on this admin
     page too - this theme hides native checkboxes elsewhere in favor of its own custom-styled
     ones (an extra wrapper span drawing the visible box), which our plain checkboxes don't
     have. Forcing EVERY native checkbox back to visible/normal-sized on these pages (this
     partial is only ever @include'd on the three Email Outreach admin views, so a broad
     selector here is still fully scoped - not a site-wide change) rather than tracking down
     and rebuilding the custom-checkbox markup, or chasing every new checkbox one at a time as
     more get added (already missed the "remove image" one once doing it per-class). --}}
input[type="checkbox"] {
    opacity: 1 !important;
    position: static !important;
    -webkit-appearance: checkbox !important;
    appearance: checkbox !important;
    width: 16px !important;
    height: 16px !important;
    display: inline-block !important;
    margin: 0 !important;
    pointer-events: auto !important;
}
</style>
