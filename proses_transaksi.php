<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil Data Header
    $tanggal = $_POST['tanggal'];
    $nama_karyawan = trim($_POST['nama_karyawan']);
    $nama_pelanggan = trim($_POST['nama_pelanggan']);
    
    // Ambil Data Keuangan
    $total_tagihan = $_POST['total_tagihan']; // Dari kalkulasi JS (tapi nanti kita hitung ulang biar aman)
    $total_dibayar = $_POST['total_dibayar'];
    
    // Hitung ulang total di backend (keamanan)
    $hargas = $_POST['harga_satuan'];
    $jumlahs = $_POST['jumlah'];
    $real_total = 0;
    for ($i = 0; $i < count($hargas); $i++) {
        $real_total += ($hargas[$i] * $jumlahs[$i]);
    }
    
    // Hitung Sisa & Status
    $sisa_tagihan = $real_total - $total_dibayar;
    $status = ($sisa_tagihan > 0) ? 'Belum Lunas' : 'Lunas';

    try {
        $pdo->beginTransaction();

        // 1. CEK/BUAT KARYAWAN (Sama seperti sebelumnya)
        $stmt = $pdo->prepare("SELECT id FROM karyawan WHERE nama_lengkap = ?");
        $stmt->execute([$nama_karyawan]);
        $k = $stmt->fetch();
        if ($k) { $karyawan_id = $k['id']; } 
        else {
            $pdo->prepare("INSERT INTO karyawan (nama_lengkap, username, password, peran) VALUES (?, ?, '123', 'Admin')")->execute([$nama_karyawan, uniqid()]);
            $karyawan_id = $pdo->lastInsertId();
        }

        // 2. CEK/BUAT PELANGGAN (Sama seperti sebelumnya)
        $stmt = $pdo->prepare("SELECT id FROM pelanggan WHERE nama = ?");
        $stmt->execute([$nama_pelanggan]);
        $p = $stmt->fetch();
        if ($p) { $pelanggan_id = $p['id']; } 
        else {
            $pdo->prepare("INSERT INTO pelanggan (nama) VALUES (?)")->execute([$nama_pelanggan]);
            $pelanggan_id = $pdo->lastInsertId();
        }

        // 3. INSERT TRANSAKSI (Dengan Data Utang)
        $sqlHeader = "INSERT INTO transaksi (pelanggan_id, karyawan_id, tanggal, total_tagihan, total_dibayar, sisa_tagihan, status_pembayaran) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sqlHeader)->execute([$pelanggan_id, $karyawan_id, $tanggal, $real_total, $total_dibayar, $sisa_tagihan, $status]);
        $transaksi_id = $pdo->lastInsertId();

        // 4. INSERT RIWAYAT PEMBAYARAN (Jika ada pembayaran di awal)
        // Ini penting! Biar ketahuan uang masuknya kapan.
        if ($total_dibayar > 0) {
            $sqlBayar = "INSERT INTO riwayat_pembayaran (transaksi_id, karyawan_id, jumlah_bayar, keterangan, tanggal_bayar) 
                         VALUES (?, ?, ?, 'Pembayaran Awal / DP', ?)";
            // Gunakan tanggal transaksi sebagai tanggal bayar
            $pdo->prepare($sqlBayar)->execute([$transaksi_id, $karyawan_id, $total_dibayar, $tanggal . ' 12:00:00']);
        }

        // 5. INSERT DETAIL BARANG
        $stmtCekBarang = $pdo->prepare("SELECT id FROM barang WHERE nama_barang = ?");
        $stmtInsBarang = $pdo->prepare("INSERT INTO barang (nama_barang, satuan, harga, stok_tersedia) VALUES (?, ?, ?, 0)");
        $stmtDetail = $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, barang_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");

        $nama_barangs = $_POST['nama_barang'];
        $satuans = $_POST['satuan'];

        for ($i = 0; $i < count($nama_barangs); $i++) {
            $nm_brg = trim($nama_barangs[$i]);
            if(!empty($nm_brg)) {
                // Cek Barang
                $stmtCekBarang->execute([$nm_brg]);
                $brg = $stmtCekBarang->fetch();
                if ($brg) { $barang_id = $brg['id']; } 
                else {
                    $stmtInsBarang->execute([$nm_brg, $satuans[$i], $hargas[$i]]);
                    $barang_id = $pdo->lastInsertId();
                }
                // Simpan Detail
                $sub = $hargas[$i] * $jumlahs[$i];
                $stmtDetail->execute([$transaksi_id, $barang_id, $jumlahs[$i], $hargas[$i], $sub]);
            }
        }

        $pdo->commit();
        echo "<script>alert('Transaksi Berhasil! Status: $status'); window.location='index.php';</script>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>