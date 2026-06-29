<?php // app/Views/layanan/pdf.php
// File ini di-render oleh Dompdf, BUKAN oleh browser langsung
// Tidak ada require layout di sini
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Jenis Layanan — Laundry-IN</title>
    <style>
        /* Dompdf mendukung CSS subset — hindari flexbox/grid */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a2e;
            background: #ffffff;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #0d9488;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20pt;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10pt;
            color: #64748b;
        }

        .meta-info {
            margin-bottom: 16px;
            font-size: 9pt;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background-color: #0d9488;
            color: #ffffff;
        }

        thead th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10pt;
            font-weight: bold;
            border: 1px solid #0d9488;
        }

        tbody tr:nth-child(even) {
            background-color: #f1f5f9;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 10pt;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-express {
            background-color: #ccfbf1;
            color: #0d9488;
        }

        .badge-reguler {
            background-color: #e0f2fe;
            color: #075985;
        }

        .harga {
            font-weight: bold;
            color: #0d9488;
        }

        .footer {
            margin-top: 24px;
            text-align: right;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        .summary {
            margin-bottom: 16px;
            padding: 10px;
            background: #f8fafc;
            border-left: 4px solid #0d9488;
            font-size: 10pt;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Laundry-IN</h1>
        <p>Sistem Manajemen Layanan Laundry</p>
        <p>Daftar Jenis Layanan</p>
    </div>

    <div class="meta-info">
        Dicetak pada: <?= date('d F Y, H:i') ?> WIB
        &nbsp;|&nbsp;
        Total: <?= count($layanan) ?> layanan aktif
    </div>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total Layanan Aktif: <?= count($layanan) ?> |
        Express: <?= count(array_filter($layanan, fn($l) => $l['kategori'] === 'express')) ?> |
        Reguler: <?= count(array_filter($layanan, fn($l) => $l['kategori'] === 'reguler')) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 25%;">Nama Layanan</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 13%;">Estimasi</th>
                <th style="width: 20%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($layanan)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data layanan.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($layanan as $index => $item): ?>
                    <tr>
                        <td style="text-align: center;"><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($item['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge badge-<?= $item['kategori'] ?>">
                                <?= ucfirst(htmlspecialchars($item['kategori'], ENT_QUOTES, 'UTF-8')) ?>
                            </span>
                        </td>
                        <td class="harga">
                            Rp <?= number_format((int)$item['harga'], 0, ',', '.') ?>
                        </td>
                        <td><?= htmlspecialchars(strtoupper($item['satuan_harga']), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['estimasi_durasi'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= $item['deskripsi']
                                ? htmlspecialchars(mb_substr($item['deskripsi'], 0, 80) . (mb_strlen($item['deskripsi']) > 80 ? '...' : ''), ENT_QUOTES, 'UTF-8')
                                : '<span style="color:#94a3b8;">—</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh sistem Laundry-IN.
        Dicetak: <?= date('d/m/Y H:i') ?>
    </div>

</body>

</html>