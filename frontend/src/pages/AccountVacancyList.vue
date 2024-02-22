<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { mdiDotsHorizontal, mdiClose, mdiPencil, mdiEye, mdiPlusCircleOutline, mdiMinusCircleOutline, mdiFolderOpenOutline } from '@quasar/extras/mdi-v6'
import AccountBase from './AccountBase.vue'
import AppTooltip from '../components/AppTooltip.vue'
import AppTableValue from '../components/AppTableValue.vue'
import { useElementSize } from '@vueuse/core'
import { useQuasar } from 'quasar'
import { showSuccess, showError } from '../functions.js'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const hiddenColumns = ref([])
const companyId = ref(0)
const rows = ref([])
const filter = ref({ status: '' })
const box = ref(null)
const { width } = useElementSize(box)
const $q = useQuasar()


const tableColumns = [
    { name: 'name', label: 'Наименование вакансии', tooltip: 'Вся информация о вакансии', align: 'left', sortable: true},
    { name: 'feedback', label: 'Отклики в работе', align: 'center', hideFrom: 900, sortable: false },
    { name: 'refusal_hr', label: 'Отказ HR', align: 'center', hideFrom: 900, sortable: false },
    { name: 'refusal_user', label: 'Отказ соискателя', align: 'center', hideFrom: 900, sortable: false },
    { name: 'reserved', label: 'В резерве', align: 'center', hideFrom: 900, sortable: false },
    { name: 'author', label: 'Создатель вакансии', tooltip: 'Личная страница создателя вакансии', align: 'left', hideFrom: 1050, sortable: false },
    { name: 'created_at', label: 'Дата создания', tooltip: 'Дата создания вакансии', align: 'left', hideFrom: 1300, sortable: false },
    { name: 'action', label: 'Действие', hideFrom: 400, sortable: false },
]

const visibleColumns = computed(() => {
    const visible = []
    hiddenColumns.value = []

    for (const col in tableColumns) {
        if ((tableColumns[col].hideFrom || 0) < width.value) {
            visible.push(tableColumns[col].name)
        } else {
            hiddenColumns.value.push(tableColumns[col].name)
        }
    }

    return visible
})

const getList = function() {
    loading.value = true
    
    axios.post('vacancy/list', { 'company_id': companyId.value, status: filter.value.status })
        .then((response) => {
            if (response.data?.data?.success) {
                rows.value = response.data.data.data
            } else {
                showError(response.data?.data?.message)
            }
        })
        .catch((error) => showError(error))
        .finally(() => loading.value = false)
}

const newVacancy = function() {
    loading.value = true
    
    axios.post('vacancy/new', { 'company_id': companyId.value})
        .then((response) => {
            if (response.data?.data?.success) {
                console.log(response.data.data)
                router.push(`/account/vacancy/${response.data.data.data.id}/edit`)
            } else {
                showError(response.data?.data?.message)
            }
        })
        .catch((error) => showError(error))
        .finally(() => loading.value = false)
}

const removeVacancy = function(id, name) {
    $q.dialog({
        title: `Удалить вакансию «${name || 'Без названия'}»?`,
        cancel: 'Отмена',
        persistent: true
    }).onOk(() => {
        axios.post('vacancy/delete', { id })
            .then((response) => {
                if (response.data.data.success) {
                    getList()
                    showSuccess('Вакансия удалена')
                } else {
                    showError(response.data.data.message)
                }
            })
            .catch((error) => showError(error))
    })
}

const changeStatus = function(id, name, status) {
    $q.dialog({
        title: `${status == 'closed' ? 'Закрыть' : 'Открыть'} вакансию «${name || 'Без названия'}»?`,
        cancel: 'Отмена',
        persistent: true
    }).onOk(() => {
        axios.post('vacancy/update', { id, status })
            .then((response) => {
                if (response.data.data.success) {
                    getList()
                    showSuccess(`Вакансия ${status == 'closed' ? 'закрыта' : 'открыта'}`)
                } else {
                    showError(response.data.data.message)
                }
            })
            .catch((error) => showError(error))
    })
}

const findColumn = function(name) {
    return tableColumns.find(el => el.name === name);
}

onMounted(async () => {
    await router.isReady()
    companyId.value = +route.params.company_id
    getList()
})
</script>

