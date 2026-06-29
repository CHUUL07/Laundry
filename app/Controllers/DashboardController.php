<?php

require_once __DIR__ . '/../Models/LayananModel.php';
require_once __DIR__ . '/../Models/PelangganModel.php';
require_once __DIR__ . '/../Helpers/auth.php';

class DashboardController
{
    private LayananModel $layananModel;

    public function __construct()
    {
        $this->layananModel = new LayananModel();
    }

    public function index(): void
    {
        requireAuth();

        $pelangganModel = new PelangganModel();

        $data = [
            'pageTitle'          => 'Dashboard',
            'activePage'         => 'dashboard',
            'totalAktif'         => $this->layananModel->countActive(),
            'totalExpress'       => $this->layananModel->countByKategori('express'),
            'totalReguler'       => $this->layananModel->countByKategori('reguler'),
            'totalArsip'         => $this->layananModel->countArchived(),
            'recentLayanan'      => $this->layananModel->recent(5),
            'totalPelanggan'     => $pelangganModel->countActive(),
            'totalPelangganArsip' => $pelangganModel->countArchived(),
        ];

        $this->render('dashboard/index.php', $data);
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $viewPath;
        $content = ob_get_clean();

        include __DIR__ . '/../Views/layouts/main.php';
    }
}
