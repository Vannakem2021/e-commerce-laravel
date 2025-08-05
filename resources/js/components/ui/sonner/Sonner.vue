<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { Toaster as Sonner } from "vue-sonner"

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    v-bind="props"
  />
</template>

<style>
/* Toast color customization - using design system colors */

/* Success Toast - Primary Teal Color */
[data-sonner-toaster] [data-sonner-toast][data-type="success"],
[data-sonner-toast][data-type="success"] {
  border-color: hsl(178 84% 32%) !important;
  color: hsl(178 84% 32%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="success"] [data-icon],
[data-sonner-toast][data-type="success"] [data-icon] {
  color: hsl(178 84% 32%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="success"] [data-title],
[data-sonner-toast][data-type="success"] [data-title],
[data-sonner-toaster] [data-sonner-toast][data-type="success"] [data-content] [data-title],
[data-sonner-toast][data-type="success"] [data-content] [data-title] {
  color: hsl(178 84% 32%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="success"] [data-description],
[data-sonner-toast][data-type="success"] [data-description] {
  color: hsl(178 84% 25%) !important;
  opacity: 0.9;
}

/* Error Toast - Destructive Red Color */
[data-sonner-toaster] [data-sonner-toast][data-type="error"],
[data-sonner-toast][data-type="error"] {
  border-color: hsl(0 84.2% 60.2%) !important;
  color: hsl(0 84.2% 60.2%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="error"] [data-icon],
[data-sonner-toast][data-type="error"] [data-icon] {
  color: hsl(0 84.2% 60.2%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="error"] [data-title],
[data-sonner-toast][data-type="error"] [data-title],
[data-sonner-toaster] [data-sonner-toast][data-type="error"] [data-content] [data-title],
[data-sonner-toast][data-type="error"] [data-content] [data-title] {
  color: hsl(0 84.2% 60.2%) !important;
}

[data-sonner-toaster] [data-sonner-toast][data-type="error"] [data-description],
[data-sonner-toast][data-type="error"] [data-description] {
  color: hsl(0 74% 42%) !important;
  opacity: 0.9;
}

/* Universal override for stubborn elements */
[data-sonner-toast][data-type="success"] * {
  color: hsl(178 84% 32%) !important;
}

[data-sonner-toast][data-type="error"] * {
  color: hsl(0 84.2% 60.2%) !important;
}
</style>