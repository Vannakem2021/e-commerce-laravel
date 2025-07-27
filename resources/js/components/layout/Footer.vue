<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Footer navigation data
const footerNavigation = ref({
    shop: [
        { name: 'Smartphones', href: '/categories/smartphones' },
        { name: 'Laptops', href: '/categories/laptops' },
        { name: 'Audio', href: '/categories/audio' },
        { name: 'Gaming', href: '/categories/gaming' },
        { name: 'Smart Home', href: '/categories/smart-home' },
        { name: 'Accessories', href: '/categories/accessories' },
    ],
    support: [
        { name: 'Help Center', href: '/support' },
        { name: 'Contact Us', href: '/contact' },
        { name: 'Shipping Info', href: '/shipping' },
        { name: 'Returns', href: '/returns' },
        { name: 'Warranty', href: '/warranty' },
        { name: 'Track Order', href: '/track-order' },
    ],
    company: [
        { name: 'About Us', href: '/about' },
        { name: 'Careers', href: '/careers' },
        { name: 'Press', href: '/press' },
        { name: 'Blog', href: '/blog' },
        { name: 'Investors', href: '/investors' },
        { name: 'Sustainability', href: '/sustainability' },
    ],
    legal: [
        { name: 'Privacy Policy', href: '/privacy' },
        { name: 'Terms of Service', href: '/terms' },
        { name: 'Cookie Policy', href: '/cookies' },
        { name: 'GDPR', href: '/gdpr' },
    ],
});

// Social media links
const socialLinks = ref([
    {
        name: 'Facebook',
        href: 'https://facebook.com',
        icon: 'M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z',
    },
    {
        name: 'Twitter',
        href: 'https://twitter.com',
        icon: 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
    },
    {
        name: 'Instagram',
        href: 'https://instagram.com',
        icon: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.004 5.367 18.637.001 12.017.001zM12 16.624c-2.563 0-4.637-2.074-4.637-4.637S9.437 7.35 12 7.35s4.637 2.074 4.637 4.637S14.563 16.624 12 16.624zm4.875-8.362c-.597 0-1.081-.484-1.081-1.081s.484-1.081 1.081-1.081 1.081.484 1.081 1.081-.484 1.081-1.081 1.081z',
    },
    {
        name: 'LinkedIn',
        href: 'https://linkedin.com',
        icon: 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
    },
]);

// Newsletter subscription
const email = ref('');
const isSubscribing = ref(false);

const handleNewsletterSubmit = async () => {
    if (!email.value) return;

    isSubscribing.value = true;
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 1000));
    isSubscribing.value = false;
    email.value = '';
    // Show success message (you can implement toast notification here)
    alert('Thank you for subscribing to our newsletter!');
};

// Current year for copyright
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="bg-gray-900 text-white">
        <!-- Main Footer Content -->
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-6">
                <!-- Company Info & Newsletter -->
                <div class="lg:col-span-2">
                    <!-- Logo & Description -->
                    <div class="mb-8">
                        <Link href="/" class="flex items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-600">
                                <span class="text-xl font-bold text-white">E</span>
                            </div>
                            <span class="ml-3 text-xl font-bold text-white">E-Commerce</span>
                        </Link>
                        <p class="mt-4 leading-relaxed text-gray-300">
                            Your trusted destination for the latest electronics and technology. Discover premium products with exceptional quality and
                            service.
                        </p>
                    </div>

                    <!-- Newsletter Subscription -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-white">Stay Updated</h3>
                        <p class="mb-4 text-gray-300">Subscribe to our newsletter for exclusive deals and latest product updates.</p>
                        <form @submit.prevent="handleNewsletterSubmit" class="flex flex-col gap-3 sm:flex-row">
                            <input
                                v-model="email"
                                type="email"
                                placeholder="Enter your email"
                                required
                                class="flex-1 rounded-lg border border-gray-600 bg-gray-800 px-4 py-3 text-white placeholder-gray-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500 focus:outline-none"
                            />
                            <button
                                type="submit"
                                :disabled="isSubscribing"
                                class="rounded-lg bg-teal-600 px-6 py-3 font-medium text-white transition-colors hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-gray-900 focus:outline-none disabled:opacity-50"
                            >
                                {{ isSubscribing ? 'Subscribing...' : 'Subscribe' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="grid grid-cols-2 gap-8 lg:col-span-4 lg:grid-cols-4">
                    <!-- Shop -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-white">Shop</h3>
                        <ul class="space-y-3">
                            <li v-for="item in footerNavigation.shop" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-gray-300 transition-colors hover:text-teal-400 focus:text-teal-400 focus:outline-none"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-white">Support</h3>
                        <ul class="space-y-3">
                            <li v-for="item in footerNavigation.support" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-gray-300 transition-colors hover:text-teal-400 focus:text-teal-400 focus:outline-none"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Company -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-white">Company</h3>
                        <ul class="space-y-3">
                            <li v-for="item in footerNavigation.company" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-gray-300 transition-colors hover:text-teal-400 focus:text-teal-400 focus:outline-none"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="mb-4 text-lg font-semibold text-white">Legal</h3>
                        <ul class="space-y-3">
                            <li v-for="item in footerNavigation.legal" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-gray-300 transition-colors hover:text-teal-400 focus:text-teal-400 focus:outline-none"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <!-- Copyright -->
                    <p class="text-sm text-gray-400">© {{ currentYear }} E-Commerce. All rights reserved.</p>

                    <!-- Social Links -->
                    <div class="flex space-x-4">
                        <a
                            v-for="social in socialLinks"
                            :key="social.name"
                            :href="social.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-400 transition-colors hover:text-teal-400 focus:text-teal-400 focus:outline-none"
                            :aria-label="social.name"
                        >
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path :d="social.icon" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* Enhanced focus states for accessibility */
a:focus {
    outline: 2px solid rgb(20 184 166);
    outline-offset: 2px;
    border-radius: 4px;
}

/* Newsletter form enhancements */
input:focus {
    box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
}

/* Smooth transitions for all interactive elements */
a,
button,
input {
    transition: all 0.2s ease-in-out;
}

/* Mobile optimizations */
@media (max-width: 640px) {
    .grid-cols-2 {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .newsletter-form input {
        margin-bottom: 0.75rem;
    }
}

/* Hover effects for social icons */
.social-icon:hover {
    transform: translateY(-2px);
}
</style>
