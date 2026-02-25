<script lang="ts">
    let {
        heading = "",
        headingLevel = "h3",
        body = "",
        anchor = "",
    } = $props();

    function getLevel(level: string): string {
        return ["h2", "h3", "h4"].includes(level) ? level : "h3";
    }
</script>

<section class="wb-text-block" aria-labelledby={anchor || undefined}>
    {#if heading}
        <svelte:element
            this={getLevel(headingLevel)}
            id={anchor || undefined}
            class="wb-heading"
        >
            <span>{heading}</span>
        </svelte:element>
    {/if}

    {#if body}
        <div class="wb-body">{@html body}</div>
    {/if}
</section>

<style>
    .wb-text-block {
        display: grid;
        gap: 0.75rem;
    }

    .wb-heading {
        display: flex;
        margin: 0;
        line-height: 1.2;
    }

    :global(h2.wb-heading) {
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-size: 0.9rem;
    }

    .wb-body :global(p) {
        margin: 0 0 0.85rem;
    }

    .wb-body :global(ul),
    .wb-body :global(ol) {
        margin: 0 0 0.85rem;
        padding-left: 1.125rem;
    }
</style>
