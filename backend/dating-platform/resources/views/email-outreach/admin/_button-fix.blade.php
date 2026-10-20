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
</style>