<template>
    <account-base>
        <q-inner-loading :showing="loading" color="primary"/>

        <div class="box" ref="box">

            <div class="row justify-between q-mb-md" >
                <q-btn @click="newVacancy()" label="Добавить вакансию" color="primary" class="col-12 col-sm-auto"/>
                <q-select
                    v-model="filter.status"
                    @update:model-value="getList()"
                    :options="[{ label: 'Только открытые', value: '' }, { label: 'Показать все', value: 'all' }]"
                    emit-value
                    map-options
                    outlined
                    class="col-12 col-sm-auto"
                />
            </div>

            <div v-if="!rows.length && !loading">
                Нет вакансий в компании
            </div>

            <q-table
                v-else
                :rows="rows" row-key="id" 
                :columns="tableColumns" :visible-columns="visibleColumns"
                binary-state-sort
                :rows-per-page-options="[0]" hide-bottom
                flat
                class="comp-table"
            >
                <!-- шапка -->
                <template v-slot:header="props">
                    <q-tr :props="props">
                        <q-th auto-width />
                        <q-th v-for="col in props.cols" :key="col.name" :props="props">
                            {{ col.label }}
                        </q-th>
                    </q-tr>
                </template>

                <template v-slot:body="props">
                    <q-tr :props="props">
                        <!-- кнопка + -->
                        <q-td auto-width>
                            <q-btn v-if="hiddenColumns.length && !props.expand"
                                @click="props.expand = !props.expand" round flat class="comp-table__expand-btn">
                                <q-icon :name="mdiPlusCircleOutline" size="22px" color="positive"/>
                            </q-btn>
                            <q-btn v-if="hiddenColumns.length && props.expand"
                                @click="props.expand = !props.expand" round flat class="comp-table__expand-btn">
                                <q-icon :name="mdiMinusCircleOutline" size="22px" color="negative"/>
                            </q-btn>
                        </q-td>
                        <!-- Ячейка -->
                        <q-td v-for="col in props.cols" :key="col.name" :props="props">
                            <template v-if="col.name === 'name'">
                                <router-link :to="`/account/vacancy/${props.row.id}/edit`"
                                    :class="props.row.status == 'closed' ? 'text-grey' : 'text-primary'"
                                >
                                    <b>{{ props.row.name || 'Без названия' }}</b>
                                    <app-tooltip>{{ col.tooltip }}</app-tooltip>
                                </router-link>
                            </template>

                            <template v-else-if="['feedback', 'refusal_hr', 'refusal_user', 'reserved'].includes(col.name)">
                                <app-table-value
                                    :link="`#`"
                                    :value="0"
                                    :tooltip="col.label"
                                    type="badge-count"
                                />
                            </template>
                            
                            <template v-else-if="col.name === 'action'">
                                <q-btn-dropdown
                                    flat :dropdown-icon="mdiDotsHorizontal" class="text-primary" size="12px"
                                    padding="6px" no-icon-animation 
                                >
                                    <q-list class="comp-table__menu">
                                        <q-item :to="`/account/vacancy/${props.row.id}/edit?tab=view`">
                                            <q-item-section avatar><q-icon :name="mdiEye" size="14px" color="secondary"/></q-item-section>
                                            <q-item-section><q-item-label>Просмотр</q-item-label> </q-item-section>
                                        </q-item>
                                        <q-item :to="`/account/vacancy/${props.row.id}/edit`">
                                            <q-item-section avatar><q-icon :name="mdiPencil" size="14px" color="positive"/></q-item-section>
                                            <q-item-section><q-item-label>Редактировать</q-item-label></q-item-section>
                                        </q-item>
                                        <q-item v-if="props.row.status != 'closed'" clickable v-close-popup @click="changeStatus(props.row.id, props.row.name, 'closed')">
                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px"/></q-item-section>
                                            <q-item-section><q-item-label>Закрыть</q-item-label></q-item-section>
                                        </q-item>
                                        <q-item v-if="props.row.status == 'closed'" clickable v-close-popup @click="changeStatus(props.row.id, props.row.name, 'opened')">
                                            <q-item-section avatar><q-icon :name="mdiFolderOpenOutline" size="14px"/></q-item-section>
                                            <q-item-section><q-item-label>Открыть</q-item-label></q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup @click="removeVacancy(props.row.id, props.row.name)">
                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px" color="negative"/></q-item-section>
                                            <q-item-section><q-item-label>Удалить</q-item-label></q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-btn-dropdown>
                            </template>

                            <template v-else-if="col.name === 'author'">
                                <q-badge color="positive">
                                    {{ props.row.author || '—' }}
                                    <app-tooltip class="bg-dark">{{ col.tooltip }}</app-tooltip>
                                </q-badge>
                            </template>

                            <template v-else-if="col.name === 'created_at'">
                                <q-badge class="_light">
                                    {{ props.row.created_at }}
                                    <app-tooltip class="bg-dark">{{ col.tooltip }}</app-tooltip>
                                </q-badge>
                            </template>

                            <template v-else>
                                <b>{{ props.row.value }}</b>
                            </template>
                        </q-td>
                    </q-tr>
                    <!-- скрытый контент -->
                    <q-tr v-show="props.expand" :props="props">
                        <q-td colspan="100%">
                            <div class="text-left">
                                <div class="comp-table__hidden">
                                    <div v-for=" i in hiddenColumns" class="comp-table__hidden-row row justify-between items-center no-wrap">
                                        <div><b>{{ findColumn(i).label }}</b></div>

                                        <div>
                                            <template v-if="['feedback', 'refusal_hr', 'refusal_user', 'reserved'].includes(i)">
                                                <app-table-value
                                                    :link="`#`"
                                                    :value="0"
                                                    :tooltip="findColumn(i).label"
                                                    type="badge-count"
                                                />
                                            </template>

                                            <template v-else-if="i === 'author'">
                                                <q-badge color="positive">
                                                    {{ props.row[i] || '—' }}
                                                    <app-tooltip class="bg-dark">{{ findColumn(i).tooltip }}</app-tooltip>
                                                </q-badge>
                                            </template>

                                            <template v-else-if="i === 'created_at'">
                                                <q-badge class="_light">
                                                    {{ props.row[i] }}
                                                    <app-tooltip class="bg-dark">{{ findColumn(i).tooltip }}</app-tooltip>
                                                </q-badge>
                                            </template>

                                            <template v-else-if="i === 'action'">
                                                <q-btn-dropdown
                                                    flat :dropdown-icon="mdiDotsHorizontal" class="text-primary" size="12px"
                                                    padding="6px" no-icon-animation 
                                                >
                                                    <q-list class="comp-table__menu">
                                                        <q-item :to="`/account/vacancy/${props.row.id}/edit?tab=view`">
                                                            <q-item-section avatar><q-icon :name="mdiEye" size="14px" color="secondary"/></q-item-section>
                                                            <q-item-section><q-item-label>Просмотр</q-item-label> </q-item-section>
                                                        </q-item>
                                                        <q-item :to="`/account/vacancy/${props.row.id}/edit`">
                                                            <q-item-section avatar><q-icon :name="mdiPencil" size="14px" color="positive"/></q-item-section>
                                                            <q-item-section><q-item-label>Редактировать</q-item-label></q-item-section>
                                                        </q-item>
                                                        <q-item v-if="props.row.status != 'closed'" clickable v-close-popup @click="changeStatus(props.row.id, props.row.name, 'closed')">
                                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px"/></q-item-section>
                                                            <q-item-section><q-item-label>Закрыть</q-item-label></q-item-section>
                                                        </q-item>
                                                        <q-item v-if="props.row.status == 'closed'" clickable v-close-popup @click="changeStatus(props.row.id, props.row.name, 'opened')">
                                                            <q-item-section avatar><q-icon :name="mdiFolderOpenOutline" size="14px"/></q-item-section>
                                                            <q-item-section><q-item-label>Открыть</q-item-label></q-item-section>
                                                        </q-item>
                                                        <q-item clickable v-close-popup @click="removeCompany(props.row.id, props.row.name)">
                                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px" color="negative"/></q-item-section>
                                                            <q-item-section><q-item-label>Удалить</q-item-label></q-item-section>
                                                        </q-item>
                                                    </q-list>
                                                </q-btn-dropdown>
                                            </template>

                                            <template v-else>
                                                <b>{{ props.row[i] }}</b>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </q-td>
                    </q-tr>
                </template>
            </q-table>
        </div>
    </account-base>
</template>

<style lang="scss">
.q-menu:has(.comp-table__menu) {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    padding: 0.5rem 0;

    .q-item {
        min-height: 40px;
    }

    .q-item__section--side {
        padding-right: 8px;
    }
}

.comp-table {
    td,
    th {
        font-size: 1em !important;

        &:first-child {
            padding: 0;
        }
    }

    th {
        font-weight: 700;
        padding-top: 1rem;
        padding-bottom: 1rem;
        background: #F6F6F7;
    }

    &__expand-btn {
        .q-focus-helper {
            display: none;
        }
    }

    &__hidden {
        max-width: min-content;
        padding-left: 12px;

        &-row {
            padding: 0.5rem 0;
            
            &:not(:last-child) {
                border-bottom: 1px solid #efefef;
            }

            & > div:first-child {
                margin-right: 1em;
            }
        }
    }

    .q-badge {
        font-weight: 600;

        &._light {
            background: #e2e7f1 !important;
            color: #1e2139
        }
    }

    a {
        display: inline-block;
        text-decoration: none;
        max-width: 240px;
        white-space: normal;
    }
}
</style>