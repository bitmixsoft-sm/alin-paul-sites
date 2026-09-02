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
                                <div class="card">
                                    <div class="card-header">
                                        <strong>Clienti</strong> Adaugare
                                    </div>
                                     <form action="" method="post" class="form-horizontal">
                                    <div class="card-body card-block">
                                       
                                            @csrf
                                            <div class="row">
                                                <div class="col-6 col-md-6">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="hf-email" class=" form-control-label">Email-uri (despartite de virgula si fara spatii)</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <textarea name="emails" required placeholder="Email" class="form-control"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-6">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="hf-email" class=" form-control-label">Sursa</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="text" name="source" placeholder="Sursa" value="@if(!$add){{$client->source}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                    <div class="col-6 col-md-6">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="hf-email" class=" form-control-label">Editor</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select name="admin_id" id="select" class="form-control">
                                                                @foreach($editors as $editor)
                                                                    <option value="{{$editor->id}}">{{$editor->name()}}</option>
                                                                @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                    </div>
                                    <div class="card-footer">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-dot-circle-o"></i> Submit
                                        </button>
                                        @if(!$add)
                                        <a href="/admin/delete/client/{{$client->id}}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Delete
                                        </a>
                                        @else
                                        <a href="/admin/clients/add/multiple" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Reset
                                        </a>
                                        @endif
                                    </div>
                                </form>
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
