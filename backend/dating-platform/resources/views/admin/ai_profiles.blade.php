@extends('admin.components.layout')
@section('content')
<div class="page-container">
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                @if(session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-robot"></i>AI Profiles
                            </h3>

                            <div class="card mb-4">
                                 <p class="text-muted small m-2">
                                        Active providers: <strong>{{ $aiSetting->liveAvatarProvider() === 'simli' ? 'Simli' : 'Tavus (CVI)' }}</strong> for live video chat,
                                        {{-- <strong>{{ ucfirst($aiSetting->avatarVideoProvider()) }}</strong> for async video replies. --}}
                                        Change these on the <a href="/admin/ai-settings">AI Settings</a> page.
                                    </p>
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Create New AI Profile</strong>
                                    <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#create-profile-form">
                                        <i class="fas fa-plus mr-3"></i>New Profile
                                    </button>
                                </div>
                                <div class="collapse" id="create-profile-form">
                                <div class="card-body">
                                   
                                    <form action="/admin/ai-profiles" method="POST" enctype="multipart/form-data" class="form-horizontal">
                                        @csrf
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Name</label></div>
                                            <div class="col-12 col-md-9"><input type="text" name="name" class="form-control" required></div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Voice ID (ElevenLabs)</label></div>
                                            <div class="col-12 col-md-9">
                                                @if(!empty($elevenLabsVoices))
                                                    <select name="voice_id" class="form-control" required>
                                                        <option value="">-- Select a voice --</option>
                                                        @foreach($elevenLabsVoices as $voiceId => $voiceName)
                                                            <option value="{{ $voiceId }}">{{ $voiceName }} ({{ $voiceId }})</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="text" name="voice_id" class="form-control" placeholder="Enter voice ID" required>
                                                @endif
                                            </div>
                                        </div>

                                        @if($aiSetting->avatarVideoProvider() === 'heygen')
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">HeyGen Talking Photo ID</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="text" name="heygen_talking_photo_id" class="form-control" placeholder="Optional: profile-level HeyGen talking photo ID">
                                                <small class="form-text text-muted">If set, this profile-specific ID overrides the global HeyGen talking photo default.</small>
                                            </div>
                                        </div>
                                        @endif

                                        @if($aiSetting->liveAvatarProvider() === 'tavus_cvi' || $aiSetting->avatarVideoProvider() === 'tavus')
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Tavus Replica ID</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="text" name="tavus_replica_id" class="form-control" placeholder="Tavus replica ID">
                                                <small class="form-text text-muted">Required for live video chat (Tavus CVI) and/or async video replies (Tavus batch). Create a Replica at tavus.io.</small>
                                            </div>
                                        </div>
                                        @endif

                                        @if($aiSetting->liveAvatarProvider() === 'simli')
                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Simli Face ID</label></div>
                                            <div class="col-12 col-md-9">
                                                <input type="text" name="simli_face_id" class="form-control" placeholder="Optional: Simli face ID for live sessions">
                                                <small class="form-text text-muted">Used for live video chat (Simli). Falls back to the global Simli face default if left empty. Create a Face at simli.com.</small>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Static Image Upload</label></div>
                                            <div class="col-12 col-md-9"><input type="file" name="static_image" class="form-control-file"></div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Or Static Image URL/Path</label></div>
                                            <div class="col-12 col-md-9"><input type="text" name="static_image_path" class="form-control" placeholder="https://... or ai-profiles/file.jpg"></div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">System Prompt</label></div>
                                            <div class="col-12 col-md-9"><textarea name="system_prompt" rows="5" class="form-control" required></textarea></div>
                                        </div>

                                        <div class="row form-group">
                                            <div class="col col-md-3"><label class="form-control-label">Active</label></div>
                                            <div class="col-12 col-md-9">
                                                <label class="au-checkbox">
                                                    <input type="checkbox" name="is_active" value="1" checked>
                                                    <span class="au-checkmark"></span>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="au-btn au-btn-icon au-btn--blue">
                                                <i class="zmdi zmdi-plus"></i>Create Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                </div>
                            </div>

                            <div class="table-responsive table-data">
                                <table class="table myTable">
                                    <thead>
                                        <tr>
                                            <td>ID</td>
                                            <td>Image</td>
                                            <td>Name</td>
                                            <td>Voice ID</td>
                                            <td>Active</td>
                                            <td>Created By</td>
                                            <td>Actions</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($profiles as $profile)
                                            @php
                                                $previewUrl = null;

                                                if (!empty($profile->static_image_path)) {
                                                    if (str_starts_with($profile->static_image_path, 'http://') || str_starts_with($profile->static_image_path, 'https://')) {
                                                        $previewUrl = $profile->static_image_path;
                                                    } else {
                                                        $previewBase = rtrim((string) config('filesystems.disks.ai_public.url', ''), '/');
                                                        $previewUrl = $previewBase . '/' . ltrim((string) $profile->static_image_path, '/');
                                                    }
                                                }
                                            @endphp
                                            <tr id="profile-row-{{ $profile->id }}">
                                                <td>{{ $profile->id }}</td>
                                                <td>
                                                    @if($previewUrl)
                                                        <img src="{{ $previewUrl }}" alt="{{ $profile->name }}" style="width:52px; height:52px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                                                    @else
                                                        <span class="text-muted">No image</span>
                                                    @endif
                                                </td>
                                                <td>{{ $profile->name }}</td>
                                                <td>{{ $profile->voice_id }}</td>
                                                <td>{{ $profile->is_active ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    @if($profile->createdByAdmin)
                                                        {{ $profile->createdByAdmin->firstname }} {{ $profile->createdByAdmin->lastname }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" type="button" data-toggle="collapse" data-target="#edit-profile-{{ $profile->id }}">Edit</button>

                                                    <form action="/admin/ai-profiles/{{ $profile->id }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this profile?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <tr class="collapse" id="edit-profile-{{ $profile->id }}">
                                                <td colspan="7" style="background-color: #f5f9ff;">
                                                    <form action="/admin/ai-profiles/{{ $profile->id }}" method="POST" enctype="multipart/form-data" class="form-horizontal p-3 border rounded bg-light">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Name</label></div>
                                                            <div class="col-12 col-md-10"><input type="text" name="name" class="form-control" value="{{ $profile->name }}" required></div>
                                                        </div>

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Voice ID</label></div>
                                                            <div class="col-12 col-md-10">
                                                                @if(!empty($elevenLabsVoices))
                                                                    <select name="voice_id" class="form-control" required>
                                                                        <option value="">-- Select a voice --</option>
                                                                        @foreach($elevenLabsVoices as $voiceId => $voiceName)
                                                                            <option value="{{ $voiceId }}" @if($profile->voice_id === $voiceId) selected @endif>{{ $voiceName }} ({{ $voiceId }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <input type="text" name="voice_id" class="form-control" value="{{ $profile->voice_id }}" required>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        @if($aiSetting->avatarVideoProvider() === 'heygen')
                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">HeyGen Talking Photo ID</label></div>
                                                            <div class="col-12 col-md-10">
                                                                <input type="text" name="heygen_talking_photo_id" class="form-control" value="{{ $profile->learning_snapshot['heygen_talking_photo_id'] ?? '' }}" placeholder="Optional: profile-level HeyGen talking photo ID">
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($aiSetting->liveAvatarProvider() === 'tavus_cvi' || $aiSetting->avatarVideoProvider() === 'tavus')
                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Tavus Replica ID</label></div>
                                                            <div class="col-12 col-md-10">
                                                                <input type="text" name="tavus_replica_id" class="form-control" value="{{ $profile->learning_snapshot['tavus_replica_id'] ?? '' }}" placeholder="Tavus replica ID">
                                                                <small class="form-text text-muted">Required for live video chat (Tavus CVI) and/or async video replies (Tavus batch).</small>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($aiSetting->liveAvatarProvider() === 'simli')
                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Simli Face ID</label></div>
                                                            <div class="col-12 col-md-10">
                                                                <input type="text" name="simli_face_id" class="form-control" value="{{ $profile->learning_snapshot['simli_face_id'] ?? '' }}" placeholder="Simli face ID for live sessions">
                                                                <small class="form-text text-muted">Used for live video chat (Simli). Falls back to the global Simli face default if left empty.</small>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">System Prompt</label></div>
                                                            <div class="col-12 col-md-10"><textarea name="system_prompt" rows="4" class="form-control" required>{{ $profile->system_prompt }}</textarea></div>
                                                        </div>

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Static Image Upload</label></div>
                                                            <div class="col-12 col-md-10"><input type="file" name="static_image" class="form-control-file"></div>
                                                        </div>

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Static Image URL/Path</label></div>
                                                            <div class="col-12 col-md-10"><input type="text" name="static_image_path" class="form-control" value="{{ $profile->static_image_path }}"></div>
                                                        </div>

                                                        <div class="row form-group">
                                                            <div class="col col-md-2"><label class="form-control-label">Active</label></div>
                                                            <div class="col-12 col-md-10">
                                                                <label class="au-checkbox">
                                                                    <input type="checkbox" name="is_active" value="1" @if($profile->is_active) checked @endif>
                                                                    <span class="au-checkmark"></span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="text-right">
                                                            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>

                                                    <form action="/admin/ai-profiles/{{ $profile->id }}/distill-style" method="POST" class="p-3 border rounded bg-white mt-3">
                                                        @csrf
                                                        <label class="form-control-label"><strong>Learn from a converting conversation</strong></label>
                                                        <p class="text-muted small mb-2">Pick one or more existing conversations that converted users well (Ctrl/Cmd+click for multiple), and/or paste extra transcript text below. Everything is combined and analyzed to update this profile's conversational style.</p>

                                                        @if(!empty($conversationOptions))
                                                            <select name="conversation_ids[]" class="form-control mb-2" multiple size="6">
                                                                @foreach($conversationOptions as $option)
                                                                    <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <p class="text-muted small">No logged conversations available yet — paste a transcript manually below.</p>
                                                        @endif

                                                        <textarea name="transcript" rows="4" class="form-control" placeholder="Optional: paste additional transcript text here (User: ...&#10;Assistant: ...)"></textarea>
                                                        <div class="text-right mt-2">
                                                            <button type="submit" class="btn btn-sm btn-primary">Distill style from selection</button>
                                                        </div>
                                                        @if(!empty($profile->learning_snapshot['style_guide']))
                                                            <div class="mt-2 small">
                                                                <strong>Current style guide</strong> (updated {{ $profile->learning_snapshot['style_guide_updated_at'] ?? 'unknown' }}):
                                                                <div class="p-2 bg-light border rounded" style="white-space:pre-wrap;">{{ $profile->learning_snapshot['style_guide'] }}</div>
                                                            </div>
                                                        @endif
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">No AI profiles yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $profiles->links() }}
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
</div>

<style>
    tr.ai-profile-row--editing {
        background-color: #eef6ff !important;
        box-shadow: inset 3px 0 0 #4a90d9;
    }
</style>
<script>
    window.addEventListener('load', function () {
        jQuery('[id^="edit-profile-"]')
            .on('show.bs.collapse', function () {
                jQuery('#profile-row-' + this.id.replace('edit-profile-', '')).addClass('ai-profile-row--editing');
            })
            .on('hide.bs.collapse', function () {
                jQuery('#profile-row-' + this.id.replace('edit-profile-', '')).removeClass('ai-profile-row--editing');
            });
    });
</script>
@endsection
