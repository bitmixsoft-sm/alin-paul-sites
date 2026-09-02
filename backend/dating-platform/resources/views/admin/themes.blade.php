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

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>Theme was not changed:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-12">
                        <div class="user-data m-b-30">
                            <h3 class="title-3 m-b-30">
                                <i class="fas fa-paint-brush"></i>Aspect
                            </h3>

                            <div class="row theme-card-row ml-4 mr-4 mb-3">
                                @foreach($themes as $slug => $theme)
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-3">
                                        <div class="card h-100 theme-card @if($activeTheme === $slug) border-primary @endif">
                                            <div class="theme-swatch theme-swatch-{{ $slug }}"></div>
                                            <div class="card-body d-flex flex-column">
                                                <h6 class="card-title">
                                                    {{ $theme['label'] }}
                                                    @if($activeTheme === $slug)
                                                        <span class="badge badge-success">Active</span>
                                                    @endif
                                                </h6>
                                                <p class="card-text text-muted flex-grow-1">{{ $theme['description'] }}</p>
                                                <form action="/admin/themes" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="theme" value="{{ $slug }}">
                                                    <button type="submit" class="au-btn au-btn-icon au-btn--blue w-100 theme-activate-btn" @if($activeTheme === $slug) disabled @endif>
                                                        <i class="zmdi zmdi-check"></i>
                                                        @if($activeTheme === $slug) Active @else Activate @endif
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <small class="form-text text-muted theme-help-text">Takes effect immediately for every visitor - no redeploy needed. "Classic" is the current, unmodified design.</small>
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
    /* Compact card grid - was 3-per-row with a 120px swatch, now up to 6-per-row (col-xl-2)
       with a shorter swatch and tighter text, per the client's request that this page felt
       oversized and that the theme previews looked too similar to tell apart at a glance. */
    .theme-card{
        border: 1px solid #e2e5ec;
    }
    .theme-card.border-primary{
        border-width: 2px;
        border-color: #2e7d32 !important;
        background: #eef6ef;
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.25);
    }
    .theme-help-text{
        display: block;
        margin-top: -10px;
        margin-bottom: 20px;
    }
    .theme-card.border-primary .theme-activate-btn{
        background-color: #2e7d32 !important;
        border-color: #2e7d32 !important;
        opacity: 1 !important;
    }
    .theme-card .card-body{
        padding: .85rem .9rem;
    }
    .theme-card .card-title{
        font-size: .95rem;
        margin-bottom: .35rem;
    }
    .theme-card .card-text{
        font-size: .78rem;
        margin-bottom: .6rem;
    }
    .theme-activate-btn{
        font-size: .8rem;
        padding: .4rem .5rem;
    }
    .theme-swatch{
        height: 68px;
        border-radius: .25rem .25rem 0 0;
    }
    /* Below sm, cards stack full-width (one per row) - at 2-per-row the "ACTIVATE" button text
       had no room and wrapped into two lines; the outer ml-4/mr-4 margin also ate into that
       already-tight width, so both are neutralized here. */
    @media (max-width: 575.98px){
        .theme-card-row{
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .theme-swatch{
            height: 90px;
        }
    }
    /* Each swatch below uses a DIFFERENT composition (not just different colors) - flat wash,
       multi-blob nebula, hard diagonal split, striped, single spotlight - so the row reads as
       five distinct visual identities even shrunk down, instead of five dark blurry blobs that
       only differ by hue. */
    .theme-swatch-classic{
        background: linear-gradient(135deg, #f5f6fa, #dfe4ea);
    }
    .theme-swatch-aurora{
        background:
            radial-gradient(65% 100% at 20% 15%, rgba(255,110,151,0.6), transparent 55%),
            radial-gradient(65% 100% at 85% 25%, rgba(142,124,255,0.55), transparent 55%),
            radial-gradient(60% 80% at 50% 100%, rgba(255,181,98,0.4), transparent 55%),
            #140F1F;
    }
    .theme-swatch-nordic{
        background:
            linear-gradient(115deg, #ff5c8a 0%, #ff5c8a 42%, #14101F 42%, #14101F 58%, #4ce0c7 58%, #4ce0c7 100%);
    }
    .theme-swatch-volt{
        background:
            repeating-linear-gradient(135deg, rgba(212,255,61,0.9) 0 10px, transparent 10px 26px, rgba(255,61,174,0.85) 26px 36px, transparent 36px 52px),
            #0A0A0A;
        border-bottom: 3px solid #0A0A0A;
    }
    .theme-swatch-velvet{
        background:
            radial-gradient(45% 90% at 50% 40%, rgba(201,162,39,0.55), transparent 70%),
            #170B10;
        box-shadow: inset 0 0 0 2px rgba(201,162,39,0.6);
    }
    /* Bloom is bright/warm (unlike the other 4 dark themes) with a full-width masonry grid -
       a few staggered coral/teal/yellow blocks on cream, hinting at that grid, instead of a
       dark nebula/spotlight/stripe. */
    .theme-swatch-bloom{
        background:
            linear-gradient(#FF6B4A, #FF6B4A) 6% 12% / 20% 76% no-repeat,
            linear-gradient(#1F8A82, #1F8A82) 30% 34% / 20% 54% no-repeat,
            linear-gradient(#FFC94A, #FFC94A) 54% 8% / 20% 68% no-repeat,
            linear-gradient(#221F1A, #221F1A) 78% 28% / 20% 48% no-repeat,
            #FBF5EA;
    }
    /* Binder is a trading-card/collector's-album look - a holographic rainbow conic-gradient
       (the same one the real card border uses) on near-black, instead of a soft nebula. */
    .theme-swatch-binder{
        background:
            conic-gradient(from 0deg, #FF4FA0, #FFC22E, #1FCB8C, #3E7BFF, #9B4FFF, #FF4FA0);
    }
</style>
@endsection
