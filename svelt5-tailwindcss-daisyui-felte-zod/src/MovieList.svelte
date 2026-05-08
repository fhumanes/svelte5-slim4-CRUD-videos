<script>
  import { onMount } from "svelte";
  import * as XLSX from "xlsx";
  import Swal from "sweetalert2";
  import api from "./lib/api.js";
  import MovieManager from "./MovieManager.svelte";

  // ============================
  // ESTADO (runes)
  // ============================
  let movies = $state([]);
  let themes = $state([]);
  let supports = $state([]);

  let loading = $state(true);
  let errorMessage = $state(null);

  let filter = $state("");
  let sortColumn = $state(null);
  let sortDirection = $state(1);

  let page = $state(1);
  let pageSize = $state(3);
  const pageSizes = [3, 5, 10];

  let selectedMovie = $state({});
  let showModal = $state(false);
  let modalMode = $state("view");

  // ============================
  // AUXILIARES
  // ============================
  function normalize(str) {
    return String(str)
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .toLowerCase();
  }

  function formatPrice(price) {
    return new Intl.NumberFormat("es-ES", {
      style: "currency",
      currency: "EUR",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(price);
  }

  // ============================
  // DERIVADOS
  // ============================
  let filteredData = $state([]);
  let totalPages = $derived(1);
  let pagedData = $state([]);

  // ============================
  // CARGA INICIAL
  // ============================
  onMount(() => {
    fetchAllData();
  });

  async function fetchAllData() {
    loading = true;
    errorMessage = null;

    try {
      const [moviesRes, themesRes, supportsRes] = await Promise.all([
        api.get("/movies"),
        api.get("/themes"),
        api.get("/supports")
      ]);

      movies = moviesRes.data.map((m) => ({
        id: m.id_movie,
        name: m.name,
        price: parseFloat(m.price),
        date: new Date(m.startDate),
        rating: parseInt(m.rating, 10),
        theme: m.theme,
        theme_id: String(m.theme_id),
        support: m.support,
        support_id: String(m.support_id)
      }));

      themes = themesRes.data;
      supports = supportsRes.data;

      filteredData = filterData();
      calculationPage();
    } catch (err) {
      errorMessage = "No se pudieron cargar los datos.";
    } finally {
      loading = false;
    }
  }

  // ============================
  // FILTRADO + ORDENACIÓN
  // ============================
  function filterData() {
    const f = normalize(filter);

    const filtered = movies.filter((row) => {
      return (
        normalize(row.name).includes(f) ||
        normalize(row.id).includes(f) ||
        normalize(row.price).includes(f) ||
        normalize(row.rating).includes(f) ||
        normalize(row.theme).includes(f) ||
        normalize(row.support).includes(f) ||
        normalize(row.date.toLocaleDateString()).includes(f)
      );
    });

    if (!sortColumn) return filtered;

    return filtered.slice().sort((a, b) => {
      const col = sortColumn;
      const dir = sortDirection;

      if (["theme", "support", "name"].includes(col)) {
        return a[col].localeCompare(b[col]) * dir;
      }
      if (col === "date") {
        return (new Date(a.date) - new Date(b.date)) * dir;
      }

      if (a[col] < b[col]) return -1 * dir;
      if (a[col] > b[col]) return 1 * dir;
      return 0;
    });
  }

  function calculationPage() {
    totalPages = Math.ceil(filteredData.length / pageSize);
    pagedData = filteredData.slice((page - 1) * pageSize, page * pageSize);

    if (totalPages === 0) page = 1;
    if (page > totalPages && totalPages > 0) page = totalPages;
  }

  // ============================
  // MODAL: VIEW / ADD / EDIT
  // ============================
  function handleView(movie) {
    modalMode = "view";
    selectedMovie = {
      ...movie,
      dateText: movie.date.toLocaleDateString("es-ES")
    };
    showModal = true;
  }

  function handleAdd() {
    modalMode = "add";
    selectedMovie = {
      id: null,
      name: "",
      price: "",
      date: "",
      rating: "1",
      theme_id: themes.length ? String(themes[0].id_theme) : "",
      support_id: supports.length ? String(supports[0].id_support) : ""
    };
    showModal = true;
  }

  function handleEdit(movie) {
    modalMode = "edit";
    selectedMovie = {
      ...movie,
      price: String(movie.price),
      date: movie.date.toISOString().split("T")[0],
      rating: String(movie.rating),
      theme_id: String(movie.theme_id),
      support_id: String(movie.support_id)
    };
    showModal = true;
  }

  async function handleDelete(id, name) {
    const result = await Swal.fire({
      title: "¿Estás seguro?",
      text: `Eliminar "${name}" es irreversible.`,
      icon: "warning",
      showCancelButton: true
    });

    if (!result.isConfirmed) return;

    try {
      await api.delete(`/movies/${id}`);
      Swal.fire("Eliminada", `"${name}" eliminada.`, "success");
      fetchAllData();
    } catch (err) {
      Swal.fire("Error", "No se pudo eliminar.", "error");
    }
  }

  function closeModal() {
    showModal = false;
    selectedMovie = {};
  }

  // ============================
  // GUARDAR (ADD / EDIT)
  // ============================
  async function saveMovie(values) {
    const normalizedName = values.name.trim().toLowerCase();
    const currentId = selectedMovie?.id != null ? Number(selectedMovie.id) : null;  // ID actual (null si es nuevo)

      // Buscar si hay otra película con el mismo nombre
    const duplicate = movies.find((m) => {
        const sameName = m.name.trim().toLowerCase() === normalizedName;
        const differentId = currentId == null ? true : m.id !== currentId;
        return sameName && differentId;
    });

    if (duplicate) {
        Swal.fire(
        "Duplicado",
        `Ya existe una película con ese nombre (ID ${duplicate.id})`,
        "warning"
        );
        return;
    }

    const movieData = {
      name: values.name,
      price: parseFloat(values.price.replace(",", ".")),
      startDate: values.date,
      rating: values.rating,
      theme_id: parseInt(values.theme_id),
      support_id: parseInt(values.support_id)
    };

    try {
      if (modalMode === "add") {
        await api.post("/movies", movieData);
        Swal.fire({
          title: "Guardada",
          text: "Película añadida correctamente",
          icon: "success",
          timer: 1500
        });
      } else {
        await api.put(`/movies/${selectedMovie.id}`, movieData);
        Swal.fire({
          title: "Actualizada",
          text: "Película actualizada correctamente",
          icon: "success",
          timer: 1500
        });
      }

      closeModal();
      fetchAllData();
    } catch (err) {
      Swal.fire({
        title: "Sin cambios",
        text: "No se ha variado ningún dato",
        icon: "info",
        timer: 1500
      });
    }
  }

  // ============================
  // ORDENACIÓN + PAGINACIÓN
  // ============================
  function toggleSort(col) {
    if (sortColumn === col) {
      sortDirection = sortDirection * -1;
    } else {
      sortColumn = col;
      sortDirection = 1;
    }
    page = 1;
    filteredData = filterData();
    calculationPage();
  }

  function prevPage() {
    if (page > 1) {
      page--;
      calculationPage();
    }
  }

  function nextPage() {
    if (page < totalPages) {
      page++;
      calculationPage();
    }
  }

  function goToPage(n) {
    if (n >= 1 && n <= totalPages) {
      page = n;
      calculationPage();
    }
  }

  // ============================
  // EXPORTACIONES
  // ============================
  function exportToCSV() {
    if (!filteredData.length) return;

    const rows = [Object.keys(filteredData[0])].concat(
      filteredData.map((r) => Object.values(r))
    );

    const csv = rows.map((r) => r.join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "peliculas.csv";
    link.click();
  }

  function exportToExcel() {
    if (!filteredData.length) return;

    const ws = XLSX.utils.json_to_sheet(filteredData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Datos");
    XLSX.writeFile(wb, "peliculas.xlsx");
  }
</script>

<!-- ========================= -->
<!--  CONTENIDO PRINCIPAL     -->
<!-- ========================= -->

<div class="max-w-6xl mt-6 px-2">

  <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
    <span class="text-primary">🎬</span> Gestión de Películas
  </h2>

  <!-- FILTRO + AÑADIR -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

    <div class="w-full md:w-1/3 relative">
      <input
        class="input input-bordered w-full pr-8"
        placeholder="🔎 Filtrar..."
        value={filter}
        oninput={(e) => {
          filter = e.target.value;
          page = 1;
          filteredData = filterData();
          calculationPage();
        }}
      />

      {#if filter}
        <button
          class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
          onclick={() => {
            filter = "";
            filteredData = filterData();
            calculationPage();
          }}
        >
          ✖
        </button>
      {/if}
    </div>
    
    <div class=" gap-2 flex align-items-end">
      <button class="btn btn-outline btn-success" onclick={handleAdd}>
        ➕ Añadir Película
      </button>
      <button class="btn btn-outline btn-neutral" onclick={exportToCSV}>CSV</button>
      <button class="btn btn-outline btn-success" onclick={exportToExcel}>Excel</button>    
    </div>
  </div>

  <!-- PAGE SIZE + EXPORT -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

    <div class="flex items-center gap-2">
      <label class="font-medium" for="page-size-select">Registros:</label>
      <select
        id="page-size-select"
        class="select select-bordered w-16"
        value={pageSize}
        onchange={(e) => {
          pageSize = parseInt(e.target.value);
          page = 1;
          filteredData = filterData();
          calculationPage();
        }}
      >
        {#each pageSizes as size}
          <option value={size}>{size}</option>
        {/each}
      </select>

      <span class="font-semibold ml-2">Total: {filteredData.length}</span>

    </div>

  </div>

  <!-- ESTADOS -->
  {#if loading}
    <p class="text-center py-8">Cargando...</p>

  {:else if errorMessage}
    <div class="alert alert-error justify-center">{errorMessage}</div>

  {:else}
    <div class="overflow-x-auto bg-base-100 rounded-xl shadow">
      <table class="table table-zebra w-full">
        <thead class="bg-gray-200" >
          <tr>
            <th>Acciones</th>

            <th class="cursor-pointer text-right" onclick={() => toggleSort("id")}>
              ID {sortColumn === "id" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer" onclick={() => toggleSort("name")}>
              Nombre {sortColumn === "name" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer text-right" onclick={() => toggleSort("price")}>
              Precio {sortColumn === "price" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer text-center" onclick={() => toggleSort("date")}>
              Fecha {sortColumn === "date" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer text-center" onclick={() => toggleSort("rating")}>
              Rating {sortColumn === "rating" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer" onclick={() => toggleSort("theme")}>
              Tema {sortColumn === "theme" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>

            <th class="cursor-pointer" onclick={() => toggleSort("support")}>
              Soporte {sortColumn === "support" ? (sortDirection === 1 ? "▲" : "▼") : ""}
            </th>
          </tr>
        </thead>

        <tbody>
          {#if pagedData.length === 0}
            <tr>
              <td colspan="8" class="text-center py-4">
                No hay resultados.
              </td>
            </tr>

          {:else}
            {#each pagedData as row (row.id)}
              <tr>
                <td>
                  <div class="flex gap-2">
                    <button class="btn btn-info btn-xs btn-soft" onclick={() => handleView(row)}>
                      👁
                    </button>
                    <button class="btn btn-warning btn-xs btn-soft" onclick={() => handleEdit(row)}>
                      ✏️
                    </button>
                    <button class="btn btn-error btn-xs btn-soft" onclick={() => handleDelete(row.id, row.name)}>
                      🗑
                    </button>
                  </div>
                </td>

                <td class="text-right">{row.id}</td>
                <td>{row.name}</td>
                <td class="text-right">{formatPrice(row.price)}</td>
                <td class="text-center">{row.date.toLocaleDateString()}</td>

                <td class="text-center">
                  <div class="flex justify-center gap-1 text-yellow-400">
                    {#each Array(5) as _, i}
                      <span>{i < row.rating ? "★" : "☆"}</span>
                    {/each}
                  </div>
                </td>

                <td>{row.theme}</td>
                <td>{row.support}</td>
              </tr>
            {/each}
          {/if}
        </tbody>
      </table>
    </div>
  {/if}

  <!-- PAGINACIÓN -->
  {#if totalPages > 1}
    <div class="flex justify-center mt-6">
      <div class="join shadow">
        <button class="join-item btn btn-sm" disabled={page === 1} onclick={prevPage}>«</button>

        {#each Array(totalPages) as _, i}
          <button
            class="join-item btn btn-sm {page === i + 1 ? 'btn-primary btn-active text-white' : ''}"
            onclick={() => goToPage(i + 1)}
          >
            {i + 1}
          </button>
        {/each}

        <button class="join-item btn btn-sm" disabled={page === totalPages} onclick={nextPage}>»</button>
      </div>
    </div>
  {/if}

  <!-- MODAL (componente separado) -->
  <MovieManager
    open={showModal}
    mode={modalMode}
    movie={selectedMovie}
    {themes}
    {supports}
    onSave={saveMovie}
    onClose={closeModal}
  />
</div>

<style>
  .btn-soft {
    opacity: 0.8;
  }
  .btn-soft:hover {
    opacity: 1;
  }
</style>
