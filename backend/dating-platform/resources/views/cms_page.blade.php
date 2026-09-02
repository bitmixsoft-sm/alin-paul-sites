@extends('layouts.layout')
@section('content')

<div class="header-spacer"></div>


<div class="container">
	<div class="row">

		<!-- Main Content -->

		<main class="col col-xl-12 order-xl-2 col-lg-12 order-lg-1 col-md-12 col-sm-12 col-12">

				<div id="generic_price_table">   
<section>
        <div class="container">

            <div class="content_block">
                <h1 id="cms_header">{{$page->name}}</h1>
                <div class="row">
                    <div class="col col-xs-12">
                    {!! $page->content !!}
                    </div>
                </div>
            </div>            
            
        </div>
    </section>
</div>

		</main>

@endsection