<?php // To-Do List — PHP + localStorage, satu file ?>
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
  <header class="bg-stone-800 text-white rounded-t-lg px-6 py-5">
    <h1 class="text-xl font-bold">To-Do List</h1>
    <p class="text-sm text-stone-400">catatan tugas harian</p>
  </header>

  <div class="bg-white border border-stone-200 border-t-0 rounded-b-lg p-5">
    <div id="alert" class="hidden mb-4 px-4 py-2.5 rounded text-sm"></div>

    <form id="todoForm" class="flex gap-2 mb-5">
      <input id="titleInput" type="text" placeholder="Tugas baru..."
        class="flex-1 border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-stone-500">
      <button type="submit"
        class="bg-stone-800 hover:bg-stone-700 text-white text-sm font-medium px-4 py-2 rounded">
        Tambah
      </button>
    </form>

    <div class="flex gap-2 mb-4 flex-wrap">
      <div class="flex gap-1 text-sm" id="filterGroup">
        <button data-filter="semua" class="px-3 py-1 rounded-full font-medium">Semua</button>
        <button data-filter="belum" class="px-3 py-1 rounded-full font-medium">Belum</button>
        <button data-filter="selesai" class="px-3 py-1 rounded-full font-medium">Selesai</button>
      </div>
      <input id="searchInput" type="text" placeholder="cari tugas..."
        class="ml-auto border border-stone-300 rounded px-3 py-1 text-sm focus:outline-none focus:border-stone-500">
    </div>

    <div id="todoList" class="flex flex-col gap-2.5"></div>
    <p id="emptyMsg" class="hidden text-center text-stone-400 text-sm py-8">belum ada tugas. tambah dulu di atas.</p>

    <footer class="text-center text-xs text-stone-400 mt-6">tersimpan otomatis di browser (localStorage)</footer>
  </div>
</div>

<script>
const KEY = "junior_todos";
let filter = "semua";
let editingId = null;

const $ = (id) => document.getElementById(id);
const list = $("todoList"), form = $("todoForm"), titleInput = $("titleInput"),
  searchInput = $("searchInput"), emptyMsg = $("emptyMsg"), alertBox = $("alert");

const load = () => { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch { return []; } };
const save = (t) => localStorage.setItem(KEY, JSON.stringify(t));

let todos = load();
if (!Array.isArray(todos)) todos = [];
if (todos.length === 0 && !localStorage.getItem("junior_seeded")) {
  todos = [
    { id: 1, judul: "Belajar HTML", status: "belum" },
    { id: 2, judul: "Kerjakan tugas UX", status: "belum" }
  ];
  save(todos);
  localStorage.setItem("junior_seeded", "1");
}

function flash(msg, ok = true) {
  alertBox.textContent = msg;
  alertBox.className = "mb-4 px-4 py-2.5 rounded text-sm " +
    (ok ? "bg-green-100 text-green-800 border border-green-200" : "bg-red-100 text-red-800 border border-red-200");
  clearTimeout(flash.t);
  flash.t = setTimeout(() => alertBox.classList.add("hidden"), 2500);
}

function visible() {
  const q = searchInput.value.trim().toLowerCase();
  return todos.filter(t =>
    (filter === "semua" || t.status === filter) &&
    t.judul.toLowerCase().includes(q));
}

