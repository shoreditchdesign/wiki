<?php snippet("head"); ?>
<?php snippet("header"); ?>

<?php
$serializedBlocks = [];
foreach ($page->blocks()->toBlocks() as $block) {
    $type = $block->type();
    if ($type === "b-wb-text") {
        $serializedBlocks[] = [
            "id" => $block->id(),
            "type" => $type,
            "data" => [
                "heading" => $block->heading()->value(),
                "headingLevel" => $block->heading_level()->or("h3")->value(),
                "body" => (string) $block->body()->kt(),
                "showInSidebar" => $block->show_in_sidebar()->toBool(),
                "anchorId" => $block->anchor_id()->value(),
            ],
        ];
        continue;
    }
    if ($type === "b-wb-table") {
        $rows = [];
        foreach ($block->rows()->toStructure() as $row) {
            $imageFile = $row->image()->toFile();
            $rows[] = [
                "image" => $imageFile?->url() ?? "",
                "imageAlt" => $row
                    ->image_alt()
                    ->or($imageFile?->alt())
                    ->value(),
                "text" => (string) $row->text()->kt(),
                "layout" => $row->layout()->or("image-left")->value(),
                "caption" => $row->caption()->value(),
                "rowCalloutVariant" => $row
                    ->row_callout_variant()
                    ->or("none")
                    ->value(),
            ];
        }
        $serializedBlocks[] = [
            "id" => $block->id(),
            "type" => $type,
            "data" => ["title" => $block->title()->value(), "rows" => $rows],
        ];
        continue;
    }
    if ($type === "b-wb-callout") {
        $serializedBlocks[] = [
            "id" => $block->id(),
            "type" => $type,
            "data" => [
                "variant" => $block->variant()->or("info")->value(),
                "title" => $block->title()->value(),
                "body" => (string) $block->body()->kt(),
            ],
        ];
        continue;
    }
    if ($type === "b-wb-image") {
        $imageFile = $block->image()->toFile();
        $serializedBlocks[] = [
            "id" => $block->id(),
            "type" => $type,
            "data" => [
                "image" => $imageFile?->url() ?? "",
                "alt" => $block->alt()->or($imageFile?->alt())->value(),
                "caption" => $block->caption()->value(),
                "fit" => $block->fit()->or("contain")->value(),
                "maxWidth" => $block->max_width()->or("lg")->value(),
            ],
        ];
        continue;
    }
    if ($type === "b-wb-divider") {
        $serializedBlocks[] = [
            "id" => $block->id(),
            "type" => $type,
            "data" => [
                "style" => $block->style()->or("line")->value(),
                "label" => $block->label()->value(),
                "spacing" => $block->spacing()->or("md")->value(),
            ],
        ];
    }
}
$props = [
    "pageTitle" => $page->doc_title()->or($page->title())->value(),
    "pageDescription" => (string) $page->doc_description()->kt(),
    "blocks" => $serializedBlocks,
];
$id = "webflow-doc-" . $page->id();
?>

<div class="page">
  <main class="main">
    <l-wb-docs id="<?= esc($id) ?>"></l-wb-docs>
    <script type="application/json" data-for="<?= esc($id) ?>">
      <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
    </script>
  </main>
  <?php snippet("footer"); ?>
</div>

<?php snippet("scripts"); ?>
