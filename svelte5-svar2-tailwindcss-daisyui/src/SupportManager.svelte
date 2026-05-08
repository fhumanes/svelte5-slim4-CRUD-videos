<script>
  import { onMount } from 'svelte';
  import { ModalArea, Button, Locale, Text } from "wx-svelte-core";
  import { Grid } from 'wx-svelte-grid';
  import { Editor, registerEditorItem } from 'wx-svelte-editor';
  import { ContextMenu } from '@svar-ui/svelte-menu';

  import gridEs from "./locales/esGrid.js";
  import editorEs from "./locales/esEditor.js";
  import { es as coreEs } from "@svar-ui/core-locales";

  import Toasts from "./notification/Toasts.svelte";
  import { addToast } from "./notification/store.js";

  import api from './lib/api.js';
  import { handleFatalError } from './lib/errorHandlers.js';

  // =========================
  // Estado
  // =========================
  let gridRef;
  let records = [];
  let loading = true;

  let showEditor = false;
  let editorValues = {};
  let editorMode = "add";

  let showDeleteConfirm = false;
  let itemToDelete = null;

  // =========================
  // Editor Items
  // =========================
  registerEditorItem("text", Text);

  let editorItems = [
    { key: "support", label: "Soporte", comp: "text", required: true, maxLength: 100 }
  ];

  // =========================
  // Cargar datos
  // =========================
  onMount(() => {
    fetchAllRecords();
  });

  async function fetchAllRecords() {
    try {
      const res = await api.get(`/supports`);
      const array = res.data;

      // Normalizamos id_support → id
      records = array.map(({ id_support: id, ...rest }) => ({
        id,
        ...rest
      }));

    } catch (err) {
      return handleFatalError({ err });
    } finally {
      loading = false;
    }
  }

  // =========================
  // CRUD
  // =========================
  function transformPayload(values) {
    return { support: values.support };
  }

  async function handleEditorAction({ item, values }) {
    const payload = transformPayload(values);

    try {
      if (item.id === "save") {
        if (editorMode === "add") {
          const res = await api.post(`/supports`, payload);
          values.id = res.data.id_support ?? Date.now();

          addToast({
            message: 'Soporte añadido correctamente',
            type: 'success',
            timeout: 2000
          });

        } else if (editorMode === "edit") {
          await api.put(`/supports/${values.id}`, payload);

          addToast({
            message: 'Soporte actualizado correctamente',
            type: 'success',
            timeout: 2000
          });
        }

        showEditor = false;
        fetchAllRecords();
      }

      if (item.id === "close" || item.id === "cancel") {
        showEditor = false;
      }

    } catch (err) {
      return handleFatalError({ err });
    }
  }

  function confirmDelete(item) {
    itemToDelete = item;
    showDeleteConfirm = true;
  }

  async function proceedDelete() {
    try {
      await api.delete(`/supports/${itemToDelete.id}`);
      records = records.filter(t => t.id !== itemToDelete.id);

      addToast({
        message: 'Soporte eliminado correctamente',
        type: 'success',
        timeout: 2000
      });

    } catch (err) {
      addToast({
        message: "No se pudo eliminar el soporte.",
        type: 'error',
        timeout: 0
      });
    } finally {
      showDeleteConfirm = false;
      itemToDelete = null;
    }
  }

  function cancelDelete() {
    showDeleteConfirm = false;
    itemToDelete = null;
  }

  // =========================
  // Grid + Context Menu
  // =========================
  function resolver(id) {
    if (id) gridRef.exec("select-row", { id });
    return id;
  }

  function clearFilters() {
    gridRef.exec("filter-rows", {});
  }

  function updateSelected() {}

  const columns = [
    { id: "id", header: "ID", width: 60, sort: true, resize: true },
    {
      id: "support",
      header: [
        "Soporte",
        {
          filter: {
            type: "text",
            config: { icon: "wxi-search", clear: true }
          }
        }
      ],
      flexgrow: 1,
      sort: true,
      resize: true
    }
  ];

  const contextOptions = [
    { id: "add", text: "Agregar", icon: "wxi-plus" },
    { id: "edit", text: "Editar", icon: "wxi-edit" },
    { id: "delete", text: "Eliminar", icon: "wxi-delete-outline" }
  ];

  function handleContext(ev) {
    const id = gridRef.getState().selectedRows[0];
    const row = id ? gridRef.getRow(id) : null;

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

      case "delete":
        if (row) confirmDelete(row);
        break;
    }
  }

  // =========================
  // Bottom Bars
  // =========================
  const bottomBarView = {
    items: [
      { comp: "button", type: "primary", text: "Cerrar", id: "close" }
    ]
  };

  const bottomBarEdit = {
    items: [
      { comp: "button", type: "primary", text: "Guardar", id: "save" },
      { comp: "button", type: "normal", text: "Cancelar", id: "cancel" }
    ]
  };
