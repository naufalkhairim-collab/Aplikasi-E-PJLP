<?php
// ==================================================================================
// 1. SETUP & CONFIG
// ==================================================================================
require_once __DIR__ . '/vendor/autoload.php'; // Load mPDF
require_once 'inc/koneksi.php'; // Koneksi Database

// Set Locale Indonesia
setlocale(LC_ALL, 'id_ID');
date_default_timezone_set('Asia/Makassar'); // Sesuaikan timezone (WITA)

// Ambil Parameter Filter dari URL
$jenis   = isset($_GET['jenis']) ? $_GET['jenis'] : '1';
$unit    = isset($_GET['unit']) ? $_GET['unit'] : 'all';
$bulan   = isset($_GET['bulan']) ? $_GET['bulan'] : date('n');
$tahun   = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$id_tugas = isset($_GET['id_tugas']) ? $_GET['id_tugas'] : 0; // Khusus SPT

// Ambil Data Pengaturan Instansi (Untuk KOP Surat)
$q_set = mysqli_query($koneksi, "SELECT * FROM tbl_pengaturan LIMIT 1");
$instansi = mysqli_fetch_assoc($q_set);

// Helper Fungsi Tanggal Indo
function tgl_indo($tgl){
    $bln_indo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $pecah = explode('-', $tgl);
    return $pecah[2] . ' ' . $bln_indo[(int)$pecah[1]] . ' ' . $pecah[0];
}

// Judul & Periode Default
$judul_laporan = "LAPORAN";
$periode_label = "";
$nama_bulan_arr = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// ==================================================================================
// 2. LOGIKA QUERY DATA (Berdasarkan Jenis 1-14)
// ==================================================================================
$data = [];
$where_unit = ($unit != 'all') ? "AND k.id_unit_kerja = '$unit'" : ""; // Filter Unit Umum
$where_unit_dpa = ($unit != 'all') ? "AND d.id_unit_kerja = '$unit'" : ""; // Filter Unit DPA

// --- GROUP 1: KEPEGAWAIAN ---
if (in_array($jenis, ['1', '2', '3', '13'])) {
    $judul_laporan = ($jenis == '1') ? "LAPORAN DATA INDUK PJLP" : 
                     (($jenis == '2') ? "LAPORAN KONTRAK PJLP AKTIF" : 
                     (($jenis == '3') ? "LAPORAN PERINGATAN KONTRAK BERAKHIR" : "SURAT KEPUTUSAN PERPANJANGAN KONTRAK"));
    
    // Query Dasar Pegawai & Kontrak
    $q = "SELECT p.*, k.nomor_kontrak, k.tgl_mulai_kontrak, k.tgl_selesai_kontrak, k.status_kontrak, 
          u.nama_unit_kerja, g.gaji_pokok 
          FROM tbl_pjlp p 
          LEFT JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp AND k.status_kontrak='Aktif'
          LEFT JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          LEFT JOIN tbl_gaji_pjlp g ON k.id_kontrak = g.id_kontrak
          WHERE 1=1 $where_unit";

    if ($jenis == '3') { // Filter Peringatan (Kontrak habis < 3 bulan)
        $q .= " AND k.tgl_selesai_kontrak BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
    }
    
    $q .= " ORDER BY u.nama_unit_kerja ASC, p.nama_lengkap ASC";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}

