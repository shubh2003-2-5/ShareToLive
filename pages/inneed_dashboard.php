<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'inneed') {
    die("Access Denied");
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart action (API endpoint)
if (isset($_POST['add_to_cart']) && isset($_POST['donation_id'])) {
    header('Content-Type: application/json');
    $donation_id = (int)$_POST['donation_id'];
    $donation = $conn->query("SELECT * FROM donations WHERE id = $donation_id")->fetch_assoc();
    
    if ($donation && !isset($_SESSION['cart'][$donation_id])) {
        $_SESSION['cart'][$donation_id] = $donation;
        echo json_encode([
            'status' => 'success',
            'cart_count' => count($_SESSION['cart']),
            'cart_html' => getCartHtml($_SESSION['cart'])
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item already in cart or not available']);
    }
    exit;
}

// Handle remove from cart action
if (isset($_GET['remove_from_cart'])) {
    $donation_id = (int)$_GET['remove_from_cart'];
    if (isset($_SESSION['cart'][$donation_id])) {
        unset($_SESSION['cart'][$donation_id]);
    }
    header('Location: inneed_dashboard.php');
    exit;
}

$donations = $conn->query("SELECT * FROM donations WHERE status = 'available' ORDER BY timestamp DESC");

function getCartHtml($cartItems) {
    ob_start();
    if (!empty($cartItems)): ?>
        <div class="cart-items">
            <?php foreach ($cartItems as $id => $item): ?>
                <div class="cart-item">
                    <img src="../uploads/<?= $item['image_path'] ?>" alt="Food Image">
                    <div class="cart-item-info">
                        <p><strong><?= $item['name'] ?></strong></p>
                        <p><?= $item['food_details'] ?></p>
                        <p>Quantity: <?= $item['quantity'] ?></p>
                    </div>
                    <a href="?remove_from_cart=<?= $id ?>" class="remove-btn">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <form method="POST" action="../includes/place_request.php" class="request-form">
            <input type="hidden" name="cart_items" value="<?= htmlspecialchars(json_encode(array_keys($cartItems))) ?>">
            
            <label for="location">Pickup Location</label>
            <input type="text" name="location" id="location" required readonly>
            <button type="button" class="btn-submit" onclick="getLocation()">📍 Use Current Location</button><br>

            <label for="note">📝 Note (Optional):</label>
            <textarea name="note" placeholder="Any special note..."></textarea>

            <button type="submit" class="btn-submit">🚚 Place Request</button>
        </form>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty. Select donations below to add them.</p>
        </div>
    <?php endif;
    return ob_get_clean();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Place Food Request - ShareToLive</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .toggle-mode {
      background: none;
      color: white;
      border: 1px solid white;
      padding: 0.5rem 1rem;
      cursor: pointer;
      border-radius: 5px;
      transition: all 0.3s ease;
    }

    .toggle-mode:hover {
      background: white;
      color: #2c3e50;
    }

    header {
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #2c3e50;
      color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .home-button {
      background: none;
      color: white;
      border: 1px solid white;
      padding: 0.5rem 0.8rem; /* Reduced horizontal padding */
      cursor: pointer;
      border-radius: 5px;
      transition: all 0.3s ease;
      width: 4px; /* Fixed width */
      text-align: center;
      font-size: 0.9rem; /* Slightly smaller font */
    }

    .home-button:hover {
      background: white;
      color: #2c3e50;
    }

    button{
        width: 80px;
    }

    .btn-submit{
        width: 150px;
    }
    :root {
      --bg-light: linear-gradient(to right, #e0f7fa, #fff);
      --bg-dark: linear-gradient(to right, #1a2a3a, #0d1b2a);
      --text-light: #333;
      --text-dark: #f8f9fa;
      --card-bg-light: #fff;
      --card-bg-dark: #34495e;
      --header-bg: #2c3e50;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: var(--bg-light);
      color: var(--text-light);
      animation: fadeIn 1s ease-in;
    }

    body.dark-mode {
      background: var(--bg-dark);
      color: var(--text-dark);
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    header {
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: var(--header-bg);
      color: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .header-right {
      display: flex;
      gap: 10px;
    }

    .header-btn {
      background: none;
      color: white;
      border: 1px solid white;
      padding: 0.5rem 1rem;
      cursor: pointer;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      min-width: 80px;
      text-align: center;
    }

    .header-btn:hover {
      background: white;
      color: var(--header-bg);
      transform: translateY(-2px);
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
    }

    /* Dark mode specific styles */
    body.dark-mode .cart-section,
    body.dark-mode .available-donations {
      background: #2c3e50;
      color: var(--text-dark);
    }

    body.dark-mode .donation-card {
      background: var(--card-bg-dark);
      color: var(--text-dark);
    }

    body.dark-mode .cart-item {
      background: #3b4a5a;
      color: var(--text-dark);
    }

    body.dark-mode .request-form input,
    body.dark-mode .request-form textarea {
      background: #3b4a5a;
      color: var(--text-dark);
      border-color: #555;
    }


  </style>
</head>
<body>
<header>
    <h1>🍽️ ShareToLive</h1>
    <div class="header-right">
      <button class="header-btn" id="homeButton">HOME</button>
      <button class="header-btn" id="toggleButton">Toggle Mode</button>
    </div>
  </header>
  <div class="container">
    <h2>🍽️ Place a Food Request</h2>
    
    <!-- Cart Section -->
    <div class="cart-section fade-in" id="cartSection">
      <h3>🛒 Your Cart (<span id="cartCount"><?= count($_SESSION['cart']) ?></span>)</h3>
      <?= getCartHtml($_SESSION['cart']) ?>
    </div>

    <!-- Available Donations -->
    <div class="available-donations fade-in delay-1">
      <h3>🍎 Available Donations</h3>
      <div class="donation-list">
        <?php while ($donation = $donations->fetch_assoc()): ?>
          <?php $inCart = isset($_SESSION['cart'][$donation['id']]); ?>
          <div class="donation-card <?= $inCart ? 'in-cart' : '' ?>" data-id="<?= $donation['id'] ?>">
            <div class="card-content">
              <img src="../uploads/<?= $donation['image_path'] ?>" alt="Food Image" class="donation-img">
              <div class="donation-info">
                <p><strong><?= $donation['name'] ?></strong></p>
                <p><?= $donation['food_details'] ?></p>
                <p>Quantity: <?= $donation['quantity'] ?></p>
                <p><small><?= $donation['timestamp'] ?></small></p>
              </div>
            </div>
            
            <button class="cart-btn <?= $inCart ? 'added' : '' ?>" 
                    data-id="<?= $donation['id'] ?>" 
                    <?= $inCart ? 'disabled' : '' ?>>
              <?= $inCart ? 'Added to Cart' : 'Add to Cart' ?>
            </button>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <script>
    // Home button functionality
    document.getElementById('homeButton').addEventListener('click', function() {
      window.location.href = '../index.html';
    });

    // Toggle Mode functionality
    document.getElementById('toggleButton').addEventListener('click', function() {
      document.body.classList.toggle('dark-mode');
      // Save preference to localStorage
      localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });

    // Check for saved preference on page load
    document.addEventListener('DOMContentLoaded', function() {
      if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
      }
    });

    function getLocation() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          document.getElementById("location").value = `Lat: ${lat}, Lng: ${lng}`;
        }, function(error) {
          alert("Location access denied or unavailable.");
        });
      } else {
        alert("Geolocation is not supported by your browser.");
      }
    }
    
    // Handle add to cart with AJAX
    document.addEventListener('DOMContentLoaded', function() {
      const cartSection = document.getElementById('cartSection');
      const cartCount = document.getElementById('cartCount');
      
      document.querySelectorAll('.cart-btn:not(.added)').forEach(button => {
        button.addEventListener('click', async function() {
          const donationId = this.dataset.id;
          const donationCard = this.closest('.donation-card');
          
          // Show loading state
          const originalText = this.innerHTML;
          this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
          
          try {
            const response = await fetch('inneed_dashboard.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `add_to_cart=1&donation_id=${donationId}`
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
              // Update button state
              this.innerHTML = 'Added to Cart';
              this.classList.add('added');
              this.disabled = true;
              donationCard.classList.add('in-cart');
              
              // Update cart count
              cartCount.textContent = data.cart_count;
              
              // Update cart section
              cartSection.innerHTML = `
                <h3>🛒 Your Cart (<span id="cartCount">${data.cart_count}</span>)</h3>
                ${data.cart_html}
              `;
              
              // Reattach event listeners to new cart elements
              attachCartEventListeners();
              
              // Scroll to cart if it was empty
              if (parseInt(cartCount.textContent) === 1) {
                cartSection.scrollIntoView({ behavior: 'smooth' });
              }
            } else {
              alert(data.message || 'Failed to add to cart');
              this.innerHTML = originalText;
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Failed to add to cart. Please try again.');
            this.innerHTML = originalText;
          }
        });
      });
      
      // Attach event listeners to cart elements
      function attachCartEventListeners() {
        // Handle form submission
        document.querySelector('.request-form')?.addEventListener('submit', async function(e) {
          e.preventDefault();
          const form = e.target;
          const submitBtn = form.querySelector('button[type="submit"]');
          
          // Show loading state
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
          
          try {
            const response = await fetch(form.action, {
              method: 'POST',
              body: new FormData(form)
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {
              window.location.href = 'inneed_dashboard.php?success=1';
            } else {
              alert('Error: ' + data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
          } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '🚚 Place Request';
          }
        });
      }
      
      // Initial attachment of event listeners
      attachCartEventListeners();
      
      // Highlight cart if coming from success
      if (new URLSearchParams(window.location.search).has('success')) {
        cartSection.style.animation = 'pulse 2s 3';
      }
    });
  </script>
</body>
</html>