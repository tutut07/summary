<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title><?= isset($pageTitle) ? $pageTitle : "Dashboard" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
    }
    #sidebar {
      width: 250px;
      height: 100vh;
      background: linear-gradient(to bottom, #ff9900, #ff6600);
      color: #fff;
      position: fixed;
      transition: width 0.3s;
      overflow-y: auto;
      z-index: 1000;
    }
    #sidebar.collapsed {
      width: 60px;
    }
    #sidebar .sidebar-link {
      display: block;
      padding: 12px 20px;
      color: #fff;
      text-decoration: none;
      white-space: nowrap;
    }
    #sidebar .sidebar-link:hover {
      background-color: rgba(255, 255, 255, 0.1);
    }
    #sidebar .sidebar-header {
      padding: 15px 20px;
      font-weight: bold;
      font-size: 1.2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }

    #content {
      margin-left: 250px;
      transition: margin-left 0.3s;
      padding: 20px;
    }
    #content.collapsed {
      margin-left: 60px;
    }

    nav.navbar {
      transition: margin-left 0.3s;
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
  <div class="sidebar-header">Koperasi</div>
  <a href="dashboard.php" class="sidebar-link">Dashboard</a>
  <a href="tampil_laporan.php" class="sidebar-link">Laporan</a>
  <a href="form_input.php" class="sidebar-link">Input Data</a>
  <a href="pengaturan.php" class="sidebar-link">Pengaturan</a>

  
</div>

<nav class="navbar navbar-expand-lg navbar-light bg-success text-white" style="margin-left: 250px;">
  <div class="container-fluid">
    <button class="btn btn-outline-light" id="btn-toggle-sidebar">&#9776;</button>
    <span class="navbar-brand ms-3 text-white"><?= isset($pageTitle) ? $pageTitle : "Dashboard" ?></span>

    <div class="d-flex ms-auto align-items-center text-white">
      <?php if (isset($_SESSION['username'])): ?>
        <span class="me-3">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn btn-sm btn-light text-dark">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-sm btn-light text-dark">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>


<!-- Konten Utama -->
<div id="content">
