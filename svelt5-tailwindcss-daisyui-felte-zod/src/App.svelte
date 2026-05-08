<script>
  import Router from 'svelte-spa-router';

  import MovieList from './MovieList.svelte';
  import ThemaManager from './ThemaManager.svelte';
  import SupportManager from './SupportManager.svelte';

  const routes = {
    '/': MovieList,
    '/movies': MovieList,
    '/themes': ThemaManager,
    '/supports': SupportManager
  };
</script>

<!-- NAVBAR DAISYUI -->
<div class="navbar bg-neutral text-neutral-content px-2 lg:px-4">
  <div class="flex-1">
    <a href="#/" class="text-xl font-bold">Gestión de Cine</a>
  </div>

  <div class="flex gap-4">
    <a href="#/movies" class="btn btn-ghost btn-sm">Películas</a>
    <a href="#/themes" class="btn btn-ghost btn-sm">Temas</a>
    <a href="#/supports" class="btn btn-ghost btn-sm">Soportes</a>
  </div>
</div>


<!-- CONTENIDO PRINCIPAL -->
<div class="p-1  bg-gray-50 min-h-screen">
  <Router {routes} />
</div>
