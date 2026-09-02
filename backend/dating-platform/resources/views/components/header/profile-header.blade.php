@auth
<div class="author-page author vcard inline-items more">
                <div class="author-thumb">
                <a href="/profile">
                    <img alt="author" src="/storage/images/{{Auth::user()->profile_image()}}" class="avatar">
                    <span id="status-header" class="icon-status online"></span>
                </a>
                    <div class="more-dropdown more-with-triangle">
                        <div class="mCustomScrollbar" data-mcs-theme="dark">
                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{l("Your Account")}}</h6>
                            </div>

                            <ul class="account-settings">
                                <li>
                                    <a href="/profile-settings">

                                        <svg class="olymp-menu-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-menu-icon"></use></svg>

                                        <span>{{l("Profile Settings")}}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/logout">
                                        <svg class="olymp-logout-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-logout-icon"></use></svg>

                                        <span>{{l("Log Out")}}</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{l("Custom Status")}}</h6>
                            </div>

                            <form id="moto-change" class="form-group with-button custom-status">
                                @csrf
                                <input name="moto" class="form-control" placeholder="" type="text" value="{{Auth::user()->moto}}">

                                <button type="submit" class="bg-purple">
                                    <svg class="olymp-check-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-check-icon"></use></svg>
                                </button>
                            </form>

                            @php

                            $langs = App\Lang::get();

                            @endphp

                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{l("Language")}}</h6>
                            </div>
                            <ul class="chat-settings">
                                @foreach($langs as $lang)
                                    <li class="select_lang">
                                        <a href="/language/{{$lang->code}}">
                                            <span><img src="/storage/lang/{{$lang->code}}.{{$lang->ext}}"></span>
                                            <span>{{$lang->name}} @if($_SESSION['lang'] == $lang->code) ({{ l('Selected') }}) @endif</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="ui-block-title ui-block-title-small">
                                <h6 class="title">{{l("About")}}</h6>
                            </div>

                            @php

                            $pages = App\CMS::select('name', 'route')->where('lang', $_SESSION['lang'])->get();

                            @endphp

                            <ul>
                                @foreach($pages as $page)
                                <li>
                                    <a href="/pages/{{$page->route}}">
                                        <span>{{l($page->name)}}</span>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                </div>
                <a href="/profile" class="author-name fn">
                    <div class="author-title">
                        {{Auth::user()->name()}} <svg class="olymp-dropdown-arrow-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-dropdown-arrow-icon"></use></svg>
                    </div>
                    <span id="moto-text" class="author-subtitle">{{Auth::user()->moto}}</span>
                </a>
            </div>
            @else
            <div class="form--login-logout top_auth">
            <a href="#" class="btn btn-purple btn-md-2" data-toggle="modal" data-target="#register-form-popup">{{l("Register")}}</a>
            <span class="top_auth_spacer">{{l("Or")}}</span>
            <a href="#" class="btn btn-primary btn-md-2" data-toggle="modal" data-target="#login-form-popup">{{l("Login")}}</a>
        </div>
            @endauth
