import { mount, unmount } from 'svelte';
import type { Component } from 'svelte';

export function registerSvelteElement(
  tag: string,
  ComponentClass: Component<any>,
  propNames: string[] = []
) {
  class SvelteElement extends HTMLElement {
    private componentInstance: Record<string, any> | null = null;

    connectedCallback() {
      if (this.componentInstance) return;
      this.innerHTML = '';

      let props: Record<string, any> = {};
      const id = this.getAttribute('id');

      if (id) {
        const script = document.querySelector(`script[data-for="${id}"]`);
        if (script) {
          try {
            props = JSON.parse(script.textContent || '{}');
          } catch {
            props = {};
          }
          script.remove();
        }
      }

      if (Object.keys(props).length === 0) {
        for (const name of propNames) {
          const attr = this.getAttribute(name);
          if (attr !== null) {
            try {
              props[name] = JSON.parse(attr);
            } catch {
              props[name] = attr;
            }
          }
        }
      }

      this.componentInstance = mount(ComponentClass, { target: this, props });
    }

    disconnectedCallback() {
      if (!this.componentInstance) return;
      unmount(this.componentInstance);
      this.componentInstance = null;
    }
  }

  if (!customElements.get(tag)) {
    customElements.define(tag, SvelteElement);
  }
}
