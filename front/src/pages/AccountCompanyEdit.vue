<script setup>
    import { ref, onMounted, watch, watchEffect } from 'vue'
    import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router'
    import axios from 'axios'
    import { useQuasar } from 'quasar'
    import { mdiAccount, mdiTextBoxOutline, mdiFileDocument, mdiEye } from '@quasar/extras/mdi-v6'
    import AccountBase from './AccountBase.vue'
    import AppFilePond from '../components/AppFilePond.vue'

    const props = defineProps({
        createNew: {},
    })

    const route = useRoute()
    const router = useRouter()
    const $q = useQuasar()

    const loading = ref(false)
    const form = ref({
        description: ''
    })
    const contactForm = ref({})
    const formEl = ref(null)
    const id = ref(0)
    const myFiles = ref([])
    const nextTab = ref(null)
    const pond = ref(null)
    const tab = ref('edit')

    const editCompany = function() {
        loading.value = true;

        const headers = {}
        let data = { id: id.value }

        if (tab.value == 'edit') {
            data = form.value
        }
        if (tab.value == 'contacts') {
            data.contacts = JSON.stringify(contactForm.value)
        }
        if (tab.value == 'logo') {
            headers['Content-Type'] = "multipart/form-data"
            if (pond.value.getFile) {
                data.logo = pond.value.getFile()
            }
        }

        axios.post(`company/update`, data, { headers })
            .then((response) => {
                getCompany()
                $q.notify({ message: 'Данные сохранены', color: 'positive' })
                router.push(`/account/company/${id.value}?tab=${nextTab.value}`)
            })
            .catch((error) => {
                alert('Ошибка')
                console.log(error);
            })
    }

    const newCompany = function() {
        axios.post(`company/new`, form.value)
            .then((response) => {
                console.log(response.data);
                if (response.data.success) {
                    $q.notify({ message: 'Организация создана', color: 'positive' })
                    router.push(`/account/company/${response.data.data.data.id}?tab=${nextTab.value}`)
                } else {
                    alert('Ошибка')
                }
            })
            .catch((error) => {
                alert('Ошибка')
                console.log(error);
            })
    }

    const submit = function() {
        props.createNew ? newCompany() : editCompany()
    }

    const getCompany = function() {
        loading.value = true;

        axios.get(`company/${id.value}`)
            .then((response) => {
                if (response.data.data.success) {
                    form.value = response.data.data.data
                    form.value.description = form.value.description || ''
                    contactForm.value = JSON.parse(response.data.data.data.contacts || '{}')
                    myFiles.value = response.data.data.data.logo ? [response.data.data.data.logo] : []
                    loading.value = false;

                    if (nextTab.value) {
                        tab.value = nextTab.value
                    }
                } else {
                    alert('Компания не найдена')
                }
            })
            .catch((error) => {
                console.log(error);
            })
    }

    onMounted(async () => {
        await router.isReady()
        id.value = +route.params.id || 0
        props.createNew || getCompany()
        tab.value = route.query.tab || 'edit'
    })

    const changeTab = function(to) {
        nextTab.value = to
        if (formEl.value) {
            formEl.value.submit()
        } else {
            tab.value = nextTab.value
            router.push(`/account/company/${id.value}?tab=${nextTab.value}`)
        }
    }

    onBeforeRouteUpdate(async (to, from) => {
        console.log(to)
        if (!props.createNew && to.params.id !== from.params.id) {
            id.value = +to.params.id
            getCompany()
        }
    })

    const fields = [
        { name: 'name', label: 'Наименование организации', rules: [ val => val && val.length > 0 || 'Заполните это поле'] },
        { name: 'brand', label: 'Бренд' },
        { name: 'address', label: 'Адрес организации' },
        { name: 'phone', label: 'Рабочий телефон', type: 'tel' },
        { name: 'site', label: 'Сайт организации' },
        { name: 'email', label: 'E-mail организации', type: 'email' },
        { name: 'inn', label: 'ИНН' },
        { name: 'ogrn', label: 'ОГРН' }
    ]

    const contactsFields = [
        { name: 'group_vk', label: 'ВКонтакте' },
        { name: 'group_ok', label: 'Одноклассники' },
        { name: 'group_other', label: 'Другие профили (через запятую)' },
        { name: 'group_tg', label: 'Телеграм канал' },
        { name: 'msg_vk', label: 'ВКонтакте' },
        { name: 'msg_wa', label: 'WhatsApp' },
        { name: 'msg_skype', label: 'Skype' },
        { name: 'msg_viber', label: 'Viber' },
    ]
</script>

