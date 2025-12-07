<?php
include 'koneksi.php';

// Ambil data untuk Autocomplete
$karyawan = $pdo->query("SELECT nama_lengkap FROM karyawan")->fetchAll(PDO::FETCH_COLUMN);
$pelanggan = $pdo->query("SELECT nama FROM pelanggan")->fetchAll(PDO::FETCH_COLUMN);
$barang = $pdo->query("SELECT nama_barang FROM barang")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-box { font-size: 1.2rem; font-weight: bold; }
        .lunas { color: green; }
        .utang { color: red; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Input Nota & Utang</h4>
            <a href="data.php" class="btn btn-dark btn-sm">📂 Lihat Data Transaksi</a>
        </div>
        <div class="card-body">
            <form action="proses_transaksi.php" method="POST">
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Nota</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nama Kasir</label>
                        <input type="text" name="nama_karyawan" class="form-control" list="list-karyawan" required placeholder="Siapa kasirnya?">
                        <datalist id="list-karyawan">
                            <?php foreach($karyawan as $k) echo "<option value='$k'>"; ?>
                        </datalist>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" class="form-control" list="list-pelanggan" required placeholder="Siapa pelanggannya?">
                        <datalist id="list-pelanggan">
                            <?php foreach($pelanggan as $p) echo "<option value='$p'>"; ?>
                        </datalist>
                    </div>
                </div>

                <hr>
                
                <table class="table table-bordered" id="tabel-barang">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Barang</th>
                            <th width="100">Satuan</th>
                            <th width="150">Harga (@)</th>
                            <th width="100">Qty</th>
                            <th width="180">Subtotal</th>
                            <th width="50">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="nama_barang[]" class="form-control" list="list-barang" required></td>
                            <td><input type="text" name="satuan[]" class="form-control"></td>
                            <td><input type="number" name="harga_satuan[]" class="form-control input-harga" onkeyup="hitungSemua()" required></td>
                            <td><input type="number" name="jumlah[]" class="form-control input-qty" value="1" onkeyup="hitungSemua()" required></td>
                            <td><input type="number" name="subtotal[]" class="form-control input-subtotal" readonly></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">X</button></td>
                        </tr>
                    </tbody>
                </table>
                <datalist id="list-barang"><?php foreach($barang as $b) echo "<option value='$b'>"; ?></datalist>
                
                <button type="button" class="btn btn-secondary btn-sm mb-3" onclick="tambahBaris()">+ Tambah Barang</button>

                <div class="row mt-4 p-3 bg-white border rounded">
                    <div class="col-md-6">
                        <label class="fw-bold">Total Tagihan (Rp)</label>
                        <input type="number" id="total_tagihan_display" name="total_tagihan" class="form-control fw-bold fs-4 mb-3" readonly>
                        
                        <label class="fw-bold">Dibayar Saat Itu (Rp)</label>
                        <input type="number" name="total_dibayar" id="total_dibayar" class="form-control border-success" placeholder="Masukkan nominal pembayaran..." onkeyup="hitungSisa()" required>
                    </div>
                    <div class="col-md-6 text-center d-flex flex-column justify-content-center">
                        <h5>Sisa Tagihan (Utang)</h5>
                        <h2 id="sisa_tagihan_display">Rp 0</h2>
                        <input type="hidden" name="sisa_tagihan" id="input_sisa_tagihan">
                        <div id="status_text" class="status-box mt-2">-</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mt-4 fw-bold">SIMPAN TRANSAKSI</button>
            </form>
        </div>
    </div>
</div>

<script>
    function hitungSemua() {
        let total = 0;
        document.querySelectorAll('#tabel-barang tbody tr').forEach(row => {
            let hrg = parseFloat(row.querySelector('.input-harga').value) || 0;
            let qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            let sub = hrg * qty;
            row.querySelector('.input-subtotal').value = sub;
            total += sub;
        });
        document.getElementById('total_tagihan_display').value = total;
        hitungSisa();
    }

    function hitungSisa() {
        let tagihan = parseFloat(document.getElementById('total_tagihan_display').value) || 0;
        let bayar = parseFloat(document.getElementById('total_dibayar').value) || 0;
        let sisa = tagihan - bayar;

        document.getElementById('sisa_tagihan_display').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(sisa);
        document.getElementById('input_sisa_tagihan').value = sisa;

        let statusEl = document.getElementById('status_text');
        if (sisa > 0) {
            statusEl.innerText = "BELUM LUNAS";
            statusEl.className = "status-box utang";
        } else if (sisa <= 0 && tagihan > 0) {
            statusEl.innerText = "LUNAS";
            statusEl.className = "status-box lunas";
        } else {
            statusEl.innerText = "-";
        }
    }

    function tambahBaris() {
        let table = document.getElementById('tabel-barang').querySelector('tbody');
        let clone = table.rows[0].cloneNode(true);
        clone.querySelectorAll('input').forEach(inp => inp.value = '');
        clone.querySelector('.input-qty').value = 1;
        table.appendChild(clone);
    }
    
    function hapusBaris(btn) {
        if(document.querySelectorAll('#tabel-barang tbody tr').length > 1) {
            btn.closest('tr').remove();
            hitungSemua();
        }
    }
</script>
</body>
</html>