function render() {
  document.querySelectorAll("#filterGroup button").forEach(b => {
    const on = b.dataset.filter === filter;
    b.className = "px-3 py-1 rounded-full font-medium " +
      (on ? "bg-stone-800 text-white" : "bg-stone-200 text-stone-600 hover:bg-stone-300");
  });

  const rows = visible();
  emptyMsg.classList.toggle("hidden", rows.length > 0);
  list.innerHTML = rows.map((t, i) => {
    if (t.id === editingId) {
      return `
    <div class="border border-amber-400 rounded-md px-3.5 py-3 bg-amber-50">
      <input data-edit-input data-id="${t.id}" type="text" value="${esc(t.judul)}"
        class="w-full border border-stone-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-amber-500">
      <div class="flex gap-1.5 mt-2">
        <button data-act="save" data-id="${t.id}" class="bg-stone-800 hover:bg-stone-700 text-white text-xs px-3 py-1.5 rounded">Simpan</button>
        <button data-act="cancel" class="bg-stone-200 hover:bg-stone-300 text-stone-700 text-xs px-3 py-1.5 rounded">Batal</button>
      </div>
    </div>`;
    }
    return `
    <div class="border border-stone-200 rounded-md px-3.5 py-3 ${t.status === "selesai" ? "bg-stone-50" : "bg-white"}">
      <label class="flex items-center gap-2.5 cursor-pointer">
        <input type="checkbox" data-act="toggle" data-id="${t.id}" ${t.status === "selesai" ? "checked" : ""} class="w-4 h-4 accent-stone-800">
        <span class="text-sm font-bold ${t.status === "selesai" ? "line-through text-stone-400" : ""}">${esc(t.judul)}</span>
      </label>
      <div class="flex justify-between items-center mt-2 ml-6">
        <span class="flex gap-1.5 items-center">
          <span class="text-[11px] font-mono text-stone-400">#${i + 1}</span>
          <span class="text-[11px] px-2 py-0.5 rounded-full ${t.status === "selesai" ? "bg-emerald-200 text-emerald-900" : "bg-amber-200 text-amber-900"}">${t.status}</span>
        </span>
        <span class="flex gap-1.5">
          <button data-act="edit" data-id="${t.id}" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-2 py-1 rounded">Edit</button>
          <button data-act="del" data-id="${t.id}" class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded">Hapus</button>
        </span>
      </div>
    </div>`;
  }).join("");

  const inp = list.querySelector("[data-edit-input]");
  if (inp) { inp.focus(); inp.setSelectionRange(inp.value.length, inp.value.length); }
}

function esc(s) {
  return String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
}

function saveEdit(id) {
  const inp = list.querySelector(`[data-edit-input][data-id="${id}"]`);
  const judul = inp ? inp.value.trim() : "";
  if (!judul) return flash("Judul gak boleh kosong", false);
  const t = todos.find(x => x.id == id);
  if (t) t.judul = judul;
  editingId = null;
  save(todos); render(); flash("Tugas diperbarui");
}

form.addEventListener("submit", (e) => {
  e.preventDefault();
  const judul = titleInput.value.trim();
  if (!judul) return flash("Judul gak boleh kosong", false);
  todos.unshift({ id: Date.now(), judul, status: "belum" });
  save(todos); titleInput.value = ""; render();
  flash("Tugas ditambahkan");
});

list.addEventListener("click", (e) => {
  const btn = e.target.closest("button[data-act]");
  const box = e.target.closest("input[data-act='toggle']");
  if (box) {
    const t = todos.find(x => x.id == box.dataset.id);
    if (t) { t.status = t.status === "selesai" ? "belum" : "selesai"; save(todos); render(); }
    return;
  }
  if (!btn) return;
  const id = btn.dataset.id;
  if (btn.dataset.act === "del") {
    const t = todos.find(x => x.id == id);
    if (!t) return;
    if (!confirm(`Hapus "${t.judul}"?`)) return;
    todos = todos.filter(x => x.id != id);
    if (editingId == id) editingId = null;
    save(todos); render(); flash("Tugas dihapus");
  } else if (btn.dataset.act === "edit") {
    const raw = todos.find(x => x.id == id);
    if (raw) editingId = raw.id;
    render();
  } else if (btn.dataset.act === "save") {
    saveEdit(id);
  } else if (btn.dataset.act === "cancel") {
    editingId = null;
    render();
  }
});

list.addEventListener("keydown", (e) => {
  const inp = e.target.closest("[data-edit-input]");
  if (!inp) return;
  if (e.key === "Enter") saveEdit(inp.dataset.id);
  if (e.key === "Escape") { editingId = null; render(); }
});

document.querySelectorAll("#filterGroup button").forEach(b =>
  b.addEventListener("click", () => { filter = b.dataset.filter; render(); }));
searchInput.addEventListener("input", render);

render();
</script>
</body>
</html>
