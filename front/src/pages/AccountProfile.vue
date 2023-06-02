<script setup>
    import { ref, onMounted, watch, watchEffect } from 'vue'
    import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router'
    import axios from 'axios'
    import { mdiAccount, mdiTextBoxOutline, mdiFileDocument, mdiEye } from '@quasar/extras/mdi-v6'
    import AccountBase from './AccountBase.vue'
    import AppInput from '../components/AppInput.vue'
    import AppFilePond from '../components/AppFilePond.vue'
    import { showSuccess, showError } from '../functions.js'

    const route = useRoute()
    const router = useRouter()

    const loading = ref(false)
    const formEl = ref(null)
    const form = ref({ description: '' })
    const formChanged = ref(false)
    const passwordForm = ref({})
    const passwordFormErrors = ref({})
    const pondFiles = ref({})
    const avatarPond = ref(null)
    const resumePond = ref(null)
    const portfolioPond = ref(null)
    const tab = ref('edit')
    const nextTab = ref(null)

    const updateProfile = function() {
        loading.value = true;

        axios.post(`profile/update`, form.value)
            .then((response) => {
                getProfile()
                showSuccess('Данные сохранены')
                formChanged.value = false
                if (nextTab.value) {
                    tab.value = nextTab.value
                }
                router.push(`/account/profile?tab=${nextTab.value}`)
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    // const uploadFile = function() {
    //     loading.value = true;

    //     const headers = {}
    //     let data = {}

    //     headers['Content-Type'] = "multipart/form-data"
    //     if (pond.value.getFile) {
    //         data.avatar = pond.value.getFile()
    //     }

    //     axios.post(`profile/update`, data, { headers })
    //         .then((response) => {
    //             delete sessionStorage.user
    //             showSuccess('Аватар сохранён')
    //             if (nextTab.value) {
    //                 tab.value = nextTab.value
    //             }
    //             router.push(`/account/profile/?tab=${nextTab.value}`)
    //         })
    //         .catch((error) => showError(error))
    //         .finally(() => loading.value = false)
    // }

    const uploadFiles = function(key, pond) {
        console.log(pond.getFiles())

        loading.value = true;
        const data = { key }
        data[key] = pond.getFiles()

        axios.post(`profile/upload_files`, data, { headers: { 'Content-Type': 'multipart/form-data' } })
            .then((response) => {
                if (response.data?.data?.success) {
                    getProfile()
                    key == 'avatar' && delete sessionStorage.user
                    showSuccess('Файлы загружены')
                } else {
                    showError(response.data?.data?.message)
                }
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    const changePassword = function() {
        loading.value = true;

        axios.post(`profile/change_password`, passwordForm.value)
            .then((response) => {
                passwordFormErrors.value = response.data?.data?.errors || {}
                if (response.data?.data?.success) {
                    showSuccess('Пароль изменён')
                }
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    const getProfile = function() {
        loading.value = true;

        axios.get(`profile`)
            .then((response) => {
                if (response.data?.data?.success) {
                    form.value = response.data.data.data
                    pondFiles.value.avatar = response.data.data.data.photo ? [response.data.data.data.photo] : []
                    pondFiles.value.portfolio = response.data.data.data.portfolio || []
                    pondFiles.value.resume = response.data.data.data.resume || []
                    passwordForm.value.username = form.value.email
                } else {
                    showError()
                }
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    onMounted(async () => {
        await router.isReady()
        getProfile()
        tab.value = route.query.tab || 'edit'
    })

    const changeTab = function(to) {
        nextTab.value = to

        /*if (tab.value == 'files' && pond.value.getFile()) {
            formEl.value && formEl.value.submit()
        } else */if (tab.value == 'edit' && formChanged.value) {
            formEl.value && formEl.value.submit()
        } else {
            tab.value = nextTab.value
            router.push(`/account/profile?tab=${nextTab.value}`)
        }
    }

    const fields = [
        { field: 'surname', label: 'Фамилия', validate: ['required'] },
        { field: 'fullname', label: 'Имя', validate: ['required'] },
        { field: 'middlename', label: 'Отчество' },
        { field: 'birthdate', label: 'Дата рождения', type: 'date' },
        { field: 'country', label: 'Страна' },
        { field: 'city', label: 'Город' },
    ]

    const contactFields = [
        { field: 'phone', label: 'Сотовый телефон', validate: ['phone'] },
        { field: 'work_phone', label: 'Рабочий телефон', validate: ['phone'] },
        { field: 'tg', label: 'Профиль в Телеграм' },
        { field: 'vk', label: 'Профиль в ВК' },
        { field: 'resume_link', label: 'Ссылка на резюме' },
        { field: 'portfolio_link', label: 'Сайт с портфолио' },
    ]

    const passwordFields = [
        { field: 'username', name: 'email', type: 'email', label: 'Логин', validate: ['required'], readonly: true },
        { field: 'old_password', type: 'password', label: 'Старый пароль' },
        { field: 'new_password', type: 'password', label: 'Новый пароль' },
        { field: 'confirm_password', type: 'password', label: 'Подтвердите новый пароль', validate: ['confirm_password'] },
    ]

    const tabs = [
        { name: 'edit', label: 'Данные<br>пользователя', icon: mdiTextBoxOutline },
        { name: 'password', label: 'Смена<br>пароля', icon: mdiAccount },
        { name: 'files', label: 'Загрузить<br>файлы', icon: mdiFileDocument },
        { name: 'view', label: 'Просмотр', icon: mdiEye },
    ]
</script>

<template>
    <account-base>
        <div class="layout-vtabs">
            <div class="">
                <div class="vtabs">
                    <q-tabs>
                        <template v-for="t in tabs">
                            <q-tab @click="changeTab(t.name)" :class="tab == t.name && '_active'" :icon="t.icon">
                                <span v-html="t.label"></span>
                            </q-tab>
                        </template>
                    </q-tabs>
                </div>
            </div>

            <!--  -->
            <div class="">
                <q-inner-loading :showing="loading" color="primary"/>

                <q-form v-if="tab == 'edit'" @submit="updateProfile" greedy ref="formEl" class="box">
                    <h2 class="text-h6">Введите личные данные:</h2>
                    <div class="row q-col-gutter-lg">
                        <template v-for="i, n in fields">
                            <div :class="n < 3 ? 'col-md-4' : 'col-md-6'" class="col-12">
                                <app-input v-model="form[i.field]" :opts="i" @update:model-value="formChanged = true"/>
                            </div>
                        </template>
                    </div>

                    <h2 class="q-mt-lg text-h6">Контактные данные:</h2>
                    <div class="row q-col-gutter-lg">
                        <template v-for="i in contactFields">
                            <div class="col-12 col-md-6">
                                <app-input v-model="form[i.field]" :opts="i" @update:model-value="formChanged = true"/>
                            </div>
                        </template>
                    </div>

                    <p class="q-mt-lg q-mb-sm">О себе:</p>
                    <q-editor v-model="form.description" @update:model-value="formChanged = true" min-height="5rem"/>
                </q-form>

                <!--  -->
                <q-form v-if="tab == 'password'" @submit="changePassword()" greedy class="box" action="">
                    <h2 class="text-h6">Изменить пароль:</h2>
                    <div class="row q-col-gutter-lg">
                        <template v-for="i in passwordFields">
                            <div class="col-12 col-md-6">
                                <!-- костыль чтоб не автокомплитил старый пароль -->
                                <input type="text" name="password" hidden>
                                <app-input v-model="passwordForm[i.field]" :opts="i"
                                    @update:model-value="passwordFormErrors[i.field] = ''"
                                    :error="passwordFormErrors[i.field]"
                                    :form="passwordForm"
                                    :readonly="!!i.readonly"
                                    autocomplete="new-password"
                                />
                            </div>
                        </template>

                        <div class="col-12">
                            <q-btn type="submit" label="Изменить пароль" color="primary"/>
                        </div>
                    </div>
                </q-form>

                <!-- Лого -->
                <div v-if="tab == 'files'" class="box">
                    <h2 class="text-h6">Загрузить аватар пользователя:</h2>
                    <app-file-pond :files="pondFiles.avatar" ref="avatarPond" label="загрузить аватар"/>
                    <q-btn @click="uploadFiles('avatar', avatarPond)" class="q-mt-md" color="primary">Загрузить</q-btn>
                    
                    <h2 class="q-mt-lg text-h6">Загрузить резюме:</h2>
                    <app-file-pond :files="pondFiles.resume" ref="resumePond" label="загрузить резюме"
                        accepted-file-types="application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/pdf"
                        caption="размер файла не должен превышать 1 МБ<br>(допустимые расширения: doc, docx, pdf)"
                    />
                    <q-btn @click="uploadFiles('resume', resumePond)" class="q-mt-md" color="primary">Загрузить</q-btn>

                    <h2 class="q-mt-lg text-h6">Загрузить примеры работ (портфолио):</h2>
                    <app-file-pond :files="pondFiles.portfolio" ref="portfolioPond" label="загрузить файлы"
                        allow-multiple="true"
                        caption="размер файла не должен превышать 6 МБ<br>(допустимые расширения: jpg, png, bmp)<br>не более 20 файлов"
                        image-preview-min-height="150"
                        image-preview-max-height="150"
                        max-file-size="6MB"
                        max-files="20"
                        class="filepond--grid"
                    />
                    <q-btn @click="uploadFiles('portfolio', portfolioPond)" class="q-mt-md" color="primary">Загрузить</q-btn>
                </div>

                <!-- Просмотр  -->
                <div v-if="tab == 'view'" class="box">
                    <div v-if="form.photo">
                        <img width="160" :src="form.photo" alt="">
                    </div>

                    <h2 class="text-h6">Общие данные:</h2>
                    <template v-for="i in fields">
                        <p class="">
                            <b>{{ i.label }}:</b> {{ form[i.field] || '—' }} 
                        </p>
                    </template>

                    <h2 class="text-h6">Контактные данные:</h2>
                    <template v-for="i in contactFields">
                        <p class="">
                            <b>{{ i.label }}:</b> {{ form[i.field] || '—' }} 
                        </p>
                    </template>

                    <p><b>О себе:</b><div v-html="form.description"></div></p>
                </div>

                <!--  -->
                <div class="row justify-between q-mt-lg">
                    <template v-if="tab == 'edit'">
                        <q-btn label="Назад" color="dark" disabled/>
                        <q-btn @click="changeTab('password')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'password'">
                        <q-btn @click="changeTab('edit')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('files')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'files'">
                        <q-btn @click="changeTab('password')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('view')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'view'">
                        <q-btn @click="changeTab('files')" label="Назад" color="dark"/>
                        <q-btn to="/account" label="Финиш" color="positive"/>
                    </template>
                </div>
            </div>
        </div>
    </account-base>
</template>