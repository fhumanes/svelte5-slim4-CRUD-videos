<script>
  import { onMount } from "svelte";
  import Swal from "sweetalert2";
  import api from "./lib/api.js";

  import { createForm } from "felte";
  import { validator } from "@felte/validator-zod";
  import { z } from "zod";

  let themes = [];
  let loading = true;
  let errorMessage = null;

  let filter = "";
  let showModal = false;
  let modalMode = "add";
  let selectedTheme = {};

  const themeSchema = z.object({
    theme: z.string().min(1, "El nombre es obligatorio")
  });


  async function fetchThemes() {
    loading = true;
    try {
      const res = await api.get("/themes");
      themes = res.data;
    } catch (err) {
      errorMessage = "No se pudieron cargar los temas.";
    } finally {
      loading = false;
    }
  }

  onMount(fetchThemes);

  // -----------------------------------------------------
  //  FELTE: inicialización (solo una vez)
  // -----------------------------------------------------
  /*
  function buildSchema() {
    const existingNames = themes
      .map(t => t.theme.trim().toLowerCase())
      .filter(name => name !== (selectedTheme.theme ?? "").trim().toLowerCase());

    return z.object({
      theme: z
        .string()
        .min(1, "El nombre es obligatorio")
        .refine(
          val => !existingNames.includes(val.trim().toLowerCase()),
          "Ya existe un tema con ese nombre"
        )
    });
  }
  */
  const { form, errors, data, setInitialValues, setTouched } = createForm({
    initialValues: { theme: "" },
    extend: validator({ schema: themeSchema }),
    onSubmit: saveTheme
  });

  // -----------------------------------------------------
  //  Acciones del modal
  // -----------------------------------------------------
  function handleAdd() {
    modalMode = "add";
    selectedTheme = { id_theme: null, theme: "" };

    setInitialValues({ theme: "" });
    setTouched({}); // fuerza recalcular schema

    showModal = true;
  }

  function handleEdit(item) {
    modalMode = "edit";
    selectedTheme = { ...item };

    setInitialValues({ theme: item.theme });
    setTouched({}); // fuerza recalcular schema

    showModal = true;
  }


  function handleView(item) {
    modalMode = "view";
    selectedTheme = { ...item };
    showModal = true;
  }

  async function handleDelete(id, name) {
    const result = await Swal.fire({
      title: "¿Eliminar tema?",
      text: `Se eliminará "${name}".`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Eliminar",
      cancelButtonText: "Cancelar"
    });

    if (!result.isConfirmed) return;

    try {
      await api.delete(`/themes/${id}`);
      Swal.fire("Eliminado", "Tema eliminado correctamente", "success");
      fetchThemes();
    } catch (err) {
      Swal.fire("Error", "No se pudo eliminar el tema", "error");
    }
  }

  // -----------------------------------------------------
  //  Guardar (Felte llama a esta función)
  // -----------------------------------------------------
  async function saveTheme(values) {
    // El schema ya validó, pero por seguridad lo volvemos a validar aquí
    const name = values.theme.trim().toLowerCase();

    const currentId = Number(selectedTheme.id_theme);

    const exists = themes.some(
      t =>
        t.theme.trim().toLowerCase() === name &&
        t.id_theme !== currentId
    );

    if (exists) {
      Swal.fire("Duplicado", "Ya existe un tema con ese nombre", "warning");
      return;
    }

    try {
      if (modalMode === "add") {
        await api.post("/themes", values);
        Swal.fire("Guardado", "Tema añadido correctamente", "success");
      } else {
        await api.put(`/themes/${selectedTheme.id_theme}`, values);
        Swal.fire("Actualizado", "Tema actualizado correctamente", "success");
      }

      showModal = false;
      fetchThemes();
    } catch (err) {
      Swal.fire("No actualizado", "El tema no ha sido actualizado", "info");
    }
  }
</script>

<!-- LISTADO -->
<div class="max-w-2xl mt-6 px-4">
  <h2 class="text-2xl font-bold mb-4">Gestión de Temas</h2>

  <div class="flex justify-between items-center mb-4">
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

    <button class="btn btn-success" onclick={handleAdd}>➕ Añadir Tema</button>

  </div>

  {#if loading}
    <p class="text-center py-6">Cargando temas...</p>
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
          {#each themes.filter(t => t.theme.toLowerCase().includes(filter.toLowerCase())) as t}
            <tr>
              <td>
                <div class="flex gap-2">
                  <button class="btn btn-info btn-xs" onclick={() => handleView(t)}>👁</button>
                  <button class="btn btn-warning btn-xs" onclick={() => handleEdit(t)}>✏️</button>
                  <button class="btn btn-error btn-xs" onclick={() => handleDelete(t.id_theme, t.theme)}>🗑</button>
                </div>
              </td>
              <td>{t.id_theme}</td>
              <td>{t.theme}</td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  {/if}

  <!-- MODAL -->
  {#if showModal}
    <dialog class="modal modal-open">
      <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">
          {modalMode === "add"
            ? "Añadir Tema"
            : modalMode === "edit"
            ? "Editar Tema"
            : "Detalles del Tema"}
        </h3>

        {#if modalMode === "view"}
          <input
            class="input input-bordered w-full mb-4"
            value={selectedTheme.theme}
            readonly
            disabled
          />
        {:else}
          <form id="felte-form" use:form>
            <label class="label" for="theme">Nombre del tema</label>
            <input
              name="theme"
              class="input input-bordered w-full mb-1"
              placeholder="Nombre del tema"
            />

            {#if $errors.theme}
              <p class="text-red-500 text-sm mb-2">{$errors.theme[0]}</p>
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
<style>
  input[disabled] {
    background-color: #e7e9eb !important; /* un gris muy claro */
    color: #1f2937; /* texto gris oscuro para buena lectura */
    opacity: 1 !important; /* evita que DaisyUI lo haga más tenue */
  }
</style>
