document.addEventListener('DOMContentLoaded', function () {
  var b = document.getElementById('menuBtn');
  if (b) b.addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
  document.addEventListener('click', function (e) {
    if (document.body.classList.contains('nav-open') && !e.target.closest('.sidebar') && e.target.id !== 'menuBtn')
      document.body.classList.remove('nav-open');
  });
  // live table search: <input data-search="tableId">
  document.querySelectorAll('[data-search]').forEach(function (inp) {
    inp.addEventListener('input', function () {
      var t = document.getElementById(inp.dataset.search); if (!t) return;
      var q = inp.value.toLowerCase();
      t.querySelectorAll('tbody tr').forEach(function (tr) { tr.style.display = tr.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none'; });
    });
  });
  // confirm dialogs: <form data-confirm="Are you sure?">
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });
});
