<template>
    <div class="checkout-form-section">
        <!-- Contact Information -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Contact Information</h3>
            <div class="checkout-grid-2">
                <div class="checkout-form-group">
                    <label for="contact_name" class="checkout-label">
                        Contact Name <span class="checkout-required">*</span>
                    </label>
                    <input
                        id="contact_name"
                        v-model="form.contact_name"
                        type="text"
                        class="checkout-input"
                        :class="{ 'checkout-input-error': errors.contact_name }"
                        placeholder="Enter full name"
                    />
                    <p v-if="errors.contact_name" class="checkout-error">
                        {{ errors.contact_name }}
                    </p>
                </div>

                <div class="checkout-form-group">
                    <label for="phone_number" class="checkout-label">
                        Phone Number <span class="checkout-required">*</span>
                    </label>
                    <input
                        id="phone_number"
                        v-model="form.phone_number"
                        type="tel"
                        class="checkout-input"
                        :class="{ 'checkout-input-error': errors.phone_number }"
                        placeholder="e.g., +855 12 345 678"
                    />
                    <p v-if="errors.phone_number" class="checkout-error">
                        {{ errors.phone_number }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Address Details</h3>
            <div class="checkout-grid-2">
                <div class="checkout-form-group">
                    <label for="house_number" class="checkout-label">
                        House Number
                    </label>
                    <input
                        id="house_number"
                        v-model="form.house_number"
                        type="text"
                        class="checkout-input"
                        placeholder="e.g., #123"
                    />
                </div>

                <div class="checkout-form-group">
                    <label for="street_number" class="checkout-label">
                        Street Number
                    </label>
                    <input
                        id="street_number"
                        v-model="form.street_number"
                        type="text"
                        class="checkout-input"
                        placeholder="e.g., Street 240"
                    />
                </div>
            </div>
        </div>

        <!-- Cambodia Address Selection -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Location</h3>

            <div class="checkout-grid-3">
                <!-- Province/City -->
                <div class="checkout-form-group">
                    <label for="city_province" class="checkout-label">
                        City/Province <span class="checkout-required">*</span>
                    </label>
                    <select
                        id="city_province"
                        v-model="selectedProvinceId"
                        @change="onProvinceChange"
                        class="checkout-select"
                        :class="{ 'checkout-input-error': errors.city_province }"
                    >
                        <option value="">Select Province/City</option>
                        <option
                            v-for="province in provinces"
                            :key="province.id"
                            :value="province.id"
                        >
                            {{ province.name_en }} ({{ province.name_kh }})
                        </option>
                    </select>
                    <p v-if="errors.city_province" class="checkout-error">
                        {{ errors.city_province }}
                    </p>
                </div>

                <!-- District/Khan -->
                <div class="checkout-form-group">
                    <label for="district_khan" class="checkout-label">
                        District/Srok/Khan <span class="checkout-required">*</span>
                    </label>
                    <select
                        id="district_khan"
                        v-model="selectedDistrictId"
                        @change="onDistrictChange"
                        :disabled="!selectedProvinceId || loadingDistricts"
                        class="checkout-select"
                        :class="{ 'checkout-input-error': errors.district_khan, 'checkout-loading': loadingDistricts }"
                    >
                        <option value="">Select District</option>
                        <option
                            v-for="district in districts"
                            :key="district.id"
                            :value="district.id"
                        >
                            {{ district.name_en }} ({{ district.name_kh }})
                        </option>
                    </select>
                    <p v-if="loadingDistricts" class="text-sm text-muted-foreground">
                        Loading districts...
                    </p>
                    <p v-if="errors.district_khan" class="checkout-error">
                        {{ errors.district_khan }}
                    </p>
                </div>

                <!-- Commune/Sangkat -->
                <div class="checkout-form-group">
                    <label for="commune_sangkat" class="checkout-label">
                        Commune/Khum/Sangkat <span class="checkout-required">*</span>
                    </label>
                    <select
                        id="commune_sangkat"
                        v-model="form.commune_sangkat"
                        @change="onCommuneChange"
                        :disabled="!selectedDistrictId || loadingCommunes"
                        class="checkout-select"
                        :class="{ 'checkout-input-error': errors.commune_sangkat, 'checkout-loading': loadingCommunes }"
                    >
                        <option value="">Select Commune</option>
                        <option
                            v-for="commune in communes"
                            :key="commune.postal_code"
                            :value="commune.name_kh"
                        >
                            {{ commune.name_en }} ({{ commune.name_kh }})
                        </option>
                    </select>
                    <p v-if="loadingCommunes" class="text-sm text-muted-foreground">
                        Loading communes...
                    </p>
                    <p v-if="errors.commune_sangkat" class="checkout-error">
                        {{ errors.commune_sangkat }}
                    </p>
                </div>
            </div>

            <!-- Postal Code (Auto-filled) -->
            <div class="max-w-xs">
                <div class="checkout-form-group">
                    <label for="postal_code" class="checkout-label">
                        Postal Code <span class="checkout-required">*</span>
                    </label>
                    <input
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        readonly
                        class="checkout-input bg-muted"
                        placeholder="Auto-filled"
                    />
                    <p class="text-sm text-muted-foreground">
                        Postal code will be automatically filled based on your area selection
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Additional Information</h3>
            <div class="checkout-form-group">
                <label for="additional_info" class="checkout-label">
                    Delivery Instructions
                </label>
                <textarea
                    id="additional_info"
                    v-model="form.additional_info"
                    rows="3"
                    class="checkout-textarea"
                    placeholder="Any special delivery instructions (optional)"
                ></textarea>
            </div>
        </div>

        <!-- Set as Default -->
        <div class="flex items-center space-x-3">
            <input
                id="is_default"
                v-model="form.is_default"
                type="checkbox"
                class="checkout-checkbox"
            />
            <label for="is_default" class="checkout-label">
                Set as default shipping address
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="checkout-actions">
            <Button
                type="button"
                variant="outline"
                @click="$emit('cancel')"
            >
                Cancel
            </Button>
            <Button
                type="button"
                @click="saveAddress"
                :disabled="loading || !isFormValid"
            >
                <span v-if="loading">Saving...</span>
                <span v-else>Save Address</span>
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'

interface Province {
    id: number
    name_kh: string
    name_en: string
    districts_count: number
}

interface District {
    id: number
    no: string
    name_kh: string
    name_en: string
    communes_count: number
}

interface Commune {
    name_kh: string
    name_en: string
    postal_code: string
}

interface AddressForm {
    type: string
    contact_name: string
    phone_number: string
    house_number: string
    street_number: string
    city_province: string
    district_khan: string
    commune_sangkat: string
    postal_code: string
    additional_info: string
    is_default: boolean
}

interface SavedAddress {
    id: number;
    contact_name: string;
    phone_number: string;
    house_number?: string;
    street_number?: string;
    city_province: string;
    district_khan: string;
    commune_sangkat: string;
    postal_code: string;
    additional_info?: string;
    is_default: boolean;
}

interface Props {
    initialData?: Partial<AddressForm>
    type?: 'shipping' | 'billing'
    savedAddresses?: SavedAddress[]
}

const props = withDefaults(defineProps<Props>(), {
    type: 'shipping',
    savedAddresses: () => []
})

const emit = defineEmits<{
    saved: [address: any]
    cancel: []
}>()

// Form data
const form = reactive<AddressForm>({
    type: props.type,
    contact_name: '',
    phone_number: '',
    house_number: '',
    street_number: '',
    city_province: '',
    district_khan: '',
    commune_sangkat: '',
    postal_code: '',
    additional_info: '',
    is_default: false,
    ...props.initialData
})

// State
const loading = ref(false)
const loadingDistricts = ref(false)
const loadingCommunes = ref(false)
const errors = ref<Record<string, string>>({})

// Address data
const provinces = ref<Province[]>([])
const districts = ref<District[]>([])
const communes = ref<Commune[]>([])
const selectedProvinceId = ref<number | null>(null)
const selectedDistrictId = ref<number | null>(null)

// Computed
const isFormValid = computed(() => {
    return form.contact_name &&
           form.phone_number &&
           form.city_province &&
           form.district_khan &&
           form.commune_sangkat &&
           form.postal_code
})

// Methods
const loadProvinces = async () => {
    try {
        const response = await axios.get('/api/addresses/provinces')
        provinces.value = response.data
    } catch (error) {
        console.error('Failed to load provinces:', error)
    }
}

const onProvinceChange = async () => {
    if (!selectedProvinceId.value) return

    // Update form data
    const selectedProvince = provinces.value.find(p => p.id === selectedProvinceId.value)
    if (selectedProvince) {
        form.city_province = selectedProvince.name_kh
    }

    // Reset dependent fields
    selectedDistrictId.value = null
    form.district_khan = ''
    form.commune_sangkat = ''
    form.postal_code = ''
    districts.value = []
    communes.value = []

    // Load districts
    loadingDistricts.value = true
    try {
        const response = await axios.get('/api/addresses/districts', {
            params: { province_id: selectedProvinceId.value }
        })
        districts.value = response.data
    } catch (error) {
        console.error('Failed to load districts:', error)
    } finally {
        loadingDistricts.value = false
    }
}

const onDistrictChange = async () => {
    if (!selectedDistrictId.value) return

    // Update form data
    const selectedDistrict = districts.value.find(d => d.id === selectedDistrictId.value)
    if (selectedDistrict) {
        form.district_khan = selectedDistrict.name_kh
    }

    // Reset dependent fields
    form.commune_sangkat = ''
    form.postal_code = ''
    communes.value = []

    // Load communes
    loadingCommunes.value = true
    try {
        const response = await axios.get('/api/addresses/communes', {
            params: { district_id: selectedDistrictId.value }
        })
        communes.value = response.data
    } catch (error) {
        console.error('Failed to load communes:', error)
    } finally {
        loadingCommunes.value = false
    }
}

const onCommuneChange = () => {
    if (!form.commune_sangkat) return

    // Find selected commune and set postal code
    const selectedCommune = communes.value.find(c => c.name_kh === form.commune_sangkat)
    if (selectedCommune) {
        form.postal_code = selectedCommune.postal_code
    }
}

const saveAddress = async () => {
    loading.value = true
    errors.value = {}

    try {
        const response = await axios.post('/api/addresses', form)
        emit('saved', response.data.address)
    } catch (error: any) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            console.error('Failed to save address:', error)
        }
    } finally {
        loading.value = false
    }
}

// Initialize
onMounted(() => {
    loadProvinces()
})
</script>
