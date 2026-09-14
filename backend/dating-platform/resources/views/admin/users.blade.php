@extends('admin.components.layout')
@section('content')
        <!-- PAGE CONTAINER-->
        <div class="page-container">
            
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- USER DATA-->
                                <div class="user-data m-b-30">
                                    <div class="row">
                                    <h3 class="title-3 m-b-30">
                                        <i class="zmdi zmdi-account-calendar"></i>Utilizatori</h3>
                                        <form class="form-header" action="" method="POST" _lpchecked="1">
                                            <input id="search-admin" onKeyup="search_users(this,'user');" data-to="user-search" class="au-input au-input--xl" type="text" name="search" placeholder="Cauta utilizatori">
                                        </form>
                                        <a href="/admin/users?option=banned" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="fas fa-ban"></i>Utilizatori blocati</a>
                                        {{-- Plain GET links (page reload), same pattern as "Utilizatori blocati" above -
                                             deliberately separate from the "Cauta utilizatori" box above, which is a
                                             live AJAX search (search_users() in dating.js) with its own endpoint;
                                             wiring gender into that too would mean touching that endpoint as well,
                                             for a feature that's really just "let me browse only the women/men"
                                             rather than "search by gender AND name at once".
                                             .add-admin (used by "Utilizatori blocati" above) is position: absolute;
                                             right: 45px - a SECOND element with that same class lands exactly on top
                                             of the first instead of next to it, which is what made "Toti"/"Femei"
                                             invisible (stacked underneath "Barbati"/the banned button). theme.css
                                             already has .add-admin-second (position: relative; margin-left: 45px)
                                             for exactly this "one more header action button" case - applied to this
                                             wrapper once, with the 3 links laid out normally (flex) inside it. --}}
                                        {{-- .au-btn's own line-height:45px/padding:0 35px makes a full-size button -
                                             fine for a single header action, too bulky for 3 small filter toggles
                                             side by side, so those are overridden smaller here instead of used
                                             as-is. --}}
                                        <div class="add-admin-second" style="gap:6px; align-items:center;">
                                            <a href="/admin/users" style="padding:0 14px; font-size:12px;" class="au-btn {{ $gender === null ? 'au-btn--blue' : 'au-btn--green' }}">Toti</a>
                                            <a href="/admin/users?gender=female" style="padding:0 14px; font-size:12px;" class="au-btn {{ $gender === 'female' ? 'au-btn--blue' : 'au-btn--green' }}">Femei</a>
                                            <a href="/admin/users?gender=male" style="padding:0 14px; font-size:12px;" class="au-btn {{ $gender === 'male' ? 'au-btn--blue' : 'au-btn--green' }}">Barbati</a>
                                        </div>
                                    @if($gender !== null)
                                        {{-- Without this, an admin who filtered to one gender and forgot about it
                                             later would just see a short/empty-looking list with no clue why -
                                             reported live as a real point of confusion. display:block/width:100%/
                                             clear:both forced explicitly - this page's surrounding elements
                                             (.add-admin/.add-admin-second) use position:absolute/relative in a way
                                             that made a plain .alert div render inline next to them instead of
                                             dropping to its own full-width line, so this doesn't rely on ambient
                                             block-flow behavior to do the right thing. --}}
                                        <div class="alert alert-info" style="display:block; width:100%; clear:both; text-align:center; margin: 15px 0;">
                                            <i class="fas fa-filter"></i>
                                            Momentan filtrat: <strong>{{ $gender === 'female' ? 'doar profile Femei' : 'doar profile Barbati' }}</strong>.
                                            <a href="/admin/users">Afiseaza toti utilizatorii</a>.
                                        </div>
                                    @endif
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Username</td>
                                                    <td>Sex</td>
                                                    <td>Pachet</td>
                                                    <td>Credite</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($users as $user)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$user->name()}}</h6>
                                                            <span>
                                                                <a href="#">{{$user->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span>
                                                                <a target="_blank" href="/profile/{{$user->username}}">{{$user->username}}</a>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                                @if($user->gender == 'male') Barbat @else Femeie @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="role user">@if($user->package()) {{$user->package()->name}} @else Nu @endif</span>
                                                    </td>
                                                    <td>
                                                        <span class="role user">{{number_format($user->credits, 0, '.', ',')}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$user->created_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/users/{{$user->username}}"><i class="zmdi zmdi-more"></i></a>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div id="pags" class="pull-right"> 
                                        {{$users->appends(['search' => Request::get('search')])->links()}}
                                        </div>
                                    </div>
                                    
                                </div>
                                <!-- END USER DATA-->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="copyright">
                                    <p>Copyright © 2019 Modele De Site. All rights reserved.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

@endsection
