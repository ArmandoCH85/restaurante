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
            max-width: 1200px;
            margin: 0 auto;
        }
        .test-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .image-test {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
        }
        .image-box {
            width: 100%;
            height: 200px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .bg-image {
            width: 100%;
            height: 200px;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            border: 1px solid #eee;
        }
        h1, h2 {
            color: #333;
        }
        .code {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Prueba de Visualización de Imágenes</h1>
    
    <div class="test-container">
        @foreach($products as $product)
            <div class="image-test">
                <h2>{{ $product->name }}</h2>
                <p>ID: {{ $product->id }} | Ruta: {{ $product->image_path }}</p>
                
                <h3>1. Con asset('storage/' . path):</h3>
                <div class="image-box">
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}">
                </div>
                <div class="code">
                    &lt;img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"&gt;
                </div>
                
                <h3>2. Con URL directa:</h3>
                <div class="image-box">
                    <img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}">
                </div>
                <div class="code">
                    &lt;img src="/storage/{{ $product->image_path }}" alt="{{ $product->name }}"&gt;
                </div>
                
                <h3>3. Con background-image:</h3>
                <div class="bg-image" style="background-image: url('{{ asset('storage/' . $product->image_path) }}')"></div>
                <div class="code">
                    style="background-image: url('{{ asset('storage/' . $product->image_path) }}')"
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
