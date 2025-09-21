export default function useAlerts() {
    const toast =  Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 5000,
        padding: '2em'
    });

    const toastSuccess = (title = 'Todo salío correctamente.') => {
        toast.fire({
            icon: 'success',
            title: title,
            padding: '2em'
        });
    }

    const toastWarning = (title = 'Advertencia.') => {
        toast.fire({
            icon: 'warning',
            title: title,
            padding: '2em'
        });
    }

    const toastError = (title = 'Se ha presentado un error.') => {
        toast.fire({
            icon: 'error',
            title: title,
            padding: '2em'
        });
    }

    return {
        toast,
        toastSuccess,
        toastWarning,
        toastError,
    }

}