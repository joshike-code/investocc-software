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
        $stocks = StockService::getAllStocks(); // Database only - no API calls
        Response::success([
            'categories' => $stockCategories,
            'stocks' => $stocks,
            'market_status' => StockService::getMarketStatus()
        ]);
    }

    public static function getStockById($id) {
        $stock = StockService::getStockByIdFromDb($id);
        Response::success($stock);
    }

    public static function getStocksByCategory($filter) {
        $stocks = StockService::getStocksByCategoryFromDb($filter);
        Response::success($stocks);
    }

    public static function searchStocks($searchTerm) {
        $stocks = StockService::searchStocksFromDb($searchTerm);
        Response::success($stocks);
    }

    public static function updateStockPrices() {
        $result = StockService::smartUpdateStockPrices();
        Response::success($result);
    }
}

