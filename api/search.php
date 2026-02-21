<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);

$query = $_GET['q'] ?? '';
$category_id = $_GET['category_id'] ?? null;
$min_price = $_GET['min_price'] ?? null;
$max_price = $_GET['max_price'] ?? null;
$sort = $_GET['sort'] ?? 'relevance';
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 12;

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Search query required']);
    exit;
}

$results = $functions->searchProducts($query, $category_id, $min_price, $max_price, $sort);

// Pagination
$offset = ($page - 1) * $limit;
$paginated_results = array_slice($results, $offset, $limit);
$total = count($results);

echo json_encode([
    'success' => true,
    'query' => $query,
    'results' => $paginated_results,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => ceil($total / $limit),
        'total_results' => $total,
        'limit' => $limit
    ]
]);
?>