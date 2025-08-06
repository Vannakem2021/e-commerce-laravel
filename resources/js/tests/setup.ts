/**
 * Test Setup File
 * 
 * Global test configuration and mocks for the cart functionality tests.
 */

import { vi } from 'vitest';

// Mock Inertia.js
vi.mock('@inertiajs/vue3', () => ({
    router: {
        visit: vi.fn(),
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
    usePage: () => ({
        props: {
            auth: { user: null },
            cart_summary: null,
        },
    }),
    Head: { template: '<div></div>' },
    Link: { template: '<a><slot /></a>' },
}));

// Mock Lucide Vue icons
vi.mock('lucide-vue-next', () => ({
    ShoppingCart: { template: '<div>ShoppingCart</div>' },
    Plus: { template: '<div>Plus</div>' },
    Minus: { template: '<div>Minus</div>' },
    Trash2: { template: '<div>Trash2</div>' },
    X: { template: '<div>X</div>' },
    ExclamationTriangleIcon: { template: '<div>ExclamationTriangleIcon</div>' },
    RotateCcw: { template: '<div>RotateCcw</div>' },
    Home: { template: '<div>Home</div>' },
}));

// Mock UI components
vi.mock('@/components/ui/button', () => ({
    Button: { template: '<button><slot /></button>' },
}));

vi.mock('@/components/ui/input', () => ({
    Input: { template: '<input />' },
}));

// Global test utilities
global.ResizeObserver = vi.fn().mockImplementation(() => ({
    observe: vi.fn(),
    unobserve: vi.fn(),
    disconnect: vi.fn(),
}));

// Mock window.location
Object.defineProperty(window, 'location', {
    value: {
        reload: vi.fn(),
        href: 'http://localhost:3000',
    },
    writable: true,
});

// Mock localStorage
const localStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
};
Object.defineProperty(window, 'localStorage', {
    value: localStorageMock,
});

// Mock sessionStorage
const sessionStorageMock = {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn(),
};
Object.defineProperty(window, 'sessionStorage', {
    value: sessionStorageMock,
});
