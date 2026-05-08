<script>
  import Router from 'svelte-spa-router';
  import { Willow } from '@svar-ui/svelte-core';

  import ThemeSelector from './themes/ThemeSelector.svelte';

  import MovieList from './MovieGrid.svelte';
  import ThemeManager from './ThemeManager.svelte';
  import SupportManager from './SupportManager.svelte';

  document.documentElement.setAttribute("data-theme", 'corporate'); // Aplica el tema inicial al cargar la aplicación
 
  const routes = {
    '/': MovieList,
    '/movies': MovieList,
    '/themes': ThemeManager,
    '/supports': SupportManager 
  };
</script>
<Willow>
  <div class="min-h-screen bg-base-100 text-base-content">

  <!-- NAVBAR DAISYUI -->
    <div class="navbar bg-neutral text-neutral-content px-2 lg:px-4">
      <div class="navbar-start gap-2">
        <a href="#/" class="text-xl font-bold">Gestión de Cine</a>
      </div>

      <div class="navbar-end flex gap-2 items-center">

          <a href="#/movies" class="btn btn-ghost ">Películas</a>
          <a href="#/themes" class="btn btn-ghost ">Temas</a>
          <a href="#/supports" class="btn btn-ghost ">Soportes</a>
          <ThemeSelector />
      </div>
    </div>
  <!-- CONTENIDO PRINCIPAL -->
    <div class="app-container ">
      <div class="content-area">
          <Router {routes} />
      </div>
    </div>
  </div>
</Willow>

<style global>
  @import "./themes/Willow-custom.css"; /* Tema de WWillow con herencia de DaisyUI */
  /*  @import "./themes/custom-theme.css"; /* Importa tu tema personalizado */

  /* Normalización de estilos para SVAR y DaisyUI */
  /* Para normalizar el Tooltip de Daisy, porque lo rompe SVAR 
  .tooltip[data-tip] {
    position: relative !important;
    pointer-events: auto !important;
    background-color: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;
    z-index: 4 !important;
  }
  */

  /* Estilos personalizados para la aplicación */
  /*
      main {
      position: fixed;
      top: 55px;      /* debajo de la cabecera fija // 55 desktop || 80 movil */
/*    left: 5px;        /* pegado al borde izquierdo */
/*    width: calc(100% - 10px); /* ocupa todo el ancho si quieres */
/*    min-height: calc(99vh - 55px); /* resto de la ventana */
/*    height: calc(100dvh - 55px);
      overflow-y: auto;
      padding: 1rem;
    }
    @media (max-width: 768px) {
      main {
        top: 80px;      /* debajo de la cabecera fija // 55 desktop || 80 movil */
/* min-height: calc(99vh - 80px);  resto de la ventana */
/*       height: calc(99dvh - 80px);
        overflow-y: auto;
      }
    }
    */
.app-container {
    display: flex;
    flex-direction: column;
    /* min-height: 97vh; */
}

.content-area {
    /* margin-top: 10px;  /* Ajusta según la altura de tu encabezado */
    flex-grow: 1; /* Permite que el área de contenido ocupe el espacio restante */
    /*  padding: 30px; */
    padding-top: 5px;
    padding-right: 30px;
    padding-bottom: 30px;
    padding-left: 30px;
    /* background-color: #dfe1e2; /* Fondo similar al de tus modales */
}

@media (max-width: 768px) {
    .content-area {
        /* margin-top: 15px;  /* Ajusta según la altura de tu encabezado */
        flex-grow: 1; /* Permite que el área de contenido ocupe el espacio restante */
        /*  padding: 30px; */
        padding-top: 5px;
        padding-right: 3px;
        padding-bottom: 3px;
        padding-left: 3px;
        /* background-color: #dfe1e2; /* Fondo similar al de tus modales */
    }
}

:global(.wx-sidearea) {
    /* Cambio del tamaño de área de edición */
    width: 300px;
    background-color: var(--color-base-200) !important;
    height: 100dvh !important;
}

