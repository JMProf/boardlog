<?php
require_once __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Media de jugadores por juego</h5>
        <form class="row gy-2 gx-2 align-items-end" method="get" action="/">
          <div class="col-sm-5">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
          </div>
          <div class="col-sm-5">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
          </div>
          <div class="col-sm-2">
            <button class="btn btn-primary w-100" type="submit">Filtrar</button>
          </div>
        </form>
        <canvas id="avgChart" height="140" class="mt-3"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-body">
        <h6>Qué ves aquí</h6>
        <p class="mb-0">Esta gráfica muestra la <strong>media de jugadores</strong> por juego en todas las partidas registradas. Puedes filtrar por fechas (inclusive).</p>
      </div>
    </div>
  </div>
</div>

<script>
const params = new URLSearchParams(window.location.search);
const url = '/graph_data.php?' + params.toString();
fetch(url)
  .then(r => r.json())
  .then(data => {
    const labels = data.map(d => d.name);
    const values = data.map(d => +d.avg_players);
    const ctx = document.getElementById('avgChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Media de jugadores', data: values }] },
      options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: 'Jugadores' } } } }
    });
  })
  .catch(console.error);
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
