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
                                        <strong>Pachete</strong> Adaugare
                                    </div>
                                    @php
                                    if($pack == ''){
                                        $add = true;
                                    }else{
                                        $add = false;
                                    }
                                    @endphp
                                     <form action="" method="post" class="form-horizontal">
                                    <div class="card-body card-block">
                                       
                                            @csrf
                                            <div class="row">
                                                <div class="col-12 col-md-12">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="hf-email" class=" form-control-label">Nume</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="text" name="name" required placeholder="Nume" value="@if(!$add){{$pack->name}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Likes</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="likes" id="select" class="form-control">
                                                                <option @if(!$add && $pack->likes == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->likes == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Newsfeed</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="newsfeed" id="select" class="form-control">
                                                                <option @if(!$add && $pack->newsfeed == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->newsfeed == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Albume</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="albums" id="select" class="form-control">
                                                                <option @if(!$add && $pack->albums == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->albums == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Imagini</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="images" id="select" class="form-control">
                                                                <option @if(!$add && $pack->images == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->images == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Prieteni</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="friends" id="select" class="form-control">
                                                                <option @if(!$add && $pack->friends == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->friends == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Chat</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="chat" id="select" class="form-control">
                                                                <option @if(!$add && $pack->chat == 'true') selected @endif value="true">Da</option>
                                                                <option @if(!$add && $pack->chat == 'false') selected @endif value="false">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Credite</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input required type="number" name="credits" placeholder="12000"  value="@if(!$add){{$pack->credits}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label required for="select" class=" form-control-label">Pret</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input required type="number" min="0" name="price" value="@if(!$add){{$pack->price}}@endif" placeholder="30" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Moneda</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="currency" id="select" class="form-control">
                                                                <option @if(!$add && $pack->currency == 'EUR') selected @endif value="EUR">Euro</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Durata (in zile)</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="number" name="duration" placeholder="15" value="@if(!$add){{$pack->duration}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Evidentiat</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="featured" id="select" class="form-control">
                                                                <option @if(!$add && $pack->featured == '1') selected @endif value="1">Da</option>
                                                                <option @if(!$add && $pack->featured == '0') selected @endif value="0">Nu</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Tip</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="type" id="select" class="form-control">
                                                                <option @if(!$add && $pack->type == 'subscription') selected @endif value="subscription">Abonament</option>
                                                                <option @if(!$add && $pack->type == 'credits') selected @endif value="credits">Credite</option>
                                                                <option @if(!$add && $pack->type == 'subscription-credits') selected @endif value="subscription-credits">Abonament cu credite</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Pret pe unitate</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="text" name="front_price" placeholder="0" value="@if(!$add){{$pack->front_price}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Link de plata</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="text" name="payment_link" placeholder="" value="@if(!$add){{$pack->payment_link}}@endif" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Blurare video chat AI (%)</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <input type="range" min="0" max="100" name="video_blur_percent" id="video-blur-percent" class="form-control-range"
                                                                   value="{{ $add ? 100 : ($pack->video_blur_percent ?? 100) }}">
                                                            <small class="form-text text-muted">0 = imagine complet clară, <span class="bold" style="font-size: medium"><span id="video-blur-percent-value">{{ $add ? 100 : ($pack->video_blur_percent ?? 100) }}</span>% </span> = puterea blur-ului actual</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-group">
                                                        <div class="col col-md-12">
                                                            <label for="select" class=" form-control-label">Sunet AI video chat</label>
                                                        </div>
                                                        <div class="col-12 col-md-12">
                                                            <select required name="audio_muted" id="select" class="form-control">
                                                                @php $muted = $add ? true : (bool) ($pack->audio_muted ?? true); @endphp
                                                                <option value="1" @if($muted) selected @endif>Blocat (fără sunet)</option>
                                                                <option value="0" @if(!$muted) selected @endif>Activ (cu sunet)</option>
                                                            </select>
                                                            <small class="form-text text-muted">Video-ul AI se mișcă/vorbește vizual oricum — aici doar sunetul se blochează sau nu.</small>
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
                                        <a href="/admin/delete/packages/{{$pack->id}}" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Delete
                                        </a>
                                        @else
                                        <a href="/admin/packages/add" class="btn btn-danger btn-sm">
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

        <script>
        (function () {
            var slider = document.getElementById('video-blur-percent');
            var value = document.getElementById('video-blur-percent-value');
            if (!slider || !value) { return; }

            slider.addEventListener('input', function () {
                value.textContent = slider.value;
            });
        })();
        </script>
@endsection
