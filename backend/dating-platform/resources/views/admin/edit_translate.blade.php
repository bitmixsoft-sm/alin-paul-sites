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
                                            <form action="/translate/update" method="post" class="form-horizontal">
                                                @csrf
                                                @php
                                                $x = 0;
                                                @endphp
                                                @foreach($langs as $key=>$value)
                                                <div class="row form-group">
                                                    <div class="col col-sm-5">
                                                        <input type="hidden" name="key_{{$x}}" value="{{$key}}">
                                                        <label for="input-normal" class=" form-control-label">{{$key}}</label>
                                                    </div>
                                                    <div class="col col-sm-6">
                                                        <input type="text" id="input-normal" name="val_{{$x}}" value="{{$value}}" class="form-control">
                                                    </div>
                                                </div>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach                                  
                                                <hr>
                                                <input type="hidden" name="lang" value="{{$id}}">
                                                <input type="hidden" name="ct" value="{{count($langs)}}">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Traducere
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
