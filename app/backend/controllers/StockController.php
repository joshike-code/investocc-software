<?php

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../services/StockService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class StockController {

    public static function getStocks() {
        $stockCategories = StockService::getStockCategories();
        $stocks = StockService::getAllStocks();
        Response::success([
            'categories' => $stockCategories,
            'stocks' => $stocks
        ]);
    }

    public static function getStockById($id) {
        $stock = StockService::getStockById($id);
        Response::success($stock);
    }

    public static function getStocksByCategory($filter) {
        $stocks = StockService::getStocksByCategory($filter);
        Response::success($stocks);
    }

    public static function searchStocks($searchTerm) {
        $stocks = StockService::searchStocks($searchTerm);
        Response::success($stocks);
    }
}

