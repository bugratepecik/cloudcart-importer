<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Yükleme</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container mt-5 w-100">
    <!-- Kart Başlangıcı -->
    <div class="card shadow-sm">
        <div class="card-header text-center">
            <h2 class="mb-0">CSV Dosyanızı Yükleyin</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ url('/cloudcart/upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="csv_file" class="form-label">CSV Dosyasını Seçin</label>
                    <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Yükle</button>
            </form>
        </div>
    </div>
    <!-- Kart Bitişi -->
</div>

<!-- SweetAlert Kullanımı -->
<script>
    @if(session('success'))
    Swal.fire({
        title: 'Başarılı!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'Tamam'
    });
    @endif
</script>

<!-- Bootstrap ve SweetAlert Scriptleri -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
