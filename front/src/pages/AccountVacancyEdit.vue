<script setup>
    import { ref, onMounted, watch, watchEffect } from 'vue'
    import { useRoute, useRouter, onBeforeRouteUpdate } from 'vue-router'
    import axios from 'axios'
    import { mdiAccount, mdiTextBoxOutline, mdiFileDocument, mdiEye } from '@quasar/extras/mdi-v6'
    import AccountBase from './AccountBase.vue'
    import AppInput from '../components/AppInput.vue'
    import { showSuccess, showError } from '../functions.js'

    const route = useRoute()
    const router = useRouter()

    const id = ref(0)
    const loading = ref(false)
    const formEl = ref(null)
    const formEmpty = {
        description: '', short_description: '', salary: {}, areas: [], address: {}, key_skills: [],
        contacts: { show: true }, working_time_modes: [], misc: { accept: [] }
    }
    const form = ref(Object.assign({}, formEmpty,))
    const formChanged = ref(false)
    const values = ref({ specializations: [], areas: [] })
    const dicts = ref({})
    const skills = ref([])
    
    const tab = ref('edit')
    const nextTab = ref(null)
    const modals = ref({})

    const getVacancy = function() {
        loading.value = true;

        axios.get(`vacancy/${id.value}`)
            .then((response) => {
                if (response.data.data.success) {
                    form.value = response.data.data.data
                    for (let i in formEmpty) {
                        if (!form.value[i]) {
                            form.value[i] = formEmpty[i]
                        }
                    }
                    loading.value = false;
                } else {
                    showError('вакансия не найдена')
                }
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    const update = function() {
        loading.value = true;

        axios.post(`vacancy/update`, form.value)
            .then((response) => {
                showSuccess('Данные сохранены')
                formChanged.value = false
                if (nextTab.value) {
                    tab.value = nextTab.value
                }
                router.push(`/account/vacancy/${id.value}/edit?tab=${nextTab.value}`)
            })
            .catch((error) => showError(error))
            .finally(() => loading.value = false)
    }

    onMounted(async () => {
        await router.isReady()
        tab.value = route.query.tab || 'main'
        id.value = route.params.id
        getVacancy()
    })

    axios.get(`vacancy/data`)
        .then((response) => {
            console.log(response.data.data.data)
            values.value.specializations = response.data?.data?.data?.specializations?.categories || []
            values.value.areas = response.data?.data?.data?.areas || []
            dicts.value = response.data?.data?.data?.dictionaries || []
        })
        .catch((error) => showError(error))
        .finally(() => loading.value = false)

    const changeTab = function(to) {
        nextTab.value = to

        if ((tab.value == 'main' || tab.value == 'secondary') && formChanged.value) {
            formEl.value && formEl.value.submit()
        } else {
            tab.value = nextTab.value
            router.push(`/account/vacancy/${id.value}/edit?tab=${nextTab.value}`)
        }
    }

    const tabs = [
        { name: 'main', label: 'Основная<br>информация', icon: mdiTextBoxOutline },
        // { name: 'password', label: 'Контактные<br>данные', icon: mdiAccount },
        { name: 'secondary', label: 'Дополнительная<br>информация', icon: mdiFileDocument },
        { name: 'view', label: 'Просмотр', icon: mdiEye },
    ]

    const filterSkills = function(val, update) {
        if (val === '') {
            update(() => {
                skills.value = []
            })
            return
        }
        update(() => {
            axios.get(`vacancy/suggests?type=skills&text=${val}`)
                .then((response) => {
                    skills.value = response.data.data.data.items
                })
                .catch((error) => showError(error))
        })
    }

    watch(
        () => form,
        (newValue, oldValue) => {
            formChanged.value = true
        },
        { deep: true }
    )
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

                <q-form v-if="tab == 'main'" @submit="update()" greedy ref="formEl" class="box">
                    <h2 class="text-h6">Основная информация:</h2>

                    <div class="row q-col-gutter-lg">
                        <div class="col-12 col-md-8">
                            <app-input v-model="form.name" :opts="{ label: 'Название вакансии', validate: ['required'] }"/>
                        </div>
                        
                        <div class="col-12 col-md-4">
                            <app-input v-model="form.code" :opts="{ label: 'Внутренний код вакансии' }"/>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Специализация</div>
                            <div>{{ form.specialization }}</div>
                            <div  @click="modals.spec = true" class="q-mt-sm text-primary">Выбрать</div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Город, в котором ищем сотрудника</div>
                            <div>
                                <template v-for="area in form.areas">
                                    <q-chip removable>{{ area }}</q-chip>
                                </template>
                            </div>
                            <div @click="modals.areas = true" class="q-mt-sm text-primary">Выбрать</div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Предполагаемый уровень дохода в месяц или за объем работ</div>
                                <div class="row q-mb-sm q-col-gutter-md">
                                    <div class="col">
                                        <app-input v-model="form.salary.from" :opts="{ label: 'от' }"
                                        />
                                    </div>
                                    <div class="col">
                                        <app-input v-model="form.salary.to" :opts="{ label: 'до' }"
                                        />
                                    </div>
                                    <div class="col-3">
                                        <q-select
                                            v-model="form.salary.currency"
                                            outlined
                                            :options="[{ label: 'руб.', value: 'руб.' }]"
                                        />
                                    </div>
                                </div>
                                <q-option-group
                                    v-model="form.salary.gross"
                                    :options="[{ label: 'до вычета налогов', value: true }, { label: 'на руки', value: false }]"
                                    type="radio"
                                />
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Где будет работать сотрудник</div>
                            <q-option-group
                                v-model="form.address.show"
                                :options="[{ label: 'Не указывать адрес', value: false }, { label: 'Указывать адрес', value: true }]"
                                type="radio"
                            />
                        </div>
                        <div v-if="form.address.show" class="col-12">
                            <app-input
                                v-model="form.address.value"
                                :opts="{ label: 'Адрес' }"
                            />
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Опыт работы</div>
                            <div v-for="item in dicts.experience">
                                <q-radio v-model="form.experience" :val="item.id" :label="item.name" />
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Расскажите про вакансию</div>
                            <q-editor v-model="form.description" min-height="5rem"/>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Короткое описание вакансии</div>
                            <q-editor v-model="form.short_description" min-height="5rem"/>
                        </div>

                        <div class="col-12">
                            <q-select
                                outlined
                                v-model="form.key_skills"
                                :options="skills"
                                label="Ключевые навыки"
                                emit-value
                                map-options
                                use-chips
                                use-input
                                option-value="text"
                                option-label="text"
                                @filter="filterSkills"
                                multiple
                            />
                        </div>
                    </div>
                </q-form>

                <!--  -->
                <q-form v-if="tab == 'secondary'" greedy class="box" action="">
                    <h2 class="text-h6">Дополнительная информация:</h2>

                    <div class="row q-col-gutter-lg">
                        <div class="col-12">
                            <q-select
                                outlined
                                v-model="form.manager_id"
                                :options="[{ label: 'Менеджер вакансии', value: 1 }]"
                                label="Менеджер вакансии"
                            />
                            <q-checkbox
                                v-model="form.misc.notify_manager"
                                label="Уведомлять об откликах и сообщениях на почту менеджера"
                                :val="true"
                                class="q-mt-sm"
                            />
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Контактная информация</div>
                            <q-option-group
                                v-model="form.contacts.show"
                                :options="[{ label: 'Не показывать в вакансии', value: false }, { label: 'Показывать в вакансии', value: true }]"
                                type="radio"
                            />
                        </div>
                        <template v-if="form.contacts.show">
                            <div class="col-12 col-md-4">
                                <app-input v-model="form.contacts.name" :opts="{ label: 'Контактное лицо' }"/>
                            </div>
                            <div class="col-12 col-md-4">
                                <app-input v-model="form.contacts.email" :opts="{ label: 'Email' }"/>
                            </div>
                            <div class="col-12 col-md-4">
                                <app-input v-model="form.contacts.phone" :opts="{ label: 'Телефон' }"/>
                            </div>
                        </template>
                        
                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Тип занятости</div>
                            <q-checkbox
                                v-model="form.misc.accept"
                                label="Возможно временное оформление"
                                val="accept_temporary"
                            />
                            <div v-for="item in dicts.employment">
                                <q-radio v-model="form.employment" :val="item.id" :label="item.name" />
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Режим работы</div>
                            <template v-for="i in ['working_days', 'working_time_intervals', 'working_time_modes']">
                                <div>
                                    <q-checkbox
                                        v-model="form.working_time_modes"
                                        :label="dicts?.[i]?.[0].name"
                                        :val="dicts?.[i]?.[0].id"
                                    />
                                </div>
                            </template>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">График работы</div>
                            <div v-for="item in dicts.schedule">
                                <q-radio v-model="form.schedule" :val="item.id" :label="item.name" />
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Знание языков</div>
                            <div class="row q-col-gutter-md">
                                <div class="col-12 col-md-6">
                                    <q-select
                                        outlined
                                        v-model="form.language"
                                        :options="[{ label: 'Английский', value: 'en' }, { label: 'Немецкий', value: 'de' }]"
                                        label="Язык"
                                    />
                                </div>
                                <div class="col-12 col-md-6">
                                    <q-select
                                        outlined
                                        v-model="form.language_level"
                                        :options="dicts.language_level"
                                        label="Уровень"
                                        option-value="id"
                                        option-label="name"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <q-select
                                outlined
                                v-model="form.driver_license_types"
                                :options="dicts.driver_license_types"
                                label="Категория прав"
                                emit-value
                                map-options
                                use-chips
                                option-value="id"
                                option-label="id"
                                multiple
                            />
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Кто может откликаться</div>
                            <div>
                                <q-checkbox
                                    v-model="form.misc.accept"
                                    label="Соискатели с инвалидностью"
                                    val="accept_handicapped"
                                />
                            </div>
                            <div>
                                <q-checkbox
                                    v-model="form.misc.accept"
                                    label="Соискатели от 14 лет"
                                    val="accept_kids"
                                />
                            </div>
                            <div>
                                <q-checkbox
                                    v-model="form.misc.accept"
                                    label="Соискатели с неполным резюме"
                                    val="accept_incomplete_resumes"
                                />
                            </div>
                            <div>
                                <q-checkbox
                                    v-model="form.misc.accept"
                                    label="Только с сопроводительным письмом"
                                    val="response_letter_required"
                                />
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="text-weight-bold q-mb-sm">Чат с соискателем</div>
                            <q-option-group
                                v-model="form.misc.chat"
                                :options="[{ label: 'Включен, возможна переписка после отклика', value: true }, { label: 'Выключен', value: false }]"
                                type="radio"
                            />
                        </div>
                    </div>
                </q-form>

                <!-- Лого -->
                <div v-if="tab == 'files'" class="box">
                    333
                </div>

                <!-- Просмотр  -->
                <div v-if="tab == 'view'" class="box">
                    <!-- <h2 class="text-h6">Общие данные:</h2>
                    <template v-for="i in fields">
                        <p class="">
                            <b>{{ i.label }}:</b> {{ form[i.field] || '—' }} 
                        </p>
                    </template>

                    <p><b>О себе:</b><div v-html="form.description"></div></p> -->
                </div>

                <!--  -->
                <div class="row justify-between q-mt-lg">
                    <template v-if="tab == 'main'">
                        <q-btn label="Назад" color="dark" disabled/>
                        <q-btn @click="changeTab('secondary')" label="Вперёд" color="primary"/>
                    </template>
                    <template v-else-if="tab == 'secondary'">
                        <q-btn @click="changeTab('main')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('view')" label="Вперёд" color="primary"/>
                    </template>
                    <!-- <template v-else-if="tab == 'files'">
                        <q-btn @click="changeTab('password')" label="Назад" color="dark"/>
                        <q-btn @click="changeTab('view')" label="Вперёд" color="primary"/>
                    </template> -->
                    <template v-else-if="tab == 'view'">
                        <q-btn @click="changeTab('secondary')" label="Назад" color="dark"/>
                        <q-btn to="/account" label="Финиш" color="positive"/>
                    </template>
                </div>
            </div>
        </div>
    </account-base>

    <q-dialog v-model="modals.spec">
        <q-card>
            <q-card-section>
                <div class="text-h6">Выберите специализацию</div>
            </q-card-section>

            <q-card-section class="q-pt-none">
                <q-tree v-if="values.specializations.length"
                        :nodes="values.specializations || []"
                        node-key="name"
                        children-key="roles"
                        label-key="name"
                    >
                        <template v-slot:default-header="prop">
                            <div class="row items-center">
                                <q-radio
                                    v-if="!prop.node.roles?.length"
                                    v-model="form.specialization"
                                    :label="prop.node.name"
                                    :val="prop.node.name"
                                />
                                <div v-else class="">{{ prop.node.name }}</div>
                            </div>
                        </template>
                    </q-tree>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="OK" color="primary" v-close-popup />
            </q-card-actions>
        </q-card>
    </q-dialog>

    <q-dialog v-model="modals.areas">
        <q-card>
            <q-card-section>
                <div class="text-h6">Выберите город</div>
            </q-card-section>

            <q-card-section class="q-pt-none">
                <q-tree v-if="values.areas.length"
                        :nodes="values.areas || []"
                        node-key="id"
                        children-key="areas"
                        label-key="name"
                        :no-transition="true"
                    >
                        <template v-slot:default-header="prop">
                            <div class="row items-center">
                                <q-checkbox v-if="!prop.node.areas?.length"
                                    v-model="form.areas"
                                    :label="prop.node.name"
                                    :val="prop.node.name"
                                />
                                <div v-else class="">{{ prop.node.name }}</div>
                            </div>
                        </template>
                    </q-tree>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat label="OK" color="primary" v-close-popup />
            </q-card-actions>
        </q-card>
    </q-dialog>
</template>