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
                                        <h3 class="title-3 m-b-30"><i class="fas fa-globe"></i>{{$on_page}}</h3>
                                    </div>
                                        <div class="container">
                                            <form action="/admin/settings/save" method="post" enctype="multipart/form-data" class="form-horizontal">
                                                @csrf
                                                @foreach($settings as $setting)
                                                <div class="row form-group">
                                                    <div class="col col-sm-5">
                                                        <label for="input-normal" class=" form-control-label">{{$setting->id}}. {{$setting->name}}</label>
                                                    </div>
                                                    @if($setting->type == 'image')
                                                    <div class="col col-sm-6">
                                                        <input type="file" name="{{$setting->id}}" value="{{$setting->value}}" class="form-control" accept="image/png, image/jpeg">
                                                    </div>
                                                    @elseif($setting->type == '' || $setting->type == 'text')
                                                    <div class="col col-sm-6">
                                                        <input type="text" name="{{$setting->id}}" value="{{$setting->value}}" class="form-control">
                                                    </div>
                                                    @elseif($setting->type == 'toggle')
                                                    <div class="col col-sm-6">
                                                        <select name="{{$setting->id}}" class="form-control">
                                                            <option @if($setting->value == 'yes') selected @endif value="yes">Da</option>
                                                            <option @if($setting->value == 'no') selected @endif value="no">Nu</option>
                                                        </select>
                                                    </div>
                                                    @elseif(strpos($setting->type, 'select|')!==false)
                                                    <div class="col col-sm-6">
                                                        <select name="{{$setting->id}}" class="form-control">
                                                            @foreach(explode("~", str_replace("select|", "", $setting->type)) as $line)
                                                            <option @if($setting->value == $line) selected @endif value="{{$line}}">{{$line}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    @endif
                                                </div>
                                                @endforeach                              
                                                <hr>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Modifica
                                                        </button>
                                            </form>       
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
