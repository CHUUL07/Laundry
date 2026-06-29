<?php

require_once __DIR__ . '/../Models/PesananModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class PesananController
{
    private PesananModel $model;

    public function __construct()
    {
        $this->model = new PesananModel();
    }

    // ----------------------------------------------------------------
    // INDEX — GET /pesanan (Daftar semua pesanan)
    // ----------------------------------------------------------------
    public function index(): void
    {
        requireAuth();

        $status = $_GET['status'] ?? '';
        $statusList = ['diterima', 'dibuat', 'siap', 'selesai'];

        if ($status !== '' && in_array($status, $statusList)) {
            $pesanan = $this->model->findByStatus($status);
        } else {
            $pesanan = $this->model->all();
            $status = '';
        }

        // Hitung per status untuk badge filter
        $counts = [];
        foreach ($statusList as $s) {
            $counts[$s] = $this->model->countByStatus($s);
        }
        $counts['all'] = $this->model->countActive();

        $this->render('pesanan/index', [
            'pageTitle'  => 'Pesanan',
            'activePage' => 'pesanan',
            'pesanan'    => $pesanan,
            'statusNow'  => $status,
            'counts'     => $counts,
        ]);
    }

    // ----------------------------------------------------------------
    // DETAIL — GET /pesanan/{id}
    // ----------------------------------------------------------------
    public function detail(int $id): void
    {
        requireAuth();

        $pesanan = $this->model->findById($id);
        if (!$pesanan) {
            $this->redirect('/pesanan', 'flash_error', 'Pesanan tidak ditemukan.');
        }

        $detail = $this->model->getDetail($id);

        $this->render('pesanan/detail', [
            'pageTitle' => 'Detail Pesanan',
            'activePage' => 'pesanan',
            'pesanan'   => $pesanan,
            'detail'    => $detail,
        ]);
    }

    // ----------------------------------------------------------------
    // UPDATE STATUS — POST /pesanan/update-status/{id}
    // ----------------------------------------------------------------
    public function updateStatus(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pesanan');
        }

        if (!validate_csrf()) {
            $this->redirect('/pesanan', 'flash_error', 'Token CSRF tidak valid.');
        }

        $pesanan = $this->model->findById($id);
        if (!$pesanan) {
            $this->redirect('/pesanan', 'flash_error', 'Pesanan tidak ditemukan.');
        }

        // Workflow status
        $nextStatus = [
            'diterima' => 'dibuat',
            'dibuat'   => 'siap',
            'siap'     => 'selesai',
        ];

        $currentStatus = $pesanan['status'];

        if (!isset($nextStatus[$currentStatus])) {
            $this->redirect('/pesanan/' . $id, 'flash_error', 'Pesanan sudah selesai, tidak bisa diubah.');
        }

        $newStatus = $nextStatus[$currentStatus];
        $this->model->updateStatus($id, $newStatus);

        $labelStatus = [
            'diterima' => 'Diterima',
            'dibuat'   => 'Sedang Dibuat',
            'siap'     => 'Siap Diambil',
            'selesai'  => 'Selesai',
        ];

        $this->redirect('/pesanan/' . $id, 'flash_success', 'Status diperbarui: ' . ($labelStatus[$newStatus] ?? $newStatus));
    }

    // ----------------------------------------------------------------
    // EXPORT PDF — GET /pesanan/export-pdf/{id}
    // ----------------------------------------------------------------
    public function exportPdf(int $id): void
    {
        requireAuth();

        // Load Dompdf
        $autoloaderPaths = [
            __DIR__ . '/../../vendor/autoload.php',
            __DIR__ . '/../../../vendor/autoload.php',
        ];
        $loaded = false;
        foreach ($autoloaderPaths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $loaded = true;
                break;
            }
        }
        if (!$loaded) {
            http_response_code(500);
            echo 'Dompdf belum terinstall.';
            return;
        }

        $pesanan = $this->model->findById($id);
        if (!$pesanan) {
            http_response_code(404);
            echo 'Pesanan tidak ditemukan.';
            return;
        }

        $detail = $this->model->getDetail($id);

        ob_start();
        extract(['pesanan' => $pesanan, 'detail' => $detail]);
        include __DIR__ . '/../Views/pesanan/struk-pdf.php';
        $html = ob_get_clean();

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Struk-Laundry-IN_' . $pesanan['kode_pesanan'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    // ----------------------------------------------------------------
    // PRINT STRUK — GET /pesanan/print-struk/{id}
    // ----------------------------------------------------------------
    public function printStruk(int $id): void
    {
        requireAuth();

        $pesanan = $this->model->findById($id);
        if (!$pesanan) {
            http_response_code(404);
            echo 'Pesanan tidak ditemukan.';
            exit;
        }

        $detail = $this->model->getDetail($id);

        ob_start();
        extract(['pesanan' => $pesanan, 'detail' => $detail]);
        include __DIR__ . '/../Views/pesanan/print-struk.php';
        $content = ob_get_clean();
        echo $content;
        exit;
    }

    // ----------------------------------------------------------------
    // Private Helpers
    // ----------------------------------------------------------------
    private function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../Views/layouts/main.php';
    }

    private function redirect(string $path, string $flashKey = '', string $flashMsg = ''): void
    {
        if ($flashKey && $flashMsg) {
            $_SESSION[$flashKey] = $flashMsg;
        }
        header("Location: {$path}");
        exit;
    }
}
