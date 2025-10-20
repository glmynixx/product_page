<header>
  <div class="container header-content">
    <?php include 'nav.php'; ?>
    <aside>
        <nav>
            <ul>
                
                <a href="cart.php" class="nav-link">🛒 (<?php echo isset($cart_count) ? $cart_count : 0; ?>)</a>
                <a href="#" class="nav-link">👤</a>

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

    .logo {
        position: absolute;
        left: 20px;
        }
        
    header {
            background: linear-gradient(135deg, #2a4055);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

     .header-content aside {
        position: absolute;
        right: 10px;
        display: flex;
        align-items: center;
        gap: 1rem;
        }

    .dropdown {
        position: relative;
        display: inline-block;
        }

    .dropbtn {
        padding: 10px 16px;
        font-size: 16px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        background-color: #2a4055;
        color: #ffffff;
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

    <?php 
    // show front only on home page
    $is_home = !isset($_GET['category']);
    if ($is_home): 
    ?>
    <div class="front">
        <div class="container front-content">
            <h1>Welcome To Aimazon!</h1>
            <h2>Enjoy Shopping</h2>
            <a href="index.php?category=pistol" class="btn btn-primary">Shop Now</a> 
        </div>
    </div>
    <?php endif; ?>

   <script src="script.js"></script>

