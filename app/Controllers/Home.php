<?php

namespace App\Controllers;

use App\Libraries\Database;

class Home extends BaseController
{
    public function index(): string
    {
        // Load data layanan via PDO
        $db = \App\Libraries\Database::getConnection();
        $stmt = $db->prepare(
            "SELECT * FROM jenis_layanan WHERE deleted_at IS NULL ORDER BY created_at DESC"
        );
        $stmt->execute();
        $layanan = $stmt->fetchAll();

        $data = [
            'pageTitle' => 'Beranda',
            'layanan'   => $layanan,
        ];

        // Render landing page dengan custom layout (seperti LandingController)
        extract($data);
        ob_start();
        include APPPATH . 'Views/landing/index.php';
        $content = ob_get_clean();

        ob_start();
        include APPPATH . 'Views/layouts/landing.php';
        return ob_get_clean();
    }
}
