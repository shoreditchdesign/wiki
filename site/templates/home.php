<?php snippet('head'); ?>
<?php snippet('header'); ?>

<?php
$id = 'home-layout';
$props = [
    'title' => $page->title()->value(),
    'heading' => strip_tags((string) $page->page_heading()->kti(), '<em><i><strong><b>'),
    'intro' => (string) $page->page_intro()->kt(),
];
?>

<div class="page">
  <main class="main">
    <l-home id="<?= $id ?>"></l-home>
    <script type="application/json" data-for="<?= $id ?>">
      <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
    </script>
  </main>
  <?php snippet('footer'); ?>
</div>

<?php snippet('scripts'); ?>
