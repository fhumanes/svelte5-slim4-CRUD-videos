<script>
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import api from "./lib/api.js";

  // Felte + Zod
  import { createForm } from "felte";
  import { validator } from "@felte/validator-zod";
  import { z } from "zod";

  let supports = [];
  let loading = true;
  let errorMessage = null;

  let filter = "";
  let showModal = false;
  let modalMode = "add";
  let selectedSupport = {};

  async function fetchSupports() {
    loading = true;
    try {
      const res = await api.get("/supports");
      supports = res.data;
    } catch (err) {
      errorMessage = "No se pudieron cargar los soportes.";
    } finally {
      loading = false;
    }
  }

  onMount(fetchSupports);

  // -----------------------------------------------------
  //  ZOD: schema estático (solo campo obligatorio)
  // -----------------------------------------------------
  const supportSchema = z.object({
    support: z.string().min(1, "El nombre es obligatorio")
  });

  // -----------------------------------------------------
  //  FELTE: inicialización (solo una vez)
  // -----------------------------------------------------
  const {
    form,
    errors,
    data,
    setInitialValues,
    setTouched
  } = createForm({
    initialValues: { support: "" },
    extend: validator({ schema: supportSchema }),
    onSubmit: saveSupport
  });

  // -----------------------------------------------------
  //  Acciones del modal
  // -----------------------------------------------------
  function handleAdd() {
    modalMode = "add";
    selectedSupport = { id_support: null, support: "" };

    setInitialValues({ support: "" });
    setTouched({});

    showModal = true;
  }

  function handleEdit(item) {
    modalMode = "edit";
    selectedSupport = { ...item };

    setInitialValues({ support: item.support });
    setTouched({});

    showModal = true;
  }

  function handleView(item) {
    modalMode = "view";
    selectedSupport = { ...item };
    showModal = true;
  }

  async function handleDelete(id, name) {
    const result = await Swal.fire({
      title: "¿Eliminar soporte?",
      text: `Se eliminará "${name}".`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar"
    });

    if (!result.isConfirmed) return;

    try {
      await api.delete(`/supports/${id}`);
      Swal.fire("Eliminado", "Soporte eliminado correctamente", "success");
      fetchSupports();
    } catch (err) {
      Swal.fire("Error", "No se pudo eliminar el soporte", "error");
    }
  }

  // -----------------------------------------------------
  //  Guardar (Felte llama a esta función)
  // -----------------------------------------------------
  async function saveSupport(values) {
    const name = values.support.trim().toLowerCase();
    const currentId = Number(selectedSupport.id_support);

    // Validación de duplicados
    const exists = supports.some(
      s =>
        s.support.trim().toLowerCase() === name &&
        s.id_support !== currentId
    );

    if (exists) {
      Swal.fire("Duplicado", "Ya existe un soporte con ese nombre", "warning");
      return;
    }

    try {
      if (modalMode === "add") {
        await api.post("/supports", values);
        Swal.fire("Guardado", "Soporte añadido correctamente", "success");
      } else {
        await api.put(`/supports/${selectedSupport.id_support}`, values);
        Swal.fire("Actualizado", "Soporte actualizado correctamente", "success");
      }

      showModal = false;
      fetchSupports();
    } catch (err) {
      Swal.fire("Error", "No se pudo guardar el soporte", "error");
    }
  }
</script>

<!-- -----------------------------------------------------
     LISTADO PRINCIPAL
------------------------------------------------------ -->
<div class="max-w-2xl  mt-6 px-4" >
  <h2 class="text-2xl font-bold mb-4">Gestión de Soportes</h2>

  <div class="flex justify-between items-center mb-4">

    <!-- Input con botón X -->
    <div class="relative w-1/3">
      <input
        type="text"
        placeholder="🔎 Filtrar..."
        class="input input-bordered w-full pr-10"
        bind:value={filter}
      />

      {#if filter}
        <button
          type="button"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
          onclick={() => (filter = "")}
        >
          ✖
        </button>
      {/if}
    </div>

    <button class="btn btn-success" onclick={handleAdd}>➕ Añadir Soporte</button>
  </div>

  {#if loading}
    <p class="text-center py-6">Cargando soportes...</p>
  {:else if errorMessage}
    <div class="alert alert-error">{errorMessage}</div>
  {:else}
    <div class="overflow-x-auto bg-base-100 rounded-xl shadow">
      <table class="table table-zebra w-full">
        <thead>
          <tr>
            <th>Acciones</th>
            <th>ID</th>
            <th>Nombre</th>
          </tr>
        </thead>
        <tbody>
          {#each supports.filter(s => s.support.toLowerCase().includes(filter.toLowerCase())) as s}
            <tr>
              <td>
                <div class="flex gap-2">
                  <button class="btn btn-info btn-xs" onclick={() => handleView(s)}>👁</button>
                  <button class="btn btn-warning btn-xs" onclick={() => handleEdit(s)}>✏️</button>
                  <button class="btn btn-error btn-xs" onclick={() => handleDelete(s.id_support, s.support)}>🗑</button>
                </div>
              </td>
              <td>{s.id_support}</td>
              <td>{s.support}</td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  {/if}

  <!-- -----------------------------------------------------
       MODAL
  ------------------------------------------------------ -->
  {#if showModal}
    <dialog class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
          {modalMode === "add"
            ? "Añadir Soporte"
            : modalMode === "edit"
            ? "Editar Soporte"
            : "Detalles del Soporte"}
        </h3>

        {#if modalMode === "view"}
          <input
            class="input input-bordered w-full mb-4"
            value={selectedSupport.support}
            readonly
            disabled
          />
        {:else}
          <form id="felte-form" use:form>
            <label class="label" for="support">Nombre del soporte</label>
            <input
              name="support"
              class="input input-bordered w-full mb-1"
              placeholder="Nombre del soporte"
            />

            {#if $errors.support}
              <p class="text-red-500 text-sm mb-2">{$errors.support[0]}</p>
            {/if}
          </form>
        {/if}

        <div class="modal-action">
          {#if modalMode !== "view"}
            <button class="btn btn-success" form="felte-form" type="submit">
              {modalMode === "add" ? "Guardar" : "Actualizar"}
            </button>
          {/if}

          <button class="btn" type="button" onclick={() => (showModal = false)}>
            Cerrar
          </button>
        </div>
      </div>
    </dialog>
  {/if}
</div>
