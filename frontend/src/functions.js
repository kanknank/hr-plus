import { Notify, Dialog } from 'quasar'
import axios from 'axios'

const showSuccess = function(message) {
    Notify.create({
        color: 'positive',
        message,
        position: 'bottom-right',
    })
}

const showError = function(message = 'неизвестная ошибка') {
    Dialog.create({
        title: 'Произошла ошибка',
        message: `Пожалуйста, перезагрузите страницу и попробуйте снова. Описание ошибки: ${message}`
    })
}

const request = function(params) {
    params.loading.value = true;

    axios[params.method || 'get'](params.path, params.data || {})
        .then((response) => {
            if (response.data?.data?.success) {
                params.successMessage && showSuccess(params.successMessage)
                params.success && params.success(response.data?.data)
            } else {
                showError(response.data?.data?.message)
            }
        })
        .catch((error) => showError(error))
        .finally(() => params.loading.value = false)
}

export { showSuccess, showError, request }