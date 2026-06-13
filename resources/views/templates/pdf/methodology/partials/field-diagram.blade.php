@php
    $items = is_array($items ?? null) ? $items : [];
    $number = fn ($value, $default = 50) => is_numeric($value) ? max(0, min(100, (float) $value)) : $default;
@endphp

<svg xmlns="http://www.w3.org/2000/svg" width="255" height="164" viewBox="0 0 100 64">
    <rect x="1" y="1" width="98" height="62" rx="1.5" fill="#ecf8ef" stroke="#39765b" stroke-width="0.45" />
    <line x1="50" y1="1" x2="50" y2="63" stroke="#39765b" stroke-width="0.45" />
    <circle cx="50" cy="32" r="9" fill="none" stroke="#39765b" stroke-width="0.45" />
    <circle cx="50" cy="32" r="1" fill="#39765b" />
    <rect x="1" y="18" width="16" height="28" fill="none" stroke="#39765b" stroke-width="0.45" />
    <rect x="83" y="18" width="16" height="28" fill="none" stroke="#39765b" stroke-width="0.45" />
    <rect x="1" y="24" width="6" height="16" fill="none" stroke="#39765b" stroke-width="0.45" />
    <rect x="93" y="24" width="6" height="16" fill="none" stroke="#39765b" stroke-width="0.45" />
    <circle cx="11" cy="32" r="1" fill="#39765b" />
    <circle cx="89" cy="32" r="1" fill="#39765b" />

    @foreach($items as $item)
        @php
            $type = data_get($item, 'type', 'player');
            $x = $number(data_get($item, 'x'), 50);
            $y = $number(data_get($item, 'y'), 32);
            $label = (string) data_get($item, 'label', '');
            $rotation = is_numeric(data_get($item, 'rotation')) ? fmod((float) data_get($item, 'rotation'), 360) : 0;
            $rotation = $rotation < 0 ? $rotation + 360 : $rotation;
        @endphp

        @if($type === 'cone')
            <path d="M {{ $x }} {{ $y - 3 }} L {{ $x - 3 }} {{ $y + 3 }} L {{ $x + 3 }} {{ $y + 3 }} Z" fill="#f97316" />
        @elseif($type === 'ball')
            <circle cx="{{ $x }}" cy="{{ $y }}" r="2.2" fill="#111827" />
        @elseif($type === 'arrow')
            <g transform="rotate({{ $rotation }} {{ $x }} {{ $y }})">
                <line x1="{{ $x - 4 }}" y1="{{ $y + 2.4 }}" x2="{{ $x + 3.15 }}" y2="{{ $y - 1.9 }}" stroke="#b91c1c" stroke-width="1.1" stroke-linecap="round" />
                <path d="M {{ $x + 4.6 }} {{ $y - 2.75 }} L {{ $x + 1.85 }} {{ $y - 2.95 }} L {{ $x + 3.15 }} {{ $y - 0.75 }} Z" fill="#b91c1c" />
            </g>
        @elseif($type === 'xmark')
            <line x1="{{ $x - 2.4 }}" y1="{{ $y - 2.4 }}" x2="{{ $x + 2.4 }}" y2="{{ $y + 2.4 }}" stroke="#111827" stroke-width="1.05" stroke-linecap="round" />
            <line x1="{{ $x + 2.4 }}" y1="{{ $y - 2.4 }}" x2="{{ $x - 2.4 }}" y2="{{ $y + 2.4 }}" stroke="#111827" stroke-width="1.05" stroke-linecap="round" />
        @elseif($type === 'text')
            <text x="{{ $x }}" y="{{ $y }}" fill="#111827" font-size="4" font-weight="700" dominant-baseline="middle" text-anchor="middle">{{ $label !== '' ? $label : 'Texto' }}</text>
        @else
            <circle cx="{{ $x }}" cy="{{ $y }}" r="2.8" fill="#2563eb" />
        @endif
    @endforeach
</svg>
