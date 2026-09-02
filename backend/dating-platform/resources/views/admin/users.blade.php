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
