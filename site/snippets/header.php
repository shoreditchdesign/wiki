<?php
$id = 'global-header';
$props = [
    'siteTitle' => $site->title()->value(),
    'homeUrl' => url('/'),
    'panelUrl' => $kirby->user() ? $kirby->url('panel') : '/panel',
];
?>
<header class="site-header">
  <c-header id="<?= $id ?>"></c-header>
  <script type="application/json" data-for="<?= $id ?>">
    <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
  </script>
</header>
