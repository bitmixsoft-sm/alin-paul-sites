
  const LOCAL_STORAGE_KEY = "theme-selector";
  const LOCAL_META_DATA = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
 
  const DARK_THEME_PATH = "/assets/css/dark-theme.css?v=1.0";
  const DARK_STYLE_LINK = document.getElementById("dark-theme-style");
  const THEME_TOGGLER = document.getElementById("theme-toggler");
  const THEME_TOGGLER_M = document.getElementById("theme-toggler-m");
  let isDark = LOCAL_META_DATA && LOCAL_META_DATA.isDark;
  const DARK_MODE = '🌙';
  const LIGHT_MODE = '🌞';
  
  if(!LOCAL_META_DATA){
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({"isDark":true}));
      isDark = true;
  }


  if (isDark) {enableDarkTheme();} else {disableDarkTheme();}

  function toggleTheme() 
  {
    isDark = !isDark;
    if (isDark) {
      enableDarkTheme();
    } else {
      disableDarkTheme();
    }
    const META = { isDark };
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(META));
  }

  function enableDarkTheme()
  {
    DARK_STYLE_LINK.setAttribute("href", DARK_THEME_PATH);
    THEME_TOGGLER.innerHTML = LIGHT_MODE;
    THEME_TOGGLER_M.innerHTML = LIGHT_MODE;
    // Lets the admin-selected site skin (aurora/nordic - see storage/app/assets/css/themes/)
    // offer its own light variant, toggled by this same existing switch.
    document.body.classList.add('site-dark-mode');
  }

function disableDarkTheme() {
  DARK_STYLE_LINK.setAttribute("href", "");
  THEME_TOGGLER.innerHTML = DARK_MODE;
  THEME_TOGGLER_M.innerHTML = DARK_MODE;
  document.body.classList.remove('site-dark-mode');
}

// Aurora/nordic only: the icon-free horizontal nav (.theme-nav in components/header.blade.php)
// is shown inline on desktop widths, but on narrow/mobile widths it's hidden by CSS and only
// shown as a dropdown panel via this hamburger toggle - same single nav markup both ways, so
// there's nothing to keep in sync between a "desktop" and "mobile" copy of the links.
function toggleThemeNavDropdown(t) {
  if (t && t.stopPropagation) { /* no-op, kept for callers passing an event-less element */ }
  document.body.classList.toggle('theme-nav-dropdown-open');
}

document.addEventListener('click', function (e) {
  if (!document.body.classList.contains('theme-nav-dropdown-open')) { return; }
  var toggle = e.target.closest ? e.target.closest('.theme-nav-toggle') : null;
  var panel = e.target.closest ? e.target.closest('.theme-nav') : null;
  if (!toggle && !panel) {
    document.body.classList.remove('theme-nav-dropdown-open');
  }
});

// Aurora/nordic mobile only (see themes/*.css): the header search box drops down below its
// icon instead of expanding sideways (which used to shove the rest of the header to the
// right). Toggled by clicking the search icon again, or by clicking anywhere outside it -
// same open/close pattern as the nav hamburger above.
function toggleThemeSearch() {
  document.body.classList.toggle('theme-search-open');
}

document.addEventListener('click', function (e) {
  if (!document.body.classList.contains('theme-search-open')) { return; }
  var box = e.target.closest ? e.target.closest('.search-bar') : null;
  if (!box) {
    document.body.classList.remove('theme-search-open');
  }
});

// Aurora/nordic/volt only: the "Online Users" link now lives in the top .theme-nav instead
// of the old left sidebar (see header.blade.php) - the #online_users_left panel it opens
// (dating.js's own click handler, unchanged, still shows/hides and fills it) is pulled back
// into view via themes/*.css with a hardcoded top/left, which just happened to roughly match
// where the old sidebar toggle used to sit. Now that the link can be anywhere in the nav
// (inline on desktop, in the hamburger dropdown on mobile), position the panel relative to
// wherever the actual clicked link is instead, clamped so it can't render off the right/
// bottom edge of the viewport.
document.addEventListener('click', function (e) {
  var link = e.target.closest ? e.target.closest('.open_online_users') : null;
  if (!link) { return; }
  var panel = document.getElementById('online_users_left');
  if (!panel) { return; }
  var rect = link.getBoundingClientRect();
  var panelWidth = 280;
  var left = Math.min(rect.left, window.innerWidth - panelWidth - 12);
  left = Math.max(left, 12);
  panel.style.top = (rect.bottom + 8) + 'px';
  panel.style.left = left + 'px';
});