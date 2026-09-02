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
                                        <h3 class="title-3 m-b-30"><i class="fas fa-percent"></i>Reduceri</h3>
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
                                            <div class="col-xs-12 col-md-12">
                                                <div class="card trans">
                                                    <div class="card-header">
                                                        Adaugare
                                                        <strong>Discount</strong>
                                                    </div>
                                                    <div class="card-body card-block">
                                                        <form action="/admin/discounts/add" method="post" class="form-horizontal">
                                                            @csrf
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Utilizator</label>
                                                                    <a href="/admin/discounts?users=all">Selecteaza tot</a>
                                                                </div>
                                                                @if(isset($_GET['users']) && $_GET['users'] == 'all')
                                                                    @php
                                                                    $select_users = App\User::select(['firstname', 'lastname', 'id'])->where('role', 'user')->where('gender', 'male')->get();
                                                                    @endphp
                                                                @endif
                                                                <div class="col col-sm-6">
                                                                    <div class="tag-input">
                                                                    @if(isset($_GET['users']) && $_GET['users'] == 'all')
                                                                    @foreach($select_users as $acc)
                                                                    <span class="acc-tag role user" data-id="{{$acc->id}}">{{$acc->name()}}<i class="fa fa-times" onclick="tag_delete({{$acc->id}})"></i><input type="hidden" name="newsletter_dest[]" value="{{$acc->id}}"></span>
                                                                    @endforeach
                                                                    @endif
                                                                    <input type="text" id="tag-search-input" placeholder="Cauta" onkeyup="search_users(this,'newsletter_dest', ex_tags);" data-to="tag-results" class="form-control">
                                                                    </div>
                                                                    <div id="tag-results"></div>
                                                                </div>
                                                            </div>
                                                            @php
                                                                $packs = App\Pack::where('name', '!=', 'Trial')->where('custom', '!=', 1)->get();
                                                            @endphp
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Pachet</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <select name="pack_id" id="select" class="form-control">
                                                                        @foreach($packs as $pack)
                                                                            <option value="{{$pack->id}}">{{$pack->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Valoare discount %:</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <input type="number" id="input-normal" name="value" class="form-control" min="1" max="100">
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-5">
                                                                    <label for="input-normal" class=" form-control-label">Data incheierii</label>
                                                                </div>
                                                                <div class="col col-sm-6">
                                                                    <input type="datetime-local" id="input-normal" name="ending_at" class="form-control">
                                                                </div>
                                                            </div>                                                     
                                                        
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa fa-dot-circle-o"></i> Adauga
                                                        </button>
                                                        <a href="/admin/discounts" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-ban"></i> Reseteaza
                                                        </a>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <h3 class="title-2 m-b-30 m-l-30"></i>Reduceri existente</h3>
                                        <hr>
                                        <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Utilizator</td>
                                                    <td>Reducere</td>
                                                    <td>Pachet</td>
                                                    <td>Data creare</td>
                                                    <td>Data expirare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @foreach($discounts as $discount)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$discount->user() ? $discount->user()->name() : ''}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$discount->value}} %</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$discount->package()->name}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$discount->created_at}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$discount->ending_at}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <a href="/admin/discounts/delete/{{$discount->id}}">Sterge</a>
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

        <script type="text/javascript">
            var ex_tags = new Array("ID");
            @if(isset($_GET['users']) && $_GET['users'] == 'all')
            @foreach($select_users as $acc)
            ex_tags.push({{$acc->id}});
            @endforeach
            @endif
        </script>


@endsection