</script>

<!-- ========================= -->
<!-- Encabezado -->
<!-- ========================= -->
<Toasts />
<div class="flex justify-between items-center mb-4 ">
  <h2 class="text-xl font-semibold">Gestión de Soportes</h2>

  <div class="flex gap-2">
    <button class="btn btn-primary" on:click={clearFilters}>🧹 Borrar filtros</button>
    <button class="btn btn-secondary" 
      on:click={() => {
        editorMode = "add";
        editorValues = {};
        showEditor = true;
      }}>➕ Añadir Soporte</button>
  </div>
</div>

<!-- ========================= -->
<!-- GRID -->
<!-- ========================= -->
{#if loading}
  <p class="text-center">Cargando soportes...</p>
{:else}
  <div class="border-2 border-primary shadow-xl shadow-gray-400 max-w-100 pr-1">

    <ContextMenu
      options={contextOptions}
      onclick={handleContext}
      at={"point"}
      resolver={resolver}
      api={gridRef}
    >
      <Locale words={{ ...gridEs, ...coreEs }}>
        <Grid
          bind:this={gridRef}
          data={records}
          {columns}
          pager={false}
          onselectrow={updateSelected}
          class="w-60"
        />
      </Locale>
    </ContextMenu>

  </div>
{/if}

<!-- ========================= -->
<!-- EDITOR LATERAL -->
<!-- ========================= -->
{#if showEditor}
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            placement="sidebar"
            bottomBar={editorMode === "view" ? bottomBarView : bottomBarEdit}
            layout="default"
            autoSave={false}
            readonly={editorMode === "view"}
            items={editorItems}
            values={editorValues}
            onaction={handleEditorAction}
          />
        </Locale>
      </div>
    </div>
  </div>
{/if}

<!-- ========================= -->
<!-- CONFIRMACIÓN DELETE -->
<!-- ========================= -->
{#if showDeleteConfirm}
  <ModalArea>
    <div class="modal-content">
      <h3>¿Eliminar este soporte?</h3>
      <p>Soporte: <b>{itemToDelete?.support}</b></p>

      <div class="actions">
        <Button type="danger" onclick={proceedDelete}>Confirmar</Button>
        <Button onclick={cancelDelete} color="secondary">Cancelar</Button>
      </div>
    </div>
  </ModalArea>
{/if}

<style>
  .variations {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
  }
  .variations > div {
    margin: 0 20px 60px 20px;
    width: 400px;
  }
  .bg {
    border-top: 1px solid #ccc;
    padding: 10px;
    height: 100%;
    width: 300px;
  }
  :global(.wx-sidearea) {
    width: 300px;
  }
  .modal-content {
    padding: 30px;
    text-align: center;
    background-color: #f1f5f9;
    border: 2px solid #f97316;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    max-width: 400px;
    margin: 0 auto;
  }
  .actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
  }
</style>
