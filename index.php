<?php
ob_start(); // Mencegah error header saat redirect
session_start();
require_once "inc/koneksi.php";
require_once "inc/helper.php"; // Memanggil helper untuk fungsi notifikasi & format

// 1. Cek Status Login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: login.php");
    exit();
}

// 2. Ambil Data User dari Session
$id_user   = $_SESSION['id_user'];
$username  = $_SESSION['username'];
$nama_user = $_SESSION['nama'];
$level     = $_SESSION['level']; // admin, pimpinan, keuangan, atau pjlp
// Cek foto, jika tidak ada pakai default
$foto_user = (isset($_SESSION['foto']) && !empty($_SESSION['foto'])) ? $_SESSION['foto'] : 'default-user.png';

// 3. Ambil Konfigurasi Aplikasi dari Database
$query_setting = mysqli_query($koneksi, "SELECT * FROM tbl_pengaturan WHERE id_pengaturan = 1");
$setting       = mysqli_fetch_assoc($query_setting);

// Fallback jika data kosong
$app_name   = !empty($setting['nama_aplikasi']) ? $setting['nama_aplikasi'] : 'E-PJLP';
$instansi   = !empty($setting['nama_instansi']) ? $setting['nama_instansi'] : 'Instansi Pemerintah';
$logo_app   = !empty($setting['logo_instansi']) ? $setting['logo_instansi'] : 'logo-dark.png'; 
$favicon    = !empty($setting['logo_instansi']) ? $setting['logo_instansi'] : 'favicon.png';
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-layout="vertical">

<head>
    <meta charset="utf-8" />
    <title><?= $app_name ?> | <?= $instansi ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Sistem Informasi Manajemen PJLP" name="description" />
    
    <script type="module" src="assets/js/layout-setup.js"></script>
    
    <link rel="shortcut icon" href="assets/img/<?= $favicon ?>">  
    
    <!--datatable css-->
    <link rel="stylesheet" href="assets/cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="assets/cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    
    <link rel="stylesheet" href="assets/libs/simplebar/simplebar.min.css">
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/libs/nouislider/nouislider.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">
</head>

