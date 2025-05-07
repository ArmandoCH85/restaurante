<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Imágenes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .image-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            width: 300px;
        }
        .image-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        .image-info {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Prueba de Visualización de Imágenes</h1>

    <div class="image-container">
        @foreach($products as $product)
            <div class="image-card">
                <h3>{{ $product->name }}</h3>

                @if($product->image_path)
                    <div>
                        <h4>1. Con asset():</h4>
                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}">
                    </div>

                    <div>
                        <h4>2. Con asset('storage/'):</h4>
                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                    </div>

                    <div>
                        <h4>3. Con ruta directa /storage/:</h4>
                        <img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}">
                    </div>

                    <div>
                        <h4>4. Con URL completa:</h4>
                        <img src="http://restaurante.test/storage/{{ $product->image_path }}" alt="{{ $product->name }}">
                    </div>

                    <div class="image-info">
                        <p><strong>Ruta en BD:</strong> {{ $product->image_path }}</p>
                    </div>
                @else
                    <p>No tiene imagen</p>
                @endif
            </div>
        @endforeach
    </div>
</body>
</html>
