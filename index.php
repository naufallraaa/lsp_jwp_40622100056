<?php // To-Do List — PHP + Tailwind + localStorage, 1 file ?>
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
    <!-- Notif singkat (sukses / gagal) -->
    <div id="alert" class="hidden mb-4 px-4 py-2.5 rounded-xl text-sm border"></div>
    <!-- Form tambah tugas -->
    <form id="todoForm" class="flex gap-2 mb-5">
      <input id="titleInput" type="text" placeholder="Tugas baru..." class="flex-1 border border-neutral-200 bg-neutral-50 rounded-xl px-4 py-2.5 text-sm placeholder:text-neutral-400 focus:outline-none focus:bg-white focus:border-neutral-400 transition">
      <button class="bg-neutral-900 hover:bg-neutral-700 active:scale-95 transition text-white text-sm font-medium px-5 py-2.5 rounded-xl">Tambah</button>
    </form>
    <!-- Filter status + pencarian -->
    <div class="flex items-center gap-2 mb-4">
      <div class="flex gap-1 text-xs bg-neutral-100 p-1 rounded-full" id="filterGroup">
        <button data-filter="semua" class="px-3 py-1.5 rounded-full">Semua</button>
        <button data-filter="belum" class="px-3 py-1.5 rounded-full">Belum</button>
        <button data-filter="selesai" class="px-3 py-1.5 rounded-full">Selesai</button>
      </div>
      <input id="searchInput" type="text" placeholder="Cari..." class="ml-auto border border-neutral-200 rounded-full px-3.5 py-1.5 text-xs w-28 placeholder:text-neutral-400 focus:outline-none focus:border-neutral-400 transition">
    </div>
    <!-- Daftar tugas diisi lewat JS -->
    <div id="todoList" class="flex flex-col gap-2"></div>
    <!-- Modal konfirmasi / edit (pengganti confirm & prompt bawaan browser) -->
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
      <div class="bg-white rounded-2xl shadow-lg w-full max-w-sm p-5">
        <h2 id="modalTitle" class="text-sm font-semibold"></h2>
        <p id="modalMsg" class="text-sm text-neutral-500 mt-1"></p>
        <input id="modalInput" type="text" class="hidden mt-3 w-full border border-neutral-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-neutral-400">
        <div class="flex justify-end gap-2 mt-4">
          <button id="modalCancel" class="text-sm px-4 py-2 rounded-xl text-neutral-500 hover:bg-neutral-100 transition">Batal</button>
          <button id="modalOk" class="text-sm font-medium px-4 py-2 rounded-xl bg-neutral-900 text-white hover:bg-neutral-700 transition">OK</button>
        </div>
      </div>
    </div>
    <!-- Tampil kalau list kosong -->
    <p id="emptyMsg" class="hidden text-center text-neutral-400 text-sm py-8">Belum ada tugas.</p>
    <footer class="text-center text-xs text-neutral-400 mt-6 pt-4 border-t border-neutral-100">Tersimpan otomatis di browser</footer>
  </div>
