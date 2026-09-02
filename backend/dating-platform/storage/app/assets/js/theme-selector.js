
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
  }

function disableDarkTheme() {
  DARK_STYLE_LINK.setAttribute("href", "");
  THEME_TOGGLER.innerHTML = DARK_MODE;
  THEME_TOGGLER_M.innerHTML = DARK_MODE;
}