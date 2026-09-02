<!-- Window-popup Update Header Photo -->

<div class="modal fade" id="update-header-photo" tabindex="-1" role="dialog" aria-labelledby="update-header-photo" aria-hidden="true">
    <div class="modal-dialog window-popup update-header-photo" role="document">
        <div class="modal-content">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>

            <div class="modal-header">
                <h6 class="title">{{l("Update Header Photo")}}</h6>
            </div>
                    <form enctype="multipart/form-data">
                        @csrf
                        <input id="upload" type="file"/>
                    </form>
            <div id="upload-options" class="modal-body">
                <div class="upload-margin">
                <a id="upload-link" href="#upload" class="upload-photo-item">
                    <svg class="olymp-computer-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-computer-icon"></use></svg>

                    <h6>{{l("Upload Photo")}}</h6>
                    <span>{{l("Browse your computer.")}}</span>
                    
                </a>

                <a id="existent_photos" href="#" class="upload-photo-item" data-toggle="modal" data-target="#choose-from-my-photo">

                    <svg class="olymp-photos-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-photos-icon"></use></svg>

                    <h6>{{l("Choose from My Photos")}}</h6>
                    <span>{{l("Choose from your uploaded photos")}}</span>
                </a>
            </div>
            <div id="upload-preview">
                <div id="show-preview"></div>
                <button id="upload-photo-button" class="btn btn-primary btn-lg">{{l("Upload Photo")}}</button>  
            </div>
            </div>
        </div>
    </div>
</div>


<!-- ... end Window-popup Update Header Photo -->

<!-- Window-popup Choose from my Photo -->
@if(Auth::id() == $user->id)
<div class="modal fade" id="choose-from-my-photo" tabindex="-1" role="dialog" aria-labelledby="choose-from-my-photo" aria-hidden="true">
    <div class="modal-dialog window-popup choose-from-my-photo" role="document">

        <div class="modal-content">
            <a href="#" class="close icon-close" data-dismiss="modal" aria-label="Close">
                <svg class="olymp-close-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-close-icon"></use></svg>
            </a>
            <div class="modal-header">
                <h6 class="title">{{l("Choose from My Photos")}}</h6>

                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home" role="tab" aria-expanded="true">
                            <svg class="olymp-photos-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-photos-icon"></use></svg>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="modal-body">
                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane active" id="choose-album-first" role="tabpanel" aria-expanded="true">
                        <div id="choose-photo-item-container" class="row margin-bottom-50"> 
                        @foreach($user->images->where('privacy', '')->take(12) as $image)
                        <div class="choose-photo-item" data-mh="choose-item">
                            <div class="checks">
                                <label class="custom-radio">
                                    <img src="/storage/images/{{$image->name}}" alt="photo">
                                    <input class="optionsRadios" type="checkbox" name="optionsRadios[]" value="{{$image->id}}" data-url="/storage/images/{{$image->name}}" data-role="{{$image->role}}">
                                    <span class="circle"></span><span class="check"></span>
                                </label>
                            </div>
                        </div>
                        @endforeach
                        </div>
                        @if($user->images->where('privacy', '')->count() > 12)
                        <div class="row">

                        <a href="#"  onclick="load_update(this);" class="btn btn-control btn-more margin-top-0"><svg class="olymp-three-dots-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-three-dots-icon"></use></svg></a></div>
                        @endif
                        <a href="#" onclick="modal_hide('choose-from-my-photo');" class="btn btn-secondary btn-lg btn--half-width">{{l("Cancel")}}</a>
                        <a id="choose-from-my-photo-submit" href="#" class="btn btn-primary btn-lg btn--half-width">{{l("Confirm Photo")}}</a>

                    </div>
                </div>
                <div class="tab-pane" id="choose-album-second" role="tabpanel" aria-expanded="false">
                        <div class="row">
                            @foreach($user->albums->take(12) as $album)
                        <div onclick="select_album(this);" data-id="{{$album->id}}" class="choose-photo-item" data-mh="choose-item">
                            @php $alb = $album->images()->latest()->first(); @endphp
                            @if($alb && $alb->pivot)
                            <figure>
                                <img src="/storage/images/{{$alb->name}}" alt="photo">
                                <figcaption>
                                    <a href="#">{{$album->name}}</a>
                                    <span>Last Added: {{$alb->pivot->created_at ? $alb->pivot->created_at->format('d/m/Y H:i') : '-'}}</span>
                                </figcaption>
                            </figure>
                            @endif
                        </div>
                        @endforeach
                        </div>
                            <a href="#" onclick="album_previous();" class="btn btn-secondary btn-lg btn--half-width">{{l("Previous Step")}}</a>
                            <a href="#" id="add-from-my-photo-submit" class="btn btn-primary disabled btn-lg btn--half-width">{{l("Save Changes")}}</a>
                        
                </div>
            </div>
        </div>

    </div>
</div>
@endif
<!-- ... end Window-popup Choose from my Photo -->
