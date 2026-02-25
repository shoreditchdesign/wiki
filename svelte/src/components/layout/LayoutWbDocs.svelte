<script lang="ts">
    import { onMount } from "svelte";
    import BlockWbText from "../blocks/BlockWbText.svelte";
    import BlockWbTable from "../blocks/BlockWbTable.svelte";
    import BlockWbCallout from "../blocks/BlockWbCallout.svelte";
    import BlockWbImage from "../blocks/BlockWbImage.svelte";
    import BlockWbDivider from "../blocks/BlockWbDivider.svelte";

    type BlockType =
        | "b-wb-text"
        | "b-wb-table"
        | "b-wb-callout"
        | "b-wb-image"
        | "b-wb-divider";

    interface BlockInput {
        id: string;
        type: BlockType | string;
        data: Record<string, any>;
    }

    interface SidebarItem {
        id: string;
        anchor: string;
        depth: number;
        title: string;
    }

    interface EnhancedBlock extends BlockInput {
        headingMeta?: {
            numberLabel: string;
            anchor: string;
        };
    }

    let {
        pageTitle = "",
        pageDescription = "",
        blocks = [] as BlockInput[],
    } = $props();

    function slugify(input: string): string {
        return input
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, "")
            .replace(/\s+/g, "-")
            .replace(/-+/g, "-");
    }

    function normalizeLevel(level: string): number {
        if (level === "h2") return 2;
        if (level === "h4") return 4;
        return 3;
    }

    function buildModel(source: BlockInput[]): {
        blocks: EnhancedBlock[];
        sidebar: SidebarItem[];
    } {
        let h2 = 0;
        let h3 = 0;
        let h4 = 0;
        let seenH2 = false;
        let seenH3 = false;

        const enhanced: EnhancedBlock[] = [];
        const sidebar: SidebarItem[] = [];

        for (const block of source) {
            if (block.type !== "b-wb-text") {
                enhanced.push(block);
                continue;
            }

            const heading = String(block.data.heading || "").trim();
            const showInSidebar = Boolean(block.data.showInSidebar ?? true);
            const level = normalizeLevel(
                String(block.data.headingLevel || "h3"),
            );

            let safeLevel = level;
            if (safeLevel === 3 && !seenH2) safeLevel = 2;
            if (safeLevel === 4 && !seenH3) safeLevel = seenH2 ? 3 : 2;

            if (heading) {
                if (safeLevel === 2) {
                    h2 += 1;
                    h3 = 0;
                    h4 = 0;
                    seenH2 = true;
                    seenH3 = false;
                } else if (safeLevel === 3) {
                    h3 += 1;
                    h4 = 0;
                    seenH3 = true;
                } else {
                    h4 += 1;
                }
            }

            const numberLabel =
                safeLevel === 2
                    ? `${h2}`
                    : safeLevel === 3
                      ? `${h2}.${h3}`
                      : `${h2}.${h3}.${h4}`;

            const anchorSource =
                String(block.data.anchorId || "").trim() ||
                `${numberLabel}-${heading || block.id}`;
            const anchor = slugify(anchorSource);

            const blockOut: EnhancedBlock = {
                ...block,
                data: {
                    ...block.data,
                    headingLevel: `h${safeLevel}`,
                },
                headingMeta: {
                    numberLabel,
                    anchor,
                },
            };

            enhanced.push(blockOut);

            if (heading && showInSidebar) {
                sidebar.push({
                    id: block.id,
                    anchor,
                    depth: safeLevel,
                    title: heading,
                });
            }
        }

        return { blocks: enhanced, sidebar };
    }

    let model = $derived(buildModel(blocks));
    let activeAnchor = $state("");

    onMount(() => {
        const headingNodes = Array.from(
            document.querySelectorAll<HTMLElement>(".wb-content [id]"),
        ).filter((el) => model.sidebar.some((item) => item.anchor === el.id));

        if (headingNodes.length === 0) return;

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort(
                        (a, b) =>
                            a.boundingClientRect.top - b.boundingClientRect.top,
                    );

                if (visible.length > 0) {
                    activeAnchor = visible[0].target.id;
                }
            },
            {
                rootMargin: "-20% 0px -65% 0px",
                threshold: [0, 1],
            },
        );

        for (const node of headingNodes) observer.observe(node);

        if (!activeAnchor) {
            activeAnchor = headingNodes[0].id;
        }

        return () => observer.disconnect();
    });
