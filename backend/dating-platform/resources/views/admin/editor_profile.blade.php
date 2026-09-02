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
                                <div class="card">
                                    <div class="card-header">
                                        <i class="fa fa-user"></i>
                                        <strong class="card-title pl-2">Profil</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="mx-auto d-block">
                                            <img class="rounded-circle mx-auto d-block" src="/storage/images/{{$user->profile_image()}}" alt="Card image cap">
                                            <h5 class="text-sm-center mt-2 mb-1">{{$user->name()}}</h5>
                                            @if($user->county != '' && $user->country != '')
                                            <div class="location text-sm-center">
                                                <i class="fas fa-map-marker"></i> {{$user->county}}, {{$user->country}}
                                            </div>
                                            @endif
                                            <div class="mt-2 mb-1 text-sm-center">
                                                <span class="role {{$user->role}}">{{$user->role}}</span>
                                            </div>
                                            @if($user->banned == 'yes')
                                            <div class="mt-2 mb-1 text-sm-center">
                                                <span class="role admin">Blocat</span>
                                            </div>
                                            @endif
                                        </div>
                                        <hr>
                                        <div class="card-text text-sm-center">
                                            <a target="_blank" href="/profile/{{$user->username}}">
                                                <i class="fas fa-chain pr-1"></i>Vezi profil
                                            </a>
                                        </div>
                                        <hr>
                                        <div class="row pl-4 mt-4">
                                                <div class="col-md-6">
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Username</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <p class="form-control-static">{{$user->username}}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Email</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <p class="form-control-static">{{$user->email}}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Data nasterii</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <p class="form-control-static">{{$user->birthday}}</p>
                                                        </div>
                                                    </div>                                                    
                                                </div>
                                                <div class="col-md-6">
                                                    <form method="POST" action="/admin/editors/update">
                                                    @csrf
                                                    <input type="hidden" name="user" value="{{$user->id}}">
                                                        <div class="row form-group">
                                                            <div class="col col-md-3">
                                                                <label class=" form-control-label">Rol</label>
                                                            </div>
                                                            <div class="col-12 col-md-9">
                                                                <select name="role" id="select" class="form-control">
                                                                    <option @if($user->role == 'admin') selected @endif value="admin">Administrator</option>
                                                                    <option @if($user->role == 'editor') selected @endif value="editor">Editor</option>
                                                                    <option @if($user->role == 'user') selected @endif value="user">Utilizator</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row form-group">
                                                            <div class="col col-md-3">
                                                                <label class=" form-control-label">Conturi pe site</label>
                                                            </div>
                                                            <div class="col-12 col-md-9">
                                                                <div class="tag-input">
                                                                    @foreach($user->getAccounts() as $acc)
                                                                    @if($acc->getUser()->id ?? false)
                                                                    <span class="acc-tag role user" data-id="{{$acc->getUser()->id}}">{{$acc->getUser()->name()}}<i class="fa fa-times" onclick="tag_delete({{$acc->getUser()->id}})"></i><input type="hidden" name="editor_accounts[]" value="{{$acc->getUser()->id}}"></span>
                                                                    @endif
                                                                    @endforeach
                                                                    <input type="text" id="tag-search-input" placeholder="Cauta" onkeyup="search_users(this,'editor_accounts', ex_tags);" data-to="tag-results" class="form-control">
                                                                </div>
                                                                <div id="tag-results"></div>
                                                            </div>
                                                        </div>
                                                        <div class="row form-group">
                                                                <div class="col col-md-3">
                                                                    <label required for="select" class=" form-control-label">Admin IP</label>
                                                                </div>
                                                                <div class="col-12 col-md-9">
                                                                    <input type="text" name="admin_ip" value="{{$user->admin_ip}}" class="form-control">
                                                                </div>
                                                        </div>
                                                        <div class="row form-group">
                                                                <div class="col col-md-3">
                                                                    <label required for="admin_reply_email" class=" form-control-label">Reply Email</label>
                                                                </div>
                                                                <div class="col-12 col-md-9">
                                                                @if($user->adminReplyEmail)
                                                                    <input type="email" name="admin_reply_email" value="{{$user->adminReplyEmail->email}}" class="form-control">
                                                                @else
                                                                    <input type="email" name="admin_reply_email" value="" class="form-control">
                                                                @endif
                                                                </div>
                                                        </div>

                                                        <div class="row form-group">
                                                                <div class="col col-md-3">
                                                                    <label required for="password" class=" form-control-label">Parola</label>
                                                                </div>
                                                                <div class="col-12 col-md-9">
                                                                    <input type="password" name="password" id="password" value="" class="form-control">
                                                                </div>
                                                        </div>
                                                        <div class="row form-group">
                                                            <div class="col col-md-12">
                                                                <div class="pull-right">
                                                                    <button type="submit" name="update" class="btn btn-primary btn-sm" value="update">
                                                                        <i class="fa fa-dot-circle-o"></i> Modifica
                                                                    </button>
                                                                    <button type="submit" name="all_profiles" class="btn btn-warning btn-sm" value="all_profiles">
                                                                        <i class="fa fa-dot-circle-o"></i> Adauga toate profilele
                                                                    </button>
                                                                    <button type="submit" name="block" class="btn btn-danger btn-sm" value="block">
                                                                        <i class="fa fa-ban"></i> Blocheaza utilizator
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
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
        <script type="text/javascript">
            var ex_tags = new Array("ID");
            @foreach($user->getAccounts() as $acc)
            @if($acc->getUser()->id ?? false)
            ex_tags.push({{$acc->getUser()->id}});
            @endif
            @endforeach
        </script>

@endsection
