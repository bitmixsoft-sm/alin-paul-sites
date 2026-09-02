<!-- MENU SIDEBAR-->
<aside class="menu-sidebar d-none d-lg-block">
    <div class="logo">
        <a href="/admin">
            <img src="/img/logo-colored.png" alt="Dating Admin"/>
            <span>Dating Admin</span>
        </a>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        <nav class="navbar-sidebar">
            <ul class="list-unstyled navbar__list">
                @if(Auth::user()->role == 'admin')
                    <li @if($on_page == 'Dashboard') class="active" @endif>
                        <a href="/admin">
                            <i class="fas fa-chart-bar"></i>Statistici</a>
                    </li>
                    <li @if($on_page == 'Administratori' || $on_page == 'Editor') class="active" @endif>
                        <a href="/admin/editors">
                            <i class="fas fa-user"></i>Editori</a>
                    </li>
                @endif
                <li @if($on_page == 'Utilizatori' || $on_page == 'Profil') class="active" @endif>
                    <a href="/admin/users">
                        <i class="fas fa-users"></i>Utilizatori</a>
                </li>
                <li @if($on_page == 'Clienti' || $on_page == 'Adauga Client'  || $on_page == 'Editare Client') class="active" @endif>
                    <a href="/admin/clients">
                        <i class="fas fa-user"></i>Clienti</a>
                </li>
                @if(Auth::user()->role == 'admin')
                    <li @if($on_page == 'Pachete' || $on_page == 'Editare Pachet') class="active" @endif>
                        <a href="/admin/packages">
                            <i class="fas fa-cart-plus"></i>Pachete</a>
                    </li>
                    <li @if($on_page == 'Reduceri' || $on_page == 'Editare reducere') class="active" @endif>
                        <a href="/admin/discounts">
                            <i class="fas fa-percent"></i>Reduceri</a>
                    </li>
                @endif
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'editor')
                    <li @if($on_page == 'AI Profiles') class="active" @endif>
                        <a href="/admin/ai-profiles">
                            <i class="fas fa-user-circle"></i>AI Profiles</a>
                    </li>
                    <li @if($on_page == 'AI Settings') class="active" @endif>
                        <a href="/admin/ai-settings">
                            <i class="fas fa-cogs"></i>AI Settings</a>
                    </li>
                @endif
                <li @if($on_page == 'Comenzi') class="active" @endif>
                    <a href="/admin/orders">
                        <i class="fas fa-shopping-cart"></i>Comenzi</a>
                </li>
                <li @if($on_page == 'Comenzi initiate') class="active" @endif>
                    <a href="/admin/order-attempts">
                        <i class="fas fa-shopping-cart"></i>Comenzi initiate</a>
                </li>
                <li @if($on_page == 'Newsletter' || $on_page == 'Trimite Newsletter') class="active" @endif>
                    <a href="/admin/newsletter/users">
                        <i class="far fa-envelope"></i>Newsletter</a>
                </li>
                @if(Auth::user()->role == 'admin')
                    <li @if($on_page == 'Traduceri') class="active" @endif>
                        <a href="/translate">
                            <i class="fas fa-globe"></i>Traduceri</a>
                    </li>
                    <li @if($on_page == 'Pagini CMS') class="active" @endif>
                        <a href="/admin/cms">
                            <i class="fas fa-copy"></i>Pagini CMS</a>
                    </li>
                    <li @if($on_page == 'Activitate utilizatori') class="active" @endif>
                        <a href="/admin/users-activity">
                            <i class="fas fa-history"></i>Activitate utilizatori</a>
                    </li>
                    <li @if($on_page == 'RuletaS') class="active" @endif>
                        <a href="/admin/roulette/values">
                            <i class="fas fa-copy"></i>Administrare ruleta</a>
                    </li>
                    <li @if($on_page == 'Setari') class="active" @endif>
                        <a href="/admin/settings">
                            <i class="fas fa-gears"></i>Setari</a>
                    </li>
                    <li @if($on_page == 'Aspect') class="active" @endif>
                        <a href="/admin/themes">
                            <i class="fas fa-paint-brush"></i>Aspect</a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
<!-- END MENU SIDEBAR-->
