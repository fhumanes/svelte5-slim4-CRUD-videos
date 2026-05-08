<script>
  import { Willow, Button } from 'wx-svelte-core';
  import Toasts from './notification/Toasts.svelte'; // Asegúrate de que la ruta sea correcta

  // Importa tus componentes de cuadrícula
  import MovieGrid from './components/MovieGrid.svelte';
  import ThemeGrid from './components/ThemeGrid.svelte';
  import SupportGrid from './components/SupportGrid.svelte';

  // Define el estado de la página actual
  let currentPage = $state('movies'); // 'movies', 'themes', 'supports'

  function navigateTo(page) {
    currentPage = page;
  }
</script>

<Toasts />
<Willow>
  <div class="app-container">
    <nav class="navbar">
      <h1 class="app-title">Mi Aplicación CRUD</h1>
      <div class="nav-buttons">
        <Button
          type={currentPage === 'movies' ? 'primary' : 'default'}
          onclick={() => navigateTo('movies')}
        >
          🎬 Películas
        </Button>
        <Button
          type={currentPage === 'themes' ? 'primary' : 'defualt'}
          onclick={() => navigateTo('themes')}
        >
          📚 Temas
        </Button>
        <Button
          type={currentPage === 'supports' ? 'primary' : 'default'}
          onclick={() => navigateTo('supports')}
        >
          📦 Soportes
        </Button>
      </div>
    </nav>

    <main class="content-area">
      {#if currentPage === 'movies'}
        <MovieGrid />
      {:else if currentPage === 'themes'}
        <ThemeGrid />
      {:else if currentPage === 'supports'}
        <SupportGrid />
      {/if}
    </main>
  </div>
</Willow>

<style>
  .app-container {
    display: flex;
    flex-direction: column;
    min-height: 97vh; 
  }

  .navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    /* background-color: #f8f9fa; /* Un gris claro para la barra de navegación */
    background-color: #a0a1a1;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    flex-wrap: wrap; /* Permite que los elementos se envuelvan en pantallas pequeñas */
  }

  .app-title {
    margin: 0;
    font-size: 1.2rem;
    color: white; /* Color blanco para el título */
    /* color: #343a40; /* Color oscuro para el título */
    font-weight: 600;
  }

  .nav-buttons {
    display: flex;
    gap: 15px; /* Espacio entre los botones */
  }




  .content-area {
    flex-grow: 1; /* Permite que el área de contenido ocupe el espacio restante */
    /*  padding: 30px; */
    padding-top: 0px;
    padding-right: 30px;
    padding-bottom: 5px;
    padding-left: 30px;
    background-color: #dfe1e2; /* Fondo similar al de tus modales */
  }


  :global(.wx-sidearea) { /* Cambio del tamaño de área de edición */
    width: 300px;
  }
  
  :global(.wx-willow-theme) {
    --wx-table-select-background: #eaedf5;
    --wx-table-select-color: var(--wx-color-font);
    --wx-table-border: 1px solid #e6e6e6;
    --wx-table-select-border: inset 3px 0 var(--wx-color-primary);
    --wx-table-header-border: var(--wx-table-border);
    --wx-table-header-cell-border: var(--wx-table-border);
    --wx-table-footer-cell-border: var(--wx-table-border);
    --wx-table-cell-border: var(--wx-table-border);
    --wx-header-font-weight: 600;
    --wx-table-header-background: #f2f3f7;
    --wx-table-fixed-column-border: 3px solid #e6e6e6;
    --wx-table-editor-dropdown-border: var(--wx-table-border);
    --wx-table-editor-dropdown-shadow: 0px 4px 20px rgba(44, 47, 60, 0.12);
    --wx-table-drag-over-background: var(--wx-background-alt);
    --wx-table-drag-zone-shadow: var(--wx-box-shadow);
   
  
    /* For Filter Builder */
    --wx-filter-value-color: var(--wx-color-primary); /* text value color in FilterBuilder*/
    --wx-filter-and-background: #fcba2e; /* background for the glue "and" logic button in FilterBuilder*/
    --wx-filter-or-background: #77d257; /* background for the glue "or" logic button in FilterBuilder*/
    --wx-filter-and-font-color: var(--wx-color-font); /* font color for the glue "and" logic button in FilterBuilder*/
    --wx-filter-or-font-color: var(--wx-color-font); /* font color for the glue "or" logic button in FilterBuilder*/
    --wx-filter-border: 1px solid #e6e6e6; /* filter border around filter blocks in  FilterEditor*/
  }
  /* Estilos globales para el área de edición */
  :global(.wx-sidearea) {
    background-color: rgb(193, 200, 211) !important;
  }
  :global(.wx-field-control:not(:has(input,select, check, radio, .wx-richselect)) ) { /* Estilos para campos sin controles de entrada */
    background-color: white;
    min-height: 30px;
    align-content: center;
    padding-left: 5px;
  }
    :global(.wx-tabs .wx-active ) { /* Estilos para la solapa activa */
    background-color: white !important;
    /* background-color:#37a9ef !important;
    color: white !important; */

  }
 
  /* Media queries para responsividad */
  @media (max-width: 768px) {
    .navbar {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }

    .nav-buttons {
      width: 100%;
      justify-content: center;
      margin-top: 10px;
    }
  }

  @media (max-width: 480px) {
    .nav-buttons {
      flex-direction: column;
      gap: 10px;
    }
  }
</style>
