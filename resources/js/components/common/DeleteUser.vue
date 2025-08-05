<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
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

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <!-- Danger Zone Section -->
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-semibold">Danger Zone</h2>
                <p class="text-muted-foreground">Irreversible and destructive actions.</p>
            </div>

            <div class="flex items-start justify-between border-l-4 border-red-500 bg-red-50/50 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100">
                        <AlertTriangle class="h-4 w-4 text-red-600" />
                    </div>
                    <div>
                        <h3 class="font-medium text-red-900">Delete Account</h3>
                        <p class="text-sm text-red-800">Permanently delete your account and all associated data. This action cannot be undone.</p>
                    </div>
                </div>
                <div>
                    <Dialog>
                        <DialogTrigger as-child>
                            <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700">
                                Delete Account
                            </button>
                        </DialogTrigger>
                        <DialogContent>
                            <form class="space-y-6" @submit="deleteUser">
                                <DialogHeader class="space-y-3">
                                    <DialogTitle class="text-lg font-semibold">Are you sure you want to delete your account?</DialogTitle>
                                    <DialogDescription>
                                        Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter
                                        your password to confirm you would like to permanently delete your account.
                                    </DialogDescription>
                                </DialogHeader>

                                <div class="space-y-2">
                                    <Label for="password">Confirm with password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        ref="passwordInput"
                                        v-model="form.password"
                                        placeholder="Enter your password to confirm"
                                        class="rounded-none border-0 border-b border-border !bg-transparent px-0 py-3 shadow-none hover:!bg-transparent focus:!bg-transparent focus-visible:border-red-500 focus-visible:ring-0 active:!bg-transparent"
                                    />
                                    <InputError :message="form.errors.password" />
                                </div>

                                <DialogFooter class="flex justify-end gap-3">
                                    <DialogClose as-child>
                                        <button
                                            type="button"
                                            class="px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                                            @click="closeModal"
                                        >
                                            Cancel
                                        </button>
                                    </DialogClose>

                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50"
                                    >
                                        <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
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
</template>
