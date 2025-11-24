@extends('layouts._partials.layout')

@section('title', 'Medición de Imagen')
@section('subtitle')
    {{ __('Medición de Imagen') }}
@endsection

@section('content')
<div class="py-5">

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-lg p-6">

        <!-- HEADER -->
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Herramienta de Medición</h2>

        <!-- SELECCIÓN DE ESCALA -->
        <div class="flex justify-end mb-4">
            <label for="scaleSelect" class="mr-3 text-gray-800 font-semibold">Escala:</label>
            <select id="scaleSelect" class="border border-gray-400 rounded-lg p-2 bg-white">
                <option value="1">1:1</option>
                <option value="0.5">1:2</option>
                <option value="0.3333">1:3</option>
                <option value="0.25">1:4</option>
                <option value="0.2">1:5</option>

                <option value="2">2:1</option>
                <option value="3">3:1</option>
                <option value="4">4:1</option>
                <option value="5">5:1</option>
            </select>
        </div>

        <!-- BOTONES DE CONTROL -->
        <div class="flex flex-wrap gap-3 mb-4">
            <button onclick="setMode('distance')" class="btn">Distancia</button>
            <button onclick="setMode('angle')" class="btn">Ángulo</button>
            <button onclick="setMode('contour')" class="btn">Contorno</button>
            <button onclick="setMode('arc')" class="btn">Arco</button>
            <button onclick="setMode('setScale')" class="btn">Escala fija</button>
            <button onclick="resetCanvas()" class="btn bg-red-500 text-white hover:bg-red-600">Resetear</button>
        </div>

        <!-- CANVAS -->
        <div class="mb-4 relative w-full max-w-5xl mx-auto">
            <img id="bgImage" src="{{ asset('images/paciente.png') }}" class="hidden">
            <canvas id="measureCanvas" class="border border-gray-300 w-full rounded-lg"></canvas>
        </div>

        <!-- RESULTADOS -->
        <div id="resultBox" class="bg-gray-100 p-4 rounded-lg text-gray-700 font-semibold"></div>

        <!-- BOTÓN GUARDAR -->
        <div class="mt-4">
            <button onclick="saveImage()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Guardar Imagen
            </button>
        </div>

    </div>

</div>

@endsection

@section('scripts')
<script>
/* ================================
   VARIABLES GLOBALES
================================ */
let mode = "";
let canvas = document.getElementById("measureCanvas");
let ctx = canvas.getContext("2d");
let bgImage = document.getElementById("bgImage");

let points = [];
let pixelScale = 1;
let scaleFactor = 1;
let userScale = 1; // 🔥 ESCALA MANUAL SELECCIONADA

/* ================================
   ESCALA MANUAL
================================ */
document.getElementById('scaleSelect').addEventListener('change', function () {
    userScale = parseFloat(this.value);
});

/* ================================
   CONFIGURACIÓN
================================ */

bgImage.onload = function () {
    canvas.width = bgImage.width;
    canvas.height = bgImage.height;
    ctx.drawImage(bgImage, 0, 0);
};

function setMode(newMode) {
    mode = newMode;
    points = [];
    showResult("");
}

function resetCanvas() {
    points = [];
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(bgImage, 0, 0);
    showResult("");
}

/* ================================
   EVENTOS DEL CANVAS
================================ */
canvas.addEventListener("click", function (event) {
    const rect = canvas.getBoundingClientRect();
    const x = (event.clientX - rect.left);
    const y = (event.clientY - rect.top);
    points.push({ x, y });
    drawPoint(x, y);

    if (mode === "distance" && points.length === 2) measureDistance();
    if (mode === "angle" && points.length === 3) measureAngle();
    if (mode === "contour" && points.length >= 2) drawContour();
    if (mode === "arc" && points.length === 3) measureArc();
    if (mode === "setScale" && points.length === 2) setScale();
});

/* ================================
   DIBUJAR PUNTOS
================================ */
function drawPoint(x, y) {
    ctx.fillStyle = "red";
    ctx.beginPath();
    ctx.arc(x, y, 4, 0, Math.PI * 2);
    ctx.fill();
}

/* ================================
   MEDIR DISTANCIA
================================ */
function measureDistance() {
    const p1 = points[0];
    const p2 = points[1];

    const distPx = Math.sqrt((p2.x - p1.x) ** 2 + (p2.y - p1.y) ** 2);
    const distMm = distPx * scaleFactor * userScale;

    ctx.strokeStyle = "blue";
    ctx.beginPath();
    ctx.moveTo(p1.x, p1.y);
    ctx.lineTo(p2.x, p2.y);
    ctx.stroke();

    showResult(`Distancia: ${distMm.toFixed(2)} mm`);
}

/* ================================
   MEDIR ÁNGULO
================================ */
function measureAngle() {
    const [p1, p2, p3] = points;

    const angle =
        Math.abs(
            Math.atan2(p1.y - p2.y, p1.x - p2.x) -
            Math.atan2(p3.y - p2.y, p3.x - p2.x)
        ) * (180 / Math.PI);

    showResult(`Ángulo: ${angle.toFixed(2)}°`);
}

/* ================================
   CONTORNO
================================ */
function drawContour() {
    ctx.strokeStyle = "green";
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);

    for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }

    ctx.stroke();

    let total = 0;
    for (let i = 1; i < points.length; i++) {
        const dx = points[i].x - points[i - 1].x;
        const dy = points[i].y - points[i - 1].y;
        total += Math.sqrt(dx * dx + dy * dy);
    }

    const totalMm = total * scaleFactor * userScale;
    showResult(`Contorno: ${totalMm.toFixed(2)} mm`);
}

/* ================================
   ARCO
================================ */
function measureArc() {
    const [center, start, end] = points;

    const r = Math.sqrt((start.x - center.x) ** 2 + (start.y - center.y) ** 2);

    const ang1 = Math.atan2(start.y - center.y, start.x - center.x);
    const ang2 = Math.atan2(end.y - center.y, end.x - center.x);

    let angle = Math.abs(ang2 - ang1);

    const arcLength = r * angle * scaleFactor * userScale;

    showResult(`Longitud del arco: ${arcLength.toFixed(2)} mm`);
}

/* ================================
   ESCALA FIJA
================================ */
function setScale() {
    const distPx = Math.sqrt(
        (points[1].x - points[0].x) ** 2 +
        (points[1].y - points[0].y) ** 2
    );

    let realMm = prompt("¿Cuántos milímetros representa esta distancia?");

    if (!realMm || realMm <= 0) {
        alert("Valor inválido");
        points = [];
        return;
    }

    scaleFactor = realMm / distPx;

    showResult(`Escala establecida: 1 px = ${scaleFactor.toFixed(4)} mm`);
}

/* ================================
   GUARDAR IMAGEN
================================ */
function saveImage() {
    let link = document.createElement('a');
    link.download = "medicion.png";
    link.href = canvas.toDataURL();
    link.click();
}

/* ================================
   MOSTRAR RESULTADO
================================ */
function showResult(text) {
    document.getElementById("resultBox").innerHTML = text;
}

</script>

<style>
.btn {
    padding: 8px 16px;
    background: #2563eb;
    color: white;
    border-radius: 6px;
    transition: 0.2s;
}
.btn:hover {
    background: #1d4ed8;
}
</style>
@endsection
