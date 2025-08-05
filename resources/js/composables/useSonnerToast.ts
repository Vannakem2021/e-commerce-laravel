import { toast } from 'vue-sonner'

export const useSonnerToast = () => {
  const success = (message: string, options?: { description?: string }) => {
    return toast.success(message, options);
  }

  const error = (message: string, options?: { description?: string }) => {
    return toast.error(message, options)
  }

  const warning = (message: string, options?: { description?: string }) => {
    return toast.warning(message, options)
  }

  const info = (message: string, options?: { description?: string }) => {
    return toast.info(message, options)
  }

  const loading = (message: string) => {
    return toast.loading(message)
  }

  const dismiss = (id?: string | number) => {
    return toast.dismiss(id)
  }

  return {
    success,
    error,
    warning,
    info,
    loading,
    dismiss,
    toast, // Export the original toast object for advanced usage
  }
}
