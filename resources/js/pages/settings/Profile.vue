<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';

import InputError from '@/components/common/InputError.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type User } from '@/types';
import { AlertTriangle, Calendar, Mail, Trash2, User as UserIcon } from 'lucide-vue-next';

interface Props {
    user?: User;
    mustVerifyEmail: boolean;
    status?: string;
}

const props = defineProps<Props>();

const page = usePage();
const toast = useSonnerToast();
// Use the user from props if available, otherwise fall back to auth user
const user = computed(() => props.user || (page.props.auth.user as User));

const form = useForm({
    name: user.value.name,
    email: user.value.email,
});

const deleteForm = useForm({
    password: '',
});

const passwordInput = ref<HTMLInputElement | null>(null);

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profile updated successfully!');
        },
        onError: (errors) => {
            // Show first error message
            const firstError = Object.values(errors)[0];
            if (firstError) {
                const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                toast.error('Update failed', { description: errorMessage });
            } else {
                toast.error('Failed to update profile. Please try again.');
            }
        },
    });
};

const deleteUser = (e: Event) => {
    e.preventDefault();

    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            toast.success('Account deleted successfully');
        },
        onError: (errors) => {
            passwordInput.value?.focus();
            const firstError = Object.values(errors)[0];
            if (firstError) {
                const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                toast.error('Deletion failed', { description: errorMessage });
            } else {
                toast.error('Failed to delete account. Please try again.');
            }
        },
        onFinish: () => deleteForm.reset(),
    });
};

const closeModal = () => {
    deleteForm.clearErrors();
    deleteForm.reset();
};

// Format dates for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return { date: 'Not available', time: '' };
    const date = new Date(dateString);
    return {
        date: date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        }),
        time: date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }),
    };
};

const registrationDate = computed(() => formatDate(user.value.created_at));
</script>

<template>
    <AppLayout>
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="mx-auto max-w-4xl space-y-8">
                <!-- Page Header -->
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold text-foreground">Profile Settings</h1>
                    <p class="text-muted-foreground">Manage your account information and preferences.</p>
                </div>

                <!-- Account Overview Cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <!-- User ID Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-50">
                                <UserIcon class="h-6 w-6 text-teal-600" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">User ID</p>
                                <p class="text-xl font-bold text-foreground">#{{ user.id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Member Since Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-teal-50">
                                <Calendar class="h-6 w-6 text-teal-600" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Member Since</p>
                                <p class="text-base font-semibold text-foreground">{{ registrationDate.date }}</p>
                                <p class="text-sm text-muted-foreground">{{ registrationDate.time }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Email Status Card -->
                    <div class="rounded-lg border bg-card p-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-50">
                                <Mail class="h-6 w-6 text-cyan-600" />
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Email Status</p>
                                <span
                                    v-if="user.email_verified_at"
                                    class="inline-block rounded-md bg-teal-100 px-2 py-1 text-sm font-medium text-teal-700"
                                    >Verified</span
                                >
                                <span v-else class="inline-block rounded-md bg-cyan-100 px-2 py-1 text-sm font-medium text-cyan-700">Unverified</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-foreground">Personal Information</h2>
                        <p class="text-muted-foreground">Update your personal details and information.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium text-foreground">Full Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Enter your full name"
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium text-foreground">Email Address</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    placeholder="Enter your email address"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button
                                type="button"
                                class="rounded-md border bg-background px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="mb-6">
                        <div class="flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-destructive" />
                            <h2 class="text-xl font-semibold text-destructive">Danger Zone</h2>
                        </div>
                        <p class="text-muted-foreground">Irreversible and destructive actions.</p>
                    </div>

                    <div class="rounded-lg border border-destructive/20 bg-destructive/5 p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-destructive">Delete Account</h3>
                                <p class="text-sm text-destructive/80">
                                    Permanently delete your account and all associated data. This action cannot be undone.
                                </p>
                            </div>
                            <Dialog>
                                <DialogTrigger as-child>
                                    <button
                                        class="flex items-center gap-2 rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Delete Account
                                    </button>
                                </DialogTrigger>
                                <DialogContent>
                                    <form class="space-y-6" @submit="deleteUser">
                                        <DialogHeader class="space-y-3">
                                            <DialogTitle class="text-lg font-semibold">Are you sure you want to delete your account?</DialogTitle>
                                            <DialogDescription>
                                                Once your account is deleted, all of its resources and data will also be permanently deleted. Please
                                                enter your password to confirm you would like to permanently delete your account.
                                            </DialogDescription>
                                        </DialogHeader>

                                        <div class="space-y-2">
                                            <Label for="password">Confirm with password</Label>
                                            <Input
                                                id="password"
                                                type="password"
                                                name="password"
                                                ref="passwordInput"
                                                v-model="deleteForm.password"
                                                placeholder="Enter your password to confirm"
                                            />
                                            <InputError :message="deleteForm.errors.password" />
                                        </div>

                                        <DialogFooter class="flex justify-end gap-3">
                                            <DialogClose as-child>
                                                <button
                                                    type="button"
                                                    class="rounded-md border bg-background px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                                    @click="closeModal"
                                                >
                                                    Cancel
                                                </button>
                                            </DialogClose>

                                            <button
                                                type="submit"
                                                :disabled="deleteForm.processing"
                                                class="rounded-md bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground hover:bg-destructive/90 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                                            >
                                                <svg v-if="deleteForm.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                Delete Account
                                            </button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
