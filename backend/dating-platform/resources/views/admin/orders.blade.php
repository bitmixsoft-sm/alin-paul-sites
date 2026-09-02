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
                                        <i class="zmdi zmdi-account-calendar"></i>Comenzi</h3>
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Utilizator</td>
                                                    <td>Pachet</td>
                                                    <td>Status</td>
                                                    <td>Pret</td>
                                                    <td>IP</td>
                                                    <td>Data creare</td>
                                                    <td>Ultima modificare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($orders as $order)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        @if($order->user())
                                                        <div class="table-data__info">
                                                            <h6>{{$order->user()->name()}}</h6>
                                                            <span>
                                                                <a href="#">{{$order->user()->email}}</a>
                                                            </span>
                                                        </div>
                                                        @else
                                                           <span>Cont Sters</span> 
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span>
                                                            <span class="role user">@if($order->package()) {{$order->package()->name}} @else Sters @endif</span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            @if($order->status == 'Pending')
                                                                <span class="badge badge-warning">{{$order->status}}</span> 
                                                            @elseif($order->status == 'Accepted')
                                                                <span class="badge badge-success">{{$order->status}}</span>
                                                            @else
                                                                <span class="badge badge-danger">{{$order->status}}</span>
                                                            @endif

                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$order->price}} {{$order->currency}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$order->ip_address}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>{{$order->created_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$order->updated_at->format('d/m/Y H:i')}}</span>
                                                    </td>                          
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div id="pags" class="pull-right"> 
                                        {{$orders->links()}}
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
        </div>

@endsection
