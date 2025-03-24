<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Yükleme</title>
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

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
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<!-- Bootstrap ve SweetAlert Scriptleri -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        let results = @json(session('upload_results'));

        if (results && results.responses) {
            results.responses.forEach(result => {
                let message = result.status_code === 201
                    ? "Başarıyla eklendi!"
                    : (result.error_message ? result.error_message : "Veritabanına eklenemedi!");

                showToast(
                    result.product_name,
                    message,
                    result.status_code === 201 ? "success" : "danger",
                    result.status_code
                );
            });
        }
    });

    function showToast(title, message, type, status_code) {
        let toastContainer = document.getElementById("toastContainer");

        let toastHTML = `
            <div class="toast align-items-center mt-sm-1 text-white bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                    ${status_code} - <strong>${title}</strong> - ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;

        let toastElement = document.createElement("div");
        toastElement.innerHTML = toastHTML;
        toastContainer.appendChild(toastElement);

        let toast = new bootstrap.Toast(toastElement.querySelector(".toast"));
        toast.show();

        setTimeout(() => {
            toastElement.remove();
        }, 10000);
    }
</script>

</html>