<body>
<div id="layout-wrapper">
    
    <header class="app-header" id="appHeader">
        <div class="container-fluid w-100">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <div class="d-inline-flex align-items-center gap-3"> 

                        <div class="d-none d-lg-block ms-2">
                            <span id="liveClock" class="fw-medium text-secondary fs-13">
                                Memuat waktu...
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex-shrink-0 d-flex align-items-center gap-1">

                    <button class="btn header-btn d-none d-md-block" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                        <i class="bi bi-gear"></i>
                    </button>
                    
                    <div class="dark-mode-btn" id="toggleMode">
                        <button class="btn header-btn active" id="lightModeBtn">
                            <i class="bi bi-brightness-high"></i>
                        </button>
                        <button class="btn header-btn" id="darkModeBtn">
                            <i class="bi bi-moon-stars"></i>
                        </button>
                    </div>

                    <div class="dropdown pe-dropdown-mega">
                        <button class="header-profile-btn btn gap-1 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="header-btn btn position-relative">
                                <img src="assets/img/<?= $foto_user ?>" alt="Foto" class="img-fluid rounded-circle" style="width:35px; height:35px; object-fit:cover;">
                            </span>
                            <div class="d-none d-lg-block pe-2">
                                <span class="d-block mb-0 fs-13 fw-semibold"><?= $nama_user ?></span>
                                <span class="d-block mb-0 fs-12 text-muted"><?= ucfirst($level) ?></span>
                            </div>
                        </button>
                        <div class="dropdown-menu dropdown-mega-sm header-dropdown-menu p-3">
                            <div class="border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                                <img src="assets/img/<?= $foto_user ?>" alt="" class="avatar-md rounded-circle" style="object-fit:cover;">
                                <div>
                                    <h6 class="mb-0 lh-base"><?= $nama_user ?></h6>
                                    <p class="mb-0 fs-13 text-muted"><?= $username ?></p>
                                </div>
                            </div>
                            <ul class="list-unstyled mb-1 border-bottom pb-1">
                                <li><a class="dropdown-item" href="index.php?page=profil"><i class="bi bi-person me-1"></i> Profil Saya</a></li> 
                            </ul>
                            <ul class="list-unstyled mb-0">
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="konfirmasiLogout()"><i class="bi bi-box-arrow-right me-1"></i> Sign Out</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <a href="index.php" class="fs-18 fw-semibold d-flex align-items-center gap-2"> 
            <span class="text-truncate"><?= $app_name ?></span>
        </a>
    </div> 
    
    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">
            
            <li class="pe-menu-title">Menu Utama</li>
            <li class="pe-slide">
                <a href="index.php?page=dashboard" class="pe-nav-link">
                    <i class="bi bi-speedometer2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Dashboard</span>
                </a>
            </li>

            <?php if ($level == 'admin'): ?>
            <li class="pe-menu-title">Administrator</li>

            <li class="pe-slide pe-has-sub">
                <a href="#collapseMaster" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseMaster">
                    <i class="bi bi-database pe-nav-icon"></i>
                    <span class="pe-nav-content">Data Master</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseMaster">
                    <li class="pe-slide-item"><a href="index.php?page=data_users" class="pe-nav-link">Data Pengguna</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=data_unit_kerja" class="pe-nav-link">Unit Kerja</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=data_lokasi" class="pe-nav-link">Lokasi Kantor (GPS)</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=data_jadwal" class="pe-nav-link">Jadwal Kerja</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=master_cuti" class="pe-nav-link">Jenis Cuti & Izin</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=master_rekening" class="pe-nav-link">Kode Rekening</a></li> 
                </ul>
            </li>

            <li class="pe-slide pe-has-sub">
                <a href="#collapsePegawai" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapsePegawai">
                    <i class="bi bi-people pe-nav-icon"></i>
                    <span class="pe-nav-content">Kepegawaian</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapsePegawai">
                    <li class="pe-slide-item"><a href="index.php?page=data_pjlp" class="pe-nav-link">Data Pegawai PJLP</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=data_kuota_cuti" class="pe-nav-link">Kuota Cuti</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=kontrak_kerja" class="pe-nav-link">Kontrak Kerja</a></li>
                    <li class="pe-slide-item"><a href="index.php?page=penugasan" class="pe-nav-link">Penugasan</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if ($level == 'admin' || $level == 'pimpinan'): ?>
            
            <li class="pe-menu-title">Manajemen & Approval</li>

            <li class="pe-slide">
                <a href="index.php?page=pengajuan_cuti_admin" class="pe-nav-link">
                    <i class="bi bi-check2-circle pe-nav-icon"></i>
                    <span class="pe-nav-content">Persetujuan Cuti</span>
                </a>
            </li>
            <li class="pe-slide">
                <a href="index.php?page=penilaian_kinerja" class="pe-nav-link">
                    <i class="bi bi-star-half pe-nav-icon"></i>
                    <span class="pe-nav-content">Penilaian Kinerja</span>
                </a>
            </li> 
            
            <?php if($level == 'pimpinan'): ?>
            <li class="pe-slide">
                <a href="index.php?page=penugasan" class="pe-nav-link">
                    <i class="bi bi-clipboard-check pe-nav-icon"></i>
                    <span class="pe-nav-content">Berikan Tugas</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="pe-menu-title">Monitoring</li>

            <li class="pe-slide">
                <a href="index.php?page=absensi_harian" class="pe-nav-link">
                    <i class="bi bi-calendar-check pe-nav-icon"></i>
                    <span class="pe-nav-content">Monitor Kehadiran</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($level == 'admin' || $level == 'keuangan'): ?>
            
            <li class="pe-menu-title">Anggaran & Gaji</li>

            <li class="pe-slide">
                <a href="index.php?page=data_dpa" class="pe-nav-link">
                    <i class="bi bi-wallet2 pe-nav-icon"></i>
                    <span class="pe-nav-content">Kelola DPA</span>
                </a>
            </li>
            <li class="pe-slide">
                <a href="index.php?page=komponen_gaji" class="pe-nav-link">
                    <i class="bi bi-list-ul pe-nav-icon"></i>
                    <span class="pe-nav-content">Komponen Gaji</span>
                </a>
            </li>
            <li class="pe-slide">
                <a href="index.php?page=proses_payroll" class="pe-nav-link">
                    <i class="bi bi-calculator pe-nav-icon"></i>
                    <span class="pe-nav-content">Proses Payroll</span>
                </a>
            </li>
            <li class="pe-slide">
                <a href="index.php?page=riwayat_gaji" class="pe-nav-link">
                    <i class="bi bi-clock-history pe-nav-icon"></i>
                    <span class="pe-nav-content">Riwayat Pembayaran</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if($level == 'keuangan'): ?>
            <li class="pe-slide">
                <a href="index.php?page=penilaian_kinerja" class="pe-nav-link">
                    <i class="bi bi-star-half pe-nav-icon"></i>
                    <span class="pe-nav-content">Penilaian Kinerja</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($level == 'admin' || $level == 'pimpinan' || $level == 'keuangan'): ?>
            <li class="pe-menu-title">Laporan</li>
            <li class="pe-slide">
                <a href="index.php?page=laporan" class="pe-nav-link">
                    <i class="bi bi-printer pe-nav-icon"></i>
                    <span class="pe-nav-content">Pusat Laporan</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($level == 'admin'): ?>
            <li class="pe-menu-title">Pengaturan</li>
            <li class="pe-slide">
                <a href="index.php?page=pengaturan" class="pe-nav-link">
                    <i class="bi bi-gear pe-nav-icon"></i>
                    <span class="pe-nav-content">Pengaturan Aplikasi</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($level == 'pjlp'): ?>
            
                <?php
                // LOGIKA CEK ABSEN HARI INI
                $id_pjlp_log = $_SESSION['id_user'];
                $tgl_log = date('Y-m-d');
                
                // Cari Kontrak Aktif
                $q_k_log = mysqli_query($koneksi, "SELECT id_kontrak FROM tbl_kontrak WHERE id_pjlp='$id_pjlp_log' AND status_kontrak='Aktif'");
                $d_k_log = mysqli_fetch_assoc($q_k_log);
                $id_k_log = $d_k_log['id_kontrak'] ?? 0;

                // Cek apakah sudah absen masuk?
                $q_a_log = mysqli_query($koneksi, "SELECT jam_masuk FROM tbl_absensi WHERE id_kontrak='$id_k_log' AND tanggal='$tgl_log'");
                $sudah_absen = (mysqli_num_rows($q_a_log) > 0);
                ?>

                <li class="pe-menu-title">Aktivitas Utama</li>

                <li class="pe-slide">
                    <a href="index.php?page=absen_masuk" class="pe-nav-link">
                        <i class="bi bi-box-arrow-in-right pe-nav-icon"></i>
                        <span class="pe-nav-content">Absen Masuk</span>
                    </a>
                </li>

                <?php if ($sudah_absen): ?>
                    <li class="pe-slide">
                        <a href="index.php?page=absen_pulang" class="pe-nav-link">
                            <i class="bi bi-box-arrow-left pe-nav-icon"></i>
                            <span class="pe-nav-content">Absen Pulang</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="index.php?page=tugas_saya" class="pe-nav-link">
                            <i class="bi bi-list-task pe-nav-icon"></i>
                            <span class="pe-nav-content">Tugas Saya</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="index.php?page=pengajuan_cuti_pjlp" class="pe-nav-link">
                            <i class="bi bi-envelope-paper pe-nav-icon"></i>
                            <span class="pe-nav-content">Ajukan Cuti/Izin</span>
                        </a>
                    </li>
                    <li class="pe-menu-title">Arsip Pribadi</li>
                    <li class="pe-slide">
                        <a href="index.php?page=riwayat_gaji" class="pe-nav-link">
                            <i class="bi bi-receipt pe-nav-icon"></i>
                            <span class="pe-nav-content">Slip Gaji</span>
                        </a>
                    </li>
                    <li class="pe-slide">
                        <a href="index.php?page=profil" class="pe-nav-link">
                            <i class="bi bi-person-circle pe-nav-icon"></i>
                            <span class="pe-nav-content">Profil Saya</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="pe-slide">
                        <a href="#" class="pe-nav-link text-muted" style="cursor: not-allowed; opacity: 0.6;" onclick="alert('Silakan Absen Masuk terlebih dahulu.'); return false;">
                            <i class="bi bi-lock-fill pe-nav-icon"></i>
                            <span class="pe-nav-content">Menu Terkunci</span>
                        </a>
                    </li>
                <?php endif; ?>

            <?php endif; ?>

        </ul>
    </nav>
