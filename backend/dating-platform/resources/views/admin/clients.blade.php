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
                                        <i class="zmdi zmdi-account-calendar"></i>{{$on_page}}</h3>
                                        <form class="form-header" action="" method="POST" _lpchecked="1">
                                            <input id="search-admin" onKeyup="search_users(this,'clients');" data-to="admin-search" class="au-input au-input--xl" type="text" name="search" placeholder="Cauta clienti">
                                        </form>
                                        <a href="/admin/clients/add" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Adauga</a>
                                        
                                        
                                        <a href="/admin/clients/add/multiple" class="add-admin add-admin-second au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Adauga mai multe</a>
                                        
                                        <label class="au-btn fa fa-trash  btn btn-danger btn-sm"  style="height:45px;" id="file-upload"><input hidden="" type="file" id="file_upload" name="file_upload"> <i data-feather="upload">   </i>Sterge clienti (CSV)</label>
                                        {{-- <label class="au-btn au-btn-icon au-btn--blue" id="file-upload"><input hidden="" type="file" id="file_upload" name="file_upload[]" multiple="multiple"> <i data-feather="upload">   </i>CSV stergere </label> --}}
                                        {{-- <a href="/admin/clients/add/multiple" class="au-btn au-btn-icon au-btn--blue">
                                        <i class="zmdi zmdi-plus"></i>Incarca fisier si sterge</a> --}}
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Tara</td>
                                                    <td>Regiune</td>
                                                    <td>Oras</td>
                                                    <td>Sursa</td>
                                                    @if(Auth::user()->role == 'admin')
                                                    <td>Adaugat de</td>
                                                    @endif
                                                    <td>Inregistrat</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="admin-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($clients as $client)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$client->name}}</h6>
                                                            <span>
                                                                <a href="#">{{$client->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span>{{$client->country}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$client->region}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$client->city}}</span>
                                                    </td>
                                                    <td>
                                                        <span>{{$client->source}}</span>
                                                    </td>
                                                    @if(Auth::user()->role == 'admin')
                                                    <td>
                                                        <span>{{$client->admin_name()}}</span>
                                                    </td>
                                                    @endif
                                                    <td>
                                                        @if($client->isRegistered()) <i class="fas fa-check"></i> @else <i class="fas fa-times"></i> @endif
                                                    </td>
                                                    <td>
                                                        <span>{{$client->created_at->format('d/m/Y H:i')}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="more">
                                                            <a href="/admin/client/edit/{{$client->id}}"><i class="zmdi zmdi-more"></i></a>
                                                        </span>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                        </table>
                                        {{$clients->links()}}
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


@section('js-vupload-script')
<script language="Javascript" type="text/javascript">
$(document).ready(function() {
    $('#file_upload').change(function() {
        var form_data = new FormData();
        /*var totalfiles = document.getElementById('file_upload').files.length;
        for (var index = 0; index < totalfiles; index++) {
            var f = document.getElementById('file_upload').files[index];
            form_data.append("files[]", document.getElementById('file_upload').files[index]);
        }*/
        f = document.getElementById('file_upload').files[0];
        if(typeof f === "undefined")
            return;
        form_data.append("file", f);
        
        form_data.append("test", "xxx");
        form_data.append('_token', "{{ csrf_token() }}");
        $.ajax({
            url: "{{route('admin_client_remove_by_emails')}}",
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: form_data,
            dataType: 'json',
            contentType: false,
            processData: false,
            async: true,
            success: function(data) {
                $("#file_upload").val(null);
                if(data.success==true)
                    alert("Au fost sterse "+data.data+' clienti.'+(data.data>0 ? '\nPagina se va reincarca.' : ''));
                else
                    alert("Fisierul nu contine data relevante");
                if(data.data)
                    location.reload();
            },
            error: function(xhr, status, error) {
                alert('Something went wrong, please try again!!!');
            }
        });
        return false;
    });
    
});
</script>
@endsection
