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
                                <!-- USER ACTIVITY DATA-->
                                <div class="user-data m-b-30">
                                    <div class="row">
                                    <h3 class="title-3 m-b-30">
                                        <i class="fas fa-history"></i>Activitate utilizatori</h3>
                                        <form class="form-header" action="" method="POST" _lpchecked="1">
                                            <input id="search-admin" onKeyup="userActivitySearch(this);" data-to="user-activity-search" class="au-input au-input--xl" type="text" name="search" placeholder="Cauta evenimente">
                                        </form>
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Eveniment</td>
                                                    <td>Detalii eveniment</td>
                                                    <td>Adresa IP</td>
                                                    <td>Locatie</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            
                                            <tbody id="user-activity-search">
                                              
                                                @foreach($activities as $key => $activity)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$activity->user->firstname . ' ' . $activity->user->lastname}}</h6>
                                                            <span>
                                                                <a href="#">{{$activity->user->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                       {{ $activity->name }}
                                                    </td>
                                                    
                                                    <td>
                                                       {{ $activity->details }}
                                                    </td>
                                                    
                                                    <td>
                                                       {{ $activity->ip }}
                                                    </td>
                                                    
                                                    <td>
                                                       {{ $activity->location }}
                                                    </td>
                                                    
                                                    <td>
                                                      {{ $activity->created_at }}
                                                    </td>
                                                </tr>
                                               
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                        <div id="pags" class="pull-right"> 
                                        {{$activities->appends(['search' => Request::get('search')])->links()}}
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
