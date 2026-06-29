<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Struk — Laundry-IN</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            padding: 20px;
            max-width: 210mm;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #0d9488;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            color: #0d9488;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            color: #64748b;
        }

        .info-section {
            margin-bottom: 16px;
            font-size: 11px;
        }

        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-section td {
            padding: 3px 4px;
            vertical-align: top;
        }

        .label {
            width: 110px;
            color: #64748b;
        }

        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.items th {
            background-color: #0d9488;
            color: #fff;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
        }

        table.items td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        table.items tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .total-row td {
            font-weight: bold;
            font-size: 13px;
            padding-top: 10px;
            border-top: 2px solid #0d9488;
        }

        .total-row .amount {
            color: #0d9488;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-diterima {
            background: #fef3c7;
            color: #92400e;
        }

        .status-dibuat {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-siap {
            background: #ccfbf1;
            color: #0d9488;
        }

        .status-selesai {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-print {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background: #0d9488;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
        }

        .btn-print:hover {
            background: #0f766e;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <button class="btn-print no-print" onclick="window.print()">
        <i class="ph-bold ph-printer"></i> Print Struk
    </button>
    <button class="btn-print no-print" onclick="window.close()" style="background:#6b7280;margin-top:8px;">
        Tutup Halaman
    </button>

    <div class="header">
        <h1>Laundry-IN</h1>
        <p>Sistem Manajemen Layanan Laundry</p>
        <p style="margin-top:4px; font-size:14px; font-weight:bold;">STRUK PEMBELIAN</p>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td class="label">Kode Pesanan</td>
                <td>: <strong><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong></td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td>: <?= date('d/m/Y H:i', strtotime($pesanan['tanggal_pesan'])) ?> WIB</td>
            </tr>
            <tr>
                <td class="label">Pelanggan</td>
                <td>: <?= htmlspecialchars($pesanan['nama'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">No. Telepon</td>
                <td>: <?= htmlspecialchars($pesanan['no_telp'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Alamat</td>
                <td>: <?= htmlspecialchars($pesanan['alamat'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Metode</td>
                <td>: <?= ucfirst($pesanan['metode_pengiriman']) ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td>: <span class="status-badge status-<?= $pesanan['status'] ?>"><?= strtoupper($pesanan['status']) ?></span></td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:40%;">Layanan</th>
                <th style="width:15%;">Harga</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            $total = 0; ?>
            <?php foreach ($detail as $item): ?>
                <tr>
                    <td style="text-align:center;"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($item['nama_layanan']) ?></td>
                    <td>Rp <?= number_format((int)$item['harga_satuan'], 0, ',', '.') ?></td>
                    <td style="text-align:center;"><?= (int)$item['quantity'] ?> <?= htmlspecialchars($item['satuan_harga'] ?? '') ?></td>
                    <td style="text-align:right;">Rp <?= number_format((int)$item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php $total += (int)$item['subtotal']; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align:right;">TOTAL</td>
                <td class="amount" style="text-align:right;">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Terima kasih telah menggunakan layanan Laundry-IN</p>
        <p>Struk ini dibuat otomatis pada <?= date('d/m/Y H:i') ?> WIB</p>
    </div>

    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js"></script>
</body>

</html>