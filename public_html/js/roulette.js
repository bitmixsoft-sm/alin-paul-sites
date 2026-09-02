var startAngle = (-90 * Math.PI / 180);
var spinAngleDegrees = 0;
var arc = Math.PI / (options.length / 2);
var spinTimeout = null;
var randomSpins = 0;

var spinArcStart = 10;
var spinTime = 0;
var spinTimeTotal = 0;

var drawInterval = 10;

var ctx;

let canvasWidth;

let finishedSpinCallback = null;
let halfCanvasWidth;

let insideRadius;
let outsideRadius;

let textRadius;

let valueIndex;
let valueText;

function byte2Hex(n){

    var nybHexString = "0123456789ABCDEF";
    return String(nybHexString.substr((n >> 4) & 0x0F, 1)) + nybHexString.substr(n & 0x0F, 1);
}

function RGB2Color(r, g, b){

    return '#' + byte2Hex(r) + byte2Hex(g) + byte2Hex(b);
}

function getColor(item, maxitem){

    var phase = 0;
    var center = 128;
    var width = 127;
    var frequency = Math.PI * 2 / maxitem;

    red = Math.sin(frequency * item + 2 + phase) * width + center;
    green = Math.sin(frequency * item + phase) * width + center;
    blue = Math.sin(frequency * item + 4 + phase) * width + center;

    return RGB2Color(red, green, blue);
}

function drawRouletteWheel(){

    var canvas = document.getElementById("canvas");

    if(canvas.getContext){

        let x = halfCanvasWidth;
        let y = halfCanvasWidth;

        ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvasWidth, canvasWidth);

        ctx.strokeStyle = "black";
        ctx.lineWidth = 2;
        if($(window).width() > 768){
            ctx.font = 'bold 18px Roboto';
        }else{
            ctx.font = 'bold 14px Roboto';  
        }

        for(var i = 0; i < options.length; i++){

            var angle = startAngle + i * arc;
            //ctx.fillStyle = colors[i];
            ctx.fillStyle = getColor(i, options.length);
            ctx.beginPath();
            ctx.arc(x, y, outsideRadius, angle, angle + arc, false);
            ctx.arc(x, y, insideRadius, angle + arc, angle, true);
            ctx.stroke();
            ctx.fill();

            ctx.save();
            ctx.shadowOffsetX = -1;
            ctx.shadowOffsetY = -1;
            ctx.shadowBlur = 0;
            //ctx.shadowColor = "rgb(220,220,220)";
            ctx.fillStyle = "black";
            ctx.translate(halfCanvasWidth + Math.cos(angle + arc / 2) * textRadius,
                halfCanvasWidth + Math.sin(angle + arc / 2) * textRadius);
            ctx.rotate(angle + arc / 2 );
            var text = options[i];
            ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
            ctx.restore();
        }

        //Arrow
        ctx.fillStyle = "black";
        ctx.beginPath();
        ctx.moveTo(halfCanvasWidth - 4, halfCanvasWidth - (outsideRadius + 5));
        ctx.lineTo(halfCanvasWidth + 4, halfCanvasWidth - (outsideRadius + 5));
        ctx.lineTo(halfCanvasWidth + 4, halfCanvasWidth - (outsideRadius - 5));
        ctx.lineTo(halfCanvasWidth + 9, halfCanvasWidth - (outsideRadius - 5));
        ctx.lineTo(halfCanvasWidth, halfCanvasWidth - (outsideRadius - 13));
        ctx.lineTo(halfCanvasWidth - 9, halfCanvasWidth - (outsideRadius - 5));
        ctx.lineTo(halfCanvasWidth - 4, halfCanvasWidth - (outsideRadius - 5));
        ctx.lineTo(halfCanvasWidth - 4, halfCanvasWidth - (outsideRadius + 5));
        ctx.fill();
    }
}

function spin(callback = null){

    finishedSpinCallback = callback;
    spinAngleStart = 5;
    spinAngleDegrees =0;
    spinTime= 0;
    startAngle = (-90 * Math.PI / 180);
    randomSpins = Math.floor(Math.random() * 4)+5;
    rotateWheel2();
    randomStopPosition = randomIntFromInterval(0, 360 / options.length)
}
function calculateStartStopWinningAngle(){
    itemDegrees = 360 / options.length; //90
    
    spinDegMin = Math.abs(360-(valueIndex+1)*itemDegrees);
    spinDegMax = spinDegMin + itemDegrees;
    return [spinDegMin,spinDegMax];
}
function checkIfPositionIsWinning(){
    var degrees = startAngle * 180 / Math.PI+90;
        var arcd = arc * 180 / Math.PI;
        var index = Math.floor((360 - degrees % 360) / arcd);
        if(index == valueIndex){
            return true;
        }
        return false;
}
function rotateWheel2(){
    //var spinAngle = spinAngleStart - easeOut(spinTime, 0, spinAngleStart, spinTimeTotal);
    var spinAngle = 0;
    var randomSpinsDeg = randomSpins*360;
    if(randomSpinsDeg <= spinAngleDegrees){
        var degLastSpin = spinAngleDegrees-randomSpinsDeg; 
        if(degLastSpin >= calculateStartStopWinningAngle()[0] + randomStopPosition +360*2 && degLastSpin <= calculateStartStopWinningAngle()[1]+360*2 && checkIfPositionIsWinning()){
            stopRotateWheel();
            return;
        }
        spinAngle = (spinAngleStart + 10)-(degLastSpin/(calculateStartStopWinningAngle()[0]+randomStopPosition+360*2)*(spinAngleStart + 10))+0.1;
    }else{
        spinAngle = spinAngleStart + 10;
    }
    spinAngleDegrees += spinAngle
    startAngle += (spinAngle * Math.PI / 180);
    drawRouletteWheel();
    spinTimeout = setTimeout('rotateWheel2()', drawInterval);
    
}

function randomIntFromInterval(min, max) { 
  return Math.floor(Math.random() * (max - min + 1) + min)
}

function stopRotateWheel(){

    clearTimeout(spinTimeout);
    var degrees = startAngle * 180 / Math.PI+90;
    var arcd = arc * 180 / Math.PI;
    var index = Math.floor((360 - degrees % 360) / arcd);
    console.log(index);
    ctx.save();
    if($(window).width() > 768){
        ctx.font = 'bold 30px Roboto';
    }else{
        ctx.font = 'bold 18px Roboto';
    }
    var text = options[index];
    ctx.fillText(text, halfCanvasWidth - ctx.measureText(text).width / 2, halfCanvasWidth + 10);
    // ctx.fillText(valueText, halfCanvasWidth - ctx.measureText(valueText).width / 2, halfCanvasWidth + 10);
    ctx.restore();

    if(finishedSpinCallback != null){
        finishedSpinCallback();
    }
}
