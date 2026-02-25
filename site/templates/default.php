<?php snippet('head'); ?>
<?php snippet('header'); ?>

<?php
$id = 'default-page';
$props = [
    'title' => $page->title()->value(),
    'heading' => strip_tags((string) $page->page_heading()->kti(), '<em><i><strong><b>'),
    'body' => (string) $page->body()->kt(),
];
?>

<div class="page">
  <main class="main">
    <l-default id="<?= $id ?>"></l-default>
    <script type="application/json" data-for="<?= $id ?>">
      <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
    </script>

    <?php foreach ($page->blocks()->toBlocks() as $block): ?>
      <?php snippet('blocks/' . $block->type(), ['block' => $block, 'blockNamespace' => 'df']); ?>
    <?php endforeach; ?>
  </main>
  <?php snippet('footer'); ?>
</div>

<?php snippet('scripts'); ?>
