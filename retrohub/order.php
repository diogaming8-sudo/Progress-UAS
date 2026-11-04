<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id        = $_SESSION['user_id'];
    $product_id     = (int)$_POST['product_id'];
    $nama_pembeli   = mysqli_real_escape_string($conn, $_POST['nama_pembeli']);
    $telepon        = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat_lengkap = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $latitude       = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude      = mysqli_real_escape_string($conn, $_POST['longitude']);

    $query = "INSERT INTO orders (user_id, product_id, nama_pembeli, telepon, alamat_lengkap, latitude, longitude) 
              VALUES ('$user_id', '$product_id', '$nama_pembeli', '$telepon', '$alamat_lengkap', '$latitude', '$longitude')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='index.php';</script>";
    } else {
        echo "Gagal menyimpan pesanan: " . mysqli_error($conn);
    }
}
?>