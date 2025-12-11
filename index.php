<?php
require 'db_connect.php';

// Fetch products
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error gracefully, maybe log it
    // echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group 9 Global Market</title>
    <style>
        body {
            background-color: #121212;
            color: #d4af37;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #1e1e1e;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .add-btn {
            background-color: #d4af37;
            color: #121212;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        .add-btn:hover {
            background-color: #b5952f;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .card {
            background-color: #1e1e1e;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #333;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border-color: #d4af37;
        }
        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #333;
        }
        .card-content {
            padding: 20px;
        }
        .card-title {
            font-size: 1.2rem;
            margin: 0 0 10px;
            color: #fff;
        }
        .card-price {
            font-size: 1.1rem;
            color: #d4af37;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .card-desc {
            font-size: 0.9rem;
            color: #aaa;
            line-height: 1.5;
        }
        .empty-state {
            text-align: center;
            grid-column: 1 / -1;
            padding: 50px;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Group 9 Global Market</div>
        <a href="add_product.php" class="add-btn">Add Product</a>
    </header>

    <div class="container">
        <div class="grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-image">
                        <div class="card-content">
                            <h2 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <div class="card-price">$<?php echo number_format($product['price'], 2); ?></div>
                            <p class="card-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>No products found</h2>
                    <p>Start by adding some products to your inventory.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
