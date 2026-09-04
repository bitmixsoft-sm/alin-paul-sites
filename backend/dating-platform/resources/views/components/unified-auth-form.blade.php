@php
    // Each instance of this partial on a page needs its own DOM scope so
    // unified-auth-form.js can find the right one via .closest('.unified-auth') - two copies
    // (e.g. the Register and Login modals both including this) would otherwise both react to
    // one page-wide "which step" state.
    $unifiedAuthId = $unifiedAuthId ?? 'unified-auth-'.\Illuminate\Support\Str::random(6);
    $showForgotPassword = $showForgotPassword ?? false;
@endphp
{{-- Single email-first auth form: type your email, "Continue" figures out via
     App\Http\Controllers\Auth\EmailCheckController whether that's an existing account (shows
     the password field) or a new one (shows the rest of the registration fields) - replaces
     making the visitor pick "Register" or "Login" themselves first. Gated on
     config('services.unified_login.enabled') by every page that includes this; the old
     tabs/modals render instead when it's off. The actual submits still go straight to the
     normal login()/register() routes, unchanged - this only changes what's shown before that. --}}
<div class="unified-auth" id="{{ $unifiedAuthId }}" data-step="email">
    <div data-unified-step="email">
        <div class="form-group label-floating">
            <label class="control-label">{{ l('Your Email') }}</label>
            <input type="email" class="form-control unified-email-input" placeholder="" autocomplete="email">
        </div>
        <button type="button" class="btn btn-lg btn-primary full-width" onclick="unifiedAuthContinue(this)">{{ l('Continue') }}</button>
        <p class="unified-auth-error text-danger" style="display:none;"></p>

        @include('components.google-auth-button')
    </div>

    <form data-unified-step="login" class="form" method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="email" class="unified-email-mirror">
        <div class="form-group label-floating">
            <label class="control-label">{{ l('Your Password') }}</label>
            <input name="password" class="form-control" placeholder="" type="password" autocomplete="current-password">
        </div>
        <div class="remember">
            <div class="checkbox">
                <label>
                    <input name="remember" type="checkbox">
                    {{ l('Remember Me') }}
                </label>
            </div>
            @if($showForgotPassword)
                <a href="#" class="forgot" data-toggle="modal" data-target="#restore-password">{{ l('Forgot my Password') }}</a>
            @endif
        </div>
        <input type="submit" class="btn btn-lg btn-primary full-width" value="{{ l('Login') }}" onclick="FB_Lead();">
        <p class="text-center"><a href="#" onclick="unifiedAuthBack(this); return false;">{{ l('Use a different email') }}</a></p>
    </form>

    <form data-unified-step="register" class="form" method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="email" class="unified-email-mirror">
        <div class="row">
            <div class="col col-12 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="form-group label-floating">
                    <label class="control-label">{{ l('First Name') }}</label>
                    <input name="firstname" required class="form-control" placeholder="" type="text">
                </div>
            </div>
            <div class="col col-12 col-xl-6 col-lg-6 col-md-6 col-sm-12">
                <div class="form-group label-floating">
                    <label class="control-label">{{ l('Last Name') }}</label>
                    <input name="lastname" required class="form-control" placeholder="" type="text">
                </div>
            </div>
            <div class="col col-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                <div class="form-group label-floating">
                    <label class="control-label">{{ l('Password') }}</label>
                    <input name="password" required class="form-control" placeholder="" type="password" autocomplete="new-password">
                </div>
                <div class="remember">
                    <div class="checkbox">
                        <label>
                            <input name="terms" required type="checkbox">
                            {{ l('I accept the') }} <a href="/pages/terms-of-use">{{ l('Terms and Conditions') }}</a> {{ l('of the website') }}
                        </label>
                    </div>
                </div>
                <input type="submit" class="btn btn-purple btn-lg full-width" value="{{ l('Complete Registration!') }}" onclick="FB_Trial();">
                <p class="text-center"><a href="#" onclick="unifiedAuthBack(this); return false;">{{ l('Use a different email') }}</a></p>
            </div>
        </div>
    </form>
</div>
