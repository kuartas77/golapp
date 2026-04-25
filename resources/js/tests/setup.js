import { afterEach, beforeEach, vi } from 'vitest';

beforeEach(() => {
    window.scrollTo = vi.fn();
});

afterEach(() => {
    document.body.innerHTML = '';
    document.head
        .querySelectorAll('script[data-signature-pad-loader="portal"]')
        .forEach((element) => element.remove());
    localStorage.clear();
});
