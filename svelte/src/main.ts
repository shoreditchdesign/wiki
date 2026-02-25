import { registerSvelteElement } from './register';

import Header from './components/global/Header.svelte';
import Footer from './components/global/Footer.svelte';
import LayoutWbDocs from './components/layout/LayoutWbDocs.svelte';

registerSvelteElement('c-header', Header, ['siteTitle', 'homeUrl', 'panelUrl']);
registerSvelteElement('c-footer', Footer, ['year', 'siteTitle']);
registerSvelteElement('l-wb-docs', LayoutWbDocs, ['pageTitle', 'pageDescription', 'blocks']);

window.dispatchEvent(new CustomEvent('svelte-ready'));
