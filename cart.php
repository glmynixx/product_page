<?php

// ensure session started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config.php';
include 'products.php';

$title = 'Shopping Cart - Aimazon';
$hide_front = true;

// handle POST actions: remove, update quantity, clear
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $product_id = (string) ($_POST['product_id'] ?? '');
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) use ($product_id) {
                return !is_array($item) || (string)$item['id'] !== $product_id;
            }));
        }
        header('Location: cart.php');
        exit;
    } elseif (isset($_POST['update_quantity'])) {
        $product_id = (string) ($_POST['product_id'] ?? '');
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $k => $item) {
                if (is_array($item) && (string)$item['id'] === $product_id) {
                    $_SESSION['cart'][$k]['quantity'] = $quantity;
                    break;
                }
            }
        }
        header('Location: cart.php');
        exit;
    } elseif (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit;
    }
}

// Debug: print cart session in HTML comment to inspect without breaking layout
echo "<!-- DEBUG: SESSION CART: ";
echo htmlspecialchars(json_encode($_SESSION['cart'] ?? 'EMPTY'));
echo " -->";

// product lookup keyed by id 
$product_lookup = [];
foreach ($products as $cat_products) {
    foreach ($cat_products as $prod) {
        if (isset($prod['id'])) {
            $product_lookup[(string)$prod['id']] = $prod;
        }
    }
}

// collect cart items from session
$cart_items = [];
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item) && isset($item['id'])) {
            // ensure quantity int
            $item['quantity'] = max(1, intval($item['quantity'] ?? 1));
            $cart_items[] = $item;
        }
    }
}

// enrich cart items with name/price/subtotal
$total = 0.0;
foreach ($cart_items as $idx => $item) {
    $id = (string)$item['id'];
    if (isset($product_lookup[$id])) {
        $prod = $product_lookup[$id];
        $cart_items[$idx]['name'] = $prod['name'];
        $cart_items[$idx]['price'] = floatval($prod['price']);
        $cart_items[$idx]['subtotal'] = $cart_items[$idx]['price'] * $cart_items[$idx]['quantity'];
        $total += $cart_items[$idx]['subtotal'];
    } else {
        $cart_items[$idx]['name'] = 'Unknown Product (ID: ' . htmlspecialchars($id) . ')';
        $cart_items[$idx]['price'] = 0;
        $cart_items[$idx]['subtotal'] = 0;
    }
}

// cart count
$cart_count = 0;
foreach ($cart_items as $it) {
    $cart_count += intval($it['quantity'] ?? 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Aimazon'; ?></title>
    <link rel="stylesheet" href="aimazon.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <?php include 'nav.php'; ?>
            <aside>
                <nav>
                    <ul>
                        <li><a href="cart.php" class="nav-link">🛒 (<?php echo isset($cart_count) ? $cart_count : 0; ?>)</a></li>
                        <li><a href="#" class="nav-link">👤</a></li>
                            <div class="dropdown">
                            <button class="dropbtn">☰</button>
                            <div class="dropdown-content">
                                <a href="#">Home</a>
                                <a href="#">About Us</a>
                                <a href="#">Contact</a>
                                <a href="#">Logout</a>
                            </div>
                        </div>
                    </ul>
                </nav>
            </aside>
        </div>
    </header>

<style>

    .dropdown {
    position: relative;
    display: inline-block;
    }

    .dropbtn {
    background-color: #2a4055;
    color: white;
    padding: 10px 16px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    }

    .dropdown-content {
    display: none;
    position: absolute;
    right: 0; 
    background-color: #f1f1f1;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 6px;
    overflow: hidden;
    }

    .dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    }

    .dropdown-content a:hover {
    background-color: #ddd;
    }

    .dropdown:hover .dropdown-content {
    display: block;
    }

    .dropdown:hover .dropbtn {
    background-color: #696969;
    }
    
</style>

<main class="container" style="padding: 2rem 0; max-width: 1300px; margin: 0 auto;">
    <div class="section-header">
        <h2>Shopping Cart</h2>

        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty. <a href="index.php?category=pistol">Continue Shopping</a></p>
        <?php else: ?>
            <div style="display: flex; justify-content: center; width: 100%;">
            <table style="width: 80%; max-wdith: 1000px; border-collapse: collapse; margin: 2rem auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <thead>
                    <tr style="background-color: #2F4F4F; color: white; font-size: 20px;">
                        <th style="padding: 1.3rem; text-align: center;">Product</th>
                        <th style="padding: 1.3rem; text-align: left; transform: translateX(-40px);">Price</th>
                        <th style="padding: 1.3rem; text-align: center;">Quantity</th>
                        <th style="padding: 1.3rem; text-align: center;">Total</th>
                        <th style="padding: 1.3rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <?php if (!empty($product_lookup[$item['id']]['image'])): ?>
                        <img src="images/<?php echo htmlspecialchars($product_lookup[$item['id']]['image']); ?>" 
                            alt="<?php echo htmlspecialchars($item['name']); ?>" 
                            style="width: 150px; height: 80px; object-fit: contain; border-radius: 6px; border: 1px solid #ccc;">
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                        </td>
                            <td style="padding: 1rem; text-align: left; transform: translateX(-50px);">₱<?php echo number_format($item['price'], 2); ?></td>
                            <td style="padding: 1rem;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <input type="number" name="quantity" value="<?php echo intval($item['quantity']); ?>" min="1" style="width: 60px; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 0.4rem;">
                                    <button type="submit" name="update_quantity" style="padding: 0.25rem 0.5rem; margin-left: 0.1rem; background: #2F4F4F; color: white; border: none; border-radius: 4px; cursor: pointer;">Update</button>
                                </form>
                            </td>
                            <td style="padding: 1rem;">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                            <td style="padding: 1rem;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <button type="submit" name="remove_item" style="background: #ff4444; color: white; padding: 0.25rem 0.5rem; border: none; border-radius: 4px; cursor: pointer;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                        </div>
            <div style="text-align: center; margin-top: 1rem;   transform: translateX(370px);  ">
                <p style="font-size: 1.5rem; font-weight: bold; color: #2F4F4F;">Total: ₱<?php echo number_format($total, 2); ?></p>
                <form method="POST" style="display:inline; margin-right:1rem;">
                    <button type="submit" name="clear_cart" style="background: #ff4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer;" onclick="return confirm('Clear entire cart?');">Clear Cart</button>
                </form>
                <a href="#" style="padding: 0.5rem 1rem; background: #2F4F4F; color: white; text-decoration: none; border-radius: 4px;">Checkout</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
