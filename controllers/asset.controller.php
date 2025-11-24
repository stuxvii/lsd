<?php

require_once ROOT_PATH . "/models/asset.php";

class AssetController extends BaseController {

    public function __CONSTRUCT() {
        parent::__CONSTRUCT();
        $this->id = $_GET["id"] ?? $_GET["assetversionid"] ?? null;
        $this->asset_model = new AssetModel();
        if ($this->id) {
            $this->item_info = $this->asset_model->getItemData($this->id);
        }
    }

    public function Thumbnail() {
        require ROOT_PATH . "/models/thumbnail.php";
    }

    public function Purchase() {
        require ROOT_PATH . "/models/purchase.php";
    }

    public function Catalog() {
        $links = [
            ['href' => 'catalog?meow=1', 'text' => 'Decals'],
            ['href' => 'catalog?meow=2', 'text' => 'Sounds'],
            ['href' => 'catalog?meow=4', 'text' => 'T-Shirts'],
            ['href' => 'catalog?meow=5', 'text' => 'Shirts'],
            ['href' => 'catalog?meow=6', 'text' => 'Pants'],
            ['href' => 'catalog?meow=7', 'text' => 'Faces'],
            ['href' => 'catalog?meow=8', 'text' => 'Heads'],
            ['href' => 'catalog?meow=9', 'text' => 'Hats'],
        ];

        $category = isset($_GET['meow']) ? $_GET['meow'] : 2;
        $page = isset($_GET['page']) ? $_GET['page'] : 0;
        $raw = isset($_GET['raw']) ? $_GET['raw'] : false;

        $category = (int) $category;
        $page = (int) $page - 1;
        $raw = (bool) $raw;

        $itemsperpage = 35;
        $offset = $itemsperpage * $page;
        
        if ($raw) {
            header('Content-type: application/json');
            $stmt = $this->db->prepare('
                SELECT *
                FROM `items`
                WHERE approved = 1 AND public = 1 AND type = ?
                ORDER BY id DESC
                LIMIT ? OFFSET ?
            ');
            $stmt->bindParam(1, $category, PDO::PARAM_INT);
            $stmt->bindParam(2, $itemsperpage, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $offset = $offset + $itemsperpage;
            $stmt->execute();
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
            die(json_encode($fetch));
        }
        
        ob_start();
        require ROOT_PATH . "/views/asset/catalog.php";
        $page_content = ob_get_clean();
        require ROOT_PATH . "/views/layout/template.php";
    }

    public function Item() {
        ob_start();
        ?><meta property="og:title" content="LSDBlox - <?=htmlspecialchars($this->item_info["name"])?>">
        <meta property="og:description" content="<?=htmlspecialchars($this->item_info["desc"])?>">
        <meta property="og:image" content="https://lsdblox.cc/asset/thumbnail?id=<?=$this->id?>&amp;=<?=time()?>">
        <meta property="og:type" content="website">
        <?php
        $meta_tags = ob_get_clean();
        ob_start();
        require ROOT_PATH . "/views/asset/item.php";
        $page_content = ob_get_clean();
        require ROOT_PATH . "/views/layout/template.php";
    }

    public function Upload() {
        if (!$this->user_info) {
            header("Location: /");
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            require ROOT_PATH . "/models/upload.php";
            exit;
        }
        ob_start();
        require ROOT_PATH . "/views/asset/upload.php";
        $page_content = ob_get_clean();
        require ROOT_PATH . "/views/layout/template.php";
    }

    public function Main() {
        $item = $this->item_info;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/modpending.png';

        if ($this->id <= 0 || !is_numeric($this->id)) {
            http_response_code(400);
            exit('Invalid file request.');
        }


        if (!$item['approved'] == 1) {
            if (!$this->user_info["isoperator"]) {
                header('Content-Length: ' . filesize($file_path));
                header('Content-Type: image/png');
                readfile($file_path);
                exit;
            }
        }

        $file_path = ROOT_PATH . '/' . $item['asset'];
        $filename = basename($file_path);

        if (!file_exists($file_path) || !is_readable($file_path)) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/404.png';
            $filename = basename($file_path);
            header('Content-Length: ' . filesize($file_path));
            header('Content-Type: image/png');
            http_response_code(404);
            readfile($file_path);
            exit;
        }

        header("Content-Disposition: attachment; filename=\"download\"");
        header("Content-type: application/octet-stream");
        echo file_get_contents($file_path);
        exit;
    }

    public function Meshvalidate() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $mesh = $_POST["mesh"];
            $output = verify_mesh($mesh);
        }
        require_once ROOT_PATH . "/views/mesh_test.php";
    }
}