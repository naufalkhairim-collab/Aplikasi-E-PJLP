<?php
ob_start();
session_start();
require_once "inc/koneksi.php";
require_once "inc/helper.php"; // Panggil helper untuk notifikasi

// --- 1. CONFIGURATION & DATABASE LOGIC ---

// Ambil data Pengaturan Aplikasi
$query_setting = mysqli_query($koneksi, "SELECT * FROM tbl_pengaturan WHERE id_pengaturan = 1");
$setting = mysqli_fetch_assoc($query_setting);

// Fallback data jika database kosong
$bg_login = !empty($setting['background_login']) ? $setting['background_login'] : 'auth_bg.jpg'; 
$logo_app = !empty($setting['logo_instansi']) ? $setting['logo_instansi'] : 'logo-dark.png';
$app_name = !empty($setting['nama_aplikasi']) ? $setting['nama_aplikasi'] : 'E-System';
$instansi = !empty($setting['nama_instansi']) ? $setting['nama_instansi'] : 'Instansi Pemerintah';

// Proses Login
if (isset($_POST['btn_login'])) {
    $username = amankan_input($_POST['username']); // Bisa berupa Username (Admin) atau NIK (PJLP)
    $password = md5($_POST['password']); 

    // ============================================================
    // CEK LEVEL 1: TABEL USERS (Admin, Pimpinan, Keuangan)
    // ============================================================
    $query_user = mysqli_query($koneksi, "SELECT * FROM tbl_users WHERE username='$username' AND password='$password'");
    $cek_user   = mysqli_num_rows($query_user);

    if ($cek_user > 0) {
        $data = mysqli_fetch_assoc($query_user);

        // Cek Status Aktif User
        if ($data['status_aktif'] == '0') {
            set_notifikasi('error', 'Akun Non-Aktif', 'Akun Anda dinonaktifkan oleh Administrator.');
            header("Location: login.php");
            exit();
        }
        
        // Set Session User
        $_SESSION['id_user']   = $data['id_user'];
        $_SESSION['username']  = $data['username'];
        $_SESSION['nama']      = $data['nama_lengkap'];
        $_SESSION['level']     = $data['level']; // admin / pimpinan / keuangan
        $_SESSION['foto']      = $data['foto']; 
        $_SESSION['status_login'] = true;

        // Update Last Login
        mysqli_query($koneksi, "UPDATE tbl_users SET last_login = NOW() WHERE id_user = '{$data['id_user']}'");

        set_notifikasi('success', 'Login Berhasil', 'Selamat Datang, ' . $data['nama_lengkap']);
        header("Location: index.php"); 
        exit();

    } else {
        // ============================================================
        // CEK LEVEL 2: TABEL PJLP (Tenaga Honorer)
        // Jika tidak ketemu di Users, cari di PJLP berdasarkan NIK
        // ============================================================
        $query_pjlp = mysqli_query($koneksi, "SELECT * FROM tbl_pjlp WHERE nik='$username' AND password='$password'");
        $cek_pjlp   = mysqli_num_rows($query_pjlp);

        if ($cek_pjlp > 0) {
            $data = mysqli_fetch_assoc($query_pjlp);

            // Cek Status Aktif PJLP
            if ($data['status_pjlp'] != 'Aktif') {
                set_notifikasi('error', 'Akses Ditolak', 'Status kepegawaian Anda Non-Aktif.');
                header("Location: login.php");
                exit();
            }

            // Set Session PJLP
            $_SESSION['id_user']   = $data['id_pjlp']; // Pakai ID PJLP
            $_SESSION['username']  = $data['nik'];     // Username diisi NIK
            $_SESSION['nama']      = $data['nama_lengkap'];
            $_SESSION['level']     = 'pjlp';           // Level hardcode jadi 'pjlp'
            $_SESSION['foto']      = 'default-user.png'; // Atau ambil dari tabel jika ada
            $_SESSION['status_login'] = true;

            set_notifikasi('success', 'Login Berhasil', 'Halo, ' . $data['nama_lengkap']);
            header("Location: index.php"); 
            exit();

        } else {
            // ============================================================
            // PENANGANAN ERROR (Jika Gagal di Kedua Tabel)
            // ============================================================
            
            // Cek apakah username/NIK sebenarnya ada tapi password salah?
            $cek_user_exist = mysqli_query($koneksi, "SELECT id_user FROM tbl_users WHERE username='$username'");
            $cek_pjlp_exist = mysqli_query($koneksi, "SELECT id_pjlp FROM tbl_pjlp WHERE nik='$username'");

            if (mysqli_num_rows($cek_user_exist) > 0 || mysqli_num_rows($cek_pjlp_exist) > 0) {
                set_notifikasi('error', 'Gagal Masuk', 'Password yang Anda masukkan salah!');
            } else {
                set_notifikasi('error', 'Akun Tidak Ditemukan', 'Username atau NIK tidak terdaftar.');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="light" data-layout="vertical">

<head>
    <meta charset="utf-8" />
    <title>Login | <?= $app_name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="<?= $app_name ?> - <?= $instansi ?>" name="description" />
    <meta content="System" name="author" />
    
    <script type="module" src="assets/js/layout-setup.js"></script>
    
    <link rel="shortcut icon" href="assets/img/<?= $logo_app ?>">    
    
    <link rel="stylesheet" href="assets/libs/simplebar/simplebar.min.css">
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="assets/libs/nouislider/nouislider.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">

    <style>
        .auth-bg-custom {
            object-fit: cover;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            z-index: -1;
        }
        .logo-login-custom {
            max-height: 50px; 
            width: auto;
        }
    </style>
</head>

<body>
<div>
    <img src="assets/img/<?= $bg_login ?>" alt="Background Login" class="auth-bg light w-full h-full opacity-60 position-absolute top-0 auth-bg-custom">
    <img src="assets/img/<?= $bg_login ?>" alt="Background Dark" class="auth-bg d-none dark w-full h-full opacity-60 position-absolute top-0 auth-bg-custom">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-10">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card mx-xxl-8">
                    <div class="card-body py-12 px-8">
                        
                        <div class="text-center mb-4">
                            <img src="assets/img/<?= $logo_app ?>" alt="Logo" class="logo-login-custom mx-auto d-block">
                        </div>

                        <h6 class="mb-3 mb-8 fw-medium text-center">
                            Selamat Datang di <br> 
                            <span class="text-primary"><?= $app_name ?></span>
                        </h6>
                        <p class="text-center text-muted mb-4 fs-12"><?= $instansi ?></p>

                        <form action="" method="POST">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="username" class="form-label">Username / NIK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username atau NIK" required autocomplete="off">
                                </div>
                                <div class="col-12">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="rememberMe">
                                            <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-8">
                                    <button type="submit" name="btn_login" class="btn btn-primary w-full mb-4">
                                        Masuk Aplikasi <i class="bi bi-box-arrow-in-right ms-1 fs-16"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>
                
                <p class="position-relative text-center fs-12 mb-0">
                    © <?= date('Y') ?> <?= $app_name ?>. <br> Crafted with ❤️ by <?= $instansi ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/js/scroll-top.init.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php tampilkan_notifikasi(); ?>

</body>
</html>