</div>
<script>
// State + penyimpanan lokal
const KEY = "junior_todos";
const $ = (id) => document.getElementById(id);
let todos = JSON.parse(localStorage.getItem(KEY) || "[]"), filter = "semua";
const save = () => localStorage.setItem(KEY, JSON.stringify(todos));
// Data awal, cuma sekali pas storage masih kosong
if (!todos.length && !localStorage.getItem("junior_seeded")) {
  todos = [
  { id: 1, judul: "Belajar HTML", status: "belum" }, 
  { id: 2, judul: "Kerjakan tugas UX", status: "belum" }];
  localStorage.setItem("junior_seeded", "1"); save();
}
// Cegah XSS pada judul tugas
const esc = (s) => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
// Notif singkat, hilang sendiri 2 detik
function flash(msg, ok = true) {
  const a = $("alert");
  a.textContent = msg;
  a.className = "mb-4 px-4 py-2.5 rounded-xl text-sm border " + (ok ? "bg-emerald-50 text-emerald-700 border-emerald-200" : "bg-red-50 text-red-700 border-red-200");
  clearTimeout(flash.t); flash.t = setTimeout(() => a.classList.add("hidden"), 2000);
}
// Render list sesuai filter + search
function render() {
  document.querySelectorAll("#filterGroup button").forEach(b =>
    b.className = "px-3 py-1.5 rounded-full transition " + (b.dataset.filter === filter ? "bg-white shadow-sm text-neutral-900 font-medium" : "text-neutral-500 hover:text-neutral-800"));
  const q = $("searchInput").value.trim().toLowerCase();
  const rows = todos.filter(t => (filter === "semua" || t.status === filter) && t.judul.toLowerCase().includes(q));
  $("emptyMsg").classList.toggle("hidden", rows.length > 0);
  $("todoList").innerHTML = rows.map((t, i) => `
    <div class="border border-neutral-200 rounded-xl px-4 py-3 transition hover:border-neutral-300 hover:shadow-sm ${t.status === "selesai" ? "bg-neutral-50" : "bg-white"}">
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" data-act="toggle" data-id="${t.id}" ${t.status === "selesai" ? "checked" : ""} class="w-4 h-4 rounded accent-neutral-900 shrink-0">
        <span class="text-sm ${t.status === "selesai" ? "line-through text-neutral-400" : "font-medium text-neutral-800"}">${esc(t.judul)}</span>
      </label>
      <div class="flex justify-between items-center mt-2 ml-7">
        <span class="flex items-center gap-1.5 text-[11px] text-neutral-400"><span class="w-1.5 h-1.5 rounded-full ${t.status === "selesai" ? "bg-emerald-500" : "bg-amber-400"}"></span>#${i + 1} &middot; ${t.status}</span>
        <span class="flex gap-1">
          <button data-act="edit" data-id="${t.id}" class="text-xs px-2.5 py-1 rounded-lg text-neutral-500 hover:bg-neutral-100 hover:text-neutral-900 transition">Edit</button>
          <button data-act="del" data-id="${t.id}" class="text-xs px-2.5 py-1 rounded-lg text-neutral-400 hover:bg-red-50 hover:text-red-600 transition">Hapus</button>
        </span>
      </div>
    </div>`).join("");
}
// Tambah tugas baru
$("todoForm").onsubmit = (e) => {
  e.preventDefault();
  const judul = $("titleInput").value.trim();
  if (!judul) return flash("Judul gak boleh kosong", false);
  todos.push({ id: Date.now(), judul, status: "belum" });
  save(); $("titleInput").value = ""; render(); flash("Tugas ditambahkan");
};
// Modal custom untuk hapus / edit
let modalCb = null;
function openModal(title, msg, showInput, def, okLabel, cb) {
  $("modalTitle").textContent = title;
  $("modalMsg").textContent = msg || "";
  const inp = $("modalInput");
  inp.classList.toggle("hidden", !showInput);
  if (showInput) { inp.value = def || ""; setTimeout(() => inp.focus(), 50); }
  $("modalOk").textContent = okLabel || "OK";
  $("modal").classList.remove("hidden");
  modalCb = cb;
}
function closeModal() { $("modal").classList.add("hidden"); modalCb = null; }
$("modalCancel").onclick = closeModal;
$("modal").onclick = (e) => { if (e.target.id === "modal") closeModal(); };
$("modalOk").onclick = () => {
  const cb = modalCb, val = $("modalInput").value;
  closeModal(); if (cb) cb(val);
};
$("modalInput").onkeydown = (e) => { if (e.key === "Enter") $("modalOk").click(); if (e.key === "Escape") closeModal(); };
// Aksi toggle selesai, hapus, dan edit
$("todoList").onclick = (e) => {
  const el = e.target.closest("[data-act]"); if (!el) return;
  const t = todos.find(x => x.id == el.dataset.id); if (!t) return;
  if (el.dataset.act === "toggle") { t.status = t.status === "selesai" ? "belum" : "selesai"; save(); render(); }
  if (el.dataset.act === "del") openModal("Hapus tugas?", `"${t.judul}" akan dihapus permanen.`, false, "", "Hapus", () => {
    todos = todos.filter(x => x.id != t.id); save(); render(); flash("Tugas dihapus");
  });
  if (el.dataset.act === "edit") openModal("Edit tugas", "", true, t.judul, "Simpan", (val) => {
    const j = (val || "").trim(); if (!j) return flash("Judul gak boleh kosong", false);
    t.judul = j; save(); render(); flash("Tugas diperbarui");
  });
};
// Filter + search + render awal
document.querySelectorAll("#filterGroup button").forEach(b => b.onclick = () => { filter = b.dataset.filter; render(); });
$("searchInput").oninput = render;
render();
</script>
</body>
</html>
