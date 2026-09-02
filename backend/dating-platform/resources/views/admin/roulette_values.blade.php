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
                                    <h3 class="title-3 m-b-30"><i class="fas fa-percent"></i>

                                        {{l("Roulette options")}}
                                    </h3>
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
                                                    {{l('Add')}}
                                                    <strong>{{l('roulette value')}}</strong>
                                                </div>
                                                <div class="card-body card-block">
                                                    <form action="{{route('admin_roulette_add_value')}}" method="post" class="form-horizontal">
                                                        @csrf
                                                        @php
                                                            $packs = App\Pack::where('name', '!=', 'Trial')->where('custom', '!=', 1)->get();
                                                        @endphp
                                                        <div class="row form-group">
                                                            <div class="col col-sm-5">
                                                                <label for="input-normal" class=" form-control-label">
                                                                    {{l('Package')}}
                                                                </label>
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
                                                                <label for="input-normal" class=" form-control-label">
                                                                    {{l('Discount')}} %:
                                                                </label>
                                                            </div>
                                                            <div class="col col-sm-6">
                                                                <input type="number" id="input-normal" name="value" class="form-control" min="1" max="100">
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-dot-circle-o"></i>
                                                                {{l('Add')}}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3 class="title-2 m-b-30 m-l-30">
                                        {{l('Values')}}
                                    </h3>
                                    <hr>
                                    <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <td>#</td>
                                                <td>
                                                    {{l('Package')}}
                                                </td>
                                                <td>
                                                    {{l('Discount')}} %:
                                                </td>
                                                <td></td>
                                            </tr>
                                            </thead>
                                            <tbody id="user-search">
                                            @php
                                                $x = 1;
                                            @endphp
                                            @foreach($rouletteValues as $rouletteValue)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$rouletteValue->package()->name}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$rouletteValue->value}} %</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <a href="{{route('admin_roulette_delete_value', ['id' => $rouletteValue->id])}}">
                                                                {{l('Delete')}}
                                                            </a>
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

{{--        <script type="text/javascript">--}}
{{--            var ex_tags = new Array("ID");--}}
{{--            @if(isset($_GET['users']) && $_GET['users'] == 'all')--}}
{{--            @foreach($select_users as $acc)--}}
{{--            ex_tags.push({{$acc->id}});--}}
{{--            @endforeach--}}
{{--            @endif--}}
{{--        </script>--}}


@endsection
