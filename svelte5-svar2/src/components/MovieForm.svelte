<script>
  import { Button, Locale } from 'wx-svelte-core'; // Eliminamos Willow de aquí
  import { Editor } from 'wx-svelte-editor';
  import { registerEditorItem } from "wx-svelte-editor";
  import { Text, Select, DatePicker, RichSelect } from "wx-svelte-core";
  import { es as coreEs } from "wx-core-locales";
  import { default as editorEs } from "../locales/esEditor.js"

  // Sistema de notificación de las acciones
  import Toasts from "../notification/Toasts.svelte"; // Se mantiene porque es para notificaciones específicas
  import { addToast } from "../notification/store.js";

  import api from '../lib/api.js';                  // Importación de la API para las peticiones
  import { toValidAPIDate, createDateFromMySQL, formatDateToDDMMYYYY } from '../lib/utils.js'; // Para formatear fechas para MySQL
  
  // Parámetros del DataGrid
  // El orden en $props() NO importa para recibir
  let { editorMode, editorValues, movies, themes,supports , handleSave, closeEdit } = $props();

  // Definiciones del Editor -------------------------------------------------------------------------------------------------------------

  registerEditorItem("text", Text);
  registerEditorItem("number", Text);
  registerEditorItem("select", RichSelect);
  registerEditorItem("datepicker", DatePicker);
  
  let updated = false; // Para controlar si se ha actualizado el formulario

  const bottomBarView={ // Definición del bottomBar para modo view
					items: [
						{
							comp: "button",
							type: "primary",
							text: "Cerrar",
							id: "close",
						},
					],
				};

    const bottomBarEdit={ // Definición del bottomBar para modo add/edit
					items: [
						{
							comp: "button",
							type: "primary",
							text: "Guardar",
							id: "save",
						},
            						{
							comp: "button",
							type: "normal",
							text: "Cancelar",
							id: "cancel",
						},
					],
				};

  let editorItems = [
    { key: "name", label: "Nombre", comp: "text", required: true, maxLength: 40 },
    { key: "price", label: "Precio", comp: "number", required: true,
       validation: val => {
          const regEx = /^\d{1,5}(\.\d{1,2})?$/;
          return val && regEx.test(val);
        },
        validationMessage: "Introduce un número válido (máx. 5 enteros y 2 decimales)" },
    { key: "startDate", label: "Fecha", comp: "datepicker", required: true },
    { key: "rating",
      label: "Valoración",
      comp: "select",
      options: [1, 2, 3, 4, 5].map(n => ({ id: n, label: `${n} estrellas` })),
      required: true
    },
    { key: "theme_id",
      label: "Tema",
      comp: "select",
      options: themes.map(t => ({
            id: t.id_theme,
            label: t.theme
          })),
      required: true
    },
    {key: "support_id",
      label: "Soporte",
      comp: "select",
      options: supports.map(s => ({
            id: s.id_support,
            label: s.support
          })),
      required: true
    }
  ];

// Manejo de acciones del editor
  function onchange(ev) {
    //console.log(`field ${ev.key} was changed to ${ev.value}`);
    // console.log("all not saved changes", ev.update);
    updated = true; // Marcamos que se ha actualizado el formulario
  }

	function handleClick( ev) {
		// need to check that there are changes and close editor after successful validation
		// otherwise, even if "save" event is not triggered, editor will be closed anyway
		// but we still can close editor if there are not any changes
    // console.log ("handleClick ev: ", ev);

    const changes = ev.changes;
    const values = ev.values;
    const item = ev.item;

		if (item.id === "save" && changes.length === 0 && Object.keys(values).length == 0  && editorMode == "add") {

        addToast({
            message:'Se deben completar los datos del formlario o cliquear Cancelar' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
          });
        console.warn("ADD - No hay cambios para guardar.");
    }

    if (item.id === "save" && updated === false && editorMode == "edit") {

        addToast({
            message:'Se deben modificar algún cambio o cliquear Cancelar' ,
            type: 'error',
            dismissible: true,
            timeout: 4000
          });
        console.warn("EDIT - No hay cambios para guardar.");
    }

		if (item.id === "cancel" || item.id === "close") closeEdit();
	}


</script>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="formHeader">
    <h3>🎬 Gestión de Películas</h3>
  </div>
  
  <div class="variations">
    <div>
      <div class="bg">
        <Locale words={{ ...editorEs, ...coreEs }}>
          <Editor
            focus={true}
            placement="inline"
            bottomBar = {editorMode === "view"?bottomBarView:bottomBarEdit}
            layout="default"
            autoSave={false}
            readonly={editorMode === "view"}
            items={editorItems}
            values={editorValues}
            onaction={handleClick}
		        onsave={handleSave}
            onchange={onchange}
          />
        </Locale>
      </div>
    </div>
  </div>
</div>

<style>
  .formHeader {
    text-align: center;
  }
  .variations .bg {
    background: var(--slate3);
    border-radius: 8px;
    padding: 8px;
    display: inline-block;
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

</style>