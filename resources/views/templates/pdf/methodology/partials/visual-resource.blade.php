@php
    $mode = ($mode ?? 'diagram') === 'image' ? 'image' : 'diagram';
    $imagePath = $imagePath ?? null;
    $imageLocalPath = null;

    if ($mode === 'image' && is_string($imagePath) && $imagePath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
        $imageLocalPath = \Illuminate\Support\Facades\Storage::disk('public')->path($imagePath);
    }
@endphp

@if($imageLocalPath)
    <img class="visual-resource-image" src="{{ $imageLocalPath }}" alt="Imagen de fase">
@else
    @include('templates.pdf.methodology.partials.field-diagram', ['items' => $items ?? []])
@endif
