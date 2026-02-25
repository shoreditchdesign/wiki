(function () {
  document.addEventListener('DOMContentLoaded', function () {
    window.dispatchEvent(new CustomEvent('app-ready'));
  });

  window.addEventListener('svelte-ready', function () {
    window.dispatchEvent(new CustomEvent('app-components-ready'));
  });
})();
