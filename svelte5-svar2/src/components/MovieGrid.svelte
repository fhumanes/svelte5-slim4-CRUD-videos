<script>
  import { onMount } from 'svelte';
  import { ModalArea } from "wx-svelte-core"; // Ventana de Confirmación
  import { Grid } from 'wx-svelte-grid';
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow de aquí
  import { Editor } from 'wx-svelte-editor';
  import { ContextMenu } from 'wx-svelte-menu';
 
  import { Text, Select, DatePicker, RichSelect } from "wx-svelte-core";
  import { SideArea } from "wx-svelte-core";
  import { es as coreEs } from "wx-core-locales";
  import { default as gridEs} from "../locales/esGrid.js";
  import { default as editorEs } from "../locales/esEditor.js";
  import { default as filterEs } from "../locales/esFilter.js";
  // Filter external
  import { Tabs } from "wx-svelte-core";
	import { FilterBar, createFilter, getOptions, getFilters, createArrayFilter } from "wx-svelte-filter";
  import * as XLSX from "xlsx"
  //
  // Sistema de notificación de las acciones
  import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
  import { addToast } from "../notification/store";

  import StarCell from './StarCell.svelte';         // Imppresión de las estrellas
  import api from '../lib/api.js';                  // Importación de la API para las peticiones
  import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL

  import MovieForm from './MovieForm.svelte';      // Formulario de edición de películas

  // Definiciones y código para la gestión del DataGrid
  let gridRef = $state();
  let selected = $state([]);
  let movies = $state([]);
  let themes = $state([]);
  let supports = $state([]);

  let loading = $state(true);    // Estado de carga para mostrar un mensaje mientras se cargan los datos

  let showDeleteConfirm = $state(false);
  let movieToDelete = $state(null);

  let menuRef = $state();         // Referencia al menú contextual

  // Definición de las columnas del Grid ------------------------------------------------------------------------------------
  const columns = $derived([
    { id: "id", header: "ID", width: 60, sort: true, resize: true },
    {
      id: "name",
      header: [
        "Nombre"
      ],
      // flexgrow: 1,
      width: 250,
      sort: true,
      resize: true
    },
    {
      id: "price",
      header: [
        "Precio"
      ],
      type: "number",
      sort: true,
      width: 120,
      resize: true,
      template: value =>
        new Intl.NumberFormat("en-US", {
          style: "currency",
          currency: "EUR",
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(value)
    },
    {
      id: "startDate",
      header: [
        "Fecha"
      ],
      width: 120,
      sort: true,
      resize: true,
      type: "date",
      template: (v) => (v ? formatDateToDDMMYYYY(v) : ""),
    },
    {
      id: "rating",
      header: [
        "Rating"
      ],
      width: 130,
      sort: true,
      resize: true,
      cell: StarCell
    },
    {
      id: "theme",
      type: "text",
      header: [
        "Tema"
      ],
      //flexgrow: 1,
      width:150,
      sort: true,
      resize: true
    },
    {
      id: "support_id",
       type: "combo",
      header: [
        "Soporte"      ],
      options: supports.map(s => ({ id: s.id_support, label: s.support})),
      // flexgrow: 1,
      width: 150,
      sort: true,
      resize: true
    }
  ]);

  // Función para resolver el ID de la película seleccionada
  function resolver(id) {
    if (id) gridRef.exec("select-row", { id });
    return id;
  }
  // Función para cargar todos los datos al inicio y reload de datos después de actualizaciones de Base de Datos
  async function fetchAllData() {
      try {
          const [resMovies, resThemes, resSupports] = await Promise.all([
              api.get('/movies'),
              api.get('/themes'),
              api.get('/supports')
          ]);
          movies = resMovies.data.map(m => {
            const dateObj = createDateFromMySQL(m.startDate);
            return {
              id: m.id_movie,
              name: m.name,
              price: parseFloat(m.price),
              startDate: dateObj,
              startDateText: dateObj.toLocaleDateString("es-ES", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric"
              }),
              rating: m.rating,
              theme: m.theme,
              support: m.support,
              theme_id: m.theme_id,
              support_id: m.support_id
            };
          });
          // console.log("Películas cargadas:", movies);
          filterMovies(); // Aplicar filtro a los datos cargados
          themes = resThemes.data;
          supports = resSupports.data;

      } catch (err) {
          console.error('Error al cargar datos:', err);
          addToast({
            message: 'No se pudieron cargar los datos. Por favor, intente de nuevo más tarde.',
            type: 'error',
            dismissible: true,
            timeout: 0
          });
      } finally {
          loading = false;
      }
  }
  onMount(fetchAllData); // Llamar a la función al montar el componente

  // Función para limpiar los filtros aplicados en el Grid
  function clearFilters() {
    // gridRef.exec("filter-rows", {});
    filter = null;
    filterMovies()
    filterId = 1; // Para poder eliminar valores de estos filtros
    // Esto es útil si no tienes un id o una clase para el input
    const inputElement = document.querySelector('input[placeholder="Buscar en todos los campos"]'); // Buscar campo de entrada del filtro
    if (inputElement) {
        inputElement.value = ''; // Limpiar el valor del input
    }
  }
  // Función para actualizar la selección de filas en el Grid
  function updateSelected() {
    selected = gridRef.getState().selectedRows;
  }
  // Estilo de las columnas del Grid
  const columnStyle = col => {
    if (col.id === "id") return "text-right";
    if (col.id === "price") return "text-right";
    if (col.id === "startDate") return "text-center";
    return "";
  };

  // Menú conextual del DataGrid
    const contextOptions = [
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    { id: "edit", text: "Editar", icon: "wxi-edit" },
    { id: "view", text: "Ver", icon: "wxi-eye" },
    { id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }
  ];

  function handleContext(ev) {
      const id = gridRef.getState().selectedRows[0];
      const row = id ? gridRef.getRow(id) : null;
     /*
      if (!Array.isArray(editorItems)) {
        console.warn("editorItems no es un array:", editorItems);
      }
    */
      switch (ev.action?.id) {
        case "add":
          editorMode = "add";
          editorValues = {};
          showEditor = true;
          break;
        case "edit":
          if (row) {
            editorMode = "edit";
            editorValues = { ...row };
            showEditor = true;
          }
          break;
        case "view":
          if (row) {
            editorMode = "view";
            editorValues = { ...row };
            editorValues = {
              ...row,
              startDate: row.startDateText
            };
            showEditor = true;
          }
          break;
        case "delete":
          if (id) {
            const movie = movies.find(m => m.id === id);
            if (movie) {
              movieToDelete = movie;
              showDeleteConfirm = true;
            }
          }
          break;
        default:
          console.warn("Acción no reconocida:", ev.action);
          break;
      }
    }


// Definiciones del Editor -------------------------------------------------------------------------------------------------------------
let showEditor = $state(false);
let editorValues = $state({});
let editorMode = $state("add")

// Función para transformar los valores del editor a un formato adecuado para la API
function transformMoviePayload(values) {
  const { name, price, startDate, rating, theme_id, support_id } = values;
  const formattedDate = toValidAPIDate(startDate);
  return {
    name, price,
    startDate: formattedDate,
    rating, theme_id, support_id
  };
}

async	function handleSave(ev) {
	const values = ev.values;
    // console.log("Guardando valores en SAVE:",values);
    const payload = transformMoviePayload(values);
    try {
        if (editorMode === "add") {
          const res = await api.post("/movies",payload);
          values.id = res.data.id_movie ?? Date.now();
          addToast({
            message:'Película añadida correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });
          fetchAllData();
        } else if (editorMode === "edit") {
          await api.put(`/movies/${values.id}`, payload);
          addToast({
            message:'Película actualizada correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });
          fetchAllData();
        }
        showEditor = false
    } catch (err) {
      console.error("Error en operación de editor:", err);
      addToast({
        message: "No se pudo completar la operación.",
        type: 'error',
        dismissible: true,
        timeout: 0
      })
      showEditor = false
    }
	}

function closeEdit() { // Función para cerrar el editor desde MovieForm
  showEditor = false;
}
// Definiciones para Delete -------------------------------------------------------------------------------------------------------------

// Función para confirmar la eliminación de una película
  function confirmDelete(movie) {
    movieToDelete = movie;
    showDeleteConfirm = true;
  }
// Función para proceder con la eliminación de una película
  async function proceedDelete() {
    try {
      await api.delete(`/movies/${movieToDelete.id}`);
      movies = movies.filter(m => m.id !== movieToDelete.id); // Eliminación local Original
      filteredMovies = filteredMovies.filter(m => m.id !== movieToDelete.id); // Eliminación local Filtrado
      addToast({
        message:'Película eliminada correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
      });
    } catch (err) {
      console.error("Error al eliminar película:", err);
      addToast({
        message: "No se pudo eliminar la película.",
        type: 'error',
        dismissible: true,
        timeout: 0
      });
    } finally {
      showDeleteConfirm = false;
      movieToDelete = null;
    }
  }
// Función para cancelar la eliminación de una película
  function cancelDelete() {
    showDeleteConfirm = false;
    movieToDelete = null;
  }

  // Filter External -------------------------------------------------------------------------------------------------  
	let filterId = $state(1);
	const filterTabs = [
		{ id: 1, label: "Todos los campos" },
		{ id: 2, label: "Por Tema" },
		{ id: 3, label: "Por un campo" },
	];

  let fieldFilter = $derived([
            { id: "name", label: "Nombre", type: "text",  placeholder: "Buscar por Nombre" },
            { id: "price", type: "number" , placeholder: "Buscar por Precio, igual o mayor", filter: "greaterOrEqual" },
            { id: "startDate", type: "date" , placeholder: "Buscar por Fecha, igual o mayor", filter: "greaterOrEqual" },
            { id: "rating",  type: "text", placeholder: "Buscar por Valoración" },
            { id: "theme",  type: "text", options: getOptions(movies, "theme"), placeholder: "Buscar por Tema" },
            { id: "support",  type: "text", options: getOptions(movies, "support"), placeholder: "Buscar por Soporte" }
          ]);

 let filteredMovies = $state(null);
 let filter = $state(null);

	function handleFilterChange({ value }) {
		filterId = value;
		gridRef.exec("filter-rows", { filter: null });
	}
 // Filtra las películas según el filtro actual
 function filterMovies() {
  // console.log("Filtrando películas con el filtro:", filter);
  if (!filter) {
    filteredMovies = movies; // Si no existe filtro, mostramos todas las películas
    } else {
    filteredMovies  = filter(movies); // Aplicamos el filtro a las películas
    }
  // console.log("Películas filtradas:", filteredMovies);
 }
// Crea el filtro 
function applyFilter(value) { 
  // console.log("Valor del filtro aplicado:", value);
  filter = createArrayFilter(value);
  // console.log("Filtro creado:", filter)
  filterMovies()
}
  // End Filter External

// Exportar Películas Filtradas --------------------------------------------------------------------------------------------------
function exportToExcel() {
    const worksheet = XLSX.utils.json_to_sheet(filteredMovies);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
    XLSX.writeFile(workbook, "peliculas-filtradas.xlsx");
}
 // End Exportar Películas Filtradas

</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>🎬 Películas con Filtros y Ordenación</h3>
   <!-- Definición de los filtros Externos -->
  <strong>Filtros:</strong> 
  <Tabs value={filterId} options={filterTabs} onchange={handleFilterChange} />
  <Locale words={{ ...filterEs, ...coreEs }}>
    {#if filterId === 1}
      <FilterBar
        fields={[
          {
            type: "all",
            by:  ["name", "price", "startDateText", "rating", "theme", "support"],
            placeholder: "Buscar en todos los campos",
          },
        ]}
        onchange={({ value }) => applyFilter(value)}
      />
    {:else if filterId === 2}
      <FilterBar
        fields={[
          {
            type: "text",
            id: "theme",
            /* label: "Tema",*/
            placeholder: "Buscar por tema",
            options:  getOptions(movies, "theme"),
          },
        ]}
        onchange={({ value }) => applyFilter(value)}
      />
    {:else if filterId === 3}
      <FilterBar
        fields={[
          {
            type: "dynamic",
            label: "Campo:",
            by: fieldFilter ,
          },
        ]}
        onchange={({ value }) => applyFilter(value)}
      />
    {/if}
  </Locale>
  <!-- Fin de la definición de los filtros Externos-->

  <!-- Definición del DataGrid -->
  <div class="d-flex gap-2">
    <Button type="primary" text="🧹 Borrar filtros" onclick={clearFilters} />
    <Button type="primary" text="➕ Agregar película" onclick={() => {
      editorMode = "add";
      editorValues = {};
      showEditor = true;
    }} />

    <Button type="default" title="Cliqueame para exportar los datos a un Excel" text="📝 Export Excel" onclick={exportToExcel} />
  </div>
  <div>Para el acceso a las acciones CRUD, botón derecho en el Grid</div>
</div>
{#if loading}
  <p class="text-center">Cargando películas...</p>
{:else}
  <ContextMenu
    options={contextOptions}
    onclick={handleContext}
    at="point"
    resolver={resolver}
    api={gridRef}
  >
    <Locale words={{ ...gridEs, ...coreEs }}>
      <div style="height: 360px; max-width: 1200px;">
      <Grid
        bind:this={gridRef}
        data={filteredMovies}
        {columns}
        {columnStyle}
        pager={false}
        onselectrow={updateSelected}
      />
      </div>
    </Locale>
  </ContextMenu>
{/if}
 <!--Fin Definición del DataGrid -->

 <!-- Definición de Edición   -->
{#if showEditor}
  <SideArea>
    <MovieForm {editorMode} {editorValues} {movies} {themes} {supports} {handleSave} {closeEdit} />
  </SideArea>

{/if}
<!-- Fin Definición de Edición -->
{#if showDeleteConfirm}
  <ModalArea>
    <div class="modal-content">
      <h3>¿Eliminar esta película?</h3>
      <p>Nombre: <b>{movieToDelete?.name}</b></p>
      <p>Fecha: {movieToDelete?.startDateText}</p>
      <div class="actions">
        <Button type="danger" onclick={proceedDelete}>Confirmar</Button>
        <Button onclick={cancelDelete} color="secondary">Cancelar</Button>
      </div>
    </div>
  </ModalArea>
{/if}

<style>
  .modal-content {
    padding: 30px;
    text-align: center;
    background-color: #f1f5f9;
    border: 2px solid #f97316;
    border-radius: 0px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    font-family: "Segoe UI", "Roboto", sans-serif;
    max-width: 400px;
    margin: 0 auto;
  }
  .modal-content h3 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    color: #1e293b;
  }
  .modal-content p {
    margin: 10px 0;
    font-size: 1rem;
    color: #334155;
  }
  .actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
  }
  :global(.wx-toast) {
    border-radius: 8px;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    font-weight: 500;
    font-family: "Segoe UI", sans-serif;
    padding: 10px 20px;
  }
  :global(.wx-cell.text-right) {
    text-align: right;
  }
  :global(.wx-cell.text-center) {
    text-align: center;
  }

  :global(.wx-sidearea) {
    width: 300px;
  }
</style>
