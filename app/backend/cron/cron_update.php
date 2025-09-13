<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PlatformService.php';
require_once __DIR__ . '/../services/StockService.php';

date_default_timezone_set('GMT');
$conn = Database::getConnection();

try {
    $query = "SELECT * FROM investments WHERE status = 'pending'";
    $result = $conn->query($query);

    while ($investment = $result->fetch_assoc()) {
        $id = $investment['id'];
        $user_id = $investment['user_id'];
        $order_ref = $investment['order_ref'];
        $amount = (float)$investment['amount'];
        $rate = (float)$investment['rate'];
        $days = (int)$investment['days'];
        $duration = strtolower($investment['duration']);
        $date_started = new DateTime($investment['date'], new DateTimeZone('GMT'));

        $now = new DateTime("now", new DateTimeZone('GMT'));
        $interval = $now->diff($date_started)->days;

        // Calculate frequency
        $frequencyDays = match ($duration) {
            'daily' => 1,
            'weekly' => 7,
            'monthly' => 30,
            'yearly' => 365,
            default => 365,
        };

        $numPeriods = $days / $frequencyDays;
        $roi = $amount * ($rate / 100) * $numPeriods;
        $profit = $roi + $amount;

        // Update all ROI
        $update = $conn->prepare("UPDATE investments SET roi = ? WHERE id = ?");
        $update->bind_param("di", $roi, $id);
        $update->execute();
        $update->close();

        if ($interval < $days) {
            continue; // Not yet matured
        }

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Update matured investment
            $update = $conn->prepare("UPDATE investments SET status = 'complete', date_completed = ? WHERE id = ?");
            $completed_date = $now->format('Y-m-d H:i:s');
            $update->bind_param("si", $completed_date, $id);
            $update->execute();
            $update->close();

            // Log to payments table
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, order_ref, method, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $method = "investment";
            $type = "credit";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdsssss", $user_id, $profit, $order_ref, $method, $type, $status, $date);
            $insert->execute();

            // Credit user
            $credit = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $credit->bind_param("di", $profit, $user_id);
            $credit->execute();
            $credit->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Failed to update investment $id: " . $e->getMessage());
        }
    }

    echo "Investment processing completed successfully.\n";

    // Update stock prices using smart update (respects configured frequency)
    echo "Starting stock price updates...\n";
    try {
        $stockUpdateResult = StockService::smartUpdateStockPrices();
        
        if ($stockUpdateResult['updated'] > 0) {
            echo "Stock update: " . $stockUpdateResult['message'] . "\n";
            echo "Updated {$stockUpdateResult['updated']} stocks, skipped {$stockUpdateResult['skipped']} with fresh cache.\n";
        } else {
            echo "Stock update: All prices are fresh (within configured update frequency).\n";
        }
        
        // Log stock update results
        $logMessage = sprintf(
            "[%s] Stock update: Updated %d/%d stocks\n",
            date('Y-m-d H:i:s'),
            $stockUpdateResult['updated'],
            $stockUpdateResult['total']
        );
        
        $logFile = __DIR__ . '/../update_stock_prices.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
    } catch (Exception $stockException) {
        error_log("Stock update failed: " . $stockException->getMessage());
        echo "Stock update failed: " . $stockException->getMessage() . "\n";
    }

    echo "Cron completed successfully.\n";

} catch (Exception $e) {
    error_log("Cron failed: " . $e->getMessage());
    echo "Cron failed.\n";
}