</aside>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    
    <main class="app-wrapper">
        <div class="container-fluid">
            <?php
                // Default page
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                
                // Sanitasi Input
                $clean_page = preg_replace('/[^a-zA-Z0-9_]/', '', $page);
                $file_path  = "pages/" . $clean_page . ".php";

                // Cek File Exists
                if (file_exists($file_path)) {
                    include $file_path;
                } else {
                    // Halaman 404 (Integrated styling)
                    echo '
                    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
                        <div class="col-md-6 text-center">
                             <div class="mb-4">
                                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="fw-bold text-dark">Halaman Tidak Ditemukan</h2>
                            <p class="text-muted mb-4">
                                Maaf, halaman <code>pages/'.$clean_page.'.php</code> belum tersedia di folder pages.
                            </p>
                            <a href="index.php" class="btn btn-primary">Kembali ke Dashboard</a>
                        </div>
                    </div>';
                }
            ?>
        </div>
    </main>

    <?php include "tema.php" ?>
    
    <footer class="footer">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <span>© <?= date('Y') ?> <?= $app_name ?> || <?= $instansi ?></span>
            </div>
        </div>
    </footer>
</div>

<script src="assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/js/scroll-top.init.js"></script>


<script src="assets/code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--datatable js-->
<script src="assets/cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="assets/cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="assets/cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="assets/cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="assets/cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="assets/cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="assets/cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="assets/cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="assets/js/dashboard/academy.init.js"></script>

