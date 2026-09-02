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
                                <div class="user-data m-b-30">
                                    <div class="row">
                                        <h3 class="title-3 m-b-30"><i class="fas fa-eye"></i>Tracking</h3>
                                    </div>
                                        <div class="container">
                                        <h3 class="title-2 m-b-30 m-l-30"></i>Destinatari - {{$email->subject}}</h3>
                                        <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Email</td>
                                                    <td>Deschis</td>
                                                    <td>Status</td>
                                                    <td>Data trimiterii</td>
                                                    <td>Data deschiderii</td>
                                                    <td>Dezabonare</td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @if($users->count() > 0)
                                                @php
                                                
                                                if(!isset($_GET['page'])){
                                                    $page = 1;
                                                }else{
                                                    $page = $_GET['page'];
                                                }
                                                $x = ($page-1)*$users->count()+1;
                                                @endphp
                                                @foreach($users as $user)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                            <span>{{$user->email}}</span>
                                                    </td>
                                                    <td>
                                                        @if($user->seen == 1)<span class="role editor"> Da </span>@else <span> Nu </span> @endif</span>
                                                    </td>
                                                    <td>
                                                        <span class="role user">{{$user->status}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$user->created_at->format('d/m/Y - H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$user->updated_at->format('d/m/Y - H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <a href="/unsubscribe/{{$user->email}}"><i class="fas fa-minus"></i></a>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                                @else
                                                <tr><td><span>Nu s-au trimis inca email-urile</span></td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                        <div> 
                                            {{$users->links()}}
                                        </div>
                                    </div>
                                    </div>
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