<script>
  import { createForm } from "felte";
  import { validator } from "@felte/validator-zod";
  import { z } from "zod";

  const {
    open = false,
    mode = "view",  // "add" | "edit" | "view"
    movie = {},
    themes = [],
    supports = [],
    onSave,
    onClose
  } = $props();


  // ============================
  // ZOD: VALIDACIÓN
  // ============================
  const movieSchema = z.object({
    name: z.string().min(1, "El nombre es obligatorio"),
    price: z
      .string()
      .min(1, "El precio es obligatorio")
      .regex(/^\d+([.,]\d{1,2})?$/, "Formato de precio inválido"),
    date: z
      .string()
      .min(1, "La fecha es obligatoria")
      .refine((v) => !isNaN(Date.parse(v)), "Fecha inválida"),
    rating: z
      .string()
      .refine(v => ["1","2","3","4","5"].includes(v), "Valoración inválida")
      .transform(v => Number(v)),
    theme_id: z.string().min(1, "Tema obligatorio"),
    support_id: z.string().min(1, "Soporte obligatorio")
  });

  // ============================
  // FELTE
  // ============================
  const {
    form,
    errors,
    setInitialValues,
    setTouched
  } = createForm({
    initialValues: {
      name: "",
      price: "",
      date: "",
      rating: 1,
      theme_id: "",
      support_id: ""
    },
    extend: validator({ schema: movieSchema }),
    onSubmit: (values) => {
      if (onSave) onSave(values);
    }
  });

  // Sincronizar valores iniciales con la película seleccionada
  // ✅✅✅✅ Necesario para cargar los datos al abrir el modal en modo "edit" o "view"

  $effect.pre(() => {
    // console.log("Pre-efecto de sincronización ejecutado. open:", open, "mode:", mode, "movie:", movie);  
    if (open && mode !== "view" && movie) {
      setInitialValues(movie);
      setTouched({});
    }
  });


  function formatPrice(price) {
    return new Intl.NumberFormat("es-ES", {
      style: "currency",
      currency: "EUR",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(price);
  }
</script>

{#if open}
  <dialog class="modal modal-open">
    <div class="modal-box w-full">

      <h3 class="font-bold text-lg mb-4">
        {mode === "add"
          ? "Añadir Película"
          : mode === "edit"
          ? "Editar Película"
          : "Detalles de la Película"}
      </h3>

      <!-- FORMULARIO ADD/EDIT -->
      {#if mode !== "view"}

        <form id="felte-form" use:form class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div class="form-control">
            <label class="label" for="name">Nombre</label>
            <input name="name" id="name" class="input input-bordered" />
            {#if $errors.name}
              <p class="text-red-500 text-sm">{$errors.name[0]}</p>
            {/if}
          </div>

          <div class="form-control">
            <label class="label" for="price">Precio</label>
            <input name="price" class="input input-bordered" />
            {#if $errors.price}
              <p class="text-red-500 text-sm">{$errors.price[0]}</p>
            {/if}
          </div>

          <div class="form-control">
            <label class="label" for="date">Fecha</label>
            <input name="date" id="date" type="date" class="input input-bordered" />
            {#if $errors.date}
              <p class="text-red-500 text-sm">{$errors.date[0]}</p>
            {/if}
          </div>

          <div class="form-control">
            <label class="label" for="rating">Valoración</label>
            <select name="rating" id="rating" class="select select-bordered">
              {#each [1, 2, 3, 4, 5] as r}
                <option value={r}>{r}</option>
              {/each}
            </select>
            {#if $errors.rating}
              <p class="text-red-500 text-sm">{$errors.rating[0]}</p>
            {/if}
          </div>

          <div class="form-control">
            <label class="label" for="theme_id">Tema</label>
            <select name="theme_id" class="select select-bordered">
              <option value="">-- Selecciona tema --</option>
              {#each themes as t}
                <option value={String(t.id_theme)}>{t.theme}</option>
              {/each}
            </select>
            {#if $errors.theme_id}
              <p class="text-red-500 text-sm">{$errors.theme_id[0]}</p>
            {/if}
          </div>

          <div class="form-control">
            <label class="label" for="support_id">Soporte</label>
            <select name="support_id" id="support_id" class="select select-bordered">
              <option value="">-- Selecciona soporte --</option>
              {#each supports as s}
                <option value={String(s.id_support)}>{s.support}</option>
              {/each}
            </select>
            {#if $errors.support_id}
              <p class="text-red-500 text-sm">{$errors.support_id[0]}</p>
            {/if}
          </div>

        </form>


        <div class="modal-action  flex justify-end gap-2 mt-4">
          <button class="btn btn-primary" type="submit" form="felte-form">
            {mode === "add" ? "Guardar" : "Actualizar"}
          </button>
          <button class="btn" type="button" onclick={onClose}>Cancelar</button>
        </div>

      {:else}
        <!-- MODO SOLO LECTURA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div class="form-control">
            <label class="font-semibold" for="selectedMovieName">Nombre</label>
            <input class="input input-bordered w-full" value={movie.name} readonly disabled />
          </div>

          <div class="form-control">
            <label class="font-semibold" for="selectedMoviePrice">Precio</label>
            <input class="input input-bordered w-full" value={formatPrice(movie.price)} readonly disabled />
          </div>

          <div>
            <label class="font-semibold" for="selectedMovieDate">Fecha</label>
            <input class="input input-bordered w-full" value={movie.dateText} readonly disabled />
          </div>

          <div class="form-control">
            <label class="font-semibold" for="selectedMovieRating">Valoración</label>
            <div class=" input input-bordered 	bg-star w-full">
              <div class="flex gap-1 text-yellow-400 mt-1">
                {#each Array(5) as _, i}
                  <span>{i < movie.rating ? "★" : "☆"}</span>
                {/each}
              </div>
            </div>
          </div>

          <div class="form-control">
            <label class="font-semibold" for="selectedMovieTheme">Tema</label>
            <input
              class="input input-bordered w-full"
              value={themes.find(t => t.id_theme == movie.theme_id)?.theme}
              readonly
              disabled
            />
          </div>

          <div class="form-control">
            <label class="font-semibold" for="selectedMovieSupport">Soporte</label>
            <input
              class="input input-bordered w-full"
              value={supports.find(s => s.id_support == movie.support_id)?.support}
              readonly
              disabled
            />
          </div>
      </div>
      <div class="modal-action  flex justify-end gap-2 mt-4">
        <button class="btn" type="button" onclick={onClose}>Cerrar</button>
      </div>

        
      {/if}

    </div>
  </dialog>
{/if}

<style>
  input[disabled] {
    background-color: #eef2f6 !important; /* un gris muy claro */
    color: #1f2937; /* texto gris oscuro para buena lectura */
    opacity: 1 !important; /* evita que DaisyUI lo haga más tenue */
  }
  .bg-star {
    background-color: #eef2f6; /* un gris muy claro */
    border:#e7e9eb
  }

</style>

