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
                                        <img class="rounded-circle mx-auto d-block"
                                             src="/storage/images/{{$user->profile_image()}}" alt="Card image cap">
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
                                                    <label class=" form-control-label">Sex</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->gender}}</p>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label">Telefon</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->phone}}</p>
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
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label">Job</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->job}}</p>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label">Status</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->social_status}}</p>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col col-md-12">
                                                    <div class="pull-left">
                                                        <a href="/admin/user/delete/{{$user->id}}"
                                                           class="btn btn-danger btn-sm">
                                                            <i class="fa fa-ban"></i> Sterge utilizator
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
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
                                                    <label class=" form-control-label">Tara</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->country}}</p>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label">Oras</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->city}}</p>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label">Credite</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static">{{$user->credits}}</p>
                                                </div>
                                            </div>
                                            @if($user->package())
                                                <div class="row form-group">
                                                    <div class="col col-md-3">
                                                        <label class=" form-control-label">Pachet detinut</label>
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <p class="form-control-static">{{$user->package()->name}}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <form method="POST" action="/admin/users/update"
                                                  enctype="multipart/form-data">
                                                @csrf

                                                <input type="hidden" name="user" value="{{$user->id}}">
                                                @if((Auth::user()->role == 'editor' ||  Auth::user()->isAdmin()) && $user->gender=='female' && \App\WebAccount::where(['admin_id'=>Auth::user()->id, 'user_id'=>$user->id])->count())
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Add video</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            @if($user->video!='' && \File::exists(public_path('videos').'/'.$user->video))
                                                                <video width="320" height="240" controls>
                                                                    <source src="{{URL::asset("/videos/$user->video")}}"
                                                                            type="video/mp4">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                                <br/>

                                                                <label class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="video-delete" value="1"
                                                                           id="video-delete"
                                                                           class="custom-control-input">
                                                                    <span
                                                                        class="custom-control-label">Remove video</span>
                                                                </label>
                                                            @else <input type="file" name="video" id="video"> @endif

                                                            @if($errors->has('video'))
                                                                @foreach($errors->get('video') as $error)
                                                                    <div class="text-danger">{{ $error }}</div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                @if(Auth::user()->role == 'admin')
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Rol</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <select name="role" id="select" class="form-control">
                                                                <option @if($user->role == 'admin') selected
                                                                        @endif value="admin">Administrator
                                                                </option>
                                                                <option @if($user->role == 'editor') selected
                                                                        @endif value="editor">Editor
                                                                </option>
                                                                <option @if($user->role == 'user') selected
                                                                        @endif value="user">Utilizator
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Schimbare sex</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <select name="gender" id="select" class="form-control">
                                                                <option @if($user->gender == 'male') selected
                                                                        @endif value="male">Barbat
                                                                </option>
                                                                <option @if($user->gender == 'female') selected
                                                                        @endif value="female">Femeie
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Permisie Discount</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <select name="discount" id="select" class="form-control">
                                                                <option @if($user->discount == '1') selected
                                                                        @endif value="1">Da
                                                                </option>
                                                                <option @if($user->discount == '0') selected
                                                                        @endif value="0">Nu
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="row form-group">
                                                    <div class="col col-md-3">
                                                        <label class=" form-control-label">Pachet</label>
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <select name="pack" id="select" class="form-control">
                                                            <option @if(!$user->package()) selected @endif value="no">
                                                                Fara pachet
                                                            </option>
                                                            @foreach($packages as $pack)
                                                                <option
                                                                    @if($user->package() && $user->package()->id == $pack->id) selected
                                                                    @endif value="{{$pack->id}}">{{$pack->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <div class="col col-md-3">
                                                        <label class=" form-control-label">Credite</label>
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <input type="text" name="credits" value="{{$user->credits}}"
                                                               class="form-control">
                                                    </div>
                                                </div>
                                                @if($user->gender == 'female')
                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Mesaj de
                                                                intampinare</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <input type="text" name="welcome_message"
                                                                   @if($user->welcomeMessage()) value="{{$user->welcomeMessage()}}"
                                                                   @endif  class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Raspuns automat AI</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <select name="ai_enabled" class="form-control">
                                                                <option value="1" @if($user->ai_enabled) selected @endif>Activ</option>
                                                                <option value="0" @if(!$user->ai_enabled) selected @endif>Inactiv</option>
                                                            </select>
                                                            <small class="form-text text-muted">Cand e activ, AI-ul raspunde automat la mesajele primite de la barbati, pana cand un admin/editor preia manual conversatia.</small>
                                                        </div>
                                                    </div>

                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Persona AI (optional)</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <textarea name="ai_system_prompt" rows="4" class="form-control" placeholder="Lasa gol pentru un profil generat automat din datele contului (nume, varsta, oras, job, descriere).">{{ $user->ai_system_prompt }}</textarea>
                                                            <small class="form-text text-muted">Descrie personalitatea, tonul si stilul de a vorbi al profilului - AI-ul va raspunde exact in acest mod. <br>Exemplu: <em>"Esti o femeie de 28 de ani din Cluj-Napoca, lucrezi ca designer grafic. Esti calda, jucausa si cu adevarat curioasa despre persoana cu care vorbesti. Iti plac calatoriile, cafeaua de dimineata si muzica indie. Raspunde natural, cu propozitii scurte, ca intr-o conversatie reala de chat, nu formal."</em></small>
                                                        </div>
                                                    </div>

                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Simli Face ID</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            <input type="text" name="simli_face_id" value="{{ $user->simli_face_id }}" class="form-control" placeholder="ex: 8f3b2c1a-..." autocomplete="off">
                                                            <small class="form-text text-muted">Necesar pentru butonul de apel video live cu AI din chat. Se creeaza manual din poza profilului in dashboard-ul Simli, apoi se lipeste aici ID-ul rezultat. Fara acest ID, butonul de apel video nu apare pentru acest profil.</small>
                                                        </div>
                                                    </div>

                                                    <div class="row form-group">
                                                        <div class="col col-md-3">
                                                            <label class=" form-control-label">Simli Voice ID (optional)</label>
                                                        </div>
                                                        <div class="col-12 col-md-9">
                                                            @if(!empty($elevenLabsVoices))
                                                                <select name="simli_voice_id" class="form-control">
                                                                    <option value="">-- Vocea implicita --</option>
                                                                    @foreach($elevenLabsVoices as $voiceId => $voiceName)
                                                                        <option value="{{ $voiceId }}" @if($user->simli_voice_id === $voiceId) selected @endif>{{ $voiceName }} ({{ $voiceId }})</option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <input type="text" name="simli_voice_id" value="{{ $user->simli_voice_id }}" class="form-control" placeholder="Lasa gol pentru vocea implicita" autocomplete="off">
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="row form-group">
                                                    <div class="col col-md-3">
                                                        <label class="form-control-label">Parola</label>
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <input type="password" name="password" value=""
                                                               class="form-control" autocomplete="new-password">
                                                    </div>
                                                </div>

                                                <div class="row form-group">
                                                    <div class="col col-md-3">
                                                        <label
                                                            class="form-control-label">{{l("Profile background image")}}</label>
                                                    </div>
                                                    <div class="col-12 col-md-9">
                                                        <input type="file" name="background_image"
                                                               class="form-control-file">
                                                    </div>
                                                </div>

                                                <div class="row form-group">
                                                    <div class="col col-md-12">
                                                        <div class="pull-right">
                                                            <button type="submit" name="update"
                                                                    class="btn btn-primary btn-sm" value="update">
                                                                <i class="fa fa-dot-circle-o"></i> Modifica
                                                            </button>
                                                            @if($user->banned == 'no')
                                                                <button type="submit" name="block"
                                                                        class="btn btn-danger btn-sm" value="block">
                                                                    <i class="fa fa-ban"></i> Blocheaza utilizator
                                                                </button>
                                                            @else
                                                                <button type="submit" name="block"
                                                                        class="btn btn-success btn-sm" value="unblock">
                                                                    <i class="fas fa-check"></i> Delocheaza utilizator
                                                                </button>
                                                            @endif
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

@endsection

@section('js-vupload-script')
    <script>
        $('#video').dropify(
            {
                allowedFileExtensions: ["mp4", "mkv", "mpg", "mpeg", "avi", "wmv", "mov"],
                maxFileSize: '10M',
                messages: {
                    'default': 'Trageți un fișier aici sau faceți clic pentru a adauga',
                    'replace': 'Trageți un fișier aici sau faceți clic pentru a schimba',
                    'remove': 'Sterge',
                    'error': 'A aparut o eroare!'
                },
                error: {
                    'fileSize': 'Dimensiunea maximă permisă pentru video este 10 MB.',
                    'fileExtension': 'Fisierele permise pentru video sunt: mp4, mkv, mpg, mpeg, avi, wmv, mov.',
                }
            });
    </script>
@endsection
