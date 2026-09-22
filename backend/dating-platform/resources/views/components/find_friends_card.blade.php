{{-- One Find Friends user card - the single source of truth for find_friends.blade.php's initial
     render AND FindFriendsController::search() ("load more"/search) AND getFindFriendsUser() (a
     user coming online), so all three always produce identical markup. Those AJAX paths used to
     hand-build their own HTML strings, which drifted from this structure (no .find_friends_item
     wrapper, no .friend-actions, no hover video, no binder back face) - every theme's CSS targets
     .find_friends_item, so under Bloom (masonry) appended cards rendered as narrow unstyled strips.
     Expects: $user, $activeTheme, $isRouletteSlot (bool); optional $forceOnline (bool). --}}
<div data-user-id="{{$user->id}}" class="col col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 find_friends_item">
    @php
        // Priced-photo paywall (client's request, 2026-09-21/22) - found live, 2026-09-22: this
        // card background is whichever photo happens to be this user's FIRST uploaded image
        // (any role, not specifically her designated profile picture - $user->images->take(1),
        // unordered by role), so a priced gallery photo could end up as the full-size card
        // background here, bypassing the paywall everywhere else already handles correctly.
        // displayName() applies the same lock/blur rule as everywhere else instead of the raw
        // filename.
        $cardBgImage = $user->images->first();
    @endphp
    <div @if (!$isRouletteSlot) class="ui-block" @endif data-mh="friend-groups-item"
        style="@if ($cardBgImage) background: url('/storage/images/{{ $cardBgImage->displayName() }}');@endif  height:415px;" >
        @if (!$isRouletteSlot && $user->video)
            {{-- Same admin-uploaded video already used as the chat popup's background
                 (ChatController::get()/setRealAiChatBackgroundVideo in dating.js) -
                 played muted/looped on hover here as a preview, mirroring that. Starts
                 paused (no autoplay) and is only played/shown via JS on hover
                 (see the delegated mouseenter/mouseleave binding in find_friends.blade.php)
                 so idle cards with a video don't all download/decode video simultaneously
                 on page load. --}}
            <video class="find-friends-hover-video" muted loop playsinline preload="none">
                <source src="{{ asset('storage/videos/'.$user->video) }}" type="video/mp4">
            </video>
        @endif
        @if (($forceOnline ?? false) || $user->status == 'online' || $user->gender == 'female')
            <span class="find_friends_status span-online">Online</span>
        @else
            <span class="find_friends_status span-offline">Offline</span>
        @endif
        {{-- Per the client's explicit answer on the Boost feature (2026-09-09): boosted
             profiles should be recognizable to admins/editors specifically ("sa se simta
             clientii mai importanti", staff should notice/pay more attention to them) -
             not to other regular users, who only ever get the silent ordering advantage
             (applyBoostOrdering() in FindFriendsController). isAdmin() covers both admin
             and editor roles (see User::isAdmin()). --}}
        @if (Auth::check() && Auth::user()->isAdmin() && $user->boosted_until && \Illuminate\Support\Carbon::parse($user->boosted_until)->isFuture())
            <span class="find_friends_boosted_badge" title="{{ l('Boosted until') }} {{ \Illuminate\Support\Carbon::parse($user->boosted_until)->format('H:i') }}">
                &#9889; {{ l('Boosted') }}
            </span>
        @endif
        @if ($isRouletteSlot && auth()->check())
            @include('components.roulette_spinner', [
                'width' => '295px',
                'height' => '295px',
            ])
        @else
            <!-- Friend Item -->
            <div class="friend-item friend-groups @if($activeTheme === 'binder') binder-flip @endif">

                <div class="friend-item-content">

                    <div class="friend-avatar">

                        <div class="author-thumb find-friends-item">
                            @if ($user->images->count() == 0)
                                <a href="/profile/{{ $user->username }}"><img
                                        src="/storage/images/{{ $user->profile_image() }}"
                                        alt="{{ $user->name() }}"></a>
                            @endif
                        </div>
                    </div>
                    <div class="friend-actions" @if($activeTheme === 'binder') data-initial="{{ strtoupper(substr($user->name(), 0, 1)) }}" @endif>
                        <div class="author-content">
                            <a href="/profile/{{ $user->username }}"
                                class="h5 author-name">{{ $user->name() }} @if ($user->age() != 0)
                                    , {{ $user->age() }}
                                @endif
                            </a>
                        </div>
                        @unless($activeTheme === 'binder')
                        <div class="control-block-button @guest justify-content-center @endguest">
                            {{-- No data-toggle="tooltip" on the See Profile caption under Rosewood - see
                                 the AI-profile block in find_friends.blade.php for why (redundant with
                                 the always-visible caption text, and mispositions itself once this
                                 button is reshaped this drastically). The Chat/message FAB keeps its
                                 tooltip. --}}
                            <a href="/profile/{{ $user->username }}" class="  btn btn-control bg-blue"
                                @unless($activeTheme === 'rosewood')
                                data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ l('See Profile') }}"
                                @endunless>
                                {{ l('See Profile') }}
                            </a>
                            @auth
                                <a href="#" data-id="{{ $user->id }}"
                                    onclick="chat_open(this,event);" class="btn btn-control bg-purple"
                                    data-toggle="tooltip" data-placement="top"
                                    data-original-title="{{ l('Start chatting') }}">
                                    {{ l('Chat') }}
                                </a>
                            @endauth
                        </div>
                        @endunless
                    </div>
                    {{-- Binder-only: real 3D card flip "back face" - see themes/binder.css's
                         "collectible-card" section. Duplicates the name + the SAME real See
                         Profile/Chat actions every theme already has, just revealed on flip
                         instead of always visible - no new data, no new behavior. --}}
                    @if($activeTheme === 'binder')
                    <div class="friend-actions-back">
                        <div class="author-content">
                            <a href="/profile/{{ $user->username }}"
                                class="h5 author-name">{{ $user->name() }} @if ($user->age() != 0)
                                    , {{ $user->age() }}
                                @endif
                            </a>
                        </div>
                        <div class="control-block-button @guest justify-content-center @endguest">
                            <a href="/profile/{{ $user->username }}" class="  btn btn-control bg-blue"
                                data-toggle="tooltip" data-placement="top"
                                data-original-title="{{ l('See Profile') }}">
                                {{ l('See Profile') }}
                            </a>
                            @auth
                                <a href="#" data-id="{{ $user->id }}"
                                    onclick="chat_open(this,event);" class="btn btn-control bg-purple"
                                    data-toggle="tooltip" data-placement="top"
                                    data-original-title="{{ l('Start chatting') }}">
                                    {{ l('Chat') }}
                                </a>
                            @endauth
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