<template>
    <account-base>
        <q-inner-loading :showing="loading" color="primary"/>

        <div class="row q-col-gutter-lg">
            <div class="main-col col-12 col-md-2">
                <div>
                    <button @click="changeTab('edit')" :class="tab == 'edit' && 'bg-primary text-white'" class="tab-btn box">
                        <q-icon :name="mdiTextBoxOutline" size="2rem"/>
                        <div>Данные<br>о компании</div>
                    </button>
                </div>
                <div class="q-mt-lg">
                    <button @click="changeTab('contacts')" :class="tab == 'contacts' && 'bg-primary text-white'" class="tab-btn box">
                        <q-icon :name="mdiAccount" size="2rem"/>
                        <div>Все<br>контакты</div>
                    </button>
                </div>
                <div class="q-mt-lg">
                    <button @click="changeTab('logo')" :class="tab == 'logo' && 'bg-primary text-white'" class="tab-btn box">
                        <q-icon :name="mdiFileDocument" size="2rem"/>
                        <div>Загрузить<br>логотип</div>
                    </button>
                </div>
                <div class="q-mt-lg">
                    <button @click="changeTab('view')" :class="tab == 'view' && 'bg-primary text-white'" class="tab-btn box">
                        <q-icon :name="mdiEye" size="2rem"/>
                        <div>Просмотр</div>
                    </button>
                </div>
            </div>

            <!--  -->
            <div class="main-col col-12 col-md-10">
                <q-form v-if="tab == 'edit'" @submit="submit" ref="formEl" class="box">
                    <h2 class="text-h6">Введите общие данные организации:</h2>

                    <div class="row q-col-gutter-lg">
                        <template v-for="i in fields">
                            <div class="col-6">
                                <q-input v-model="form[i.name]" outlined :name="i.name" :type="i.type || 'text'" :label="i.label"
                                    :rules="i.rules || null"
                                    :hide-bottom-space="true"
                                    lazy-rules
                                />
                            </div>
                        </template>

                        <div class="col-12">
                            <p>Описание организации</p>
                            <q-editor v-model="form.description" min-height="5rem" />
                        </div>

                        <!-- <div class="col-12">
                            <q-btn label="Сохранить" type="submit" color="primary"/>
                        </div> -->
                    </div>
                </q-form>

                <!--  -->
                <q-form v-if="tab == 'contacts'" @submit="submit" ref="formEl" class="box" action="">
                    <h2 class="text-h6">Группы в соцсетях и каналы в мессенджерах:</h2>
                    <div class="row q-col-gutter-lg">
                        <template v-for="i in contactsFields">
                            <div v-if="i.name.startsWith('group_')" class="col-6">
                                <q-input v-model="contactForm[i.name]" outlined :name="i.name" :label="i.label"/>
                            </div>
                        </template>
                    </div>

                    <h2 class="text-h6">Мессенджеры:</h2>
                    <div class="row q-col-gutter-lg">
                        <template v-for="i in contactsFields">
                            <div v-if="i.name.startsWith('msg_')" class="col-6">
                                <q-input v-model="contactForm[i.name]" outlined :name="i.name" :label="i.label"/>
                            </div>
                        </template>
                    </div>
                </q-form>

                <!-- Лого -->
                <q-form v-if="tab == 'logo'" @submit="submit" ref="formEl" class="box "  action="">
                    <h2 class="text-h6">Загрузить логотип организации:</h2>
                    <app-file-pond
                        :files="myFiles"
                        ref="pond"
                    />
                </q-form>

                <!-- Просмотр  -->
                <div v-if="tab == 'view'" class="box">
                    <div>
                        <img width="160" :src="form.logo" alt="">
                    </div>

                    <h2 class="text-h6">Общие данные:</h2>
                    <template v-for="i in fields">
                        <p class="">
                            <b>{{ i.label }}:</b> {{ form[i.name] || '—' }} 
                        </p>
                    </template>

                    <h2 class="q-mt-lg text-h6">Группы в соцсетях и каналы в мессенджерах:</h2>
                    <template v-for="i in contactsFields">
                        <p v-if="i.name.startsWith('group_')" class="">
                            <b>{{ i.label }}:</b> {{ contactForm[i.name] || '—' }} 
                        </p>
                    </template>

                    <h2 class="q-mt-lg text-h6">Мессенджеры:</h2>
                    <template v-for="i in contactsFields">
                        <p v-if="i.name.startsWith('msg_')" class="">
                            <b>{{ i.label }}:</b> {{ contactForm[i.name] || '—' }} 
                        </p>
                    </template>
                </div>

                <!--  -->
                <div class="row justify-between q-mt-lg">
                    <template v-if="tab == 'edit'">
                        <q-btn label="Назад" color="dark" disabled/>
                        <q-btn @click="changeTab('contacts')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'contacts'">
                        <q-btn @click="changeTab('edit')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('logo')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'logo'">
                        <q-btn @click="changeTab('contacts')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('view')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'view'">
                        <q-btn @click="changeTab('logo')" label="Назад" color="dark"/>
                        <q-btn to="/account/companies" label="Финиш" color="positive"/>
                    </template>
                </div>
            </div>
        </div>
    </account-base>
</template>

<style lang="scss" scoped>
    .main-col {
        padding-top: 0;
    }
    .tab-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding-top: 32px;
        padding-bottom: 32px;
        width: 100%;
        border: none;
        box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1);
        text-align: center;
        color: #505d69;
        cursor: pointer;

        .q-icon {
            margin-bottom: 8px;
        }
    }
</style>