// --- GROUP 2: ABSENSI & KINERJA ---
elseif ($jenis == '4') { // Rekap Absen Bulanan
    $judul_laporan = "REKAPITULASI ABSENSI BULANAN";
    $periode_label = strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun;
    
    $q = "SELECT p.nama_lengkap, u.nama_unit_kerja,
          SUM(CASE WHEN a.status_kehadiran='Hadir' THEN 1 ELSE 0 END) as hadir,
          SUM(CASE WHEN a.status_kehadiran='Sakit' THEN 1 ELSE 0 END) as sakit,
          SUM(CASE WHEN a.status_kehadiran='Izin' THEN 1 ELSE 0 END) as izin,
          SUM(CASE WHEN a.status_kehadiran='Alpa' THEN 1 ELSE 0 END) as alpa,
          SUM(CASE WHEN a.status_masuk='Terlambat' THEN 1 ELSE 0 END) as telat
          FROM tbl_pjlp p
          JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          LEFT JOIN tbl_absensi a ON k.id_kontrak = a.id_kontrak 
               AND MONTH(a.tanggal) = '$bulan' AND YEAR(a.tanggal) = '$tahun'
          WHERE k.status_kontrak='Aktif' $where_unit
          GROUP BY p.id_pjlp ORDER BY u.nama_unit_kerja ASC, p.nama_lengkap ASC";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}
elseif ($jenis == '5') { // Detail Harian
    $judul_laporan = "LAPORAN DETAIL ABSENSI HARIAN";
    $periode_label = tgl_indo($tanggal);
    
    $q = "SELECT p.nama_lengkap, u.nama_unit_kerja, a.jam_masuk, a.jam_pulang, a.status_kehadiran, a.status_masuk
          FROM tbl_pjlp p
          JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          LEFT JOIN tbl_absensi a ON k.id_kontrak = a.id_kontrak AND a.tanggal = '$tanggal'
          WHERE k.status_kontrak='Aktif' $where_unit
          ORDER BY u.nama_unit_kerja ASC, p.nama_lengkap ASC";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}
elseif ($jenis == '6') { // Cuti
    $judul_laporan = "REKAPITULASI PENGAJUAN CUTI & IZIN";
    $periode_label = strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun;
    
    $q = "SELECT p.nama_lengkap, u.nama_unit_kerja, c.*, mc.nama_cuti 
          FROM tbl_pengajuan_cuti c
          JOIN tbl_pjlp p ON c.id_pjlp = p.id_pjlp
          JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp AND k.status_kontrak='Aktif'
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          JOIN tbl_master_cuti mc ON c.id_master_cuti = mc.id_master_cuti
          WHERE MONTH(c.tgl_mulai) = '$bulan' AND YEAR(c.tgl_mulai) = '$tahun' $where_unit
          ORDER BY c.tgl_diajukan DESC";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}
elseif ($jenis == '7') { // Kinerja
    $judul_laporan = "LAPORAN PENILAIAN KINERJA";
    $periode_label = strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun;
    
    // QUERY UPDATED: Mengambil nilai per aktor menggunakan Subquery
    $q = "SELECT 
            p.nama_lengkap, 
            p.nik, 
            u.nama_unit_kerja, 
            h.nilai_akhir,
            -- Ambil Nilai Rata-rata Admin
            (SELECT COALESCE(AVG(nilai_input), 0) FROM tbl_penilaian_detail WHERE id_penilaian = h.id_penilaian AND tipe_penilai = 'admin') as nilai_admin,
            -- Ambil Nilai Rata-rata Keuangan
            (SELECT COALESCE(AVG(nilai_input), 0) FROM tbl_penilaian_detail WHERE id_penilaian = h.id_penilaian AND tipe_penilai = 'keuangan') as nilai_keuangan,
            -- Ambil Nilai Rata-rata Pimpinan
            (SELECT COALESCE(AVG(nilai_input), 0) FROM tbl_penilaian_detail WHERE id_penilaian = h.id_penilaian AND tipe_penilai = 'pimpinan') as nilai_pimpinan
          FROM tbl_penilaian_header h
          JOIN tbl_kontrak k ON h.id_kontrak = k.id_kontrak
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          JOIN tbl_pjlp p ON k.id_pjlp = p.id_pjlp
          WHERE h.bulan = '$bulan' AND h.tahun = '$tahun' $where_unit
          ORDER BY h.nilai_akhir DESC, p.nama_lengkap ASC";

    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}
elseif ($jenis == '8') { // Tugas
    $judul_laporan = "LAPORAN MONITORING PENUGASAN";
    $periode_label = strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun;
    
    $q = "SELECT p.nama_lengkap, u.nama_unit_kerja, t.* FROM tbl_tugas t
          JOIN tbl_pjlp p ON t.id_pjlp_penerima = p.id_pjlp
          JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp AND k.status_kontrak='Aktif'
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          WHERE MONTH(t.tgl_beri) = '$bulan' AND YEAR(t.tgl_beri) = '$tahun' $where_unit
          ORDER BY t.tgl_beri DESC";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}
elseif ($jenis == '14') { // Surat Perintah Tugas (SPT)
    $judul_laporan = "SURAT PERINTAH TUGAS";
    // Query khusus 1 Tugas
    // FIX: Menggunakan pg.nama_pimpinan
    $q = "SELECT t.*, 
          p.nama_lengkap as nama_penerima, p.nik as nik_penerima, p.alamat as alamat_penerima,
          u.nama_unit_kerja,
          pg.nama_pimpinan as nama_pemberi, pg.nip_pimpinan as nip_pemberi
          FROM tbl_tugas t
          JOIN tbl_pjlp p ON t.id_pjlp_penerima = p.id_pjlp
          LEFT JOIN tbl_kontrak k ON p.id_pjlp = k.id_pjlp AND k.status_kontrak='Aktif'
          LEFT JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          JOIN tbl_pengaturan pg ON pg.id_pengaturan = 1
          WHERE t.id_tugas = '$id_tugas'";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}

// --- GROUP 3: KEUANGAN ---
elseif (in_array($jenis, ['9', '10', '12'])) { // Gaji
    $judul_laporan = ($jenis == '9') ? "DAFTAR GAJI NOMINATIF" : (($jenis == '10') ? "SLIP GAJI PEGAWAI" : "REKAPITULASI POTONGAN GAJI");
    $periode_label = strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun;
    
    $q = "SELECT p.nama_lengkap, p.nik, u.nama_unit_kerja, k.nomor_kontrak, py.* FROM tbl_payroll py
          JOIN tbl_kontrak k ON py.id_kontrak = k.id_kontrak
          JOIN tbl_unit_kerja u ON k.id_unit_kerja = u.id_unit_kerja
          JOIN tbl_pjlp p ON k.id_pjlp = p.id_pjlp
          WHERE py.bulan = '$bulan' AND py.tahun = '$tahun' $where_unit
          ORDER BY u.nama_unit_kerja ASC, p.nama_lengkap ASC";
    $exec = mysqli_query($koneksi, $q);
    
    // Jika Slip Gaji (10) atau Potongan (12), kita butuh detail komponennya juga
    while($r = mysqli_fetch_assoc($exec)) {
        if ($jenis == '10' || $jenis == '12') {
            // Ambil detail komponen
            $q_det = mysqli_query($koneksi, "SELECT * FROM tbl_payroll_detail WHERE id_payroll = '{$r['id_payroll']}'");
            $details = [];
            while($d = mysqli_fetch_assoc($q_det)) $details[] = $d;
            $r['detail'] = $details;
        }
        $data[] = $r;
    }
}
elseif ($jenis == '11') { // Realisasi Anggaran
    $judul_laporan = "LAPORAN REALISASI ANGGARAN";
    $periode_label = "TAHUN ANGGRAN " . $tahun;
    
    $q = "SELECT r.kode_rekening, r.nama_rekening, dd.pagu_anggaran_rekening, 
          (SELECT COALESCE(SUM(py.gaji_diterima),0) FROM tbl_payroll py WHERE py.id_dpa_detail = dd.id_dpa_detail AND py.status_bayar='Lunas') as realisasi
          FROM tbl_dpa_detail dd
          JOIN tbl_dpa d ON dd.id_dpa = d.id_dpa
          JOIN tbl_master_rekening r ON dd.id_rekening = r.id_rekening
          WHERE d.tahun_anggaran = '$tahun' $where_unit_dpa";
    $exec = mysqli_query($koneksi, $q);
    while($r = mysqli_fetch_assoc($exec)) $data[] = $r;
}

// ==================================================================================
// 3. BUFFER HTML CONTENT
// ==================================================================================
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; }
        .kop-surat { width: 100%; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        .kop-img { width: 80px; height: auto; }
        .kop-text { text-align: center; }
        .kop-instansi { font-size: 14pt; font-weight: bold; margin: 0; }
        .kop-alamat { font-size: 10pt; margin: 0; }
        
        .judul-laporan { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; text-transform: uppercase; }
        .periode { text-align: center; font-size: 11pt; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-data th { border: 1px solid #000; padding: 6px; background-color: #eee; font-size: 10pt; }
        .table-data td { border: 1px solid #000; padding: 5px; font-size: 10pt; vertical-align: top; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        /* Layout Surat (SK & SPT) */
        .surat-header { text-align: center; margin-bottom: 20px; text-decoration: underline; font-weight: bold; }
        .surat-body { text-align: justify; line-height: 1.5; }
        .surat-table td { border: none; padding: 4px; vertical-align: top; }
        
        .ttd { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <?php 
    // FUNGSI RENDER KOP (Dipanggil di setiap halaman jika perlu, tapi mPDF biasanya handle header)
    function render_kop($instansi) {
        $logo = (!empty($instansi['logo_instansi']) && file_exists('assets/img/'.$instansi['logo_instansi'])) ? 'assets/img/'.$instansi['logo_instansi'] : ''; 
        $html = '<table class="kop-surat"><tr>';
        if($logo) $html .= '<td width="15%"><img src="'.$logo.'" class="kop-img"></td>';
        $html .= '<td class="kop-text">';
        $html .= '<h3 class="kop-instansi">'.strtoupper($instansi['nama_instansi']).'</h3>';
        $html .= '<span class="kop-alamat">'.$instansi['alamat_instansi'].'</span><br>';
        $html .= '<span class="kop-alamat">Telp: '.$instansi['telp_instansi'].' | Email: '.$instansi['email_instansi'].'</span>';
        $html .= '</td></tr></table>';
        return $html;
    }
    ?>

    <?php 
    // A. JIKA FORMAT SURAT KEPUTUSAN (SK PERPANJANGAN) - JENIS 13
    if ($jenis == '13') {
        foreach($data as $d):
            echo render_kop($instansi);
    ?>
        <div class="surat-header">SURAT KEPUTUSAN PERPANJANGAN KONTRAK KERJA<br>NOMOR: <?= $d['nomor_kontrak'] ?></div>
        
        <div class="surat-body">
            <p>Yang bertanda tangan di bawah ini:</p>
            <table class="surat-table">
                <tr><td width="150">Nama</td><td>: <b><?= $instansi['nama_pimpinan'] ?></b></td></tr>
                <tr><td>NIP</td><td>: <?= $instansi['nip_pimpinan'] ?></td></tr>
                <tr><td>Jabatan</td><td>: Pimpinan Instansi</td></tr>
            </table>
            
            <p>Bertindak untuk dan atas nama <?= $instansi['nama_instansi'] ?>, selanjutnya disebut <b>PIHAK PERTAMA</b>.</p>
            
            <p>Dengan ini memperpanjang kontrak kerja kepada:</p>
            <table class="surat-table">
                <tr><td width="150">Nama</td><td>: <b><?= $d['nama_lengkap'] ?></b></td></tr>
                <tr><td>NIK</td><td>: <?= $d['nik'] ?></td></tr>
                <tr><td>Unit Kerja</td><td>: <?= $d['nama_unit_kerja'] ?></td></tr>
            </table>
            <p>Selanjutnya disebut <b>PIHAK KEDUA</b>.</p>

            <p>Dengan ketentuan sebagai berikut:</p>
            <ol>
                <li>Jangka waktu kontrak terhitung mulai tanggal <b><?= tgl_indo($d['tgl_mulai_kontrak']) ?></b> sampai dengan <b><?= tgl_indo($d['tgl_selesai_kontrak']) ?></b>.</li>
                <li>Pihak Kedua akan menerima gaji pokok sebesar <b>Rp <?= number_format($d['gaji_pokok'],0,',','.') ?></b> per bulan.</li>
                <li>Pihak Kedua wajib mematuhi segala peraturan dan tata tertib yang berlaku.</li>
            </ol>

            <p>Demikian Surat Keputusan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <table class="ttd">
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Banjarmasin, <?= tgl_indo(date('Y-m-d')) ?><br>
                    Pimpinan Instansi,<br><br><br><br>
                    <u><b><?= $instansi['nama_pimpinan'] ?></b></u><br>
                    NIP. <?= $instansi['nip_pimpinan'] ?>
                </td>
            </tr>
        </table>
        <div class="page-break"></div>
    <?php 
        endforeach;
    }

    // B. JIKA FORMAT SURAT TUGAS (SPT) - JENIS 14
    elseif ($jenis == '14') {
        if(count($data) > 0) {
            $d = $data[0];
            echo render_kop($instansi);
    ?>
        <div class="surat-header">SURAT PERINTAH TUGAS<br>NOMOR: 800 / <?= $d['id_tugas'] ?> / SPT / <?= date('Y') ?></div>
        
        <div class="surat-body">
            <p>Yang bertanda tangan di bawah ini:</p>
            <table class="surat-table">
                <tr><td width="150">Nama</td><td>: <b><?= $d['nama_pemberi'] ?></b></td></tr>
                <tr><td>NIP</td><td>: <?= $d['nip_pemberi'] ?></td></tr>
                <tr><td>Jabatan</td><td>: Pimpinan Instansi</td></tr>
            </table>

            <p>Memberikan perintah tugas kepada:</p>
            <table class="surat-table">
                <tr><td width="150">Nama</td><td>: <b><?= $d['nama_penerima'] ?></b></td></tr>
                <tr><td>NIK</td><td>: <?= $d['nik_penerima'] ?></td></tr>
                <tr><td>Unit Kerja</td><td>: <?= $d['nama_unit_kerja'] ?></td></tr>
            </table>

            <p>Untuk melaksanakan tugas sebagai berikut:</p>
            <table class="table-data" style="width:100%">
                <tr>
                    <td width="30%" style="background:#eee"><b>Judul Tugas</b></td>
                    <td><?= $d['judul_tugas'] ?></td>
                </tr>
                <tr>
                    <td style="background:#eee"><b>Deskripsi</b></td>
                    <td><?= nl2br($d['deskripsi_tugas']) ?></td>
                </tr>
                <tr>
                    <td style="background:#eee"><b>Deadline</b></td>
                    <td><?= ($d['deadline']) ? tgl_indo(date('Y-m-d', strtotime($d['deadline']))) : 'Tentative' ?></td>
                </tr>
            </table>

            <p>Demikian Surat Perintah Tugas ini dibuat untuk dilaksanakan dengan penuh tanggung jawab.</p>
        </div>

        <table class="ttd">
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Banjarmasin, <?= tgl_indo(date('Y-m-d')) ?><br>
                    Pemberi Perintah,<br><br><br><br>
                    <u><b><?= $d['nama_pemberi'] ?></b></u><br>
                    NIP. <?= $d['nip_pemberi'] ?>
                </td>
            </tr>
        </table>
    <?php
        } else { echo "<center>Data Tugas Tidak Ditemukan</center>"; }
    }

    // C. JIKA FORMAT SLIP GAJI (JENIS 10)
    elseif ($jenis == '10') {
        foreach($data as $d):
            // Hitung di PHP (jika di query belum final)
            // Asumsi $d['detail'] berisi array komponen
            $tunjangan_list = "";
            $potongan_list = "";
            
            if(isset($d['detail'])) {
                foreach($d['detail'] as $item) {
                    if($item['tipe'] == 'tunjangan') {
                        $tunjangan_list .= '<tr><td>'.$item['nama_komponen'].'</td><td class="text-right">Rp '.number_format($item['jumlah'],0,',','.').'</td></tr>';
                    } else {
                        $potongan_list .= '<tr><td>'.$item['nama_komponen'].'</td><td class="text-right">Rp '.number_format($item['jumlah'],0,',','.').'</td></tr>';
                    }
                }
            }
    ?>
        <div style="border: 1px solid #000; padding: 20px; margin-bottom: 20px;">
            <?= render_kop($instansi) ?>
            <h3 class="text-center" style="margin-top:0;">SLIP GAJI PEGAWAI</h3>
            <p class="text-center">Periode: <?= strtoupper($nama_bulan_arr[$bulan]) . " " . $tahun ?></p>
            <hr>
            
            <table style="width:100%; margin-bottom:10px;">
                <tr>
                    <td width="15%">Nama</td><td width="35%">: <b><?= $d['nama_lengkap'] ?></b></td>
                    <td width="15%">Unit Kerja</td><td width="35%">: <?= $d['nama_unit_kerja'] ?></td>
                </tr>
                <tr>
                    <td>NIK</td><td>: <?= $d['nik'] ?></td>
                    <td>Status</td><td>: <?= $d['status_bayar'] ?></td>
                </tr>
            </table>

            <table width="100%" cellspacing="0" cellpadding="5" border="1" style="border-collapse: collapse;">
                <tr style="background-color:#eee;">
                    <th width="50%">PENERIMAAN</th>
                    <th width="50%">POTONGAN</th>
                </tr>
                <tr>
                    <td valign="top">
                        <table width="100%">
                            <tr><td>Gaji Pokok</td><td class="text-right">Rp <?= number_format($d['gaji_pokok'],0,',','.') ?></td></tr>
                            <?= $tunjangan_list ?>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td><b>Total Penerimaan</b></td><td class="text-right"><b>Rp <?= number_format($d['gaji_pokok'] + $d['total_tunjangan'],0,',','.') ?></b></td></tr>
                        </table>
                    </td>
                    <td valign="top">
                        <table width="100%">
                            <?= $potongan_list ?>
                            <tr><td><br></td><td></td></tr>
                            <tr><td colspan="2"><hr></td></tr>
                            <tr><td><b>Total Potongan</b></td><td class="text-right"><b>Rp <?= number_format($d['total_potongan'],0,',','.') ?></b></td></tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color:#eee;">
                    <td colspan="2" class="text-center" style="padding:10px;">
                        <b>GAJI BERSIH DITERIMA: Rp <?= number_format($d['gaji_diterima'],0,',','.') ?></b>
                        <br><small><i>(Terbilang: <?= number_format($d['gaji_diterima'],0,',','.') ?> Rupiah)</i></small>
                    </td>
                </tr>
            </table>

            <table class="ttd">
                <tr>
                    <td width="70%"></td>
                    <td class="text-center">
                        Banjarmasin, <?= tgl_indo(date('Y-m-d')) ?><br>
                        Bendahara Pengeluaran,<br><br><br>
                        <u>(...........................................)</u>
                    </td>
                </tr>
            </table>
        </div>
        <div class="page-break"></div>
    <?php 
        endforeach;
    }

    // D. JIKA FORMAT TABEL BIASA (DEFAULT 1-12 kecuali 10)
    else {
        echo render_kop($instansi);
        echo '<div class="judul-laporan">'.$judul_laporan.'</div>';
        if($periode_label) echo '<div class="periode">Periode: '.$periode_label.'</div>';
    ?>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <?php if (in_array($jenis, ['1','2','3'])): ?>
                        <th>Nama Pegawai</th><th>NIK/NIB</th><th>Unit Kerja</th><th>Kontrak</th><th>Status</th>
                    <?php elseif ($jenis == '4'): ?>
                        <th>Nama Pegawai</th><th>Unit</th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Telat</th>
                    <?php elseif ($jenis == '5'): ?>
                        <th>Nama Pegawai</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th>
                    <?php elseif ($jenis == '6'): ?>
                        <th>Nama Pegawai</th><th>Unit</th><th>Jenis Cuti</th><th>Tanggal</th><th>Status</th>
                    <?php elseif ($jenis == '7'): ?>
                        <th>Nama Pegawai</th>
                        <th>Unit Kerja</th>
                        <th width="10%">Nilai Admin<br><small>(30%)</small></th>
                        <th width="10%">Nilai Keuangan<br><small>(20%)</small></th>
                        <th width="10%">Nilai Pimpinan<br><small>(50%)</small></th>
                        <th width="10%">Total Skor</th>
                        <th width="15%">Predikat</th>
                    <?php elseif ($jenis == '8'): ?>
                        <th>Nama Pegawai</th><th>Judul Tugas</th><th>Deadline</th><th>Status</th>
                    <?php elseif ($jenis == '9'): ?>
                        <th>Nama Pegawai</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Potongan</th><th>Diterima</th>
                    <?php elseif ($jenis == '11'): ?>
                        <th>Kode Rekening</th><th>Uraian</th><th>Pagu</th><th>Realisasi</th><th>Sisa</th>
                    <?php elseif ($jenis == '12'): ?>
                        <th>Nama Pegawai</th><th>Unit</th><th>Rincian Potongan</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if(empty($data)) echo "<tr><td colspan='10' class='text-center'>Tidak ada data.</td></tr>";
                
                foreach($data as $d): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <?php if (in_array($jenis, ['1','2','3'])): ?>
                        <td><b><?= $d['nama_lengkap'] ?></b></td>
                        <td><?= $d['nik'] ?><br><?= $d['nib_nomor'] ?></td>
                        <td><?= $d['nama_unit_kerja'] ?></td>
                        <td><?= ($d['tgl_mulai_kontrak']) ? tgl_indo($d['tgl_mulai_kontrak']).' s/d '.tgl_indo($d['tgl_selesai_kontrak']) : '-' ?></td>
                        <td><?= $d['status_kontrak'] ?></td>
                    
                    <?php elseif ($jenis == '4'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td><?= $d['nama_unit_kerja'] ?></td>
                        <td class="text-center"><?= $d['hadir'] ?></td>
                        <td class="text-center"><?= $d['sakit'] ?></td>
                        <td class="text-center"><?= $d['izin'] ?></td>
                        <td class="text-center"><?= $d['alpa'] ?></td>
                        <td class="text-center"><?= $d['telat'] ?></td>

                    <?php elseif ($jenis == '5'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td class="text-center"><?= $d['jam_masuk'] ?></td>
                        <td class="text-center"><?= $d['jam_pulang'] ?></td>
                        <td><?= $d['status_kehadiran'] ?> (<?= $d['status_masuk'] ?>)</td>

                    <?php elseif ($jenis == '6'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td><?= $d['nama_unit_kerja'] ?></td>
                        <td><?= $d['nama_cuti'] ?></td>
                        <td><?= tgl_indo($d['tgl_mulai']) ?> - <?= tgl_indo($d['tgl_selesai']) ?></td>
                        <td><?= $d['status_pengajuan'] ?></td>

                    <?php elseif ($jenis == '7'): 
                        // Logika Predikat
                        $skor = $d['nilai_akhir'];
                        if ($skor >= 90) $predikat = "Sangat Baik";
                        elseif ($skor >= 75) $predikat = "Baik";
                        elseif ($skor >= 60) $predikat = "Cukup";
                        else $predikat = "Kurang";
                    ?>
                        <td>
                            <b><?= $d['nama_lengkap'] ?></b><br>
                            <small>NIK: <?= $d['nik'] ?></small>
                        </td>
                        <td><?= $d['nama_unit_kerja'] ?></td>
                        
                        <td align="center"><?= number_format($d['nilai_admin'], 1) ?></td>
                        <td align="center"><?= number_format($d['nilai_keuangan'], 1) ?></td>
                        <td align="center"><?= number_format($d['nilai_pimpinan'], 1) ?></td>
                        
                        <td align="center"><b><?= number_format($d['nilai_akhir'], 2) ?></b></td>
                        <td align="center"><?= $predikat ?></td>

                    <?php elseif ($jenis == '8'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td><?= $d['judul_tugas'] ?></td>
                        <td><?= ($d['deadline']) ? tgl_indo(date('Y-m-d', strtotime($d['deadline']))) : '-' ?></td>
                        <td><?= $d['status_tugas'] ?></td>

                    <?php elseif ($jenis == '9'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td class="text-right"><?= number_format($d['gaji_pokok'],0,',','.') ?></td>
                        <td class="text-right"><?= number_format($d['total_tunjangan'],0,',','.') ?></td>
                        <td class="text-right"><?= number_format($d['total_potongan'],0,',','.') ?></td>
                        <td class="text-right"><b><?= number_format($d['gaji_diterima'],0,',','.') ?></b></td>

                    <?php elseif ($jenis == '11'): ?>
                        <td><?= $d['kode_rekening'] ?></td>
                        <td><?= $d['nama_rekening'] ?></td>
                        <td class="text-right"><?= number_format($d['pagu_anggaran_rekening'],0,',','.') ?></td>
                        <td class="text-right"><?= number_format($d['realisasi'],0,',','.') ?></td>
                        <td class="text-right"><?= number_format($d['pagu_anggaran_rekening'] - $d['realisasi'],0,',','.') ?></td>

                    <?php elseif ($jenis == '12'): ?>
                        <td><?= $d['nama_lengkap'] ?></td>
                        <td><?= $d['nama_unit_kerja'] ?></td>
                        <td class="text-right"><?= number_format($d['total_potongan'],0,',','.') ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table class="ttd">
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Banjarmasin, <?= tgl_indo(date('Y-m-d')) ?><br>
                    Mengetahui,<br>
                    Pimpinan Instansi,<br><br><br><br>
                    <u><b><?= strtoupper($instansi['nama_pimpinan']) ?></b></u><br>
                    NIP. <?= $instansi['nip_pimpinan'] ?>
                </td>
            </tr>
        </table>
    <?php 
    } // End ELSE (Tabel Biasa)
    ?>

</body>
</html>
<?php
// ==================================================================================
// 4. OUTPUT PDF
// ==================================================================================
$html = ob_get_contents();
ob_end_clean();

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8', 
        'format' => 'A4', 
        'orientation' => 'P',
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 20,
        'margin_bottom' => 20
    ]);
    
    $mpdf->SetTitle($judul_laporan);
    $mpdf->SetFooter('Dicetak pada: {DATE j-m-Y H:i} | Halaman {PAGENO} dari {nbpg}');
    $mpdf->WriteHTML($html);
    $mpdf->Output('Laporan.pdf', 'I'); // I = Inline Browser, D = Download

} catch (\Mpdf\MpdfException $e) {
    echo "Terjadi kesalahan saat membuat PDF: " . $e->getMessage();
}
?>