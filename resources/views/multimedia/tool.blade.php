@extends('layouts._partials.layout')
@section('title', 'Herramientas Multimedia')
@section('subtitle')
{{ __('Herramientas') }}
@endsection

@section('content')
<div class="flex justify-end pt-5 pr-5">
    <a href="{{ route('multimedia.show', $study->id) }}" class="botton1">Volver al Estudio</a>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">
    {{ $study->name_patient }} - {{ $study->ci_patient }}
</h1>

{{-- Selección de imagen --}}
<div class="flex justify-center mb-4">
    <select id="imageSelect" class="border border-gray-300 rounded-lg p-2">
        @foreach($imageUrls as $url)
            <option value="{{ $url }}">Imagen {{ $loop->iteration }}</option>
        @endforeach
    </select>
</div>

{{-- Botones de filtros --}}
<div class="relative flex justify-center space-x-2 mb-6">
    @php
        $filters = [
            ['id'=>'zoomIn','img'=>'zoom.png','title'=>'Acercar'],
            ['id'=>'zoomOut','img'=>'unzoom.png','title'=>'Alejar'],
            ['id'=>'magnifier','img'=>'lupa.png','title'=>'Lupa'],
            ['id'=>'invertColors','img'=>'negative.png','title'=>'Negativo'],
            ['id'=>'increaseBrightness','img'=>'filter3.png','title'=>'Más Brillo'],
            ['id'=>'decreaseBrightness','img'=>'filter4.png','title'=>'Menos Brillo'],
            ['id'=>'increaseContrast','img'=>'filter1.png','title'=>'Más Contraste'],
            ['id'=>'decreaseContrast','img'=>'filter2.png','title'=>'Menos Contraste'],
            ['id'=>'edgesButton','img'=>'edge.png','title'=>'Bordes'],
            ['id'=>'save','img'=>'save.png','title'=>'Guardar'],
            ['id'=>'downloadImage','img'=>'download.png','title'=>'Descargar'],
        ];
    @endphp
    @foreach($filters as $filter)
    <div class="group relative">
        @if($filter['id'] === 'save')
        <form id="saveImageForm" action="{{ route('tool.store', ['radiography_id' => 0, 'tomography_id' => 0, 'ci_patient' => $study->ci_patient, 'id' => $study->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <button id="{{ $filter['id'] }}" class="btnimg" type="submit">
                <img src="{{ asset('assets/images/'.$filter['img']) }}" width="50" height="50">
            </button>
        </form>
        @else
            <button id="{{ $filter['id'] }}" class="btnimg">
                <img src="{{ asset('assets/images/'.$filter['img']) }}" width="50" height="50">
            </button>
        @endif
        <div class="hidden group-hover:block absolute left-0 mt-2 bg-gray-500 bg-opacity-50 text-center rounded-md px-2 py-1">
            <span class="text-sm text-gray-100">{{ $filter['title'] }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Imagen principal --}}
<div class="relative flex justify-center mt-4 mb-4">
    <img id="studyImage" src="{{ $imageUrls[0] ?? '' }}" class="border rounded-lg" style="max-width:1100px; max-height:800px;">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const img = document.getElementById('studyImage');
    const imageSelect = document.getElementById('imageSelect');

    let zoom = 1;
    let brightness = 0;
    let contrast = 1;
    let isNegative = false;
    let edgesApplied = false;

    function updateFilters() {
        let filterStr = `brightness(${1 + brightness}) contrast(${contrast})`;
        if(isNegative) filterStr += ' invert(1)';
        img.style.filter = filterStr;
    }

    imageSelect.addEventListener('change', (e) => {
        img.src = e.target.value;
        zoom = 1; brightness = 0; contrast = 1; isNegative = false; edgesApplied = false;
        img.style.transform = `scale(1)`;
        updateFilters();
    });

    document.getElementById('zoomIn').addEventListener('click', () => {
        zoom += 0.1;
        img.style.transform = `scale(${zoom})`;
    });
    document.getElementById('zoomOut').addEventListener('click', () => {
        zoom = Math.max(0.1, zoom - 0.1);
        img.style.transform = `scale(${zoom})`;
    });

    document.getElementById('increaseBrightness').addEventListener('click', () => { brightness = Math.min(brightness + 0.1, 1); updateFilters(); });
    document.getElementById('decreaseBrightness').addEventListener('click', () => { brightness = Math.max(brightness - 0.1, -1); updateFilters(); });
    document.getElementById('increaseContrast').addEventListener('click', () => { contrast = Math.min(contrast + 0.1, 3); updateFilters(); });
    document.getElementById('decreaseContrast').addEventListener('click', () => { contrast = Math.max(contrast - 0.1, 0); updateFilters(); });
    document.getElementById('invertColors').addEventListener('click', () => { isNegative = !isNegative; updateFilters(); });

    // Borde (sobel simplificado usando canvas)
    document.getElementById('edgesButton').addEventListener('click', () => {
        if(edgesApplied){
            img.src = imageSelect.value;
            edgesApplied = false;
            updateFilters();
            return;
        }

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const tempImg = new Image();
        tempImg.crossOrigin = "anonymous";
        tempImg.onload = () => {
            canvas.width = tempImg.width;
            canvas.height = tempImg.height;
            ctx.drawImage(tempImg,0,0);
            let imageData = ctx.getImageData(0,0,canvas.width,canvas.height);
            let data = imageData.data;
            for(let i=0;i<data.length;i+=4){
                const avg = (data[i]+data[i+1]+data[i+2])/3;
                data[i]=data[i+1]=data[i+2]=avg;
            }
            ctx.putImageData(imageData,0,0);
            img.src = canvas.toDataURL();
            edgesApplied = true;
        }
        tempImg.src = img.src;
    });

    // Descargar
    document.getElementById('downloadImage').addEventListener('click', () => {
        const link = document.createElement('a');
        link.download = 'imagen_filtrada.png';
        link.href = img.src;
        link.click();
    });
});
</script>
@endsection
