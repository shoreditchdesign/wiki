<script lang="ts">
    let { style = "line", label = "", spacing = "md" } = $props();

    function getGapClass(value: string): string {
        return ["sm", "md", "lg"].includes(value) ? value : "md";
    }

    function getMode(value: string): string {
        return ["line", "space", "line-label"].includes(value) ? value : "line";
    }
</script>

<div
    class={`wb-divider wb-divider-${getMode(style)} wb-divider-gap-${getGapClass(spacing)}`}
    aria-hidden="true"
>
    {#if getMode(style) === "line"}
        <hr />
    {/if}

    {#if getMode(style) === "line-label"}
        <div class="label-wrap">
            <hr />
            <span>{label}</span>
            <hr />
        </div>
    {/if}
</div>

<style>
    .wb-divider-gap-sm {
        padding: 0.5rem 0;
    }
    .wb-divider-gap-md {
        padding: 1rem 0;
    }
    .wb-divider-gap-lg {
        padding: 1.5rem 0;
    }

    .wb-divider-space {
        min-height: 0.75rem;
    }

    hr {
        border: 0;
        border-top: 1px solid var(--color-border);
        margin: 0;
    }

    .label-wrap {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
        gap: 0.75rem;
    }

    .label-wrap span {
        font-size: 0.75rem;
        color: var(--color-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
</style>
