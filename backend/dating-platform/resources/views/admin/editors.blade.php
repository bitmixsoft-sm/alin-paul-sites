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
                                        <i class="zmdi zmdi-account-calendar"></i>Administratori</h3>
                                        <form class="form-header" action="" method="POST" _lpchecked="1">
                                            <input id="search-admin" onKeyup="search_users(this,'admin');" data-to="admin-search" class="au-input au-input--xl" type="text" name="search" placeholder="Cauta administratori">
                                        </form>
                                        <a href="/admin/users" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Adauga</a>
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Avatar</td>
                                                    <td>Rol</td>
                                                    <td>Data creare</td>
                                                    <td>Conturi pe site</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="admin-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($administrators as $admin)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$admin->name()}}</h6>
                                                            <span>
                                                                <a href="#">{{$admin->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <img src="/storage/images/{{$admin->profile_image()}}" alt="{{$admin->name()}}" style="width: 75px;border-radius: 50%;">
                                                    </td>
                                                    <td>
                                                        <span class="role {{$admin->role}}">{{$admin->role}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$admin->created_at->format('d/m/Y')}}</span>
                                                    </td>
                                                    <td style="max-width:300px;">
                                                        @php
                                                            $i = 1;
                                                            $web_acc = "";
                                                            @endphp
                                                            @foreach($admin->getAccounts() as $acc)
                                                                @php
                                                                if($acc->getUser())
                                                                {
                                                                    $name = $acc->getUser()->name();
                                                                    if($i == 1){
                                                                        $web_acc = $name;
                                                                    }else{
                                                                        $web_acc .= ", ".$name;
                                                                    }
                                                                    $i++;
                                                                }
                                                                @endphp
                                                            @endforeach
                                                        <span>{{$web_acc}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/editors/{{$admin->username}}"><i class="zmdi zmdi-more"></i></a>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
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
