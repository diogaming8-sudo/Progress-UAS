<?php
require_once 'config.php';

// Ambil data produk & kategori dari database Laragon
$categories_query = mysqli_query($conn, "SELECT * FROM categories");
$products_query   = mysqli_query($conn, "SELECT p.*, c.nama_kategori, c.slug FROM products p JOIN categories c ON p.category_id = c.id");
$orders_query     = mysqli_query($conn, "SELECT o.*, p.nama_produk, p.harga FROM orders o JOIN products p ON o.product_id = p.id ORDER BY o.id DESC");

// Cek apakah user yang sedang login adalah Admin / Penjual
$is_admin = false;
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'penjual')) {
    $is_admin = true;
}

// Fungsi pembantu mencocokkan gambar secara otomatis
function getProductImage($imgName, $productName) {
    if(!empty($imgName) && file_exists($imgName)) {
        return $imgName;
    }
    $pLower = strtolower($productName);
    if(strpos($pLower, 'walkman') !== false) return 'Sony_Walkman_Pink_Y2K_Illustration.png';
    if(strpos($pLower, 'discman') !== false || strpos($pLower, 'mp3') !== false) return 'MP3_Pink_Y2K_Illustration.png';
    if(strpos($pLower, 'playstation') !== false || strpos($pLower, 'psp') !== false) return 'PSP_Pink_Y2K_Illustration.png';
    if(strpos($pLower, 'cyber') !== false || strpos($pLower, 'kamera') !== false) return 'Kamera_Pink_Y2K_Illustration.png';
    
    return 'retro-removebg-preview.png';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Retro Electronics Hub - Y2K World</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Silkscreen:wght@400;700&family=VT323&family=Press+Start+2P&display=swap" rel="stylesheet">
<style>
:root{--pink:#ff5c9a;--hot-pink:#ff2f7d;--light-pink:#ffd6e7;--blue:#65dff2;--sky:#c9f7ff;--dark-blue:#0097b2;--yellow:#ffe66d;--orange:#ffad4d;--purple:#a66cff;--lavender:#eadcff;--cream:#fff9ed;--white:#fff;--dark:#4a3568}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;font-family:'Fredoka',sans-serif;background:radial-gradient(circle at 10% 10%,rgba(255,255,255,.8) 0 5px,transparent 6px),radial-gradient(circle at 90% 20%,rgba(255,255,255,.8) 0 4px,transparent 5px),var(--cream);background-size:80px 80px;color:var(--dark);overflow-x:hidden;font-size:18px;line-height:1.7}

/* NAVBAR */
.navbar-retro{background:rgba(255,255,255,.88);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px);border-bottom:4px solid var(--pink);box-shadow:0 6px 0 rgba(255,92,154,.12);padding:14px 0}
.navbar-brand{font-family:'Silkscreen',monospace;color:var(--hot-pink)!important;font-size:24px!important;text-shadow:3px 3px 0 var(--yellow);cursor:pointer}
.nav-link{color:var(--dark)!important;font-weight:600;margin-left:12px;border-radius:12px;transition:.2s;cursor:pointer}
.nav-link:hover,.nav-link.active{background:var(--light-pink);color:var(--hot-pink)!important}
.form-select,.form-control{border:2px solid var(--pink);border-radius:12px;font-family:'Fredoka',sans-serif;font-size:16px!important}

/* MULTI-VIEW SECTION */
.view-section{display:none;animation:fadeIn 0.3s ease-in-out}
.view-section.active-view{display:block}
@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* HERO */
.hero{min-height:650px;display:flex;align-items:center;overflow:hidden;position:relative;background:linear-gradient(135deg,#ffe3ee,#d8faff,#eee1ff)}
.hero h1{font-family:'Press Start 2P';font-size:clamp(28px,4vw,52px)!important;line-height:1.35;color:var(--hot-pink);text-shadow:4px 4px 0 #fff,7px 7px 0 var(--blue)}
.hero p{font-size:21px!important;line-height:1.75;color:#604b79}
.hero-badge{display:inline-block;background:var(--yellow);color:var(--dark);padding:8px 15px;border-radius:30px;font-weight:700;transform:rotate(-3deg);box-shadow:4px 4px 0 var(--orange);margin-bottom:20px}

/* RETRO TV */
.retro-tv{width:390px;max-width:90%;margin:auto;background:#ffcae0;border:8px solid #fff;border-radius:35px;padding:25px;box-shadow:12px 12px 0 var(--hot-pink),20px 20px 0 rgba(101,223,242,.7);transform:rotate(2deg);position:relative}
.tv-screen{height:230px;background:linear-gradient(135deg,#65dff2,#a9f2ff);border:12px solid #6d5a76;border-radius:50%/15%;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center}
.tv-screen video{width:100%;height:100%;object-fit:cover}

/* FLOATING */
.floating{position:absolute;font-size:40px;animation:floating 4s ease-in-out infinite;z-index:2}
@keyframes floating{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-15px) rotate(5deg)}}
.star-1{top:100px;left:4%}.star-2{top:250px;right:5%;animation-delay:1s}.star-3{bottom:80px;left:8%;animation-delay:2s}

/* HISTORY & CARDS */
.section-title{font-family:'Silkscreen',monospace;color:var(--hot-pink);text-align:center;font-size:32px!important;text-shadow:3px 3px 0 var(--blue);margin-bottom:14px}
.section-subtitle{text-align:center;color:#604b79;max-width:680px;margin:0 auto 45px;line-height:1.7;font-size:18px!important}
.history-section{padding:80px 0;background:#fff}
.sony-history-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;margin-top:35px}
.sony-history-card{background:#fff;border:4px solid #604b79;border-radius:18px;overflow:hidden;box-shadow:6px 7px 0 #ffe66d;transition:transform .2s ease}
.sony-history-card:hover{transform:translateY(-6px) rotate(-1deg);box-shadow:8px 10px 0 var(--hot-pink)}
.sony-history-image{height:210px;background:#f4f0ff;display:flex;align-items:center;justify-content:center;padding:18px}
.sony-history-image img{width:100%;height:100%;object-fit:contain}
.sony-history-info{padding:18px;background:#fff8fc}
.sony-history-info span{display:inline-block;font-family:'Silkscreen',monospace;font-size:11px;color:#fff;background:var(--hot-pink);padding:6px 9px;border-radius:6px}
.sony-history-info h3{font-size:22px;color:#604b79;margin:10px 0 6px}

.read-story-btn{display:inline-block;margin-top:12px;padding:9px 14px;background:var(--hot-pink);color:#fff!important;border:3px solid #fff;border-radius:10px;box-shadow:4px 4px 0 var(--yellow);font-family:'Silkscreen',monospace;font-size:11px;text-decoration:none}

/* STORIES ARCHIVE */
.story-archive{padding:50px 0;background:#fff9ed;border-top:4px dashed var(--pink);margin-top:50px}
.story-panel{background:#fff;border:4px solid #604b79;border-radius:24px;padding:30px;margin-bottom:30px;box-shadow:8px 8px 0 var(--pink)}
.story-panel h3{font-family:'Silkscreen',monospace;color:var(--hot-pink);font-size:26px}
.story-note{background:#fff3d1;border-left:6px solid var(--orange);padding:15px;border-radius:12px;font-weight:500;margin-top:20px}

/* ARCADE GAMES SECTION */
.arcade-container{background:#231a38;border:6px solid #604b79;border-radius:30px;padding:30px;box-shadow:12px 12px 0 var(--hot-pink);color:#fff}
.arcade-title{font-family:'Press Start 2P';color:var(--yellow);text-shadow:3px 3px 0 var(--hot-pink);font-size:26px}
.game-box-card{background:#2d2247;border:4px solid var(--pink);border-radius:20px;padding:20px;text-align:center}
canvas.game-canvas{background:#baf4ff;border:4px solid #fff;border-radius:16px;box-shadow:0 8px 0 rgba(0,0,0,.3);max-width:100%}

/* FLOATING SLIDE MUSIC PLAYER WIDGET */
.floating-music-widget {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.music-widget-box {
    width: 320px;
    background: #fff;
    border: 4px solid #604b79;
    border-radius: 20px;
    padding: 18px;
    box-shadow: 8px 8px 0 var(--hot-pink);
    position: relative;
}
.floating-music-widget.collapsed .music-widget-box {
    display: none;
}
.music-toggle-btn {
    background: var(--yellow);
    border: 3px solid #604b79;
    border-radius: 50px;
    padding: 10px 18px;
    font-family: 'Silkscreen', monospace;
    font-size: 13px;
    font-weight: bold;
    color: var(--dark);
    box-shadow: 4px 4px 0 var(--hot-pink);
    cursor: pointer;
    transition: transform 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.music-toggle-btn:hover {
    transform: scale(1.05);
}

/* ABOUT & FOOTER */
.about-section{padding:80px 0;background:#fff}
.about-box{max-width:850px;margin:auto;text-align:center;padding:45px;border-radius:30px;background:linear-gradient(135deg,#fff0f7,#e8fbff);border:3px solid #ffd1e2}
.retro-footer{background:#4a3568;color:#fff;padding:40px 0 20px;border-top:6px solid var(--pink)}

@media(max-width:992px){.sony-history-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:576px){.sony-history-grid{grid-template-columns:1fr}}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-retro sticky-top">
    <div class="container">
        <a class="navbar-brand" onclick="switchDashboard('home')">RETRO-HUB</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" id="nav-home" onclick="switchDashboard('home')">Home</a></li>
                <li class="nav-item"><a class="nav-link" id="nav-katalog" onclick="switchDashboard('katalog', 'all')">Katalog Jualan</a></li>
                <li class="nav-item"><a class="nav-link" id="nav-game" onclick="switchDashboard('game')">Arcade Games 🎮</a></li>
                <li class="nav-item"><a class="nav-link" id="nav-about" onclick="switchDashboard('about')">About</a></li>
                
                <!-- TOMBOL ADMIN HANYA MUNCUL JIKA USER MERUPAKAN ADMIN / PENJUAL -->
                <?php if($is_admin): ?>
                <li class="nav-item ms-lg-2">
                    <button class="btn btn-sm btn-warning fw-bold px-3 text-dark border-2 border-dark" onclick="switchDashboard('admin')" style="border-radius:10px;">
                        ⚙️ Dashboard Penjual
                    </button>
                </li>
                <?php endif; ?>

                <li class="nav-item ms-lg-2">
                    <select class="form-select form-select-sm" id="languageSelect" style="width:75px;">
                        <option value="id">ID</option>
                        <option value="en">EN</option>
                    </select>
                </li>

                <li class="nav-item ms-2">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="badge bg-danger text-white px-2 py-2">👤 <?= htmlspecialchars($_SESSION['username']); ?> (<?= strtoupper($_SESSION['role'] ?? 'user'); ?>)</span>
                        <a href="logout.php" class="btn btn-sm btn-outline-dark fw-bold ms-1">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm btn-danger fw-bold px-3" style="border-radius:10px;">Login</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-0">

    <!-- ================= VIEW 1: HOME ================= -->
    <div id="view-home" class="view-section active-view">
        <!-- HERO -->
        <section id="home" class="hero">
            <div class="floating star-1">⭐</div>
            <div class="floating star-2">💿</div>
            <div class="floating star-3">💗</div>

            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <span class="hero-badge">✦ Y2K • RETRO • ELECTRONICS ✦</span>
                        <h1>Welcome to Retro Electronics!</h1>
                        <p class="mt-4">
                            Yuk nostalgia bareng teknologi yang pernah menemani kehidupan
                            sehari-hari. Dari radio, TV tabung, Walkman, CD player, kamera
                            digital, sampai game console yang ikonik.
                        </p>
                        <button onclick="switchDashboard('katalog', 'all')" class="read-story-btn border-0 py-3 px-4 fs-6">EXPLORE KATALOG →</button>
                    </div>

                    <div class="col-lg-6 text-center">
                        <div class="retro-tv">
                            <div class="tv-screen">
                                <video width="100%" autoplay muted loop playsinline>
                                    <source src="Video Bouncing.mp4" type="video/mp4">
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- RETRO SONY ELECTRONICS GRID -->
        <section class="history-section">
            <div class="container">
                <h2 class="section-title">RETRO SONY ELECTRONICS</h2>
                <p class="section-subtitle">
                    Koleksi perangkat elektronik Sony dari era retro yang menunjukkan
                    bagaimana teknologi berkembang dari waktu ke waktu.
                </p>

                <div class="sony-history-grid">
                    <?php mysqli_data_seek($products_query, 0); ?>
                    <?php if(mysqli_num_rows($products_query) > 0): ?>
                        <?php while($item = mysqli_fetch_assoc($products_query)): 
                            $imgSrc = getProductImage($item['gambar'], $item['nama_produk']);
                        ?>
                            <div class="sony-history-card">
                                <div class="sony-history-image">
                                    <img src="<?= $imgSrc; ?>" alt="<?= htmlspecialchars($item['nama_produk']); ?>">
                                </div>
                                <div class="sony-history-info">
                                    <span><?= htmlspecialchars($item['nama_kategori']); ?> / <?= htmlspecialchars($item['tahun']); ?></span>
                                    <h3><?= htmlspecialchars($item['nama_produk']); ?></h3>
                                    <p class="fw-bold text-danger mb-1 fs-5">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></p>
                                    <p><?= htmlspecialchars(substr($item['deskripsi'], 0, 65)); ?>...</p>
                                    <a href="detail.php?id=<?= $item['id']; ?>" class="read-story-btn">LIHAT & BELI →</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- ================= VIEW 2: KATALOG JUALAN TERPISAH + STORIES ================= -->
    <div id="view-katalog" class="view-section py-5 bg-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title text-start mb-0">✦ KATALOG PENJUALAN ✦</h2>
                <input type="search" id="catalogSearch" class="form-control w-25" placeholder="Cari barang..." onkeyup="filterCatalog()">
            </div>

            <div class="mb-4">
                <button class="btn btn-sm btn-outline-danger me-1 fw-bold px-3 py-2 active" onclick="filterCategory('all')">Semua Barang</button>
                <?php mysqli_data_seek($categories_query, 0); ?>
                <?php while($cat = mysqli_fetch_assoc($categories_query)): ?>
                    <button class="btn btn-sm btn-outline-danger me-1 fw-bold px-3 py-2" onclick="filterCategory('<?= $cat['slug']; ?>')"><?= $cat['nama_kategori']; ?></button>
                <?php endwhile; ?>
            </div>

            <div class="row g-4" id="catalogGrid">
                <?php mysqli_data_seek($products_query, 0); ?>
                <?php while($p = mysqli_fetch_assoc($products_query)): 
                    $pImgSrc = getProductImage($p['gambar'], $p['nama_produk']);
                ?>
                    <div class="col-md-3 col-6 catalog-item" data-category="<?= strtolower($p['slug']); ?>" data-name="<?= strtolower($p['nama_produk']); ?>">
                        <div class="sony-history-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="sony-history-image">
                                    <img src="<?= $pImgSrc; ?>" alt="Produk">
                                </div>
                                <div class="sony-history-info">
                                    <span><?= $p['nama_kategori']; ?></span>
                                    <h3 class="fs-5"><?= htmlspecialchars($p['nama_produk']); ?></h3>
                                    <div class="text-danger fw-bold fs-5">Rp <?= number_format($p['harga'], 0, ',', '.'); ?></div>
                                </div>
                            </div>
                            <div class="p-3 pt-0">
                                <a href="detail.php?id=<?= $p['id']; ?>" class="read-story-btn w-100 text-center">DETAIL & BELI →</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- STORIES ARCHIVE -->
            <section class="story-archive">
                <div class="text-center mb-4">
                    <div class="section-kicker fw-bold text-danger">✦ THE STORIES BEHIND THE DEVICES ✦</div>
                    <h2 class="section-title">More Than Just Electronics.</h2>
                </div>

                <article class="story-panel">
                    <div class="text-muted font-monospace small mb-2">ARCHIVE 001 / MUSIC CULTURE / 2006</div>
                    <h3>Walkman & Mixtapes: Music on Your Own Terms</h3>
                    <p>Pemutar kaset portabel seperti Sony Walkman mengubah kebiasaan mendengarkan musik dengan memungkinkan orang membawa musik ke sekolah, perjalanan, atau saat berjalan kaki.</p>
                    <div class="story-note">
                        RETRO FACT — Mixtape memungkinkan pendengarnya menyusun pengalaman musik sendiri. Pilihan lagu dan urutannya sering menjadi bagian dari pesan khusus.
                    </div>
                </article>

                <article class="story-panel">
                    <div class="text-muted font-monospace small mb-2">ARCHIVE 002 / DIGITAL MUSIC / 2000s</div>
                    <h3>MP3 Players: An Entire Music Library in Your Pocket</h3>
                    <p>Pemutar MP3 mengubah cara orang membawa musik. Berbeda dengan kaset, MP3 player menyimpan musik sebagai berkas digital tanpa harus mengganti kaset atau CD.</p>
                </article>
            </section>
        </div>
    </div>

    <!-- ================= VIEW 3: ARCADE GAMES ================= -->
    <div id="view-game" class="view-section py-5">
        <div class="container">
            <div class="arcade-container">
                <div class="text-center mb-4">
                    <div class="section-kicker text-warning fw-bold">✦ ARCADE CORNER ✦</div>
                    <h2 class="arcade-title mt-2">RETRO MINI GAMES</h2>
                    <p class="text-light">Pilih game favorit Anda di bawah ini dan pecahkan rekor skor tertinggi!</p>
                </div>

                <div class="row g-4">
                    <!-- GAME 1: PIXEL JUMP -->
                    <div class="col-lg-6">
                        <div class="game-box-card">
                            <h4 class="font-monospace text-warning mb-2">1. PIXEL JUMP 🏃</h4>
                            <p class="small text-light mb-3">Tekan <strong>SPACE</strong> di keyboard atau tombol JUMP untuk melompat!</p>
                            <canvas id="jumpCanvas" class="game-canvas" width="450" height="200"></canvas>
                            <div class="mt-3 d-flex justify-content-center align-items-center gap-3">
                                <button class="btn btn-danger btn-sm fw-bold px-3" onclick="startJumpGame()">RESTART ↗</button>
                                <button class="btn btn-warning btn-sm fw-bold px-3 text-dark" onclick="jumpAction()">JUMP ↑</button>
                                <span class="fw-bold text-warning">Skor: <span id="jumpScore">0</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- GAME 2: GAME ULAR (SNAKE) -->
                    <div class="col-lg-6">
                        <div class="game-box-card">
                            <h4 class="font-monospace text-warning mb-2">2. GAME ULAR (SNAKE) 🐍</h4>
                            <p class="small text-light mb-3">Gunakan <strong>Tombol Panah Keyboard (Up, Down, Left, Right)</strong>!</p>
                            <canvas id="snakeCanvas" class="game-canvas" width="450" height="200" style="background:#111b11;"></canvas>
                            <div class="mt-3 d-flex justify-content-center align-items-center gap-3">
                                <button class="btn btn-danger btn-sm fw-bold px-3" onclick="startSnakeGame()">RESTART ↗</button>
                                <span class="fw-bold text-warning">Skor: <span id="snakeScore">0</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= VIEW 4: ABOUT ================= -->
    <div id="view-about" class="view-section about-section">
        <div class="container">
            <div class="about-box">
                <h2>ABOUT ELECTRONICS</h2>
                <p>Elektronik merupakan bagian penting dari perkembangan teknologi. Dari radio, televisi, Walkman, hingga konsol game portable, setiap perangkat menunjukkan bagaimana komponen elektronik berkembang menjadi lebih ringkas dan praktis.</p>
            </div>
        </div>
    </div>

    <!-- ================= VIEW 5: DASHBOARD PENJUAL (DIPROTEKSI HANYA UNTUK ADMIN) ================= -->
    <div id="view-admin" class="view-section py-5 bg-white">
        <div class="container">
            <?php if($is_admin): ?>
                <h2 class="section-title text-start mb-4">⚙️ DASHBOARD PENJUAL (ADMIN)</h2>
                <div class="sony-history-card p-4">
                    <h5 class="fw-bold mb-3">📋 Data Pesanan Masuk & Koordinat Peta Maps</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Pembeli</th>
                                    <th>Produk</th>
                                    <th>Telepon</th>
                                    <th>Alamat Lengkap & Koordinat Maps</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($orders_query) > 0): ?>
                                    <?php $no=1; while($ord = mysqli_fetch_assoc($orders_query)): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($ord['nama_pembeli']); ?></td>
                                            <td><?= htmlspecialchars($ord['nama_produk']); ?></td>
                                            <td><?= htmlspecialchars($ord['telepon']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($ord['alamat_lengkap']); ?><br>
                                                <span class="badge bg-danger">Lat: <?= $ord['latitude']; ?>, Lng: <?= $ord['longitude']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada pesanan masuk.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger text-center p-5 rounded-4 border-3 border-dark">
                    <h3 class="fw-bold text-danger">⚠️ AKSES DITOLAK!</h3>
                    <p class="fs-5 text-dark mb-3">Halaman ini khusus untuk Penjual/Admin. Anda masuk sebagai Pembeli biasa.</p>
                    <a href="login.php" class="btn btn-danger fw-bold px-4 py-2">Login Sebagai Admin Penjual</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- FLOATING MUSIC PLAYER WIDGET -->
<div class="floating-music-widget" id="musicWidget">
    <button class="music-toggle-btn mb-2 ms-auto" onclick="toggleMusicWidget()">
        <span id="musicToggleIcon">🎵</span> <span id="musicToggleText">Tutup Player</span>
    </button>
    <div class="music-widget-box">
        <div style="font-family:'Silkscreen',monospace; color:var(--hot-pink); font-size:12px; margin-bottom:4px;">✦ RETRO MUSIC PLAYER ✦</div>
        <div style="font-weight:bold; font-size:14px;">Promiscuous</div>
        <div style="font-size:12px; color:#604b79;" class="mb-2">Nelly Furtado ft. Timbaland</div>
        <audio controls preload="metadata" class="w-100">
            <source src="Nelly Furtado - Promiscuous (Lyrics) ft. Timbaland.mp3" type="audio/mpeg">
        </audio>
    </div>
</div>

<!-- FOOTER -->
<footer class="retro-footer">
    <div class="container text-center">
        <div class="font-monospace fw-bold fs-4 text-warning mb-2" style="font-family:'Silkscreen',monospace;">RETRO-HUB</div>
        <div class="small text-light">© 2026 RETRO-HUB • Y2K Retro Electronics</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const IS_ADMIN = <?= $is_admin ? 'true' : 'false'; ?>;

function toggleMusicWidget() {
    const widget = document.getElementById('musicWidget');
    const icon = document.getElementById('musicToggleIcon');
    const text = document.getElementById('musicToggleText');
    
    widget.classList.toggle('collapsed');
    if(widget.classList.contains('collapsed')) {
        icon.innerText = '🎧';
        text.innerText = 'Putar Musik';
    } else {
        icon.innerText = '🎵';
        text.innerText = 'Tutup Player';
    }
}

function switchDashboard(viewName, categorySlug = 'all') {
    if(viewName === 'admin' && !IS_ADMIN) {
        alert('Akses ditolak! Anda harus login sebagai Penjual/Admin untuk membuka halaman ini.');
        return;
    }

    const views = document.querySelectorAll('.view-section');
    views.forEach(v => v.classList.remove('active-view'));

    const navs = document.querySelectorAll('.nav-link');
    navs.forEach(n => n.classList.remove('active'));

    const targetView = document.getElementById('view-' + viewName);
    if(targetView) targetView.classList.add('active-view');

    const targetNav = document.getElementById('nav-' + viewName);
    if(targetNav) targetNav.classList.add('active');

    if(viewName === 'katalog') filterCategory(categorySlug);
    if(viewName === 'game') {
        startJumpGame();
        startSnakeGame();
    }

    window.scrollTo({top: 0, behavior: 'smooth'});
}

function filterCategory(slug) {
    let items = document.querySelectorAll('.catalog-item');
    items.forEach(item => {
        let cat = item.getAttribute('data-category');
        if (slug === 'all' || cat === slug) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterCatalog() {
    let input = document.getElementById('catalogSearch').value.toLowerCase();
    let items = document.querySelectorAll('.catalog-item');
    items.forEach(item => {
        let name = item.getAttribute('data-name');
        if (name.includes(input)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

/* GAME 1: PIXEL JUMP */
const jumpCanvas = document.getElementById('jumpCanvas');
const jctx = jumpCanvas.getContext('2d');
let jumpPlayer, obstacleX, jumpScore, jumpTimer;

function startJumpGame() {
    clearInterval(jumpTimer);
    jumpPlayer = { y: 140, velocity: 0, jumping: false };
    obstacleX = 450;
    jumpScore = 0;
    document.getElementById('jumpScore').innerText = jumpScore;

    jumpTimer = setInterval(() => {
        jctx.clearRect(0, 0, 450, 200);

        jctx.fillStyle = '#74d6b1';
        jctx.fillRect(0, 175, 450, 25);

        jumpPlayer.velocity += 1.2;
        jumpPlayer.y += jumpPlayer.velocity;
        if(jumpPlayer.y >= 140) {
            jumpPlayer.y = 140;
            jumpPlayer.jumping = false;
        }
        jctx.fillStyle = '#ff2f7d';
        jctx.fillRect(50, jumpPlayer.y, 28, 35);

        obstacleX -= 7;
        if(obstacleX < -20) {
            obstacleX = 450;
            jumpScore++;
            document.getElementById('jumpScore').innerText = jumpScore;
        }
        jctx.fillStyle = '#4a3568';
        jctx.fillRect(obstacleX, 145, 22, 30);

        if(obstacleX < 78 && obstacleX > 30 && jumpPlayer.y > 115) {
            clearInterval(jumpTimer);
            alert('Game Over Pixel Jump! Skor Anda: ' + jumpScore);
        }
    }, 30);
}

function jumpAction() {
    if(!jumpPlayer.jumping) {
        jumpPlayer.velocity = -15;
        jumpPlayer.jumping = true;
    }
}

document.addEventListener('keydown', (e) => {
    if(e.code === 'Space') jumpAction();
});

/* GAME 2: GAME ULAR */
const snakeCanvas = document.getElementById('snakeCanvas');
const sctx = snakeCanvas.getContext('2d');
let snake, food, dx, dy, snakeScore, snakeTimer;

function startSnakeGame() {
    clearInterval(snakeTimer);
    snake = [{x: 100, y: 100}, {x: 90, y: 100}, {x: 80, y: 100}];
    food = {x: 200, y: 100};
    dx = 10; dy = 0;
    snakeScore = 0;
    document.getElementById('snakeScore').innerText = snakeScore;

    snakeTimer = setInterval(() => {
        sctx.clearRect(0, 0, 450, 200);

        const head = {x: snake[0].x + dx, y: snake[0].y + dy};
        snake.unshift(head);

        if(head.x === food.x && head.y === food.y) {
            snakeScore += 10;
            document.getElementById('snakeScore').innerText = snakeScore;
            food = {
                x: Math.floor(Math.random() * 44) * 10,
                y: Math.floor(Math.random() * 19) * 10
            };
        } else {
            snake.pop();
        }

        if(head.x < 0 || head.x >= 450 || head.y < 0 || head.y >= 200) {
            clearInterval(snakeTimer);
            alert('Game Over Ular! Skor Anda: ' + snakeScore);
        }

        sctx.fillStyle = '#ff2f7d';
        sctx.fillRect(food.x, food.y, 10, 10);

        sctx.fillStyle = '#ffe66d';
        snake.forEach(part => sctx.fillRect(part.x, part.y, 10, 10));

    }, 80);
}

document.addEventListener('keydown', (e) => {
    if(e.key === 'ArrowUp' && dy === 0) { dx = 0; dy = -10; }
    if(e.key === 'ArrowDown' && dy === 0) { dx = 0; dy = 10; }
    if(e.key === 'ArrowLeft' && dx === 0) { dx = -10; dy = 0; }
    if(e.key === 'ArrowRight' && dx === 0) { dx = 10; dy = 0; }
});
</script>
</body>
</html>