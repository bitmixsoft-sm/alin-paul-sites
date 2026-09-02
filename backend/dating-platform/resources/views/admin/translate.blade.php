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
                                        <h3 class="title-3 m-b-30"><i class="fas fa-globe"></i>Traduceri</h3>
                                    </div>
                                        <div class="container">
                                            @if(session()->has('error'))
                                                <div class="sufee-alert alert with-close alert-danger alert-dismissible fade show">
                                                    <span class="badge badge-pill badge-danger">Eroare</span>
                                                    {{ session()->get('error') }}
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                            @endif
                                            @if(session()->has('success'))
                                                <div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
                                                    <span class="badge badge-pill badge-success">Succes!</span>
                                                    {{ session()->get('success') }}
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                            @endif
                                            <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="card trans">
                                                    <div class="card-header">
                                                        Adaugare
                                                        <strong>Limba</strong>
                                                    </div>
                                                    <div class="card-body card-block">
                                                        <form action="/translate/add" method="post" enctype='multipart/form-data' class="form-horizontal">
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Nume</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <input type="text" id="input-normal" name="name" placeholder="Romana" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Cod (en, it, ro)</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <input type="text" id="input-normal" name="code" placeholder="ro" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Steag</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <input type="file" id="input-normal" name="image" class="form-control">
                                                                </div>
                                                            </div>                                                     
                                                        
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Adauga
                                                        </button>
                                                        <a href="/translate" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-ban"></i> Reseteaza
                                                        </a>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="card trans">
                                                    <div class="card-header">
                                                        Traducere
                                                        <strong>Limba</strong>
                                                    </div>
                                                    <div class="card-body card-block">
                                                        <form action="/translate/edit" method="post" class="form-horizontal">
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Selecteaza limba</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <select name="lang" id="select" class="form-control">
                                                                        @foreach($langs as $lang)
                                                                        <option value="{{$lang->code}}">{{$lang->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Selecteaza
                                                        </button>
                                                    </div>
                                                </form>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <h3 class="title-2 m-b-30 m-l-30"></i>Limbi existente</h3>
                                        <hr>
                                        <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Cod</td>
                                                    <td>Steag</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($langs as $lang)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$lang->name}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$lang->code}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <img src="/storage/lang/{{$lang->code}}.{{$lang->ext}}" width="30px">
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$lang->created_at}}</h6>
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
