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
                                        <h3 class="title-3 m-b-30"><i class="far fa-envelope"></i>Newsletter</h3>
                                        @php
                                            $newsletter_tracking = App\Settings::where('id',26)->firstOrFail();
                                        @endphp
                                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'editor' && $newsletter_tracking->value == 'yes')
                                        <a href="/admin/emailtracking" class="add-admin au-btn au-btn-icon au-btn--blue">
                                        <i class="fas fa-eye"></i>Tracking</a>
                                        @endif
                                    </div>
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-xs-12 col-md-12">
                                                    <form method="POST" action="/admin/send/newsletter">
                                                        @csrf
                                                            <div class="row form-group">
                                                            <div class="col col-md-2">
                                                                <label class=" form-control-label">Selecteaza destinatari</label>
                                                            </div>
                                                            <div class="col-12 col-md-10">
                                                                <div class="tag-input">
                                                                    <input type="text" id="tag-search-input" placeholder="Cauta" onkeyup="search_users(this,'newsletter_dest', ex_tags);" data-to="tag-results" class="form-control">
                                                                </div>
                                                                <div id="tag-results"></div>
                                                            </div>
                                                        </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-2">
                                                                    <label for="input-normal" class=" form-control-label">Selecteaza expeditor</label>
                                                                </div>
                                                                <div class="col col-sm-10">
                                                                    <select name="from" id="mail_from_user" class="form-control">
                                                                        <option value="{{Auth::user()->id}}">{{Auth::user()->name()}}</option>
                                                                        @foreach($send_from as $adm_user)
                                                                            @if($adm_user->getUserRelationship)
                                                                                <option value="{{$adm_user->getUserRelationship->id}}">{{$adm_user->getUserRelationship->name()}}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-2">
                                                                    <label for="input-normal" class=" form-control-label">Subiect</label>
                                                                </div>
                                                                <div class="col col-sm-10">
                                                                    <input name="header" id="header" type="text" placeholder="Subiect" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="row form-group">
                                                                <div class="col col-sm-12">
                                                                    <span>Variabile speciale:</span><br>
                                                                    <b>_nume_</b> -> Pentru a insera numele destinatarului.
                                                                </div>
                                                                <div class="col col-sm-12">
                                                                    <textarea name="mail_message" id="mail_message" rows="9" placeholder="Mesaj" class="form-control"></textarea>
                                                                </div>
                                                            </div>
                                                            @if($users)
                                                            <input type="hidden" name="option" value="users">
                                                            @else
                                                            <input type="hidden" name="option" value="clients">
                                                            @endif
                                                            <div class="row form-group">
                                                                <div class="col col-sm-12">
                                                                    <button type="submit" name="submit" value="send_dest" class="btn btn-warning">Trimite</button>
                                                                    <button type="submit" name="submit" value="send_all" class="btn btn-info">Trimite la toti</button>
                                                                </div>
                                                            </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <hr>
                                        <h3 class="title-2 m-b-30 m-l-30"></i>Destinatari @if($users != '') Utilizatori @else Clienti @endif</h3>
                                        <hr>
                                        <div class="row">
                                            <div class="col-xs-12 col-md-12">
                                                <a class="btn btn-primary @if($users != '') active @endif" href="/admin/newsletter/users">Utilizatori inregistrati @if($users != '') (selectat) @endif</a>
                                                <a class="btn btn-info @if($users != 'clients') active @endif" href="/admin/newsletter/clients">Clienti neinregistrati @if($clients != '') (selectat) @endif</a>
                                            </div>    
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-md-12">
                                                    <button class="btn btn-warning" id="newsletter_select_all">Selecteaza tot</button>
                                            </div>
                                            @if(is_array($all_users_names))
                                            @foreach($all_users_names as $u)
                                            <input class="all_users_select" data-id='{{$u[0]}}' type="hidden" value="{{$u[1]}}">
                                            @endforeach
                                            @endif
                                            @if(is_array($all_clients_names))
                                            @foreach($all_clients_names as $u)
                                            <input class="all_clients_select" data-id='{{$u[0]}}' type="hidden" value="{{$u[1]}}">
                                            @endforeach
                                            @endif     
                                        </div>
                                        <div class="table-responsive table-data">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <td>#</td>
                                                    <td>Nume</td>
                                                    <td>Pachet</td>
                                                    <td>Credite</td>
                                                    <td>Data creare</td>
                                                    <td></td>
                                                </tr>
                                            </thead>
                                            <tbody id="user-search">
                                                @php
                                                $x = 1;
                                                @endphp
                                                @if($users)
                                                @foreach($users as $user)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$user->name()}}</h6>
                                                            <span>
                                                                <a href="#">{{$user->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($user->package())
                                                        <span class="role user">{{$user->package()->name}}</span>
                                                        @else
                                                        <span class="role user">Nu</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="role user">{{$user->credits}}</span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$user->created_at->format('d/m/Y')}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a class="add_dest" onclick="add_dest({{$user->id}},event);" data-id="{{$user->id}}" data-name="{{$user->name()}}" href="#"><i class="fas fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                                @endif
                                                @if($clients)
                                                @foreach($clients as $user)
                                                <tr>
                                                    <td>{{$x}}</td>
                                                    <td>
                                                        <div class="table-data__info">
                                                            <h6>{{$user->name}}</h6>
                                                            <span>
                                                                <a href="#">{{$user->email}}</a>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="role user">Nu</span>
                                                    </td>
                                                    <td>
                                                        <span class="role user">0</span>
                                                    </td>
                                                    <td>
                                                        <span>
                                                            <h6>{{$user->created_at->format('d/m/Y')}}</h6>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a class="add_dest" onclick="add_dest({{$user->id}},event);" data-id="{{$user->id}}" data-name="{{$user->name}}" href="#"><i class="fas fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                @php
                                                $x++;
                                                @endphp
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <div id="newsletter_pages"> 
                                        @if($clients)
                                            {{$clients->links()}}
                                        @endif
                                        @if($users)
                                            {{$users->links()}}
                                        @endif
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
        <script type="text/javascript">
            var ex_tags = new Array("ID");
        </script>

@endsection
