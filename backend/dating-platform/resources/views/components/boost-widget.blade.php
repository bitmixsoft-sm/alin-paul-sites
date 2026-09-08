@php
    // Shared by every spot this can appear (header, Find Friends banner, Profile Settings) -
    // one small partial instead of the same button/indicator/error markup three times, kept
    // in sync via the shared .boost-* classes activateBoost() (storage/app/assets/js/dating.js)
    // updates on success regardless of which instance was clicked.
    $boostCost = (int) (\App\Settings::where('name', 'BOOST_COST_CREDITS')->value('value') ?? 50);
    $boostActive = Auth::check() && Auth::user()->boosted_until && \Illuminate\Support\Carbon::parse(Auth::user()->boosted_until)->isFuture();
    $boostUntilText = $boostActive ? \Illuminate\Support\Carbon::parse(Auth::user()->boosted_until)->format('H:i') : '';
    $boostIcon = $boostIcon ?? false;
    $boostDropdownItem = $boostDropdownItem ?? false;
@endphp
@if($boostDropdownItem)
    {{-- <li> variant matching the sibling entries in the profile avatar's account dropdown
         (components/header/profile-header.blade.php's ul.account-settings: icon + <span>
         text, one per <li>) - a second access point since the icon-only header button
         (below) has to disappear below 768px (see themes/*.css's mobile .control-block rules)
         for lack of room, and this dropdown doesn't have that space constraint. --}}
    <li class="boost-widget">
        {{-- .more-dropdown (the "Your Account" panel) is a fixed 180px wide, leaving ~105px
             for text next to the icon - "Boost now (50 credits)" wrapped onto 2 lines there,
             unlike the other single-word/short entries. Dropping the price here (still shown
             on hover in the header icon, and always visible on Profile Settings/Find Friends)
             plus white-space: nowrap keeps this one line like its siblings. --}}
        <a href="#" class="boost-activate-btn" onclick="activateBoost(this); return false;" @if($boostActive) style="display:none;" @endif>
            <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>
            <span style="white-space: nowrap;">{{ l('Boost now') }}</span>
        </a>
        <a href="/profile-settings" class="boost-active-indicator" @unless($boostActive) style="display:none;" @endunless>
            <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>
            <span style="white-space: nowrap;">{{ l('Boosted until') }} <span class="boost-until-time">{{ $boostUntilText }}</span></span>
        </a>
        <div class="boost-error-msg text-danger" style="display:none; font-size:11px; padding:0 15px;"></div>
    </li>
@elseif($boostIcon)
    {{-- Compact icon-only variant (header.blade.php) - styled like the other .control-icon
         header buttons (friend-requests/chat/notifications) instead of a text link, since
         "Packages + Boost now (50 credits) + credits + 3 icons" all as text/pills didn't fit
         together at medium (tablet/small-laptop) widths, only wrapping below 768px. Price/
         state shown via `title` tooltip instead of always-visible text; errors use a plain
         alert() (see activateBoost()) rather than a persistent message box, since there's no
         room here for one without disrupting the icon row's layout. --}}
    <span class="boost-widget boost-widget-icon" style="margin-left: 16px;">
        {{-- .control-icon's own CSS only adds margin-right (spacing to whatever comes AFTER
             it) - Packages (.link-find-friend/.packages-top) has no margin of its own either,
             so with nothing between them the two sat flush against each other. margin-left
             here (not on .control-icon itself, so the other real .control-icon instances -
             friend-requests/chat/notifications - are untouched) is the fix. --}}
        <a href="#" class="control-icon boost-activate-btn boost-icon-only" onclick="activateBoost(this); return false;" title="{{ l('Boost now') }} ({{ $boostCost }} {{ l('credits') }})" @if($boostActive) style="display:none;" @endif>
            <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>
        </a>
        <a href="/profile-settings" class="control-icon boost-active-indicator boost-icon-only-active" title="{{ l('Boosted until') }} {{ $boostUntilText }}" @unless($boostActive) style="display:none;" @endunless>
            <svg class="olymp-thunder-icon"><use xlink:href="/svg-icons/sprites/icons.svg#olymp-thunder-icon"></use></svg>
            <span class="boost-until-time" style="display:none;">{{ $boostUntilText }}</span>
        </a>
        <span class="boost-error-msg" style="display:none;"></span>
    </span>
@else
    <span class="boost-widget {{ $boostWidgetClass ?? '' }}">
        {{-- <a>, not <button> - so it can drop into places styled for anchors (e.g. header.blade.
             php's .link-find-friend links) without fighting default button chrome. --}}
        <a href="#" class="boost-activate-btn {{ $boostButtonClass ?? '' }}" onclick="activateBoost(this); return false;" @if($boostActive) style="display:none;" @endif>
            {!! $boostButtonLabel ?? '&#9889; ' . l('Boost now') !!} ({{ $boostCost }} {{ l('credits') }})
        </a>
        <span class="boost-active-indicator {{ $boostActiveClass ?? '' }}" @unless($boostActive) style="display:none;" @endunless>
            &#9889; {{ l('Boosted until') }} <span class="boost-until-time">{{ $boostUntilText }}</span>
        </span>
        <span class="boost-error-msg text-danger" style="display:none;"></span>
    </span>
@endif
