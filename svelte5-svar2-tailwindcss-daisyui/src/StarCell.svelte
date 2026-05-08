<script>
    let { row } = $props();
    const stars = Array.from({ length: 5 }, (_, i) =>
        i < row.rating ? "★" : "★"  // "☆"
    );
</script>

<div class="star-cell">
    {#each stars as s, i}
        <span class={i < row.rating ? "filled" : "empty"}>{s}</span>
    {/each}
</div>

<style>
    .star-cell {
        text-align: center;
        font-size: 1.5em;
    }
    .filled {
        color: gold;
    }
    .empty {
        color: lightgray;
    }
</style>
