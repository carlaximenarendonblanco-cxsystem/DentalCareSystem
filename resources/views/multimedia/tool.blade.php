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

{{-- Botones --}}
<div class="relative flex justify-center space-x-2 mb-6">
    @php
    $filters = [
        ['id'=>'zoomIn','img'=>'zoom.png','title'=>'Acercar'],
        ['id'=>'zoomOut','img'=>'unzoom.png','title'=>'Alejar'],
        ['id'=>'invertColors','img'=>'negative.png','title'=>'Negativo'],
        ['id'=>'increaseBrightness','img'=>'filter3.png','title'=>'Más Brillo'],
        ['id'=>'decreaseBrightness','img'=>'filter4.png','title'=>'Menos Brillo'],
        ['id'=>'increaseContrast','img'=>'filter1.png','title'=>'Más Contraste'],
        ['id'=>'decreaseContrast','img'=>'filter2.png','title'=>'Menos Contraste'],
        ['id'=>'downloadImage','img'=>'download.png','title'=>'Descargar'],
    ];
    @endphp

    @foreach($filters as $filter)
    <div class="group relative">
        <button id="{{ $filter['id'] }}" class="btnimg">
            <img src="{{ asset('assets/images/'.$filter['img']) }}" width="50">
        </button>
        <div class="hidden group-hover:block absolute left-0 mt-2 bg-gray-800 text-white text-xs rounded-md px-2 py-1">
            {{ $filter['title'] }}
        </div>
    </div>
    @endforeach
</div>

{{-- CONTENEDOR - movimiento mejorado --}}
<div id="imgContainer" class="relative flex justify-center border rounded-lg overflow-hidden bg-black mx-auto"
     style="width:1100px; height:800px; cursor:grab;">
    <img id="studyImage" src="{{ $imageUrls[0] ?? '' }}" draggable="false"
         style="max-width:100%; max-height:100%; object-fit:contain; transform:scale(1); user-select:none; transition:transform .2s;">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const img = document.getElementById('studyImage');
    const container = document.getElementById('imgContainer');
    const imageSelect = document.getElementById('imageSelect');

    let zoom = 1, brightness = 0, contrast = 1, negative = false;

    function applyFilters(){
        img.style.filter = `
            brightness(${1 + brightness})
            contrast(${contrast})
            ${negative ? 'invert(1)' : ''}
        `;
    }

    /* CAMBIAR IMAGEN */
    imageSelect.onchange = e=>{
        img.src = e.target.value;
        zoom=1; brightness=0; contrast=1; negative=false;
        img.style.transform=`scale(1)`;
        applyFilters();
    }

    /* ZOOM */
    zoomIn.onclick = ()=>{ zoom = Math.min(3, zoom+0.15); img.style.transform=`scale(${zoom})`; }
    zoomOut.onclick = ()=>{ zoom = Math.max(.4, zoom-0.15); img.style.transform=`scale(${zoom})`; }

    /* FILTROS */
    increaseBrightness.onclick =()=>{ brightness = Math.min(brightness+0.1,1); applyFilters(); }
    decreaseBrightness.onclick =()=>{ brightness = Math.max(brightness-0.1,-1); applyFilters(); }
    increaseContrast.onclick   =()=>{ contrast = Math.min(contrast+0.1,3); applyFilters(); }
    decreaseContrast.onclick   =()=>{ contrast = Math.max(contrast-0.1,0); applyFilters(); }
    invertColors.onclick       =()=>{ negative = !negative; applyFilters(); }

    /* DESCARGAR IMAGEN */
    downloadImage.onclick = ()=>{
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        let temp = new Image(); temp.crossOrigin="anonymous";
        temp.onload = ()=>{
            canvas.width=temp.width; canvas.height=temp.height;
            ctx.filter = `brightness(${1+brightness}) contrast(${contrast}) ${negative?'invert(1)':''}`;
            ctx.drawImage(temp,0,0);
            let a=document.createElement('a');
            a.download="imagen_filtrada.png";
            a.href=canvas.toDataURL("image/png");
            a.click();
        }
        temp.src=img.src;
    };

    /* DRAG PARA MOVER LA IMAGEN */
    let drag=false, sx,sy, scrollX, scrollY;
    container.addEventListener('mousedown',e=>{
        drag=true; container.style.cursor='grabbing';
        sx=e.clientX; sy=e.clientY;
        scrollX=container.scrollLeft; scrollY=container.scrollTop;
    });
    window.addEventListener('mouseup',()=>{ drag=false; container.style.cursor='grab'; });
    container.addEventListener('mousemove',e=>{
        if(!drag || zoom<=1) return;
        container.scrollLeft=scrollX-(e.clientX-sx);
        container.scrollTop =scrollY-(e.clientY-sy);
    });

});
</script>
@endsection
