<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Yükleme</title>
</head>
<body>
<h2>CSV Dosyanızı Yükleyin</h2>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<form action="{{ url('/cloudcart/upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="csv_file" accept=".csv" required>
    <button type="submit">Yükle</button>
</form>
</body>
</html>
