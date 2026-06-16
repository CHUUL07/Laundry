<?php

require_once __DIR__ . '/../Models/LayananModel.php';

class LandingController
{
    private LayananModel $layananModel;

    public function __construct()
    {
        $this->layananModel = new LayananModel();
    }

    /**
     * GET / — Show public landing page with all active services
     */
    public function index(): void
    {
        $layanan = $this->layananModel->all();

        $data = [
            'pageTitle' => 'Beranda',
            'layanan'   => $layanan,
        ];

        $this->render('landing/index.php', $data);
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        ob_start();
        include __DIR__ . '/../Views/' . $viewPath;
        $content = ob_get_clean();

        include __DIR__ . '/../Views/layouts/landing.php';
    }
}
