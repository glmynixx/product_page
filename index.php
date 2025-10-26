<?php

// ensure session is started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config.php';
include 'products.php';

$category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : null;
$title = 'Aimazon - ' . ucfirst($category ?? 'Home');

// add to cart or buy now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_to_cart']) || isset($_POST['buy_now']))) {
    $product_id = (string) ($_POST['product_id'] ?? '');
    $redirect_category = htmlspecialchars($_POST['current_category'] ?? $category ?? 'pistol');

    // check if product exists
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
        $_SESSION['message'] = 'Invalid product.';
        header('Location: index.php?category=' . urlencode($redirect_category));
        exit;
    }

    // ensure cart exists
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // add or update quantity
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if (is_array($item) && isset($item['id']) && (string)$item['id'] === $product_id) {
            $_SESSION['cart'][$key]['quantity'] = intval($item['quantity'] ?? 1) + 1;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = ['id' => $product_id, 'quantity' => 1];
    }

    // handle buy now
    if (isset($_POST['buy_now'])) {
        $_SESSION['message'] = "🛒 Product added — proceeding to checkout!";
        header('Location: cart.php');
        exit;
    }

    // handle add to cart
    $_SESSION['message'] = "✅ Product added to cart!";
    header('Location: index.php?category=' . urlencode($redirect_category));
    exit;
}

// prepare current category products
if (!$category) {
    $current_products = [];
} else {
    $current_products = $products[$category] ?? $products['pistol'] ?? [];
}

// count cart items
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
<?php
if (isset($_SESSION['message'])):
?>
  <div class="popup-center">
    <?php echo htmlspecialchars($_SESSION['message']); ?>
  </div>
  <?php unset($_SESSION['message']); ?>
<?php endif; ?>

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

                            <div class="card" onclick="showDescription('<?php echo htmlspecialchars($product['id']); ?>', event)">
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

                       
                        <?php foreach ($current_products as $product): ?>
                            <?php if (!empty($product['description'])): ?>
                                <div id="desc-<?php echo htmlspecialchars($product['id']); ?>" class="description-box">
                                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                                    <button onclick="closeDescription()">Close</button>
                                </div>
                            <?php endif; ?>
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

    <style>
    .description-box {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0.9);
      background: #808080;
      color: white;
      padding: 25px;
      border-radius: 15px;
      width: 400px;
      max-width: 90%;
      z-index: 1000;
      box-shadow: 0 0 20px ; #2F4F4F;
      opacity: 0;
      transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .description-box[style*="display: block"] {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }

    .description-box h2 {
        text-align: center;
        color: #244F4F;
        margin-bottom: 1px;
    }
    .description-box p {
        color: #FAF0E6;
        line-height: 1.5;
    }

    body.modal-open::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 999;
    }

    .description-box button {
        background-color: transparent;
        border: 2px solid #244F4F;
        color: #244F4F;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .description-box button:hover {
        background-color: #2a4055;
        color: white;
        transform: scale(1.05);
    }

    .popup-center {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #F5FFFA;
        color: #0b0b0b;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 20px;
        font-weight: 300;
        text-align: center;
        z-index: 99999;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        opacity: 0;
        animation: fadeInOutCenter 2.8s ease forwards;
}

    @keyframes fadeInOutCenter {
    0% {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    10%, 80% {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
    100% {
        opacity: 0;
        transform: translate(-50%, -40%);
    }
}

    </style>

    <script>
    function showDescription(id, event) {
      // prevent opening modal when clicking Add/Buy buttons
      if (event.target.tagName === 'BUTTON' || event.target.closest('form')) {
        return;
      }

      // close any open boxes
      document.querySelectorAll('.description-box').forEach(b => b.style.display = 'none');

      // open selected box
      const box = document.getElementById('desc-' + id);
      if (box) {
        box.style.display = 'block';
        document.body.classList.add('modal-open');
      }
    }

    function closeDescription() {
      document.querySelectorAll('.description-box').forEach(b => b.style.display = 'none');
      document.body.classList.remove('modal-open');
    }
    </script>
</main>

<?php include 'footer.php'; ?>
