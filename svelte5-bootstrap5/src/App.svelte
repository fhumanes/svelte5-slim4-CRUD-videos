<script>
    import * as XLSX from "xlsx";

    // Datos iniciales de las películas
    let data = [
        { id: 1, name: 'Alien: El Octavo Pasajero', price: 18.75, date: new Date('1979-05-25'), rating: 5, theme: 'Ciencia Ficción', support: 'Blue-ray' },
        { id: 2, name: 'Regreso al Futuro', price: 12.00, date: new Date('1985-07-03'), rating: 5, theme: 'Aventura', support: 'DVD' },
        { id: 3, name: 'Origen', price: 25.50, date: new Date('2010-07-16'), rating: 5, theme: 'Ciencia Ficción', support: 'Blue-ray' },
        { id: 4, name: 'El Señor de los Anillos', price: 22.00, date: new Date('2001-12-19'), rating: 5, theme: 'Fantasía', support: 'DVD' },
        { id: 5, name: 'Interestellar', price: 28.99, date: new Date('2014-11-07'), rating: 4, theme: 'Ciencia Ficción', support: 'Blue-ray' },
        { id: 6, name: 'Matrix', price: 9.50, date: new Date('1999-03-31'), rating: 5, theme: 'Ciencia Ficción', support: 'DVD' },
        { id: 7, name: 'Parásitos', price: 15.00, date: new Date('2019-05-30'), rating: 5, theme: 'Drama', support: 'DVD' },
        { id: 8, name: 'Psicosis', price: 8.00, date: new Date('1960-06-16'), rating: 5, theme: 'Terror', support: 'CDROM' },
        { id: 9, name: 'Pulp Fiction', price: 11.20, date: new Date('1994-10-14'), rating: 4, theme: 'Crimen', support: 'Blue-ray' },
        { id: 10, name: 'El Rey León', price: 14.99, date: new Date('1994-06-24'), rating: 5, theme: 'Animación', support: 'DVD' }
    ];

    // Estado filtro, orden y paginación
    let filter = '';
    let sortColumn = null;
    let sortDirection = 1;

    let page = 1;
    let pageSize = 3;
    const pageSizes = [3, 5, 10];

    // --- Estado para el modal de acciones (Añadir/Editar/Visualizar) ---
    let selectedMovie = {};
    let showModal = false; // Controla la visibilidad del modal
    let modalMode = 'view'; // Puede ser 'view', 'add', o 'edit'

    // --- Funciones para las acciones ---
    function handleView(movie) {
        modalMode = 'view';
        selectedMovie = { ...movie, date: movie.date.toISOString().split('T')[0] }; // Formato YYYY-MM-DD para input[type="date"]
        showModal = true;
    }

    function handleAdd() {
        modalMode = 'add';
        // Crea un objeto vacío con valores por defecto
        selectedMovie = {
            id: Math.max(...data.map(m => m.id)) + 1, // Nuevo ID (simple, para demo)
            name: '',
            price: 0,
            date: new Date().toISOString().split('T')[0], // Fecha actual en formato YYYY-MM-DD
            rating: 1, // Valoración inicial por defecto
            theme: 'Acción',
            support: 'DVD'
        };
        showModal = true;
    }

    function handleEdit(movie) {
        modalMode = 'edit';
        // Crea una copia de la película para evitar modificar el original
        selectedMovie = { ...movie, date: movie.date.toISOString().split('T')[0] };
        showModal = true;
    }
    
    function handleDelete(id, name) {
        if (confirm(`¿Estás seguro de que quieres eliminar "${name}"?`)) {
            data = data.filter(d => d.id !== id);
            // Ajustar página si se queda vacía después de eliminar
            if ((page - 1) * pageSize >= data.length && page > 1) {
                page--;
            }
        }
    }

    function handleSave() {
        // Validación básica
        if (!selectedMovie.name || selectedMovie.price === undefined || selectedMovie.date === undefined || selectedMovie.rating === undefined || !selectedMovie.theme || !selectedMovie.support) {
            alert('Por favor, rellena todos los campos.');
            return;
        }

        if (modalMode === 'add') {
            data = [...data, { ...selectedMovie, date: new Date(selectedMovie.date) }];
        } else if (modalMode === 'edit') {
            data = data.map(movie => 
                movie.id === selectedMovie.id ? { ...selectedMovie, date: new Date(selectedMovie.date) } : movie
            );
        }
        closeModal();
    }

    function closeModal() {
        showModal = false;
        selectedMovie = {}; // Limpiar el objeto de la película seleccionada
    }

    // --- Lógica de la tabla ---
    function toggleSort(column) {
        if (sortColumn === column) {
            sortDirection = -sortDirection;
        } else {
            sortColumn = column;
            sortDirection = 1;
        }
        page = 1;
    }

    function normalize(str) {
        return String(str).normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    }

    $: filteredData = data
        .filter(row => {
            const normalizedFilter = normalize(filter);
            return (
                normalize(row.name).includes(normalizedFilter) ||
                normalize(row.id).includes(normalizedFilter) ||
                normalize(row.price).includes(normalizedFilter) ||
                normalize(row.rating).includes(normalizedFilter) ||
                normalize(row.theme).includes(normalizedFilter) ||
                normalize(row.support).includes(normalizedFilter) ||
                normalize(row.date.toLocaleDateString()).includes(normalizedFilter)
            );
        })
        .sort((a, b) => {
            if (!sortColumn) return 0;
            if (a[sortColumn] < b[sortColumn]) return -1 * sortDirection;
            if (a[sortColumn] > b[sortColumn]) return 1 * sortDirection;
            return 0;
        });

    $: totalPages = Math.ceil(filteredData.length / pageSize);
    $: pagedData = filteredData.slice((page - 1) * pageSize, page * pageSize);

    $: {
        if (page > totalPages && totalPages > 0) {
            page = totalPages;
        } else if (totalPages === 0) {
            page = 1;
        }
    }

    function prevPage() { if (page > 1) page--; }
    function nextPage() { if (page < totalPages) page++; }
    function goToPage(n) { if (n >= 1 && n <= totalPages) page = n; }

    // --- Exportar ---
    function exportToCSV() {
        const rows = [Object.keys(filteredData[0])].concat(filteredData.map(Object.values));
        const csvContent = rows.map(e => e.join(",")).join("\n");
        const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.setAttribute("download", "peliculas-filtradas.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportToExcel() {
        const worksheet = XLSX.utils.json_to_sheet(filteredData);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
        XLSX.writeFile(workbook, "peliculas-filtradas.xlsx");
    }
</script>

<style>
    /* --- Styles for elements not directly covered by Bootstrap or needing overrides --- */
    /* Star Rating Styles (kept as custom for fine control) */
    .star-rating {
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        user-select: none;
        margin-top: 0.25rem;
    }
    .star-rating .star {
        cursor: pointer;
        color: #ccc; /* Color por defecto (gris) */
        transition: color 0.2s;
    }
    .star-rating .star.filled {
        color: gold; 
    }
    .star-rating .star.disabled {
        cursor: default;
    }

    /* Adjust form-group margin for Bootstrap's row/col gap */
    .form-group {
        margin-bottom: 1rem; /* Restore default margin-bottom for form groups */
    }

    /* Custom button colors (can be replaced by Bootstrap's btn-secondary etc.) */
    .add-btn { background-color: #28a745; border-color: #28a745; }
    .view-btn { background-color: #007bff; border-color: #007bff; }
    .edit-btn { background-color: #fd7e14; border-color: #fd7e14; }
    .delete-btn { background-color: #dc3545; border-color: #dc3545; }
    
    /* Overrides for Bootstrap modal backdrop */
    .modal-backdrop.show {
        opacity: 0.5; /* Default Bootstrap backdrop opacity is 0.5 */
        background-color: #000; /* Ensure black background */
    }

    /* Ensure close button in modal is properly positioned if not using btn-close-white */
    .modal-header .btn-close {
        padding: 0.5rem;
        margin: -0.5rem -0.5rem -0.5rem auto; /* Default Bootstrap margin for close button */
    }

    /* Modal dialog custom width */
    .modal-dialog-custom {
        max-width: 600px; /* Custom width for modal */
    }

    /* Ensure some default spacing for inputs that might lose it with Bootstrap */
    input, select {
        margin-bottom: 0.5rem; /* Small margin for inputs in form groups if needed */
    }

    /* Remove default button background/border for action buttons */
    .action-btn {
        border: none; /* Remove default button border */
    }

    /* Custom styling for pagination buttons if not using Bootstrap's default btn-light etc. */
    .pagination .btn {
        background-color: #f8f9fa; /* Light background for pagination buttons */
        border: 1px solid #dee2e6; /* Light border */
        color: #0d6efd; /* Link color */
    }
    .pagination .btn:hover {
        background-color: #e9ecef;
    }
    .pagination .btn:disabled {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }

</style>

<div class="container mt-4">
    <h2 class="text-center mb-4">🎬 Gestión de Películas</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div style="width: 25%;">
            <input type="text" class="form-control" placeholder="🔎 Filtrar..." bind:value={filter} />
        </div>
        <button class="btn btn-success" on:click={handleAdd}>
            <i class="fa-solid fa-plus-circle me-1"></i> Añadir Película
        </button>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <label for="pageSizeSelect" class="me-2">Registros por página:</label>
            <select id="pageSizeSelect" class="form-select w-auto" bind:value={pageSize} on:change={() => (page = 1)}>
                {#each pageSizes as size}
                    <option value={size}>{size}</option>
                {/each}
            </select>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary" on:click={exportToCSV}>
                <i class="fa-solid fa-file-csv me-1"></i> Exportar CSV
            </button>
            <button class="btn btn-outline-success" on:click={exportToExcel}>
                <i class="fa-solid fa-file-excel me-1"></i> Exportar Excel
            </button>
            <span class="fw-bold ms-3">Total de películas: {filteredData.length}</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">Acciones</th>
                    <th scope="col" class="cursor-pointer text-end" on:click={() => toggleSort('id')}>
                        ID
                        {#if sortColumn === 'id'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer" on:click={() => toggleSort('name')}>
                        Nombre
                        {#if sortColumn === 'name'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer text-end" on:click={() => toggleSort('price')}>
                        Precio (EUR)
                        {#if sortColumn === 'price'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer text-center" on:click={() => toggleSort('date')}>
                        Fecha
                        {#if sortColumn === 'date'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer text-center" on:click={() => toggleSort('rating')}>
                        Valoración
                        {#if sortColumn === 'rating'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer" on:click={() => toggleSort('theme')}>
                        Tema
                        {#if sortColumn === 'theme'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                    <th scope="col" class="cursor-pointer" on:click={() => toggleSort('support')}>
                        Soporte
                        {#if sortColumn === 'support'}
                            {sortDirection === 1 ? '▲' : '▼'}
                        {/if}
                    </th>
                </tr>
            </thead>
            <tbody>
                {#if pagedData.length === 0}
                    <tr><td colspan="8" class="text-center">No hay películas que coincidan con los filtros.</td></tr>
                {:else}
                    {#each pagedData as row (row.id)}
                        <tr>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info" on:click={() => handleView(row)} title="Visualizar">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" on:click={() => handleEdit(row)} title="Editar">
                                        <i class="fa-solid fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" on:click={() => handleDelete(row.id, row.name)} title="Eliminar">
                                        <i class="fa-solid fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-end">{row.id}</td>
                            <td>{row.name}</td>
                            <td class="text-end">€{row.price.toFixed(2)}</td>
                            <td class="text-center">{row.date.toLocaleDateString()}</td>
                            
                            <td class="text-center">
                                <div class="star-rating">
                                    {#each Array(5) as _, i}
                                        <i class="fa-solid fa-star star disabled" class:filled={i < row.rating}></i>
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

    {#if totalPages > 1}
        <nav aria-label="Navegación de páginas">
            <ul class="pagination justify-content-center">
                <li class="page-item" class:disabled={page === 1}>
                    <button class="page-link" on:click={prevPage}>Anterior</button>
                </li>
                {#each Array(totalPages) as _, i}
                    <li class="page-item" class:active={i + 1 === page}>
                        <button class="page-link" on:click={() => goToPage(i + 1)}>
                            {i + 1}
                        </button>
                    </li>
                {/each}
                <li class="page-item" class:disabled={page === totalPages}>
                    <button class="page-link" on:click={nextPage}>Siguiente</button>
                </li>
            </ul>
        </nav>
    {/if}
</div>

<!-- Modal -->
{#if showModal}
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-dialog-custom" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {#if modalMode === 'add'}Añadir Película{:else if modalMode === 'edit'}Editar Película{:else}Detalles de la película{/if}
                    </h5>
                    <button type="button" class="btn-close" aria-label="Cerrar" on:click={closeModal}></button>
                </div>
                <div class="modal-body">
                    {#if modalMode === 'add' || modalMode === 'edit'}
                        <form on:submit|preventDefault={handleSave}>
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input id="name" type="text" class="form-control" bind:value={selectedMovie.name} required />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="price" class="form-label">Precio (EUR)</label>
                                    <input id="price" type="number" step="0.01" class="form-control" bind:value={selectedMovie.price} required />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="date" class="form-label">Fecha</label>
                                    <input id="date" type="date" class="form-control" bind:value={selectedMovie.date} required />
                                </div>
                                
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label">Valoración (1-5)</label>
                                    <div class="star-rating">
                                        {#each Array(5) as _, i}
                                            <i 
                                                class="fa-solid fa-star star" 
                                                class:filled={i < selectedMovie.rating} 
                                                on:click={() => selectedMovie = { ...selectedMovie, rating: i + 1 }}
                                            ></i>
                                        {/each}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label for="theme" class="form-label">Tema</label>
                                    <select id="theme" class="form-select" bind:value={selectedMovie.theme} required>
                                        <option>Acción</option><option>Aventura</option><option>Ciencia Ficción</option>
                                        <option>Comedia</option><option>Drama</option><option>Fantasía</option>
                                        <option>Terror</option><option>Crimen</option><option>Animación</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="support" class="form-label">Soporte</label>
                                    <select id="support" class="form-select" bind:value={selectedMovie.support} required>
                                        <option>CDROM</option><option>DVD</option><option>Blue-ray</option>
                                    </select>
                                </div>
                            </div>
                        </form>

                    {:else if modalMode === 'view'}
                        {#if selectedMovie}
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="id-view" class="form-label">ID</label>
                                    <input id="id-view" type="text" class="form-control" value={selectedMovie.id} readonly disabled />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="name-view" class="form-label">Nombre</label>
                                    <input id="name-view" type="text" class="form-control" value={selectedMovie.name} readonly disabled />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="price-view" class="form-label">Precio (EUR)</label>
                                    <input id="price-view" type="number" class="form-control" value={selectedMovie.price} readonly disabled />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="date-view" class="form-label">Fecha</label>
                                    <input id="date-view" type="date" class="form-control" value={selectedMovie.date} readonly disabled />
                                </div>
                                
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="rating-view" class="form-label">Valoración (1-5)</label>
                                    <div class="star-rating">
                                        {#each Array(5) as _, i}
                                            <i class="fa-solid fa-star star disabled" class:filled={i < selectedMovie.rating}></i>
                                        {/each}
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="theme-view" class="form-label">Tema</label>
                                    <input id="theme-view" type="text" class="form-control" value={selectedMovie.theme} readonly disabled />
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="support-view" class="form-label">Soporte</label>
                                    <input id="support-view" type="text" class="form-control" value={selectedMovie.support} readonly disabled />
                                </div>
                            </div>
                        {/if}
                    {/if}
                </div>
                <div class="modal-footer">
                    {#if modalMode === 'add' || modalMode === 'edit'}
                        <button type="submit" class="btn btn-success" on:click={handleSave}>
                            {#if modalMode === 'add'}Guardar{:else}Actualizar{/if}
                        </button>
                    {/if}
                    <button type="button" class="btn btn-secondary" on:click={closeModal}>Cerrar</button>
                </div>
            </div>
        </div>
    </div>
{/if}