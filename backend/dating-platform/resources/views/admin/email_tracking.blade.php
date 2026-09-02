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
                                        <h3 class="title-2 m-b-30 m-l-30"></i>Email-uri trimise</h3>
                                        @if(Auth::user()->role == 'admin')
                                        <span style="display: block; margin-left: 30px;">Email-uri trimise azi: {{$email_global}}</span>
                                        <span style="display: block; margin-left: 30px;">Email-uri trimise azi de pe acest site: {{$email_local}}</span>
                                        @endif
                                        <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Email</td>
                                                    <td>Destinatari</td>
                                                    <td>Deschideri</td>
                                                    @if(Auth::user()->role == 'admin')
                                                    <td>Site</td>
                                                    @endif
                                                    <td>Data trimitere</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @if($emails->count() > 0)
                                                @php
                                                
                                                if(!isset($_GET['page'])){
                                                    $page = 1;
                                                }else{
                                                    $page = $_GET['page'];
                                                }
                                                $x = ($page-1)*$emails->count()+1;
                                                @endphp
                                                @foreach($emails as $email)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$email->subject}}</h6>
                                                            <span>
                                                                <a href="#">{{$email->message}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span>{{$email->receivers()->count()}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$email->seen()->count()}}</span>
                                                    </td>
                                                    @if(Auth::user()->role == 'admin')
                                                    <td>
                                                        <span>{{$email->website}}</span>
                                                    </td>
                                                    @endif
                                                    <td>
                                                        <span>{{$email->created_at->format('d/m/Y - H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <a href="/admin/emailtracking/{{$email->id}}"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                                @else
                                                <tr><td><span>Nu s-au trimis inca email-uri</span></td></tr>
                                                @endif
                                            </tbody>
                                        </table>
                                        <div> 
                                            {{$emails->links()}}
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
