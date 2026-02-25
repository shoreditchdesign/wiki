<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= html($page->meta_title()->or($page->title())->value()) ?> | <?= html($site->title()->value()) ?></title>
  <meta name="description" content="<?= html($page->meta_description()->or($site->site_description())->value()) ?>">

  <link rel="stylesheet" href="/assets/css/normalize.css">
  <link rel="stylesheet" href="/assets/css/variables.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link rel="stylesheet" href="/assets/css/keyframes.css">
  <link rel="stylesheet" href="/assets/css/svelte.css">
</head>
<body class="body">
