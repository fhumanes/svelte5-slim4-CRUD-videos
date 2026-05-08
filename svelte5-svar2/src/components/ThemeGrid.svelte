<script>
  import { onMount } from 'svelte';
  import { ModalArea } from "wx-svelte-core";
  import { Grid } from 'wx-svelte-grid';
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow
  import { Editor } from 'wx-svelte-editor';
  import { ContextMenu } from 'wx-svelte-menu';
  import { registerEditorItem } from "wx-svelte-editor";
  import { Text } from "wx-svelte-core";

  import { es as coreEs } from "wx-core-locales";
  import { default as gridEs} from "../locales/esGrid.js";
  import { default as editorEs } from "../locales/esEditor.js";

  import Toasts from "../notification/Toasts.svelte";
  import { addToast } from "../notification/store";
  import api from '../lib/api.js';

  let gridRef = $state();
  let selected = $state([]);
  let themes = $state([]);
  let loading = $state(true);

  let showEditor = $state(false);
  let editorValues = $state({});
  let editorMode = $state("add");
  let showDeleteConfirm = $state(false);
  let itemToDelete = $state(null);

  registerEditorItem("text", Text);

  let editorItems = $derived([
    { key: "theme", label: "Tema", comp: "text", required: true, maxLength: 30 }
  ]);

  async function fetchAllThemes() {
    try {
      const res = await api.get('/themes');
      themes = res.data.map(t => ({
        id: t.id_theme,
        theme: t.theme
      }));
      /*
      addToast({
        message:'Temas cargados correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
      });
      */
    } catch (err) {
      console.error('Error al cargar temas:', err);
      addToast({
        message: 'No se pudieron cargar los temas. Por favor, intente de nuevo más tarde.',
        type: 'error',
        dismissible: true,
        timeout: 0
      });
    } finally {
      loading = false;
    }
  }

  function transformThemePayload(values) {
    const { theme } = values;
    return { theme };
  }

  async function handleEditorAction({ item, values }) {
    const payload = transformThemePayload(values);
    try {
      if (item.id === "save") {
        if (editorMode === "add") {
          const res = await api.post("/themes", payload);
          values.id = res.data.id_theme ?? Date.now();
          addToast({
            message:'Tema añadido correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });
          fetchAllThemes();
        } else if (editorMode === "edit") {
          await api.put(`/themes/${values.id}`, payload);
          addToast({
            message:'Tema actualizado correctamente' ,
            type: 'success',
            dismissible: true,
            timeout: 2000
          });
          fetchAllThemes();
        }
        showEditor = false;
      } else if (item.id === "close" || item.id === "cancel") {
        showEditor = false;
      }
    } catch (err) {
      console.error("Error en operación de editor:", err);
      addToast({
        message: "No se pudo completar la operación.",
        type: 'error',
        dismissible: true,
        timeout: 0
      });
    }
  }

  function confirmDelete(item) {
    itemToDelete = item;
    showDeleteConfirm = true;
  }

  async function proceedDelete() {
    try {
      await api.delete(`/themes/${itemToDelete.id}`);
      themes = themes.filter(t => t.id !== itemToDelete.id);
      addToast({
        message:'Tema eliminado correctamente' ,
        type: 'success',
        dismissible: true,
        timeout: 2000
      });
    } catch (err) {
      console.error("Error al eliminar tema:", err);
      addToast({
        message: "No se pudo eliminar el tema.",
        type: 'error',
        dismissible: true,
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
        if (id) {
          const themeItem = themes.find(t => t.id === id);
          if (themeItem) {
            confirmDelete(themeItem);
          }
        }
        break;
      default:
        console.warn("Acción no reconocida:", ev.action);
        break;
    }
  }

  function resolver(id) {
    if (id) gridRef.exec("select-row", { id });
    return id;
  }

  onMount(fetchAllThemes);

  function clearFilters() {
    gridRef.exec("filter-rows", {});
  }

  function updateSelected() {
    selected = gridRef.getState().selectedRows;
  }

  const columnStyle = col => {
    if (col.id === "id") return "text-right";
    return "";
  };

  const columns = $derived([
    { id: "id", header: "ID", width: 60, sort: true, resize: true },
    {
      id: "theme",
      header: [
        "Tema",
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
  ]);
</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>📚 Gestión de Temas</h3>
  <div class="d-flex gap-2">
    <Button type="primary" text="🧹 Borrar filtros" onclick={clearFilters} />
    <Button type="primary" text="➕ Agregar tema" onclick={() => {
      editorMode = "add";
      editorValues = {};
      showEditor = true;
    }} />
  </div>
  <div>Para el acceso a las acciones CRUD, botón derecho en el Grid</div>
</div>

{#if loading}
  <p class="text-center">Cargando temas...</p>
{:else}
  <ContextMenu
    options={contextOptions}
    onclick={handleContext}
    at="point"
    resolver={resolver}
    api={gridRef}
  >
    <Locale words={{ ...gridEs, ...coreEs }}>
    <div style="height: 400px; max-width: 500px;">
      <Grid
        bind:this={gridRef}
        data={themes}
        {columns}
        {columnStyle}
        pager={false}
        onselectrow={updateSelected}
      />
    </div>
    </Locale>
  </ContextMenu>
{/if}

{#if showEditor}
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            header={true}
            placement="sidebar"
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

{#if showDeleteConfirm}
  <ModalArea>
    <div class="modal-content">
      <h3>¿Eliminar este tema?</h3>
      <p>Tema: <b>{itemToDelete?.theme}</b></p>
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
</style>
