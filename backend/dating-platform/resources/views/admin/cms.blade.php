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
                                        <i class="zmdi zmdi-account-calendar"></i>Pagini CMS</h3>
                                        <a href="/admin/cms/add" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Adauga</a>
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Link</td>
                                                    <td>Limba</td>
                                                    <td>Data creare</td>
                                                    <td>Ultima modificare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($pages as $page)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <span>
                                                            {{$page->name}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$page->route}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            {{$page->lang}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>{{$page->created_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$page->updated_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/cms/{{$page->id}}"><i class="zmdi zmdi-more"></i></a>
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
        </div>

@endsection
