<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$query = mysqli_query($conn, "SELECT p.*, c.nama_kategori FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = $id");
$product = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $product['nama_produk']; ?> - RETRO-HUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background: #fff9ed; color: #4a3568; }
        #map { height: 220px; border-radius: 12px; border: 2px solid #ff5c9a; margin-top: 10px; }
    </style>
</head>
<body class="p-4">
<div class="container">
    <a href="index.php" class="btn btn-outline-dark mb-4">← Kembali</a>
    
    <div class="row">
        <div class="col-md-5 text-center">
            <img src="assets/uploads/<?= $product['gambar']; ?>" class="img-fluid rounded" alt="Produk" onerror="this.src='https://via.placeholder.com/300'">
        </div>
        <div class="col-md-7">
            <span class="badge bg-danger"><?= $product['nama_kategori']; ?></span>
            <h2 class="fw-bold mt-2"><?= $product['nama_produk']; ?></h2>
            <h4 class="text-danger fw-bold">Rp <?= number_format($product['harga'], 0, ',', '.'); ?></h4>
            <p><?= $product['deskripsi']; ?></p>
            
            <hr>

            <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-warning">
                    <strong>Perhatian!</strong> Anda harus Login terlebih dahulu sebelum membeli.
                </div>
                <a href="login.php" class="btn btn-danger btn-lg w-100 fw-bold">LOGIN UNTUK MEMBELI</a>
            <?php else: ?>
                <h5 class="fw-bold">Form Checkout & Alamat Terkini</h5>
                <form action="order.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <div class="mb-2">
                        <input type="text" name="nama_pembeli" class="form-control" placeholder="Nama Lengkap Penerima" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="telepon" class="form-control" placeholder="Nomor WhatsApp/Telepon" required>
                    </div>
                    <div class="mb-2">
                        <textarea name="alamat_lengkap" class="form-control" rows="2" placeholder="Alamat Detail Lengkap" required></textarea>
                    </div>
                    
                    <label class="form-label fw-bold small text-muted">LOKASI MAPS TERKINI:</label>
                    <button type="button" class="btn btn-sm btn-info text-white mb-2" onclick="getLocation()">📍 Gunakan GPS Lokasi Saya</button>
                    <div id="map"></div>
                    <input type="hidden" id="lat" name="latitude">
                    <input type="hidden" id="lng" name="longitude">

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold mt-3">BELI SEKARANG</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var defaultLat = -6.200000;
    var defaultLng = 106.816666;

    var map = L.map('map').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    var marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

    function updateCoords(lat, lng) {
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    }
    updateCoords(defaultLat, defaultLng);

    marker.on('dragend', function (e) {
        var latlng = marker.getLatLng();
        updateCoords(latlng.lat, latlng.lng);
    });

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);
                updateCoords(lat, lng);
            });
        }
    }
</script>
</body>
</html>