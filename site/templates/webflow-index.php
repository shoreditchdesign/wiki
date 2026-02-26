<?php snippet('head'); ?>
<?php snippet('header'); ?>

<div class="page">
  <main class="main">
    <section>
      <h1><?= html($page->title()->value()) ?></h1>
      <p>Use the Kirby Panel to manage client Webflow docs.</p>
    </section>
  </main>
  <?php snippet('footer'); ?>
</div>

<?php snippet('scripts'); ?>
