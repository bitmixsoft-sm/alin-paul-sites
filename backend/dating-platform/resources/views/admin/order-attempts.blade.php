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
                                        <i class="zmdi zmdi-account-calendar"></i>Comenzi initiate</h3>
                                        <div class="row">
                                        <div class="col col-sm-3"></div>
                                        <div class="col col-sm-9 float-right">
                                        <form class="form-header" action="/admin/order-attempts/delete" method="POST">
                                            @csrf
                                            <div class="row form-group">
                                                <div class="col col-sm-4">
                                                    <label for="input-normal" class=" form-control-label">Nr. zile:</label>
                                                </div>
                                                <div class="col col-sm-4">
                                                    <input type="text" name="days" value="" size=3 class="form-control" />
                                                </div>
                                                <div class="col col-sm-4">
                                                    <input type="submit" onClick="return confirm('Are you sure?')" name="submit" value="Sterge comenzi initiate" class="btn btn-danger btn-sm" style="width:220px;" />
                                                </div>
                                            </div>
                                        </form>
                                        </div>
                                        </div>
                                        
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Utilizator</td>
                                                    <td>Pachet</td>
                                                    <td>Pret</td>
                                                    <td>IP</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($order_attemps as $order_attemp)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        @if($order_attemp->user())
                                                        <div class="table-data__info">
                                                            <h6>{{$order_attemp->user()->name()}}</h6>
                                                            <span>
                                                                <a href="#">{{$order_attemp->user()->email}}</a>
                                                            </span>
                                                        </div>
                                                        @else
                                                           <span>Cont Sters</span> 
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span>
                                                            <span class="role user">@if($order_attemp->package()) {{$order_attemp->package()->name}} @else Sters @endif</span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$order_attemp->price}} {{$order_attemp->currency}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$order_attemp->ip_address}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>{{$order_attemp->created_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <a href="/admin/order-attempts/delete/{{$order_attemp->id}}" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-ban"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div id="pags" class="pull-right"> 
                                        {{$order_attemps->links()}}
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
