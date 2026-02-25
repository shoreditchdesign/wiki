<script lang="ts">
    let {
        image = "",
        alt = "",
        caption = "",
        fit = "contain",
        maxWidth = "lg",
    } = $props();

    function getWidthClass(value: string): string {
        return ["sm", "md", "lg", "full"].includes(value) ? value : "lg";
    }

    function getFitClass(value: string): string {
        return value === "cover" ? "cover" : "contain";
    }
</script>

{#if image}
    <figure class={`wb-image wb-image-${getWidthClass(maxWidth)}`}>
        <img
            src={image}
            alt={alt || ""}
            class={getFitClass(fit)}
            loading="lazy"
        />
        {#if caption}
            <figcaption>{caption}</figcaption>
        {/if}
    </figure>
{/if}

<style>
    .wb-image {
        margin: 0;
        display: grid;
        gap: 0.5rem;
    }

    .wb-image-sm {
        max-width: 32rem;
    }
    .wb-image-md {
        max-width: 44rem;
    }
    .wb-image-lg {
        max-width: 56rem;
    }
    .wb-image-full {
        max-width: 100%;
    }

    .wb-image img {
        width: 100%;
        max-height: 34rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: #fff;
    }

    .wb-image img.contain {
        object-fit: contain;
    }
    .wb-image img.cover {
        object-fit: cover;
    }

    figcaption {
        font-size: 0.85rem;
        color: var(--color-muted);
    }
</style>