</script>

<section class="wb-docs">
    <aside class="wb-sidebar" aria-label="Table of contents">
        <p class="wb-sidebar-title">On this page</p>
        <nav>
            <ul>
                {#each model.sidebar as item}
                    <li class={`depth-${item.depth}`}>
                        <a
                            href={`#${item.anchor}`}
                           
                            class:active={activeAnchor === item.anchor}
                        >
                            <span>{item.title}</span>
                        </a>
                    </li>
                {/each}
            </ul>
        </nav>
    </aside>

    <article class="wb-content">
        <header class="wb-doc-header">
            <h1>{pageTitle}</h1>
            {#if pageDescription}
                <div class="description">{@html pageDescription}</div>
            {/if}
        </header>

        <div class="wb-block-stack">
            {#each model.blocks as block}
                {#if block.type === "b-wb-text"}
                    <BlockWbText
                        heading={block.data.heading}
                        headingLevel={block.data.headingLevel}
                        body={block.data.body}
                        anchor={block.headingMeta?.anchor || ""}
                    />
                {:else if block.type === "b-wb-table"}
                    <BlockWbTable
                        title={block.data.title}
                        rows={block.data.rows || []}
                    />
                {:else if block.type === "b-wb-callout"}
                    <BlockWbCallout
                        variant={block.data.variant}
                        title={block.data.title}
                        body={block.data.body}
                    />
                {:else if block.type === "b-wb-image"}
                    <BlockWbImage
                        image={block.data.image}
                        alt={block.data.alt}
                        caption={block.data.caption}
                        fit={block.data.fit}
                        maxWidth={block.data.maxWidth}
                    />
                {:else if block.type === "b-wb-divider"}
                    <BlockWbDivider
                        style={block.data.style}
                        label={block.data.label}
                        spacing={block.data.spacing}
                    />
                {/if}
            {/each}
        </div>
    </article>
</section>

<style>
    .wb-docs {
        display: grid;
        grid-template-columns: minmax(14rem, 18rem) minmax(0, 1fr);
        gap: 2rem;
    }

    .wb-sidebar {
        position: sticky;
        top: 1rem;
        align-self: start;
        padding: 0.5rem 0;
        max-height: calc(100vh - 2rem);
        overflow: auto;
    }

    .wb-sidebar-title {
        margin: 0 0 0.5rem;
        color: var(--color-muted);
        font-size: 0.85rem;
        font-weight: 600;
    }

    .wb-sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0.25rem;
    }

    .wb-sidebar li a {
        display: block;
        text-decoration: none;
        color: inherit;
        font-size: 0.875rem;
        padding: 0.2rem 0.25rem;
        border-radius: 0.375rem;
        transition:
            color 120ms ease,
            background-color 120ms ease;
    }

    .wb-sidebar li {
        position: relative;
    }

    .wb-sidebar li.depth-2 a {
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--color-text);
        margin-top: 0.35rem;
    }

    .wb-sidebar li.depth-3,
    .wb-sidebar li.depth-4 {
        margin-left: 0.8rem;
    }

    .wb-sidebar li.depth-3::before,
    .wb-sidebar li.depth-4::before {
        content: "";
        position: absolute;
        left: -0.45rem;
        top: 0.2rem;
        bottom: 0.2rem;
        width: 1px;
        background: var(--color-border);
    }

    .wb-sidebar li.depth-3 a {
        color: var(--color-secondary-1);
    }

    .wb-sidebar li.depth-4 a {
        color: var(--color-secondary-2);
        margin-left: 0.65rem;
    }

    .wb-sidebar li a.active {
        color: var(--color-accent);
        background: #eef2ff;
    }

    .wb-content {
        min-width: 0;
        display: grid;
        gap: 1.5rem;
    }

    .wb-doc-header h1 {
        margin: 0;
    }

    .description :global(p) {
        margin: 0.5rem 0 0;
        color: var(--color-muted);
    }

    .wb-block-stack {
        display: grid;
        gap: 1rem;
    }

    @media (max-width: 980px) {
        .wb-docs {
            grid-template-columns: 1fr;
        }

        .wb-sidebar {
            position: static;
            max-height: none;
        }
    }
</style>
