<?php
$id = 'global-footer';
$props = [
    'year' => date('Y'),
    'siteTitle' => $site->title()->value(),
];
?>
<footer class="site-footer">
  <c-footer id="<?= $id ?>"></c-footer>
  <script type="application/json" data-for="<?= $id ?>">
    <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
  </script>
</footer>