<script src="assets/js/table/datatable.init.js"></script>
<script src="assets/js/form/form-validation.init.js"></script>
<script type="module" src="assets/js/app.js"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php tampilkan_notifikasi(); ?>

<script>
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        
        // 1. Logika Ucapan Selamat
        var greeting = "";
        if (hours >= 0 && hours < 11) {
            greeting = "Selamat Pagi";
        } else if (hours >= 11 && hours < 15) {
            greeting = "Selamat Siang";
        } else if (hours >= 15 && hours < 18) {
            greeting = "Selamat Sore";
        } else {
            greeting = "Selamat Malam";
        }

        // 2. Format Hari, Tanggal Bulan Tahun (Indonesia)
        var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        var dayName = days[now.getDay()];
        var date = now.getDate();
        var monthName = months[now.getMonth()];
        var year = now.getFullYear();

        // 3. Format Jam Menit Detik (Tambahkan 0 jika di bawah 10)
        hours = hours < 10 ? '0' + hours : hours;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;

        // 4. Gabungkan String
        var finalTime = greeting + " || " + dayName + ", " + date + " " + monthName + " " + year + " || " + hours + ":" + minutes + ":" + seconds;

        // 5. Tampilkan ke Element ID liveClock
        var clockElement = document.getElementById('liveClock');
        if (clockElement) {
            clockElement.innerHTML = finalTime;
        }
    }

    // Jalankan fungsi setiap 1 detik (1000 ms)
    setInterval(updateClock, 1000);
    
    // Jalankan sekali saat halaman pertama kali dimuat agar tidak menunggu 1 detik
    updateClock();
</script>

<script>
    // Konfirmasi Hapus Data
    function konfirmasiHapus(url) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }

    // Konfirmasi Logout
    function konfirmasiLogout() {
        Swal.fire({
            title: 'Konfirmasi Logout',
            text: "Apakah Anda yakin ingin keluar dari sistem?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        })
    }
    
    // Script Auto Format Rupiah (Input dengan class 'rupiah')
    document.querySelectorAll('.rupiah').forEach(function(el) {
        el.addEventListener('keyup', function(e) {
            el.value = formatRupiah(this.value);
        });
    });

    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split    = number_string.split(','),
            sisa     = split[0].length % 3,
            rupiah   = split[0].substr(0, sisa),
            ribuan   = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }
</script>

</body>
</html>