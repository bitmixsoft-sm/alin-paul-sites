<style>
    #actions-container, #roulette-container {
        text-align: center;
        color: #333;
        font-size: 18px;
    }

    #spin-button {
        display: initial;
        width: auto;
    }
</style>

<div id="roulette-container">
    <canvas @if(!(empty($width) || empty($height))) style="width: {{ $width }}; height: {{ $height }};" @endif id="canvas"></canvas>
</div>

<div id="actions-container">
    @if($canSpin)
        <input type="button" value="{{l("Spin")}}" id='spin-button' class="btn btn-primary btn-lg"/>
        @php
            $actionMessageClass = "invisible";
        @endphp
    @else
        @php
            $actionMessageClass = "";
        @endphp
    @endif

    <div id="action-message" class="{{$actionMessageClass}}" style="margin-top: 50px;">
        <a href="{{ route('packages') }}" id="spin-button" class="btn btn-primary btn-lg"/>{{l("Go to packages")}}</a>
    </div>
</div>

<script type="text/javascript">

    const options = [

        @foreach($rouletteOptions as $rouletteOption)
            "{{$rouletteOption->display_text}}",
        @endforeach
    ];
</script>

<script type="text/javascript" src="{{URL::asset('js/roulette.js')}}"></script>

<script type="text/javascript">
    window.addEventListener('load', function () {
        var collapseWidth = 1200;
        if ($(window).width() > collapseWidth || window.location.pathname !== '/find-friends') {
            canvasWidth = Math.floor($('.container')[0].clientWidth * 70 / 100) * 0.6;
        } else {
            canvasWidth = Math.floor($('.container')[0].clientWidth * 70 / 100);
        }
        halfCanvasWidth = Math.floor(canvasWidth / 2);
        if ($(window).width() > collapseWidth) {
            insideRadius = halfCanvasWidth / 2;
        } else {
            insideRadius = halfCanvasWidth / 2 - 30;
        }
        outsideRadius = Math.floor((canvasWidth - 10) / 2);
        if ($(window).width() > collapseWidth) {
            textRadius = Math.floor((halfCanvasWidth / 2) + (outsideRadius - insideRadius) / 2);
        } else {
            textRadius = Math.floor((halfCanvasWidth / 2) + (outsideRadius - insideRadius) / 2) - 30;
        }

        $('#canvas')[0].width = canvasWidth;
        $('#canvas')[0].height = canvasWidth;

        drawRouletteWheel();

        let spinButton = $("#spin-button");

        if (spinButton != null) {

            spinButton.click(function () {

                $.ajax({

                    dataType: 'json',
                    url: "{{route('roulette_random_value')}}",
                    type: 'GET'
                }).done(function (data) {

                    valueIndex = data.value;
                    spin(function () {

                        $("#spin-button").hide();
                        $("#action-message").removeClass('invisible');
                    });
                });
            });
        }
    });
</script>