:global(.wx-willow-theme) {
    /* Personalización de variables para el tema Willow .wx-willow-daisy */
    --wx-table-select-background: var(--color-base-300); /* #dfdadc; */
    /* color de fondo para filas seleccionadas en tablas */
    --wx-table-select-color: var(--wx-color-font);
    --wx-table-border: 1px solid var(--color-primary); /* borde de las tablas */
    --wx-table-select-border: inset 3px 0 var(--color-primary);
    --wx-table-header-border: var(--wx-table-border);
    --wx-table-header-cell-border: var(--wx-table-border);
    --wx-table-footer-cell-border: var(--wx-table-border);
    --wx-table-cell-border: var(--wx-table-border);
    --wx-header-font-weight: 600;
    --wx-table-header-background: var(--color-base-200);
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

:global(.wx-field-control:not(:has(input, select, check, radio, .wx-richselect)) ) {
    /* Estilos para campos sin controles de entrada */
    background-color: white;
    min-height: 30px;
    align-content: center;
    padding-left: 5px;
}

:global(.wx-tabs > button) {
    /* Estilos para la solapa activa */
    background: var(--color-base-100) !important;
    color: var(--color-base-content) !important;
    /* background-color:#37a9ef !important;
    color: white !important; */
}
:global(.wx-menu) {
    /* Estilos para el menu contextual */
    background: var(--color-accent) !important;
    color: var(--color-accent-content) !important;
}
:global(.wx-menu > .wx-option) {
    /* Estilos para el menu contextual */
    background: var(--color-accent) !important;
    color: var(--color-accent-content) !important;
}

:global(.wx-menu > .wx-option:hover) {
    background: var(--color-primary) !important;
    color: var(--color-primary-content) !important;
}
:global(.wx-menu > .wx-option:hover .wx-icon,
        .wx-menu > .wx-option:hover .wx-value) {
    background: var(--color-primary) !important;
    color: var(--color-primary-content) !important;
}

:global(.wx-menu > .wx-option > .wx-icon) {
    /* Estilos para el menu contextual */
    background: var(--color-accent) !important;
    color: var(--color-accent-content) !important;
}

:global(.wx-menu > .wx-option > .wx-value) {
    /* Estilos para el menu contextual */
    background: var(--color-accent) !important;
    color: var(--color-accent-content) !important;
}
:global(.wx-filter-bar > .wx-label) {
    color: var(--color-base-content) !important;
}
:global(.wx-field > .wx-label) {
    color: var(--color-base-content) !important;
}
:global(.wx-field > .wx-field-control) {
    color: var(--color-gray-900) !important;
}


:global(.wx-sections) {
    text-align: left;
    --wx-field-width: 600px;
    margin: 12px 5px 0 5px;
}

@media (max-width: 768px) {
    :global(.wx-sections) {
        --wx-field-width: 250px;
        margin: 12px 5px 0 5px;
    }
}

:global(.wx-normal) {
    /* Para normalizar el tamaño de los botones */
    background-color: var(--color-base-200) !important;
    color: var(--color-primary) !important;
    border-color: var(--color-primary) !important;
}

:global(.wx-button) {
    /* Para normalizar el tamaño de los botones */
    border-radius: var(--radius-field) !important;
}

:global(.wx-f-row) {
    /* Para normalizar el GRID*/
    height: 1px !important;
}

:global(.wx-row) {
    /* Para normalizar el GRID*/
    background-color: var(--color-base-100)!important;
}
:global(.wx-row.wx-selected) {
    /* Para normalizar el GRID*/
    background-color: var(--color-primary)!important;
    color: var(--color-primary-content)!important;
}

:global(.wx-f-row > .wx-cell) {
    /* Para normalizar el GRID*/
    height: 1px !important;
    padding: 1px !important;
}

:global(.wx-filter-bar) {
    /* Para normalizar el filter*/
    padding: 2px !important;
}
  
</style>

