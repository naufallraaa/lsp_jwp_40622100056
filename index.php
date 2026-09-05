<?php // To-Do List — PHP + Tailwind + localStorage, 1 file ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>To-Do List</title>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-stone-100 text-stone-800 min-h-screen">
<div class="max-w-2xl mx-auto py-8 px-4">
  <!-- Judul atas -->
  <header class="bg-stone-800 text-white rounded-t-lg px-6 py-5">
    <h1 class="text-xl font-bold">To-Do List</h1>
    <p class="text-sm text-stone-400">catatan tugas harian</p>
  </header>
  <div class="bg-white border border-stone-200 border-t-0 rounded-b-lg p-5">
    <!-- Notif singkat (sukses / gagal) -->
    <div id="alert" class="hidden mb-4 px-4 py-2 rounded text-sm"></div>
    <!-- Form tambah tugas -->
    <form id="todoForm" class="flex gap-2 mb-4">
      <input id="titleInput" type="text" placeholder="Tugas baru..." class="flex-1 border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-stone-500">
      <button class="bg-stone-800 hover:bg-stone-700 text-white text-sm px-4 py-2 rounded">Tambah</button>
    </form>
    <!-- Filter status + pencarian -->
    <div class="flex gap-1 mb-4 text-sm" id="filterGroup">
      <button data-filter="semua" class="px-3 py-1 rounded-full">Semua</button>
      <button data-filter="belum" class="px-3 py-1 rounded-full">Belum</button>
      <button data-filter="selesai" class="px-3 py-1 rounded-full">Selesai</button>
      <input id="searchInput" type="text" placeholder="cari..." class="ml-auto border border-stone-300 rounded px-3 py-1 text-sm w-32 focus:outline-none focus:border-stone-500">
    </div>
    <!-- Daftar tugas diisi lewat JS -->
    <div id="todoList" class="flex flex-col gap-2"></div>
    <!-- Tampil kalau list kosong -->
    <p id="emptyMsg" class="hidden text-center text-stone-400 text-sm py-8">belum ada tugas.</p>
    <footer class="text-center text-xs text-stone-400 mt-6">tersimpan otomatis di browser</footer>
  </div>
</div>
<script>
// Kunci penyimpanan di localStorage
const KEY = "junior_todos";
// Shortcut ambil elemen by id
const $ = (id) => document.getElementById(id);
// Ambil semua tugas, default array kosong
let todos = JSON.parse(localStorage.getItem(KEY) || "[]"), filter = "semua";
// Simpan array todos ke localStorage
const save = () => localStorage.setItem(KEY, JSON.stringify(todos));
// Dummy awal: array 2 tugas, cuma dimasukkan sekali pas storage masih kosong
if (!todos.length && !localStorage.getItem("junior_seeded")) {
  todos = [
  { id: 1, judul: "Belajar HTML/CSS", status: "belum" }, 
  { id: 2, judul: "Kerjakan tugas UX", status: "belum" }];
  localStorage.setItem("junior_seeded", "1"); save();
}
// Amankan teks biar tag HTML tidak dieksekusi
const esc = (s) => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
// Notif kecil di atas form, hilang sendiri 2 detik
function flash(msg, ok = true) {
  const a = $("alert");
  a.textContent = msg;
  a.className = "mb-4 px-4 py-2 rounded text-sm " + (ok ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");
  clearTimeout(flash.t); flash.t = setTimeout(() => a.classList.add("hidden"), 2000);
}
// Gambar ulang list sesuai filter + search, nomor pakai urutan (i+1)
function render() {
  // Tandai tombol filter yang aktif
  document.querySelectorAll("#filterGroup button").forEach(b =>
    b.className = "px-3 py-1 rounded-full " + (b.dataset.filter === filter ? "bg-stone-800 text-white" : "bg-stone-200 text-stone-600"));
  const q = $("searchInput").value.trim().toLowerCase();
  const rows = todos.filter(t => (filter === "semua" || t.status === filter) && t.judul.toLowerCase().includes(q));
  $("emptyMsg").classList.toggle("hidden", rows.length > 0);
  $("todoList").innerHTML = rows.map((t, i) => `
    <div class="border border-stone-200 rounded px-3 py-2.5 ${t.status === "selesai" ? "bg-stone-50" : "bg-white"}">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" data-act="toggle" data-id="${t.id}" ${t.status === "selesai" ? "checked" : ""} class="w-4 h-4 accent-stone-800">
        <span class="text-sm font-bold ${t.status === "selesai" ? "line-through text-stone-400" : ""}">${esc(t.judul)}</span>
      </label>
      <div class="flex justify-between items-center mt-1.5 ml-6">
        <span class="text-[11px] text-stone-400">#${i + 1} · ${t.status}</span>
        <span class="flex gap-1.5">
          <button data-act="edit" data-id="${t.id}" class="bg-amber-500 text-white text-xs px-2 py-1 rounded">Edit</button>
          <button data-act="del" data-id="${t.id}" class="bg-red-500 text-white text-xs px-2 py-1 rounded">Hapus</button>
        </span>
      </div>
    </div>`).join("");
}
// Tambah tugas baru ke paling atas
$("todoForm").onsubmit = (e) => {
  e.preventDefault();
  const judul = $("titleInput").value.trim();
  if (!judul) return flash("Judul gak boleh kosong", false);
  todos.unshift({ id: Date.now(), judul, status: "belum" });
  save(); $("titleInput").value = ""; render(); flash("Tugas ditambahkan");
};
// Satu handler untuk centang selesai, hapus, dan edit
$("todoList").onclick = (e) => {
  const el = e.target.closest("[data-act]"); if (!el) return;
  const t = todos.find(x => x.id == el.dataset.id); if (!t) return;
  if (el.dataset.act === "toggle") t.status = t.status === "selesai" ? "belum" : "selesai";
  if (el.dataset.act === "del") { if (!confirm(`Hapus "${t.judul}"?`)) return; todos = todos.filter(x => x.id != t.id); flash("Tugas dihapus"); }
  if (el.dataset.act === "edit") { const j = prompt("Edit tugas:", t.judul)?.trim(); if (j === undefined) return; if (!j) return flash("Judul gak boleh kosong", false); t.judul = j; flash("Tugas diperbarui"); }
  save(); render();
};
// Ganti filter + ketik pencarian langsung render ulang
document.querySelectorAll("#filterGroup button").forEach(b => b.onclick = () => { filter = b.dataset.filter; render(); });
$("searchInput").oninput = render;
// Tampilan pertama kali
render();
</script>
</body>
</html>
