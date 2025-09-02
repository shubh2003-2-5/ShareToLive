<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access Denied!");
}

// Fetch analytics data
$total_donations = $conn->query("SELECT SUM(quantity) AS total_quantity FROM donations")->fetch_assoc()['total_quantity'];
$total_requests = $conn->query("SELECT SUM(quantity) AS total_quantity FROM requests")->fetch_assoc()['total_quantity'];
$meals_saved = $total_donations;  // Assuming saved meals = donated meals
$locations_served = $conn->query("SELECT COUNT(DISTINCT location) AS total_locations FROM donations")->fetch_assoc()['total_locations'];

$donations = $conn->query("SELECT * FROM donations ORDER BY timestamp DESC");
$requests = $conn->query("SELECT * FROM requests ORDER BY created_at DESC");
$points = $conn->query("SELECT * FROM delivery_points")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - ShareToLive</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(to right, #e0f7fa, #fff);
      color: #333;
      animation: fadeIn 1s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
    }
    h1, h2 {
      color: #2c3e50;
      margin-bottom: 20px;
      transition: transform 0.3s ease;
    }
    .heading{
        color: white;
    }
    h1:hover, h2:hover {
      transform: scale(1.05);
    }
    .stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .stat-card {
      background: linear-gradient(to bottom, #ffffff, #f1f1f1);
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      text-align: center;
      transition: box-shadow 0.3s ease;
    }
    .stat-card:hover {
      box-shadow: 0 6px 14px rgba(0,0,0,0.2);
    }
    .stat-card h3 {
      font-size: 1.1rem;
      margin-bottom: 10px;
      color: #555;
    }
    .stat-card p {
      font-size: 1.4rem;
      font-weight: bold;
      color: #2c3e50;
    }
    canvas {
      margin: 30px auto;
      display: block;
      max-width: 600px;
    }
    .card-container {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: space-between;
}

.card {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  width: calc(33.33% - 13.33px); /* 3 cards per row with spacing */
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
    .card p {
      margin: 5px 0;
    }
    .card img {
  margin-top: 10px;
  border-radius: 10px;
  max-width: 100%;
  height: auto;
  object-fit: cover;
}
    section {
      margin-bottom: 50px;
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

    .header-right {
      display: flex;
      gap: 10px; /* Space between buttons */
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
      min-width: 80px; /* Minimum width for both buttons */
      text-align: center;
    }

    .header-btn:hover {
      background: white;
      color: #2c3e50;
      transform: translateY(-2px);
    }
   /* Responsive tweak for smaller screens */
@media (max-width: 1024px) {
  .card {
    width: calc(50% - 10px); /* 2 per row on medium screens */
  }
}

@media (max-width: 600px) {
  .card {
    width: 100%; /* 1 per row on small screens */
  }
}
/* Dark Mode Styles */
    body.dark-mode {
      background: linear-gradient(to right, #1a2a3a, #0d1b2a);
      color: #f8f9fa;
    }

    body.dark-mode .container {
      background: #2c3e50;
    }

    body.dark-mode .stat-card {
      background: linear-gradient(to bottom, #34495e, #2c3e50);
      color: #f8f9fa;
    }

    body.dark-mode .stat-card h3 {
      color: #bdc3c7;
    }

    body.dark-mode .stat-card p {
      color: #f8f9fa;
    }

    body.dark-mode .card {
      background-color: #34495e;
      color: #f8f9fa;
    }

    body.dark-mode h1,
    body.dark-mode h2 {
      color: #f8f9fa;
    }

    body.dark-mode .heading {
      color: #f8f9fa;
    }

  </style>
</head>
<body>
<header>
    <h1 class="heading">👨‍💼 ShareToLive</h1>
    <div class="header-right">
      <button class="header-btn" id="homeButton">HOME</button>
      <button class="header-btn" id="toggleButton">Toggle Mode</button>
    </div>
  </header>
  <div class="container">
    <h1>👨‍💼 Admin Dashboard</h1>

    <!-- 📊 Dashboard Analytics -->
    <section>
      <h2>📊 Key Stats</h2>
      <div class="stats">
        <div class="stat-card">
          <h3>Total Donations</h3>
          <p><?= $total_donations ?? 0 ?> meals</p>
        </div>
        <div class="stat-card">
          <h3>Total Requests</h3>
          <p><?= $total_requests ?? 0 ?> meals</p>
        </div>
        <div class="stat-card">
          <h3>Meals Saved</h3>
          <p><?= $meals_saved ?? 0 ?> meals</p>
        </div>
        <div class="stat-card">
          <h3>Locations Served</h3>
          <p><?= $locations_served ?? 0 ?></p>
        </div>
      </div>
      <canvas id="donationsRequestsChart"></canvas>
    </section>

    <!-- 📦 Food Donations -->
    <section>
      <h2>📦 Recent Food Donations</h2>
      <div class="card-container">
      <?php while($row = $donations->fetch_assoc()): ?>
        <div class="card">
          <p><strong>Donater:</strong> <?= $row['name'] ?></p>
          <p><strong>GSTIN:</strong> <?= $row['gstin'] ?></p>
          <p><strong>License:</strong> <?= $row['license'] ?></p>
          <p><strong>Food:</strong> <?= $row['food_details'] ?></p>
          <p><strong>Quantity:</strong> <?= $row['quantity'] ?></p>
          <p><strong>Location:</strong> <?= $row['location'] ?></p>
          <p><strong>Time:</strong> <?= $row['timestamp'] ?></p>
          <?php if (!empty($row['image_path'])): ?>
            <img src="../uploads/<?= $row['image_path'] ?>" alt="Food Image">
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
      </div>
    </section>

    <!-- 🛒 Food Requests -->
    <section>
      <h2>🛒 Latest Food Requests</h2>
      <div class="card-container">
      <?php while($req = $requests->fetch_assoc()): ?>
        <div class="card">
          <p><strong>Food Needed:</strong> <?= $req['food_needed'] ?? 'N/A' ?></p>
          <p><strong>Quantity:</strong> <?= $req['quantity'] ?? 'N/A' ?></p>
          <p><strong>Note:</strong> <?= $req['note'] ?></p>
          <p><strong>Location:</strong> <?= $req['location'] ?></p>
          <p><strong>Status:</strong> <strong style="color: <?= $req['status'] === 'pending' ? '#e67e22' : '#27ae60' ?>"><?= ucfirst($req['status']) ?></strong></p>
          <p><strong>Requested At:</strong> <?= $req['created_at'] ?></p>
        </div>
      <?php endwhile; ?>
      </div>
    </section>
  </div>

  <h2>    📍 Food Delivery Points</h2>
<div id="map" style="height: 500px; width: 100%; margin-bottom: 50px;"></div>

  <!-- 📈 Chart.js Setup -->
  <script>
  // Home button functionality
    document.getElementById('homeButton').addEventListener('click', function() {
      window.location.href = '../index.html';
    });

    // Enhanced Toggle Mode functionality
    document.getElementById('toggleButton').addEventListener('click', function() {
      document.body.classList.toggle('dark-mode');
      
      // Save preference to localStorage
      const isDarkMode = document.body.classList.contains('dark-mode');
      localStorage.setItem('darkMode', isDarkMode);
      
      // Update chart colors
      const chart = Chart.getChart('donationsRequestsChart');
      if (chart) {
        chart.update();
      }
    });

    // Check for saved preference on page load
    document.addEventListener('DOMContentLoaded', function() {
      if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
      }
    });
    const ctx = document.getElementById('donationsRequestsChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Donations', 'Requests'],
        datasets: [{
          label: 'Meals',
          data: [<?= $total_donations ?? 0 ?>, <?= $total_requests ?? 0 ?>],
          backgroundColor: ['#3498db80', '#e74c3c80'],
          borderColor: ['#3498db', '#e74c3c'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision:0
            }
          }
        }
      }
    });

    function initMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
      zoom: 12,
      center: { lat: 28.6139, lng: 77.2090 }, // Centered on Delhi, change as needed
    });

    const deliveryPoints = <?= json_encode($points) ?>;

    deliveryPoints.forEach(point => {
      const marker = new google.maps.Marker({
        position: { lat: parseFloat(point.latitude), lng: parseFloat(point.longitude) },
        map: map,
        title: point.name
      });

      const infoWindow = new google.maps.InfoWindow({
        content: `<strong>${point.name}</strong><br>${point.description || ''}`
      });

      marker.addListener('click', () => {
        infoWindow.open(map, marker);
      });
    });
  }
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCZFXMPff8H7tpxkP_NKtGgq8gW48469jA&callback=initMap" async defer></script>
</body>
</html>
