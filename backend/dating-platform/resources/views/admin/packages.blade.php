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
                                        <i class="fas fa-shopping-cart"></i>Pachete</h3>
                                        <a href="/admin/packages/add" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Adauga</a>
                                    <div class="table-responsive table-data">
                                        <table class="table myTable">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Likes</td>
                                                    <td>Newsfeed</td>
                                                    <td>Albume</td>
                                                    <td>Imagini</td>
                                                    <td>Prieteni</td>
                                                    <td>Chat</td>
                                                    <td>Credite</td>
                                                    <td>Pret</td>
                                                    <td>Evidentiat</td>
                                                    <td>Durata</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($packages as $pack)
                                                <tr>
                                                    <td data-th="#">{{$x}}</td>
                                                    <td data-th="Nume">
                                                            <span>
                                                                <a href="/admin/packages/{{$pack->id}}">{{$pack->name}}</a>
                                                            </span>
                                                    </td>
                                                    <td data-th="Likes">
                                                        <span>@if($pack->likes == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Newsfeed">
                                                        <span>@if($pack->newsfeed == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Albume">
                                                        <span>@if($pack->albums == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Imagini">
                                                        <span>@if($pack->images == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Prieteni">
                                                        <span>@if($pack->friends == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Chat">
                                                        <span>@if($pack->chat == 'true') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Credite">
                                                        <span class="role user">{{number_format($pack->credits, 0, '.', ',')}}</span>
                                                    </td>
                                                    <td data-th="Pret">
                                                        <span>{{$pack->price}} {{$pack->currency}}</span>
                                                    </td>
                                                    <td data-th="Evidentiat">
                                                        <span>@if($pack->featured == '1') Da @else Nu @endif</span>
                                                    </td>
                                                    <td data-th="Durata">
                                                        <span>{{$pack->duration}} zile</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/packages/{{$pack->id}}"><i class="zmdi zmdi-more"></i></a>
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
