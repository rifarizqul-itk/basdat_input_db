<?php
include 'koneksi.php';

// Ambil Data Transaksi (Header)
$sql = "SELECT t.*, p.nama AS nama_pelanggan, k.nama_lengkap AS nama_karyawan 
        FROM transaksi t
        JOIN pelanggan p ON t.pelanggan_id = p.id
        JOIN karyawan k ON t.karyawan_id = k.id
        ORDER BY t.tanggal DESC, t.id DESC";
$transaksi = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Transaksi Lengkap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .list-barang { font-size: 0.9rem; margin-bottom: 0; padding-left: 1.2rem; }
        .nominal { font-weight: bold; white-space: nowrap; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📋 Riwayat 44 Nota Transaksi</h3>
        <a href="index.php" class="btn btn-primary fw-bold">+ Input Transaksi Baru</a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Tgl & ID</th>
                            <th>Pelanggan & Kasir</th>
                            <th width="35%">Detail Barang (Qty x Harga)</th>
                            <th>Keuangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transaksi as $t): ?>
                        <tr>
                            <td class="text-center">
                                <strong><?= date('d/m/Y', strtotime($t['tanggal'])) ?></strong><br>
                                <span class="badge bg-secondary">#<?= $t['id'] ?></span>
                            </td>

                            <td>
                                🏢 <strong><?= htmlspecialchars($t['nama_pelanggan']) ?></strong><br>
                                <small class="text-muted">Kasir: <?= htmlspecialchars($t['nama_karyawan']) ?></small>
                            </td>

                            <td class="bg-light">
                                <ul class="list-barang">
                                    <?php 
                                    // Query Detail Barang per Transaksi
                                    $stmt_detail = $pdo->prepare("
                                        SELECT d.*, b.nama_barang, b.satuan 
                                        FROM detail_transaksi d 
                                        JOIN barang b ON d.barang_id = b.id 
                                        WHERE d.transaksi_id = ?
                                    ");
                                    $stmt_detail->execute([$t['id']]);
                                    $items = $stmt_detail->fetchAll();

                                    foreach($items as $item): 
                                    ?>
                                        <li>
                                            <?= $item['nama_barang'] ?> 
                                            <span class="text-muted">
                                                (<?= $item['jumlah'] ?> <?= $item['satuan'] ?> x <?= number_format($item['harga_satuan']) ?>)
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>

                            <td class="text-end">
                                Tagihan: <span class="nominal">Rp <?= number_format($t['total_tagihan']) ?></span><br>
                                Dibayar: <span class="text-success">Rp <?= number_format($t['total_dibayar']) ?></span><br>
                                <?php if($t['sisa_tagihan'] > 0): ?>
                                    <hr class="my-1">
                                    Sisa: <span class="text-danger fw-bold">Rp <?= number_format($t['sisa_tagihan']) ?></span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if($t['status_pembayaran'] == 'Lunas'): ?>
                                    <span class="badge bg-success">LUNAS</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">BELUM LUNAS</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>