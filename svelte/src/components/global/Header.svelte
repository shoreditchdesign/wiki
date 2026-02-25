<script lang="ts">
  let { siteTitle = '', homeUrl = '/', panelUrl = '/panel' } = $props();
</script>

<div class="header-inner">
  <a class="brand" href={homeUrl}>{siteTitle}</a>
  <a class="panel-link" href={panelUrl}>Panel</a>
</div>

<style>
  .header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .brand,
  .panel-link {
    color: inherit;
    text-decoration: none;
    font-weight: 600;
  }

  .panel-link {
    font-weight: 500;
    opacity: 0.8;
  }
</style>
