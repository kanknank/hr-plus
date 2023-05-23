import { Notify, Dialog } from 'quasar'

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

export { showSuccess, showError }