<script lang="ts">
  let { year = '', siteTitle = '' } = $props();
</script>

<div class="footer-inner">
  <small>{siteTitle} · {year}</small>
</div>

<style>
  .footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  small {
    opacity: 0.8;
  }
</style>
