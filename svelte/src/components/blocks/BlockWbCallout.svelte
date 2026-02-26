<script lang="ts">
    let { variant = "info", title = "", body = "" } = $props();

    function getTone(value: string): string {
        return ["info", "success", "warning", "danger"].includes(value)
            ? value
            : "info";
    }
</script>

<aside class={`wb-callout wb-callout-${getTone(variant)}`}>
    {#if title}
        <h4>{title}</h4>
    {/if}
    <div class="wb-callout-body">{@html body}</div>
</aside>

<style>
    .wb-callout {
        border: 1px solid transparent;
        border-radius: var(--radius-base);
        padding: 1.25rem 0.875rem;
    }

    .wb-callout h4 {
        margin: 0 0 0.5rem;
        font-weight: 500;
    }

    .wb-callout-body :global(p) {
        margin: 0;
        opacity: 0.6;
    }

    .wb-callout-info {
        background: #eaf1ff;
        border-color: #c6d8ff;
        color: #1f3e8a;
    }
    .wb-callout-success {
        background: #e9f8ef;
        border-color: #b7e5c8;
        color: #1f6a3a;
    }
    .wb-callout-warning {
        background: #fff6e7;
        border-color: #f2d39a;
        color: #7f5a12;
    }
    .wb-callout-danger {
        background: #feecec;
        border-color: #f0b3b3;
        color: #8f2929;
    }
</style>
