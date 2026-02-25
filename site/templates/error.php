<?php snippet('head'); ?>
<?php snippet('header'); ?>

<?php
$id = 'error-page';
$props = [
    'title' => $page->title()->value(),
    'heading' => $page->page_heading()->or('Page not found')->value(),
    'body' => (string) $page->body()->kt(),
];
?>

<div class="page">
  <main class="main">
    <l-error id="<?= $id ?>"></l-error>
    <script type="application/json" data-for="<?= $id ?>">
      <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
    </script>
  </main>
  <?php snippet('footer'); ?>
</div>

<?php snippet('scripts'); ?>
