<?php require_once __DIR__ . '/helper.php'; ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title><?= e($judul ?? 'Nilai Mahasiswa') ?> | AdminLTE 4</title>
  <script>
    (() => {
      let s = null; try { s = localStorage.getItem('lte-theme'); } catch {}
      const t = (s === 'dark' || s === 'light') ? s : (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', t);
    })();
  </script>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="adminlte/css/adminlte.min.css">
  <style>
    .app-wrapper { grid-template-areas: "lte-app-header" "lte-app-main" "lte-app-footer"; grid-template-columns: 1fr; }
    .foto-mhs { width: 34px; height: 34px; object-fit: cover; border-radius: 50%; }
    th a.sort { color: inherit; text-decoration: none; white-space: nowrap; }
    th a.sort .bi { opacity: .35; } th a.sort.aktif .bi { opacity: 1; color: var(--bs-primary); }
  </style>
</head>
<body class="layout-fixed bg-body-tertiary">
<div class="app-wrapper">
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <a href="bacadata.php" class="navbar-brand d-flex align-items-center">
        <img src="adminlte/assets/img/AdminLTELogo.png" alt="Logo" class="brand-image opacity-75 shadow me-2" style="height:28px;">
        <span class="fw-light">Nilai Mahasiswa</span>
      </a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><span class="nav-link"><i class="bi bi-mortarboard me-1"></i> Sistem Nilai Mahasiswa</span></li>
      </ul>
    </div>
  </nav>
  <main class="app-main">
    <div class="app-content-header"><div class="container-fluid">
      <div class="row"><div class="col-sm-6"><h3 class="mb-0"><?= e($judul ?? '') ?></h3></div></div>
    </div></div>
    <div class="app-content"><div class="container-fluid">
