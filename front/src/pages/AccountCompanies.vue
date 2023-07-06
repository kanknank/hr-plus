<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { mdiDotsHorizontal, mdiClose, mdiPencil, mdiEye, mdiPlusCircleOutline, mdiMinusCircleOutline } from '@quasar/extras/mdi-v6'
import AccountBase from './AccountBase.vue'
import AppTooltip from '../components/AppTooltip.vue'
import AppTableValue from '../components/AppTableValue.vue'
import { useElementSize } from '@vueuse/core'
import { useQuasar } from 'quasar'
import { showSuccess, showError } from '../functions.js'

const loading = ref(true)
const hiddenColumns = ref([])
const rows = ref([])
const box = ref(null)
const { width } = useElementSize(box)
const $q = useQuasar()

const tableColumns = [
    { name: 'name', label: 'Наименование организации', field: 'name', align: 'left', sortable: true},
    { name: 'inn', label: 'ИНН', field: 'inn', align: 'left', hideFrom: 1200, sortable: false },
    { name: 'vacancy_count', label: 'Вакансии', field: 'vacancy_count', align: 'center', hideFrom: 900, sortable: false },
    { name: 'users', label: 'Пользователи', field: 'users', align: 'center', hideFrom: 900, sortable: false },
    { name: 'feedback', label: 'Отклики в работе', field: 'feedback', align: 'center', hideFrom: 900, sortable: false },
    { name: 'author', label: 'Создатель аккаунта', field: 'author', align: 'left', hideFrom: 1050, sortable: false },
    { name: 'createdon', label: 'Дата создания', field: 'createdon', align: 'left', hideFrom: 1300, sortable: false },
    { name: 'action', label: 'Действие', field: 'action', hideFrom: 400, sortable: false },
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

const getCompanies = function(name) {
    loading.value = true
    
    axios.post('company/list')
        .then((response) => {
            rows.value = response.data.data.data
            console.log(response.data.data.data)
        })
        .catch((error) => showError(error))
        .finally(() => loading.value = false)
}

const findColumn = function(name) {
    return tableColumns.find(el => el.name === name);
}

const removeCompany = function(id, name) {
    $q.dialog({
        title: `Удалить компанию «${name}»?`,
        cancel: 'Отмена',
        persistent: true
    }).onOk(() => {
        axios.post('company/delete', { id })
            .then((response) => {
                console.log(response.data)
                if (response.data.data.success) {
                    getCompanies()
                    showSuccess('Компания удалена')
                } else {
                    showError(response.data.data.message)
                }
            })
            .catch((error) => showError(error))
    })
}

getCompanies()
</script>

<template>
    <account-base>
        <q-inner-loading :showing="loading" color="primary"/>

        <div class="box" ref="box">
            <q-table
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
                                <router-link :to="`/account/company/${props.row.id}`" class="text-primary">
                                    <b>{{ props.row.name }}</b>
                                    <app-tooltip>Вся информация об организации</app-tooltip>
                                </router-link>
                            </template>

                            <template v-else-if="col.name === 'vacancy_count'">
                                <app-table-value
                                    :link="`/account/company/${props.row.id}/vacancies`"
                                    :value="props.row.vacancy_count"
                                    tooltip="Посмотреть все вакансии организации"
                                    type="badge-count"
                                />
                            </template>

                            <template v-else-if="col.name === 'users'">
                                <q-badge color="negative">0<app-tooltip class="bg-dark">Пригласить специалиста в организацию</app-tooltip></q-badge>
                            </template>

                            <template v-else-if="col.name === 'feedback'">
                                <q-badge color="negative">0<app-tooltip class="bg-dark">Нет откликов на вакансии организации</app-tooltip></q-badge>
                            </template>
                            
                            <template v-else-if="col.name === 'action'">
                                <q-btn-dropdown
                                    flat :dropdown-icon="mdiDotsHorizontal" class="text-primary" size="12px"
                                    padding="6px" no-icon-animation 
                                >
                                    <q-list class="comp-table__menu">
                                        <q-item :to="`/account/company/${props.row.id}?tab=view`">
                                            <q-item-section avatar><q-icon :name="mdiEye" size="14px" color="secondary"/></q-item-section>
                                            <q-item-section><q-item-label>Просмотр</q-item-label> </q-item-section>
                                        </q-item>
                                        <q-item :to="`/account/company/${props.row.id}`">
                                            <q-item-section avatar><q-icon :name="mdiPencil" size="14px" color="positive"/></q-item-section>
                                            <q-item-section><q-item-label>Редактирование</q-item-label></q-item-section>
                                        </q-item>
                                        <q-item clickable v-close-popup @click="removeCompany(props.row.id, props.row.name)">
                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px" color="negative"/></q-item-section>
                                            <q-item-section><q-item-label>Удаление</q-item-label></q-item-section>
                                        </q-item>
                                    </q-list>
                                </q-btn-dropdown>
                            </template>

                            <template v-else-if="col.name === 'author'">
                                <q-badge color="positive">
                                    {{ col.value || '—' }}
                                    <app-tooltip class="bg-dark">Личная страница создателя аккаунта</app-tooltip>
                                </q-badge>
                            </template>

                            <template v-else-if="col.name === 'createdon'">
                                <q-badge class="_light">
                                    {{ col.value }}
                                    <app-tooltip class="bg-dark">Дата создания профиля организации</app-tooltip>
                                </q-badge>
                            </template>

                            <template v-else>
                                <b>{{ col.value }}</b>
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
                                            <template v-if="i === 'vacancy_count'">
                                                <q-badge color="positive">{{ props.row[i] }}<app-tooltip class="bg-dark">Посмотреть все вакансии организации</app-tooltip></q-badge>
                                            </template>

                                            <template v-else-if="i === 'users'">
                                                <q-badge color="negative">0<app-tooltip class="bg-dark">Пригласить специалиста в организацию</app-tooltip></q-badge>
                                            </template>

                                            <template v-else-if="i === 'feedback'">
                                                <q-badge color="negative">0<app-tooltip class="bg-dark">Нет откликов на вакансии организации</app-tooltip></q-badge>
                                            </template>

                                            <template v-else-if="i === 'author'">
                                                <q-badge color="positive">
                                                    {{ props.row[i] || '—' }}
                                                    <app-tooltip class="bg-dark">Личная страница создателя аккаунта</app-tooltip>
                                                </q-badge>
                                            </template>

                                            <template v-else-if="i === 'createdon'">
                                                <q-badge class="_light">
                                                    {{ props.row[i] }}
                                                    <app-tooltip class="bg-dark">Дата создания профиля организации</app-tooltip>
                                                </q-badge>
                                            </template>

                                            <template v-else-if="i === 'action'">
                                                <q-btn-dropdown
                                                    flat :dropdown-icon="mdiDotsHorizontal" class="text-primary" size="12px"
                                                    padding="6px" no-icon-animation 
                                                >
                                                    <q-list class="comp-table__menu">
                                                        <q-item :to="`/account/company/${props.row.id}?tab=view`">
                                                            <q-item-section avatar><q-icon :name="mdiEye" size="14px" color="secondary"/></q-item-section>
                                                            <q-item-section><q-item-label>Просмотр</q-item-label> </q-item-section>
                                                        </q-item>
                                                        <q-item :to="`/account/company/${props.row.id}`">
                                                            <q-item-section avatar><q-icon :name="mdiPencil" size="14px" color="positive"/></q-item-section>
                                                            <q-item-section><q-item-label>Редактирование</q-item-label></q-item-section>
                                                        </q-item>
                                                        <q-item clickable v-close-popup @click="removeCompany(props.row.id, props.row.name)">
                                                            <q-item-section avatar><q-icon :name="mdiClose" size="14px" color="negative"/></q-item-section>
                                                            <q-item-section><q-item-label>Удаление</q-item-label></q-item-section>
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