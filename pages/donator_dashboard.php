<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'donator') {
    die("Access Denied!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Donater Dashboard - ShareToLive</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f0f0f0;
      --text: #111;
      --primary: #27ae60;
      --accent: #2ecc71;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: all 0.3s ease;
    }

    body.dark-mode {
      --bg: #1f1f1f;
      --text: #f9f9f9;
    }

    header {
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #2c3e50;
      color: white;
    }

    .toggle-mode {
      background: none;
      color: white;
      border: 1px solid white;
      padding: 0.5rem 1rem;
      cursor: pointer;
      border-radius: 5px;
    }

    .container {
      max-width: 800px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    body.dark-mode .container {
      background: #2c3e50;
    }

    h1 {
      color: var(--primary);
      text-align: center;
      margin-bottom: 1.5rem;
    }

    label {
      font-weight: bold;
      margin-top: 1rem;
      display: block;
    }

    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 10px;
      background: #fff;
      color: #000;
    }

    body.dark-mode input,
    body.dark-mode textarea,
    body.dark-mode select {
      background: #34495e;
      color: #f1f1f1;
      border: 1px solid #888;
    }

    .btn {
      margin-top: 1.5rem;
      background: var(--primary);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .btn:hover {
      background: var(--accent);
    }

    .terms-box {
      background: #ecf0f1;
      padding: 1rem;
      border-radius: 12px;
      font-size: 14px;
      margin-bottom: 1rem;
    }

    body.dark-mode .terms-box {
      background: #3b3b3b;
    }

    .hidden {
      display: none;
    }

    footer {
      background: #34495e;
      color: white;
      text-align: center;
      padding: 1rem;
      margin-top: 3rem;
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
    :root {
      --bg: #f0f0f0;
      --text: #111;
      --primary: #27ae60;
      --accent: #2ecc71;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      color: var(--text);
      transition: all 0.3s ease;
    }

    body.dark-mode {
      --bg: #1f1f1f;
      --text: #f9f9f9;
    }

    header {
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #2c3e50;
      color: white;
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
    }

  </style>
</head>
<body>

  <header>
    <h2>🍽️ ShareToLive</h2>
    <div class="header-right">
      <button class="header-btn" id="homeButton">HOME</button>
      <button class="header-btn" id="toggleButton" onclick="toggleMode()">Toggle Mode</button>
    </div>
  </header>

  <div class="container">
    <h1>Donater Dashboard</h1>

    <form id="donationForm" action="../includes/save_donation.php" method="POST" enctype="multipart/form-data">
      <div class="terms-box">
        <h3>Terms & Conditions</h3>
        <ul>
          <li>Food must be safe, non-expired, and handled hygienically.</li>
          <li>No harmful substances or prohibited items are allowed.</li>
          <li>Violations will result in legal action and permanent removal from the platform.</li>
        </ul>
        <label><input type="checkbox" id="agree" required> I agree to the terms and conditions.</label>
      </div>

      <div id="formFields" class="hidden">
        <label for="name">Name of Restaurant / Kitchen</label>
        <input type="text" name="name" required>

        <label for="gstin">GSTIN Number</label>
        <input type="text" name="gstin" required>

        <label for="license">Food License Certificate Number</label>
        <input type="text" name="license" required>

        <label for="food_details">Food Details</label>
        <textarea name="food_details" rows="3" required></textarea>

        <label for="quantity">Quantity (Kg/Ltr/Items)</label>
        <input type="text" name="quantity" required>

        <label for="location">Pickup Location</label>
        <input type="text" name="location" id="location" required readonly>
        <button type="button" class="btn" onclick="getLocation()">📍 Use Current Location</button>

        <label for="food_image">Upload Picture of the Food</label>
        <input type="file" name="food_image" accept="image/*" required>

        <button type="submit" class="btn">Donate Food</button>
      </div>
    </form>
  </div>

  <footer>
    &copy; 2025 ShareToLive. All Rights Reserved.
  </footer>

  <script>
  
document.getElementById('homeButton').addEventListener('click', function() {
      window.location.href = '../index.html'; // Adjust path as needed
    });
    document.getElementById("agree").addEventListener("change", function () {
      document.getElementById("formFields").classList.toggle("hidden", !this.checked);
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

    function toggleMode() {
      document.body.classList.toggle("dark-mode");
    }
  </script>
</body>
</html>
