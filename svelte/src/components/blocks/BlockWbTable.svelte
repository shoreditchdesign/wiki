<script lang="ts">
  interface TableRow {
    image?: string;
    imageAlt?: string;
    imageCaption?: string;
    title?: string;
    headingLevel?: 'h2' | 'h3' | 'h4' | string;
    anchor?: string;
    text?: string;
    layout?: 'image-left' | 'image-right' | string;
  }

  let {
    title = '',
    titleHeadingLevel = 'h3',
    titleAnchor = '',
    rows = [] as TableRow[],
  } = $props();

  function getHeadingLevel(level?: string): string {
    return level === 'h2' || level === 'h4' ? level : 'h3';
  }

  function rowClass(row: TableRow): string {
    const layout = row.layout === 'image-right' ? 'image-right' : 'image-left';
    const hasImage = !!row.image;
    const hasText = !!row.text;
    const spanClass = hasImage && hasText ? 'split' : 'single';
    return `${layout} ${spanClass}`.trim();
  }
</script>

<section class="wb-table-block">
  {#if title}
    <svelte:element
      this={getHeadingLevel(titleHeadingLevel)}
      id={titleAnchor || undefined}
      class="wb-table-title"
    >
      {title}
    </svelte:element>
  {/if}

  <div class="wb-table-rows">
    {#each rows as row}
      {@const hasImage = !!row.image}
      {@const hasText = !!row.text}
      {#if hasImage || hasText}
        <article class={`wb-table-row ${rowClass(row)}`}>
          {#if hasImage}
            <div class="wb-cell wb-cell-image">
              <figure class="wb-image-figure">
                <img src={row.image} alt={row.imageAlt || ''} loading="lazy" />
                {#if row.imageCaption}
                  <figcaption>{row.imageCaption}</figcaption>
                {/if}
              </figure>
            </div>
          {/if}

          {#if hasText}
            <div class="wb-cell wb-cell-text">
              <div class="wb-cell-stack">
                {#if row.title}
                  <svelte:element
                    this={getHeadingLevel(row.headingLevel)}
                    id={row.anchor || undefined}
                    class="wb-row-title"
                  >
                    <span>{row.title}</span>
                  </svelte:element>
                {/if}
                <div class="wb-richtext">{@html row.text}</div>
              </div>
            </div>
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
    font-weight: 500;
  }

  .wb-table-rows {
    display: grid;
    gap: 0;
    background: #fff;
    outline: 1px solid var(--color-border);
    outline-offset: -1px;
    border-radius: 4px;
    overflow: hidden;
  }

  .wb-table-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    outline: 1px solid var(--color-border);
    outline-offset: -1px;
    border-radius: 0;
    padding: 20px 16px;
    margin-top: -1px;
  }

  .wb-table-row.image-left.split {
    padding: 0;
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
    border-radius: 2px;
    border: 0;
  }

  .wb-image-figure {
    margin: 0;
    display: grid;
    gap: 0;
  }

  .wb-image-figure figcaption {
    margin: 16px 0 0;
    font-size: 14px;
    line-height: 1.4;
    color: var(--color-text-secondary);
  }

  .wb-cell-text {
    min-width: 0;
    outline: 1px solid var(--color-border);
    outline-offset: -1px;
  }

  .wb-table-row.image-left.split .wb-cell-text {
    padding: 20px 16px;
  }

  .wb-table-row.image-left.split .wb-cell-image {
    padding: 20px 16px;
  }

  .wb-cell-stack {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .wb-row-title {
    margin: 0;
    font-weight: 500;
  }

  .wb-richtext :global(p) {
    margin: 0 0 0.75rem;
    font-size: 14px;
    color: var(--color-text-secondary);
  }

  .wb-richtext :global(ul),
  .wb-richtext :global(ol) {
    margin: 0;
    padding-left: 1rem;
    font-size: 14px;
    color: var(--color-text-secondary);
  }


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
