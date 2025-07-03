<?php
require_once '../../php/db_connect.php';

// Initialize response array
$response = [];

try {
    // Get product statistics by category
    $categoryStats = $pdo->query("
        SELECT 
            category,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
            SUM(stock) as total_stock
        FROM products 
        GROUP BY category
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for chart
    $productStats = [
        'labels' => [],
        'status' => [],
        'stock' => []
    ];

    foreach ($categoryStats as $stat) {
        $productStats['labels'][] = $stat['category'];
        $productStats['status'][] = [
            'active' => (int)$stat['active'],
            'inactive' => (int)$stat['inactive']
        ];
        $productStats['stock'][] = (int)$stat['total_stock'];
    }

    // Get product summary
    $summary = $pdo->query("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
            SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock_products,
            SUM(stock) as total_stock
        FROM products
    ")->fetch(PDO::FETCH_ASSOC);

    $response['productStats'] = $productStats;
    $response['productSummary'] = [
        'total_products' => (int)$summary['total_products'],
        'active_products' => (int)$summary['active_products'],
        'low_stock_products' => (int)$summary['low_stock_products'],
        'total_stock' => (int)$summary['total_stock']
    ];

    // Get monthly sales and profit data
    $monthlySales = $pdo->query("
        SELECT 
            COALESCE(SUM(o.total_amount), 0) as total,
            COUNT(*) as order_count,
            COALESCE(SUM(oi.total_price - (oi.quantity * p.cost)), 0) as profit,
            COALESCE(SUM(CASE 
                WHEN o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)
                AND o.created_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                THEN o.total_amount 
                ELSE 0 
            END), 0) as previous_month_total,
            COALESCE(SUM(CASE 
                WHEN o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)
                AND o.created_at < DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                THEN (oi.total_price - (oi.quantity * p.cost))
                ELSE 0 
            END), 0) as previous_month_profit
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 2 MONTH)
        AND o.payment_status = 'paid'
    ")->fetch(PDO::FETCH_ASSOC);

    // Calculate growth percentages
    $currentMonthTotal = (float)$monthlySales['total'];
    $previousMonthTotal = (float)$monthlySales['previous_month_total'];
    $currentMonthProfit = (float)$monthlySales['profit'];
    $previousMonthProfit = (float)$monthlySales['previous_month_profit'];

    $salesGrowth = $previousMonthTotal > 0 
        ? (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100 
        : 100;

    $profitGrowth = $previousMonthProfit > 0 
        ? (($currentMonthProfit - $previousMonthProfit) / $previousMonthProfit) * 100 
        : 100;

    $response['monthlySales'] = [
        'total' => $currentMonthTotal,
        'profit' => $currentMonthProfit,
        'order_count' => (int)$monthlySales['order_count'],
        'growth' => round($salesGrowth, 2),
        'profit_growth' => round($profitGrowth, 2)
    ];

    // Get recent transactions
    $recentTransactions = $pdo->query("
        SELECT 
            o.id,
            o.order_number,
            o.total_amount,
            o.payment_status,
            o.status,
            o.created_at,
            c.name as customer_name,
            GROUP_CONCAT(p.name) as products
        FROM orders o
        JOIN customers c ON o.user_id = c.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $response['recentTransactions'] = $recentTransactions;

    // Get top performing products with profit metrics
    $topProducts = $pdo->query("
        SELECT 
            p.id,
            p.name,
            p.images,
            COUNT(oi.id) as total_sales,
            SUM(oi.total_price) as total_revenue,
            SUM(oi.total_price - (oi.quantity * p.cost)) as total_profit,
            SUM(oi.quantity) as units_sold,
            AVG(oi.total_price - (oi.quantity * p.cost)) as avg_profit_per_sale
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        AND o.payment_status = 'paid'
        AND o.status = 'completed'
        GROUP BY p.id
        ORDER BY total_revenue DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $response['topProducts'] = $topProducts;

    // Get order timeline with enhanced tracking
    $orderTimeline = $pdo->query("
        SELECT 
            o.id,
            o.order_number,
            o.status,
            o.payment_status,
            o.total_amount,
            o.created_at,
            o.updated_at,
            c.name as customer_name,
            GROUP_CONCAT(p.name) as products,
            SUM(oi.total_price - (oi.quantity * p.cost)) as order_profit
        FROM orders o
        JOIN customers c ON o.user_id = c.id
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $response['orderTimeline'] = $orderTimeline;

    // Get new users count (last 7 days)
    $newUsers = $pdo->query("
        SELECT COUNT(*) as count
        FROM customers
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    ")->fetch(PDO::FETCH_ASSOC);

    $response['newUsers'] = (int)$newUsers['count'];

    // Get pending orders count
    $pendingOrders = $pdo->query("
        SELECT COUNT(*) as count
        FROM orders
        WHERE status = 'pending'
    ")->fetch(PDO::FETCH_ASSOC);

    $response['pendingOrders'] = (int)$pendingOrders['count'];

    // Get unread messages count
    $unreadMessages = $pdo->query("
        SELECT COUNT(*) as count
        FROM contact_messages
        WHERE status = 'unread'
    ")->fetch(PDO::FETCH_ASSOC);

    $response['unreadMessages'] = (int)$unreadMessages['count'];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>