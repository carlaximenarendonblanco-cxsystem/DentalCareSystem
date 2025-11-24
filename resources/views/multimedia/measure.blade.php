@extends('layouts._partials.layout')
@section('title', 'Herramientas')
@section('subtitle')
{{ __('Herramientas') }}
@endsection

@section('content')
<div class="flex justify-end pt-5 pr-5">
    <a href="{{ route('multimedia.show', $study->id) }}" class="botton1">Volver al Estudio</a>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-6 text-center"> {{ $study->name_patient }} - {{ $study->ci_patient }}</h1>

<div class="flex justify-center mb-4">
    <select id="imageSelect" class="border border-gray-300 rounded-lg p-2">
        @foreach($imageUrls as $url)
            <option value="{{ $url }}">Imagen {{ $loop->iteration }}</option>
        @endforeach
    </select>
</div>



<div class="relative flex justify-center space-x-2 mb-6">
    @php
        $tools = [
            ['id'=>'distance','img'=>'distance.png','title'=>'Medir Distancia'],
            ['id'=>'delimited','img'=>'distances.png','title'=>'Marcar Contorno'],
            ['id'=>'angle','img'=>'angle.png','title'=>'Medir Ángulo'],
            ['id'=>'arco','img'=>'arco.png','title'=>'Medir Arco']
        ];
    @endphp
    @foreach($tools as $tool)
    <div class="group relative">
        <button id="{{ $tool['id'] }}" class="btnimg">
            <img src="{{ asset('assets/images/'.$tool['img']) }}" width="50" height="50">
        </button>
        <div class="hidden group-hover:block absolute left-0 mt-2 bg-blue-300 bg-opacity-50 text-center rounded-md px-2 py-1">
            <span class="text-xs text-gray-800">{{ $tool['title'] }}</span>
        </div>
    </div>
    @endforeach
</div>
<div class="flex justify-end p-5 pt-1">
    <label for="scaleSelect" class="mr-2 font-semibold text-gray-800">Escala:</label>
    <select id="scaleSelect" class="border border-gray-300 rounded-lg p-2">
        <option value="1">1:1</option>
        <option value="0.5">1:2</option>
        <option value="0.33333">1:3</option>
        <option value="0.25">1:4</option>
        <option value="0.2">1:5</option>
        <option value="2">2:1</option>
        <option value="3">3:1</option>
        <option value="4">4:1</option>
        <option value="5">5:1</option>
    </select>
</div>
<div class="flex justify-center mt-4 mb-2">
    <canvas id="measureCanvas" class="border rounded-lg"></canvas>
</div>

<p id="scaleMessage" class="text-center text-sm text-yellow-500 mb-2"></p>

<p id="measureOutput" class="font-semibold text-gray-700 text-center mb-4">
    Selecciona una herramienta para comenzar.
</p>

<div class="flex justify-center mb-4">
    <button id="resetBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
        Reiniciar
    </button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
<script>
const canvas = new fabric.Canvas('measureCanvas', { preserveObjectStacking: true });
const imageSelect = document.getElementById('imageSelect');
const scaleSelect = document.getElementById('scaleSelect');
const output = document.getElementById('measureOutput');
const scaleMessage = document.getElementById('scaleMessage');
const resetBtn = document.getElementById('resetBtn');

let currentImage;
let scaleFactor = parseFloat(scaleSelect.value);
let activeTool = null;
const measurementColor = '#FFD700'; // amarillo brillante

// === CARGAR IMAGEN ===
function loadImage(url) {
    fabric.Image.fromURL(url, function(fabricImg) {
        canvas.clear();
        currentImage = fabricImg;

        // Escalado automático según tamaño máximo
        const maxWidth = 1200;
        const maxHeight = 900;
        let autoScale = 1;

        if (fabricImg.width > maxWidth || fabricImg.height > maxHeight) {
            const widthScale = fabricImg.width / maxWidth;
            const heightScale = fabricImg.height / maxHeight;
            autoScale = 1 / Math.max(widthScale, heightScale);
            fabricImg.scale(autoScale);
            scaleMessage.textContent = `Imagen escalada automáticamente 1:${Math.round(1/autoScale)}`;
        } else {
            scaleMessage.textContent = '';
        }

        canvas.setWidth(fabricImg.width * autoScale);
        canvas.setHeight(fabricImg.height * autoScale);
        fabricImg.set({ left: 0, top: 0, selectable: false });
        canvas.setBackgroundImage(fabricImg, canvas.renderAll.bind(canvas));
        output.textContent = "Selecciona una herramienta para comenzar.";
    }, { crossOrigin: 'anonymous' });
}

// === CAMBIO DE IMAGEN ===
imageSelect.addEventListener('change', (e) => loadImage(e.target.value));

// === CAMBIO DE ESCALA ===
scaleSelect.addEventListener('change', (e) => {
    scaleFactor = parseFloat(e.target.value);
    // scaleMessage.textContent = `Escala seleccionada 1:${Math.round(1/scaleFactor)}`;
});

// === ACTIVAR HERRAMIENTAS ===
function activateTool(tool) {
    activeTool = tool;
    output.textContent = `Herramienta activa: ${tool}`;
    canvas.off('mouse:down');
    if(tool==='distance') activateDistanceTool();
    if(tool==='angle') activateAngleTool();
    if(tool==='delimited') activateContourTool();
    if(tool==='arco') activateArcTool();
}

