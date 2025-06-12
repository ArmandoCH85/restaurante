@props([
    'product',
    'size' => 'md', // xs, sm, md, lg, xl
    'shape' => 'rounded', // rounded, circle, square
    'showCategory' => false,
    'class' => '',
    'loading' => 'lazy'
])

@php
    $sizeClasses = [
        'xs' => 'product-image-xs',
        'sm' => 'product-image-sm',
        'md' => 'product-image-md',
        'lg' => 'product-image-lg',
        'xl' => 'product-image-xl'
    ];

    $shapeClasses = [
        'rounded' => 'product-image-rounded',
        'circle' => 'product-image-circle',
        'square' => 'product-image-square'
    ];

    $sizeClass = $sizeClasses[$size] ?? 'product-image-md';
    $shapeClass = $shapeClasses[$shape] ?? 'product-image-rounded';
@endphp

<div class="product-image-container {{ $sizeClass }} {{ $shapeClass }} {{ $class }}">
    @if($product->image_path ?? $product->image ?? null)
        <img
            src="{{ $product->image ?? asset('storage/' . $product->image_path) }}"
            alt="{{ $product->name }}"
            class="product-image"
            loading="{{ $loading }}"
            onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'product-image-fallback\'><div class=\'product-initials\'>{{ strtoupper(substr($product->name, 0, 2)) }}</div>{{ $showCategory && ($product->category->name ?? null) ? '<div class=\'product-category-badge\'>' . $product->category->name . '</div>' : '' }}</div>';"
        >
    @else
        <div class="product-image-fallback">
            <div class="product-initials">
                {{ strtoupper(substr($product->name, 0, 2)) }}
            </div>
            @if($showCategory && ($product->category->name ?? null))
                <div class="product-category-badge">
                    {{ $product->category->name }}
                </div>
            @endif
        </div>
    @endif

    @if($product->available === false ?? false)
        <div class="product-image-unavailable"></div>
    @endif
</div>
