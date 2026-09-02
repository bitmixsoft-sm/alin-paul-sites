@extends('admin.components.layout')
@section('content')
<script src="/admin_assets/vendor/jquery-3.2.1.min.js"></script>
 <script src="https://cdn.tiny.cloud/1/giw1gn161y0hc27r12dnpertld1oopy15nd54dugxeew9ogm/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
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
                                        <div id="error"></div>
                                        <form class="form-horizontal" action="" method="POST">
                                            @csrf
                                            <div class="row form-group">
                                                    <div class="col col-sm-5">
                                                        <label for="input-normal" class=" form-control-label">Limba paginii</label>
                                                    </div>
                                                    <div class="col col-sm-6">
                                                        @php

                                                        $langs = App\Lang::get();

                                                        @endphp
                                                        <select required id="lang" name="lang" id="select" class="form-control">
                                                                @foreach($langs as $lang)
                                                                <option @if($page != '' && $page->lang==$lang->code) selected @endif value="{{$lang->code}}">{{$lang->name}}</option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            <div class="row form-group">
                                                    <div class="col col-sm-5">
                                                        <label for="input-normal" class=" form-control-label">Numele paginii</label>
                                                    </div>
                                                    <div class="col col-sm-6">
                                                        <input type="hidden" id="id" class="form-control" value="@if($page != ''){{$page->id}}@endif">
                                                        <input type="text" id="name" name="val_0" placeholder="Numele paginii" value="@if($page != ''){{$page->name}}@endif" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <div class="col col-sm-5">
                                                        <label for="input-normal" class=" form-control-label">Link pagina</label>
                                                    </div>
                                                    <div class="col col-sm-6">
                                                        <input type="text" id="route" name="val_0" placeholder="numele-paginii-cu-liniuta" value="@if($page != ''){{$page->route}}@endif" class="form-control">
                                                    </div>
                                                </div>
                                        </form>
                                        <div id="editor">@if($page != ''){!! $page->content !!}@endif</div>
                                            <div class="row" style="margin-top:30px;">
                                                <div class="col col-md-12">
                                                    <div class="pull-right">
                                                        <button id="update" type="submit" name="update" class="btn btn-primary btn-sm" value="update">
                                                            <i class="fa fa-dot-circle-o"></i> Modifica
                                                        </button>
                                                        @if($page != '')
                                                        <a href="/admin/cms/delete/{{$page->id}}" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-ban"></i> Delete
                                                        </a>
                                                        @endif
                                                    </div>
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
        </div>
        <script type="text/javascript">
                tinymce.init({
                    selector: '#editor',
                    menubar: 'file edit view insert format tools table help',
                    plugins: 'link lists code table',
                    toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink | code',
                    toolbar_mode: 'wrap'
                });

                $('#update').click(function () {
                    var content_value = tinymce.get('editor').getContent();
                    var name = $('#name').val();
                    var id = $('#id').val();
                    var lang = $('#lang').val();
                    var route = $('#route').val();
                    var CSRF_TOKEN = $('input[name="_token"]').val();

                    $.ajax({
                        url: '/admin/cms/add',
                        type: 'POST',
                        data: {
                            _token: CSRF_TOKEN,
                            name: name,
                            id: id,
                            route: route,
                            lang: lang,
                            content_value: content_value
                        },
                        dataType: 'JSON',
                        success: function (data) {
                            if (data.deny) {
                                $('#error').html('<div class="sufee-alert alert with-close alert-danger alert-dismissible fade show"><span class="badge badge-pill badge-danger">Eroare</span> ' + data.deny + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>');
                            } else {
                                window.location.href = data.route;
                            }
                        }
                    });
                });
        </script>

@endsection