// === DISTANCIA ===
function activateDistanceTool() {
    let point1 = null;
    canvas.on('mouse:down', function(opt){
        const p = canvas.getPointer(opt.e);
        if(!point1){
            point1 = p;
            canvas.add(new fabric.Circle({ left:p.x-4, top:p.y-4, radius:4, fill:measurementColor, selectable:false }));
        } else {
            const p2 = p;
            canvas.add(new fabric.Circle({ left:p2.x-4, top:p2.y-4, radius:4, fill:measurementColor, selectable:false }));
            const line = new fabric.Line([point1.x,point1.y,p2.x,p2.y], { stroke:measurementColor, strokeWidth:2, selectable:false });
            canvas.add(line);

            const distPx = Math.hypot(p2.x-point1.x, p2.y-point1.y);
            const distMm = distPx * scaleFactor;
            const text = new fabric.Text(`Distancia: ${Math.round(distMm)} mm`, {
                left:(point1.x+p2.x)/2, top:(point1.y+p2.y)/2-20, fontSize:16, fill:measurementColor, selectable:false
            });
            canvas.add(text);
            point1 = null;
        }
    });
}

// === ÁNGULO ===
function activateAngleTool(){
    let points = [];
    canvas.on('mouse:down', function(opt){
        const p = canvas.getPointer(opt.e);
        points.push(p);
        canvas.add(new fabric.Circle({ left:p.x-4, top:p.y-4, radius:4, fill:measurementColor, selectable:false }));

        if(points.length===3){
            const [A,B,C]=points;
            canvas.add(new fabric.Line([A.x,A.y,B.x,B.y],{ stroke:measurementColor, strokeWidth:2, selectable:false }));
            canvas.add(new fabric.Line([B.x,B.y,C.x,C.y],{ stroke:measurementColor, strokeWidth:2, selectable:false }));
            const angle = calculateAngle(A,B,C);
            const text = new fabric.Text(`Ángulo: ${Math.round(angle)}°`,{
                left:(A.x+B.x+C.x)/3, top:(A.y+B.y+C.y)/3-30, fontSize:16, fill:measurementColor, selectable:false
            });
            canvas.add(text);
            points=[];
        }
    });
}

function calculateAngle(A,B,C){
    const AB={x:A.x-B.x, y:A.y-B.y};
    const CB={x:C.x-B.x, y:C.y-B.y};
    const dot = AB.x*CB.x + AB.y*CB.y;
    const magAB = Math.hypot(AB.x,AB.y);
    const magCB = Math.hypot(CB.x,CB.y);
    const cosAngle = dot/(magAB*magCB);
    return Math.acos(cosAngle)*180/Math.PI;
}

// === CONTORNO ===
function activateContourTool(){
    let points=[], line, text;
    canvas.on('mouse:down', function(opt){
        const p = canvas.getPointer(opt.e);
        points.push(p);
        canvas.add(new fabric.Circle({ left:p.x-4, top:p.y-4, radius:4, fill:measurementColor, selectable:false }));
        if(line) canvas.remove(line);

        if(points.length>=2){
            const pathStr = points.map((pt,i)=> i===0?`M ${pt.x} ${pt.y}`:`L ${pt.x} ${pt.y}`).join(' ');
            line = new fabric.Path(pathStr,{ stroke:measurementColor, strokeWidth:2, fill:'', selectable:false });
            canvas.add(line);
            const length = calculateContourLength(points);
            if(text) canvas.remove(text);
            text = new fabric.Text(`Longitud: ${Math.round(length)} mm`,{
                left:points[points.length-1].x, top:points[points.length-1].y-20, fontSize:16, fill:measurementColor, selectable:false
            });
            canvas.add(text);
        }
    });
}

function calculateContourLength(points){
    let length=0;
    for(let i=1;i<points.length;i++){
        length+=Math.hypot(points[i].x-points[i-1].x, points[i].y-points[i-1].y);
    }
    return length*scaleFactor;
}

// === ARCO ===
function activateArcTool(){
    let points=[];
    canvas.on('mouse:down', function(opt){
        const p = canvas.getPointer(opt.e);
        points.push(p);
        canvas.add(new fabric.Circle({ left:p.x-4, top:p.y-4, radius:4, fill:measurementColor, selectable:false }));
        if(points.length===3){
            drawArc(points[0],points[1],points[2]);
            points=[];
        }
    });
}

function drawArc(p1,p2,center){
    const r = Math.hypot(center.x-p1.x, center.y-p1.y);
    const a1 = Math.atan2(p1.y-center.y, p1.x-center.x);
    const a2 = Math.atan2(p2.y-center.y, p2.x-center.x);
    const path = new fabric.Path(`M ${p1.x} ${p1.y} A ${r} ${r} 0 0 1 ${p2.x} ${p2.y}`,{ stroke:measurementColor, strokeWidth:2, fill:'', selectable:false });
    canvas.add(path);
    const angle = Math.abs((a2-a1)*(180/Math.PI));
    const arcLength = r*(angle*Math.PI/180)*scaleFactor;
    const text = new fabric.Text(`Longitud: ${Math.round(arcLength)} mm`,{
        left:(p1.x+p2.x)/2, top:(p1.y+p2.y)/2-30, fontSize:16, fill:measurementColor, selectable:false
    });
    canvas.add(text);
}

// === BOTONES ===
document.getElementById('distance').onclick = ()=>activateTool('distance');
document.getElementById('angle').onclick = ()=>activateTool('angle');
document.getElementById('delimited').onclick = ()=>activateTool('delimited');
document.getElementById('arco').onclick = ()=>activateTool('arco');

resetBtn.onclick = ()=> {
    canvas.off('mouse:down');
    loadImage(imageSelect.value);
    activeTool = null;
    output.textContent = "Selecciona una herramienta para comenzar.";
};

// === CARGA INICIAL ===
if(imageSelect.options.length>0) loadImage(imageSelect.value);

</script>
@endsection
