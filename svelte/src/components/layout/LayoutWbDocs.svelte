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

    function applyHeadingMeta(
        title: string,
        level: string,
        anchorId: string,
        fallbackId: string,
        counters: {
            h2: number;
            h3: number;
            h4: number;
            seenH2: boolean;
            seenH3: boolean;
        },
    ): { anchor: string; safeLevel: number; numberLabel: string } {
        let safeLevel = normalizeLevel(level);
        if (safeLevel === 3 && !counters.seenH2) safeLevel = 2;
        if (safeLevel === 4 && !counters.seenH3)
            safeLevel = counters.seenH2 ? 3 : 2;

        if (title) {
            if (safeLevel === 2) {
                counters.h2 += 1;
                counters.h3 = 0;
                counters.h4 = 0;
                counters.seenH2 = true;
                counters.seenH3 = false;
            } else if (safeLevel === 3) {
                counters.h3 += 1;
                counters.h4 = 0;
                counters.seenH3 = true;
            } else {
                counters.h4 += 1;
            }
        }

        const numberLabel =
            safeLevel === 2
                ? `${counters.h2}`
                : safeLevel === 3
                  ? `${counters.h2}.${counters.h3}`
                  : `${counters.h2}.${counters.h3}.${counters.h4}`;

        const anchorSource = anchorId || `${numberLabel}-${title || fallbackId}`;
        const anchor = slugify(anchorSource);

        return { anchor, safeLevel, numberLabel };
    }

    function buildModel(source: BlockInput[]): {
        blocks: EnhancedBlock[];
        sidebar: SidebarItem[];
    } {
        const counters = {
            h2: 0,
            h3: 0,
            h4: 0,
            seenH2: false,
            seenH3: false,
        };

        const enhanced: EnhancedBlock[] = [];
        const sidebar: SidebarItem[] = [];

        for (const block of source) {
            if (block.type !== "b-wb-text") {
                if (block.type === "b-wb-table") {
                    const rows = Array.isArray(block.data?.rows)
                        ? block.data.rows
                        : [];
                    const mappedRows = rows.map((row: any, index: number) => {
                        const rowTitle = String(row.title || "").trim();
                        const showInSidebar = Boolean(
                            row.showInSidebar ?? false,
                        );
                        const { anchor, safeLevel } = applyHeadingMeta(
                            rowTitle,
                            String(row.headingLevel || "h3"),
                            "",
                            `${block.id}-${index + 1}`,
                            counters,
                        );

                        if (rowTitle && showInSidebar) {
                            sidebar.push({
                                id: `${block.id}-${index + 1}`,
                                anchor,
                                depth: safeLevel,
                                title: rowTitle,
                            });
                        }

                        return {
                            ...row,
                            headingLevel: `h${safeLevel}`,
                            anchor,
                        };
                    });

                    enhanced.push({
                        ...block,
                        data: {
                            ...block.data,
                            rows: mappedRows,
                        },
                    });
                } else {
                    enhanced.push(block);
                }
                continue;
            }

            const heading = String(block.data.heading || "").trim();
            const showInSidebar = Boolean(block.data.showInSidebar ?? true);
            const { anchor, safeLevel, numberLabel } = applyHeadingMeta(
                heading,
                String(block.data.headingLevel || "h3"),
                String(block.data.anchorId || "").trim(),
                block.id,
                counters,
            );

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
            document.querySelectorAll<HTMLElement>(".wb-docs-content [id]"),
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

<div class="wb-docs">
    <aside class="wb-sidebar" aria-label="Table of contents">
        <p class="wb-sidebar-title">{pageTitle}</p>
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

    <div class="wb-main">
        <div class="wb-docs-inner">
            <div class="wb-docs-container">
                <div class="wb-docs-content">
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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .wb-docs {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    .wb-main {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 0;
    }

    .wb-docs-inner {
        flex: 1;
        display: flex;
    }

    .wb-docs-container {
        width: 100%;
        margin: 0 auto;
        padding: 4rem var(--space-4);
        box-sizing: border-box;
    }

    .wb-docs-content {
        width: 100%;
        max-width: var(--content-max);
        margin: 0 auto;
    }

    .wb-sidebar {
        position: sticky;
        top: 0;
        align-self: stretch;
        padding: 4rem 0.75rem 0.75rem;
        height: 100vh;
        overflow: auto;
        background: #fff;
        border: 1px dashed var(--color-border);
        border-radius: 2px;
        min-width: 16rem;
    }

    .wb-sidebar-title {
        margin: 0;
        color: var(--color-muted);
        font-size: 1.2rem;
        line-height: 1.4;
        font-weight: 400;
    }

    .wb-sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0;
    }

    .wb-sidebar li a {
        display: block;
        text-decoration: none;
        color: inherit;
        font-size: 0.8125rem;
        line-height: 1rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0;
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
        font-size: 0.75rem;
        font-weight: 400;
        color: var(--color-text);
        padding: 1.25rem 0.25rem;
    }

    .wb-sidebar li.depth-2 a.active {
        color: var(--color-text);
        background: transparent;
    }

    .wb-sidebar li.depth-3,
    .wb-sidebar li.depth-4 {
        margin-left: 0;
    }

    .wb-sidebar li.depth-3 a {
        color: var(--color-secondary-1);
        padding-left: 1.25rem;
        padding-top: 12px;
        padding-bottom: 12px;
    }

    .wb-sidebar li.depth-4 a {
        color: var(--color-secondary-2);
        padding: 0.75rem;
        margin: 0 0 0 1.125rem;
        border-left: 2px solid rgba(230, 230, 230, 0.25);
    }

    .wb-sidebar li a.active {
        color: #fc6f54;
        background: #fff6f4;
    }

    .wb-sidebar li.depth-4 a.active {
        border-left-color: transparent;
    }

    .wb-sidebar li.depth-3 {
        border-left: 2px solid #e6e6e6;
    }

    .wb-sidebar li.depth-4 {
        border-left: 2px solid #e6e6e6;
    }

    .wb-sidebar li:has(> a.active) {
        border-left-color: #fc6f54;
    }

    .wb-block-stack {
        display: grid;
        gap: 3rem;
    }

    @media (max-width: 980px) {
        .wb-sidebar {
            max-height: none;
            width: 100%;
            max-width: none;
            height: auto;
        }

        .wb-docs {
            flex-direction: column;
        }
    }
</style>
