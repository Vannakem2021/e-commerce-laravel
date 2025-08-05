<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';

import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Eye, EyeOff, Info, Lock, Shield } from 'lucide-vue-next';

interface Props {
    status?: string;
}

defineProps<Props>();

const toast = useSonnerToast();
const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success('Password updated successfully!');
        },
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
                const errorMessage = Array.isArray(errors.password) ? errors.password[0] : errors.password;
                toast.error('Password update failed', { description: errorMessage });
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
                const errorMessage = Array.isArray(errors.current_password) ? errors.current_password[0] : errors.current_password;
                toast.error('Password update failed', { description: errorMessage });
            }

            // Show general error if no specific field errors
            if (!errors.password && !errors.current_password) {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    toast.error('Password update failed', { description: errorMessage });
                } else {
                    toast.error('Failed to update password. Please try again.');
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="mx-auto max-w-4xl space-y-8">
                <!-- Page Header -->
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                        <Lock class="h-6 w-6 text-primary" />
                    </div>
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold text-foreground">Password Settings</h1>
                        <p class="text-muted-foreground">Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                </div>

                <!-- Update Password Form -->
                <div class="rounded-lg border bg-card p-6">
                    <div class="mb-6 flex items-start gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Shield class="h-4 w-4 text-primary" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-foreground">Update Password</h2>
                            <p class="text-muted-foreground">Change your account password to keep your account secure.</p>
                        </div>
                    </div>

                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="current_password" class="text-sm font-medium text-foreground">Current Password</Label>
                                <div class="relative">
                                    <Input
                                        id="current_password"
                                        ref="currentPasswordInput"
                                        v-model="form.current_password"
                                        :type="showCurrentPassword ? 'text' : 'password'"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Enter your current password"
                                        class="pr-10"
                                    />
                                    <button
                                        type="button"
                                        @click="showCurrentPassword = !showCurrentPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground hover:text-foreground"
                                    >
                                        <Eye v-if="!showCurrentPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.current_password" />
                            </div>

                            <div class="space-y-2">
                                <Label for="password" class="text-sm font-medium text-foreground">New Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        ref="passwordInput"
                                        v-model="form.password"
                                        :type="showNewPassword ? 'text' : 'password'"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Enter your new password"
                                        class="pr-10"
                                    />
                                    <button
                                        type="button"
                                        @click="showNewPassword = !showNewPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground hover:text-foreground"
                                    >
                                        <Eye v-if="!showNewPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation" class="text-sm font-medium text-foreground">Confirm New Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        required
                                        autocomplete="new-password"
                                        placeholder="Confirm your new password"
                                        class="pr-10"
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-muted-foreground hover:text-foreground"
                                    >
                                        <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <!-- Password Security Tips -->
                        <div class="rounded-lg bg-teal-50 p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100">
                                    <Info class="h-4 w-4 text-teal-600" />
                                </div>
                                <div>
                                    <h3 class="font-medium text-teal-900">Password Security Tips</h3>
                                    <ul class="mt-2 space-y-1 text-sm text-teal-800">
                                        <li>• Use at least 8 characters with letters, numbers, and symbols</li>
                                        <li>• Choose a unique password not used elsewhere</li>
                                        <li>• Consider using a password manager</li>
                                    </ul>
                                </div>
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
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
