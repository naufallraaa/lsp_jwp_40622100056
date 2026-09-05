<?php // To-Do List — full PHP + MySQL, tanpa JS
require __DIR__ . "/config/db.php";

// Ambil input form (POST) — act: add/toggle/edit/del
$act = $_POST["act"] ?? "";
$id = (int)($_POST["id"] ?? 0);
$judul = trim($_POST["judul"] ?? "");
// Simpan tugas baru
if ($act === "add" && $judul !== "") $pdo->prepare("INSERT INTO todos (judul) VALUES (?)")->execute([$judul]);
// Balik status belum <-> selesai
elseif ($act === "toggle") $pdo->prepare("UPDATE todos SET status=IF(status='selesai','belum','selesai') WHERE id=?")->execute([$id]);
// Ganti judul tugas
elseif ($act === "edit" && $judul !== "") $pdo->prepare("UPDATE todos SET judul=? WHERE id=?")->execute([$judul, $id]);
// Hapus tugas permanen
elseif ($act === "del") $pdo->prepare("DELETE FROM todos WHERE id=?")->execute([$id]);
// Habis POST langsung redirect biar gak ke-submit ulang pas refresh
if ($act) { header("Location: index.php"); exit; }

// Filter status + kata kunci pencarian (GET)
$filter = $_GET["filter"] ?? "semua";
$q = trim($_GET["q"] ?? "");
$w = []; $args = [];
// Saring status kalau bukan "semua"
if ($filter !== "semua") { $w[] = "status=?"; $args[] = $filter; }
// Cari judul yang mengandung kata kunci
if ($q !== "") { $w[] = "judul LIKE ?"; $args[] = "%$q%"; }
// Ambil tugas sesuai filter, urut dari yang paling lama
$sql = "SELECT id, judul, status FROM todos" . ($w ? " WHERE " . implode(" AND ", $w) : "") . " ORDER BY id ASC";
$st = $pdo->prepare($sql); $st->execute($args);
$todos = $st->fetchAll();
// ID baris yang lagi diedit (null kalau gak ada)
$edit = (int)($_GET["edit"] ?? 0);
// Escape output biar judul ber-tag HTML gak jebol tampilan (XSS)
$e = fn($s) => htmlspecialchars($s ?? "", ENT_QUOTES);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>To-Do List</title>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-neutral-100 text-neutral-900 min-h-screen antialiased">
<div class="max-w-xl mx-auto py-10 px-4">
  <!-- Kepala halaman -->
  <header class="mb-6">
    <p class="text-xs font-medium uppercase tracking-widest text-neutral-400">Catatan harian</p>
    <h1 class="text-2xl font-semibold tracking-tight">To-Do List</h1>
    <p class="text-sm text-neutral-500 mt-1">Fokus ke yang penting, satu tugas sekali jalan.</p>
  </header>
  <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm p-5">
    <!-- Form tambah tugas baru -->
    <form method="post" class="flex gap-2 mb-5">
      <input type="hidden" name="act" value="add">
      <input name="judul" type="text" placeholder="Tugas baru..." class="flex-1 border border-neutral-200 bg-neutral-50 rounded-xl px-4 py-2.5 text-sm placeholder:text-neutral-400 focus:outline-none focus:bg-white focus:border-neutral-400 transition">
      <button class="bg-neutral-900 hover:bg-neutral-700 active:scale-95 transition text-white text-sm font-medium px-5 py-2.5 rounded-xl">Tambah</button>
    </form>
    <!-- Filter status + pencarian -->
    <form method="get" class="flex items-center gap-2 mb-4">
      <!-- Pilihan filter: Semua / Belum / Selesai -->
      <div class="flex gap-1 text-xs bg-neutral-100 p-1 rounded-full">
        <?php foreach (["semua" => "Semua", "belum" => "Belum", "selesai" => "Selesai"] as $f => $label): ?>
          <a href="?filter=<?= $f ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-full transition <?= $filter === $f ? "bg-white shadow-sm text-neutral-900 font-medium" : "text-neutral-500 hover:text-neutral-800" ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>
      <!-- Simpan filter aktif biar gak reset pas nyari -->
      <input type="hidden" name="filter" value="<?= $e($filter) ?>">
      <!-- Kolom kata kunci pencarian -->
      <input name="q" type="text" value="<?= $e($q) ?>" placeholder="Cari..." class="ml-auto border border-neutral-200 rounded-full px-3.5 py-1.5 text-xs w-28 placeholder:text-neutral-400 focus:outline-none focus:border-neutral-400 transition">
    </form>
    <!-- Daftar tugas -->
    <div class="flex flex-col gap-2">
      <?php foreach ($todos as $i => $t): ?>
        <div class="border border-neutral-200 rounded-xl px-4 py-3 transition hover:border-neutral-300 hover:shadow-sm <?= $t["status"] === "selesai" ? "bg-neutral-50" : "bg-white" ?>">
          <?php if ($t["id"] === $edit): ?>
            <!-- Mode edit: form ganti judul + tombol batal -->
            <form method="post" class="flex gap-2">
              <input type="hidden" name="act" value="edit">
              <input type="hidden" name="id" value="<?= $t["id"] ?>">
              <input name="judul" type="text" value="<?= $e($t["judul"]) ?>" class="flex-1 border border-neutral-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-neutral-400">
              <button class="text-xs font-medium px-3 py-2 rounded-lg bg-neutral-900 text-white hover:bg-neutral-700 transition">Simpan</button>
              <a href="index.php" class="text-xs px-3 py-2 rounded-lg text-neutral-500 hover:bg-neutral-100 transition">Batal</a>
            </form>
          <?php else: ?>
            <!-- Baris normal: tombol bulet = toggle selesai/belum -->
            <form method="post" class="flex items-center gap-3">
              <input type="hidden" name="act" value="toggle">
              <input type="hidden" name="id" value="<?= $t["id"] ?>">
              <button title="Ubah status" class="w-4 h-4 rounded-full border shrink-0 <?= $t["status"] === "selesai" ? "bg-neutral-900 border-neutral-900" : "border-neutral-300 hover:border-neutral-500" ?>"></button>
              <span class="text-sm <?= $t["status"] === "selesai" ? "line-through text-neutral-400" : "font-medium text-neutral-800" ?>"><?= $e($t["judul"]) ?></span>
            </form>
            <!-- Nomor urut + status + aksi Edit / Hapus -->
            <div class="flex justify-between items-center mt-2 ml-7">
              <span class="flex items-center gap-1.5 text-[11px] text-neutral-400"><span class="w-1.5 h-1.5 rounded-full <?= $t["status"] === "selesai" ? "bg-emerald-500" : "bg-amber-400" ?>"></span>#<?= $i + 1 ?> &middot; <?= $e($t["status"]) ?></span>
              <span class="flex gap-1">
                <a href="?edit=<?= $t["id"] ?>" class="text-xs px-2.5 py-1 rounded-lg text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 transition">Edit</a>
                <!-- Hapus pakai confirm bawaan browser -->
                <form method="post" class="inline" onsubmit="return confirm('Hapus tugas ini?')">
                  <input type="hidden" name="act" value="del">
                  <input type="hidden" name="id" value="<?= $t["id"] ?>">
                  <button class="text-xs px-2.5 py-1 rounded-lg text-neutral-400 hover:bg-red-50 hover:text-red-600 transition">Hapus</button>
                </form>
              </span>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <!-- Tampil kalau filter/search gak nemu apa-apa -->
      <?php if (!$todos): ?><p class="text-center text-neutral-400 text-sm py-8">Belum ada tugas.</p><?php endif; ?>
    </div>
    <footer class="text-center text-xs text-neutral-400 mt-6 pt-4 border-t border-neutral-100">Tersimpan di database MySQL</footer>
  </div>
</div>
</body>
</html>
