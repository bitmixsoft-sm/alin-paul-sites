@php
    // Admin/editor only (gated by the @include in layouts/layout.blade.php) - lets staff swap
    // the site's active skin from any page, without a trip to /admin/themes. Deliberately
    // self-contained (fixed inline colors, not the --{theme}-* variables) so it looks and
    // reads the same regardless of which theme is currently active - it's a tool ABOUT the
    // themes, so it shouldn't visually belong to any one of them.
    $quickSwitchActive = \App\Support\ActiveTheme::current();
    $quickSwitchThemes = \App\Support\ActiveTheme::available();
@endphp
<div id="theme-quickswitch">
    <button type="button" id="theme-quickswitch-toggle" onclick="toggleThemeQuickswitch();">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="10.5" r="2.5"/><circle cx="8.5" cy="7.5" r="2.5"/><circle cx="6.5" cy="12.5" r="2.5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.5-.755 1.5-1.5 0-.396-.156-.756-.407-1.023-.267-.253-.407-.606-.407-.977 0-.756.63-1.5 1.5-1.5H16c3.31 0 6-2.69 6-6 0-4.42-4.03-8-10-8z"/></svg>
        <span>{{ ucfirst($quickSwitchActive) }}</span>
    </button>
    <div id="theme-quickswitch-menu">
        @foreach($quickSwitchThemes as $quickSwitchSlug)
            <form method="POST" action="/admin/themes">
                @csrf
                <input type="hidden" name="theme" value="{{ $quickSwitchSlug }}">
                <button type="submit" @if($quickSwitchActive === $quickSwitchSlug) class="active" @endif>
                    {{ ucfirst($quickSwitchSlug) }}
                    @if($quickSwitchActive === $quickSwitchSlug)
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
<style>
    #theme-quickswitch {
        position: fixed;
        left: 16px;
        bottom: 16px;
        z-index: 9999;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    #theme-quickswitch-toggle {
        display: flex;
        align-items: center;
        gap: 7px;
        background: #1c1c24;
        color: #e8e6f0;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 100px;
        padding: 9px 16px 9px 12px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.5);
    }
    #theme-quickswitch-toggle:hover {
        border-color: rgba(255, 255, 255, 0.3);
    }
    #theme-quickswitch-menu {
        display: none;
        position: absolute;
        left: 0;
        bottom: calc(100% + 8px);
        min-width: 160px;
        background: #1c1c24;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 12px;
        padding: 6px;
        box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.6);
    }
    #theme-quickswitch.open #theme-quickswitch-menu {
        display: block;
    }
    #theme-quickswitch-menu form {
        margin: 0;
    }
    #theme-quickswitch-menu button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        background: none;
        border: none;
        color: #c8c4d6;
        font-size: 13px;
        font-weight: 500;
        text-align: left;
        padding: 8px 10px;
        border-radius: 8px;
        cursor: pointer;
    }
    #theme-quickswitch-menu button:hover {
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
    }
    #theme-quickswitch-menu button.active {
        color: #7c5ac2;
        font-weight: 700;
    }
    @media screen and (max-width: 767px) {
        #theme-quickswitch {
            left: 10px;
            bottom: 10px;
        }
        #theme-quickswitch-toggle span {
            display: none;
        }
    }
</style>
<script>
    function toggleThemeQuickswitch() {
        var el = document.getElementById('theme-quickswitch');
        el.classList.toggle('open');
    }
    document.addEventListener('click', function (e) {
        var el = document.getElementById('theme-quickswitch');
        if (el && el.classList.contains('open') && !el.contains(e.target)) {
            el.classList.remove('open');
        }
    });
</script>
