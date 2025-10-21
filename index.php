<?php

// ensure session is started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config.php'; // if config.php also starts session that's fine
include 'products.php';   // loads $products and $section_descriptions

$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : null;
$title = 'Aimazon - ' . ucfirst($category ?? 'Home');

// add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_to_cart']) || isset($_POST['buy_now']))) {
    // sanitize input
    $product_id = (string) ($_POST['product_id'] ?? '');
    $redirect_category = htmlspecialchars($_POST['current_category'] ?? $category ?? 'pistol');

    // basic validation: exist in product list
    $product_exists = false;
    foreach ($products as $cat => $cat_products) {
        foreach ($cat_products as $p) {
            if (isset($p['id']) && (string)$p['id'] === $product_id) {
                $product_exists = true;
                break 2;
            }
        }
    }

    if (!$product_exists) {
        // invalid id — set message and redirect back
        $_SESSION['message'] = 'Invalid product.';
        header('Location: index.php?category=' . urlencode($redirect_category));
        exit;
    }

    // ensure cart array
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // prevent accidental double-processing of same POST 
    $post_token = bin2hex(random_bytes(8));
    $_SESSION['last_post_token'] = $post_token;

    // add or increment using keys
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if (is_array($item) && isset($item['id']) && (string)$item['id'] === $product_id) {
            // increment quantity
            $current_qty = isset($item['quantity']) ? intval($item['quantity']) : 1;
            $_SESSION['cart'][$key]['quantity'] = $current_qty + 1;
            $found = true;
            break;
        }
    }

    if (!$found) {
        // append new item
        $_SESSION['cart'][] = ['id' => $product_id, 'quantity' => 1];
    }

   if (isset($_POST['buy_now'])) {
    header('Location: cart.php');
    exit;
} else {
    $_SESSION['message'] = 'Product added to cart!';
    header('Location: index.php?category=' . urlencode($redirect_category));
    exit;
}

}

// prepare products for current category
if (!$category) {
    $current_products = [];
} else {
    $current_products = $products[$category] ?? $products['pistol'] ?? [];
}

// compute cart count 
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) {
        if (is_array($it)) {
            $cart_count += intval($it['quantity'] ?? 1);
        }
    }
}
?>

<?php include 'header.php'; ?>
<main>
    <head> 
        <link rel="stylesheet" href="aimazon.css">
</head>
    <?php if ($category): ?>

    <section id="<?php echo htmlspecialchars($category); ?>" class="page-content">
        <div class="container">
            <div class="section-header">
                <h2><?php echo ucfirst(htmlspecialchars($category)); ?></h2>
                <p><?php echo htmlspecialchars($section_descriptions[$category] ?? 'Products in this category'); ?></p>
            </div>
            <div class="product-flex">
                <div class="gun-grid">
                    <?php if (empty($current_products)): ?>
                        <p>No products found.</p>
                    <?php else: ?>
                        <?php foreach ($current_products as $product): ?>
                                <div class="card">
                                <div class="card-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                <div class="card-content">
                                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                                    <p class="price"><strong>Price: ₱<?php echo number_format($product['price'], 2); ?></strong></p><br>
                                    <div class="cart-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                            <input type="hidden" name="current_category" value="<?php echo htmlspecialchars($category); ?>">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                                            <button type="submit" name="buy_now" class="btn btn-primary">Buy Now</button>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="container" style="text-align: center; padding: 1rem; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>
