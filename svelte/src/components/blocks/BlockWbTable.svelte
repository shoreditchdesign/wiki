<script lang="ts">
  interface TableRow {
    image?: string;
    imageAlt?: string;
    text?: string;
    layout?: 'image-left' | 'image-right' | string;
    caption?: string;
    rowCalloutVariant?: 'none' | 'info' | 'warning' | 'danger' | string;
  }

  let { title = '', rows = [] as TableRow[] } = $props();

  function rowClass(row: TableRow): string {
    const layout = row.layout === 'image-right' ? 'image-right' : 'image-left';
    const hasImage = !!row.image;
    const hasText = !!row.text;
    const spanClass = hasImage && hasText ? 'split' : 'single';
    const tone = row.rowCalloutVariant && row.rowCalloutVariant !== 'none' ? `tone-${row.rowCalloutVariant}` : '';
    return `${layout} ${spanClass} ${tone}`.trim();
  }
</script>

<section class="wb-table-block">
  {#if title}
    <h3 class="wb-table-title">{title}</h3>
  {/if}

  <div class="wb-table-rows">
    {#each rows as row}
      {@const hasImage = !!row.image}
      {@const hasText = !!row.text}
      {#if hasImage || hasText}
        <article class={`wb-table-row ${rowClass(row)}`}>
          {#if hasImage}
            <div class="wb-cell wb-cell-image">
              <img src={row.image} alt={row.imageAlt || ''} loading="lazy" />
            </div>
          {/if}

          {#if hasText}
            <div class="wb-cell wb-cell-text">
              <div class="wb-richtext">{@html row.text}</div>
            </div>
          {/if}

          {#if row.caption}
            <p class="wb-row-caption">{row.caption}</p>
          {/if}
        </article>
      {/if}
    {/each}
  </div>
</section>

<style>
  .wb-table-block {
    display: grid;
    gap: 0.75rem;
  }

  .wb-table-title {
    margin: 0;
  }

  .wb-table-rows {
    display: grid;
    gap: 0.75rem;
  }

  .wb-table-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
    border: 1px solid var(--color-border);
    border-radius: 0.5rem;
    padding: 0.75rem;
  }

  .wb-table-row.split {
    grid-template-columns: minmax(220px, 42%) 1fr;
  }

  .wb-table-row.image-right.split .wb-cell-image {
    order: 2;
  }

  .wb-table-row.image-right.split .wb-cell-text {
    order: 1;
  }

  .wb-cell-image img {
    width: 100%;
    height: auto;
    border-radius: 0.375rem;
    border: 1px solid var(--color-border);
  }

  .wb-row-caption {
    grid-column: 1 / -1;
    margin: 0;
    font-size: 0.85rem;
    color: var(--color-muted);
  }

  .wb-richtext :global(p) {
    margin: 0 0 0.75rem;
  }

  .wb-richtext :global(ul),
  .wb-richtext :global(ol) {
    margin: 0;
    padding-left: 1rem;
  }

  .tone-info { border-color: #9ac1ff; }
  .tone-warning { border-color: #f3c46f; }
  .tone-danger { border-color: #ee8f8f; }

  @media (max-width: 860px) {
    .wb-table-row.split {
      grid-template-columns: 1fr;
    }

    .wb-table-row.image-right.split .wb-cell-image,
    .wb-table-row.image-right.split .wb-cell-text {
      order: initial;
    }
  }
</style>
