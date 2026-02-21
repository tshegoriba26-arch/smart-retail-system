<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$functions = new Functions($db);

$categories = $functions->getCategories();
?>
<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="page-header">
        <h1>Product Categories</h1>
        <p>Browse our wide range of product categories</p>
    </div>

    <div class="grid grid-4">
        <?php foreach ($categories as $category): ?>
            <div class="category-card">
                <div class="category-image">
                    <img src="<?php echo $category['image_url'] ?? 'images/category-placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($category['category_name']); ?>">
                </div>
                <div class="category-content">
                    <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description'] ?? 'Explore our products'); ?></p>
                    <a href="products.php?category=<?php echo $category['category_id']; ?>" class="btn btn-primary">
                        Browse Products
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<style>
    .page-header {
        text-align: center;
        margin-bottom: var(--space-8);
    }

    .category-card {
        background: var(--white);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow);
        transition: var(--transition);
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .category-image {
        height: 200px;
        overflow: hidden;
    }

    .category-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .category-card:hover .category-image img {
        transform: scale(1.1);
    }

    .category-content {
        padding: var(--space-5);
        text-align: center;
    }

    .category-content h3 {
        margin-bottom: var(--space-2);
        color: var(--dark);
    }

    .category-content p {
        color: var(--gray);
        margin-bottom: var(--space-4);
    }
</style>