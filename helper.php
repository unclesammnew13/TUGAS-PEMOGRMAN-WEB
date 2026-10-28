<?php
if (session_status() === PHP_SESSION_NONE) session_start();

const UPLOAD_DIR = __DIR__ . '/uploads/';
const UPLOAD_URL = 'uploads/';
const DEFAULT_FOTO = 'adminlte/assets/img/avatar.png';

const DAFTAR_PREDIKAT = ['Sangat Memuaskan', 'Memuaskan', 'Cukup Memuaskan', 'Kurang', 'Sangat Kurang'];

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function hitung_predikat(float $n): array {
    if ($n >= 80.51) return ['A', 'Sangat Memuaskan'];
    if ($n >= 65.51) return ['B', 'Memuaskan'];
    if ($n >= 50.51) return ['C', 'Cukup Memuaskan'];
    if ($n >= 34.51) return ['D', 'Kurang'];
    return ['E', 'Sangat Kurang'];
}

function foto_url(?string $foto): string {
    return ($foto && is_file(UPLOAD_DIR . $foto)) ? UPLOAD_URL . rawurlencode($foto) : DEFAULT_FOTO;
}

function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }

function redirect(string $url): void { header('Location: ' . $url); exit; }

function simpan_foto(string $field = 'foto'): array {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Upload foto gagal (kode ' . $f['error'] . ').'];
    if ($f['size'] > 2 * 1024 * 1024) return [null, 'Ukuran foto maksimal 2 MB.'];
    $info = @getimagesize($f['tmp_name']);
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
    if (!$ext) return [null, 'Format foto harus JPG, PNG, GIF, atau WEBP.'];
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $nama = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $nama)) return [null, 'Gagal menyimpan file foto.'];
    return [$nama, null];
}

function hapus_foto(?string $foto): void {
    if ($foto && is_file(UPLOAD_DIR . $foto)) @unlink(UPLOAD_DIR . $foto);
}

//menampilkan pesan sukses/gagal
function tampil_flash(): void {
    if (empty($_SESSION['flash'])) return;
    [$type, $msg] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">'
       . e($msg)
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
