<script>
  let selected = "corporate"; // valor inicial visible en el select

  const daisyThemes = [
    "light", "dark", "cupcake", "bumblebee", "emerald", "corporate",
    "synthwave", "retro", "cyberpunk", "valentine", "halloween", "garden",
    "forest", "aqua", "lofi", "pastel", "fantasy", "wireframe", "black",
    "luxury", "dracula", "cmyk", "autumn", "business", "acid", "lemonade",
    "night", "coffee", "winter"
  ];

  function applyTheme() {
    document.documentElement.setAttribute("data-theme", selected);
  }
</script>

<!-- Tooltip forzado hacia abajo para que siempre se vea -->
<!--<div class="tooltip tooltip-bottom  w-full" data-tip="Selecciona un tema DaisyUI"> -->
  <select
    class="select  select-sm w-30 text-base-content min-w-0"
    bind:value={selected}
    on:change={applyTheme}
  >
    {#each daisyThemes as theme}
      <option value={theme}>{theme}</option>
    {/each}
  </select>
<!--</div> -->
