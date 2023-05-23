<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import axios from 'axios'
import { useRoute, useRouter } from 'vue-router'
import { mdiMenuDown, mdiMenu, mdiFullscreen,  mdiFullscreenExit, mdiAccountOutline, mdiAccountDetails, mdiExitToApp, mdiChevronRight, mdiChevronDown } from '@quasar/extras/mdi-v6'
import { useQuasar } from 'quasar'
import { showSuccess, showError } from '../functions.js'

const $q = useQuasar()
const route = useRoute()
const router = useRouter()
const loading = ref(true)
const title = ref('')
const user = ref({})
const year = new Date().getFullYear()

const logout = function () {
    axios.get('auth/logout')
        .then((response) => {
            delete sessionStorage.user
            if (response.data.data.success === true) {
                router.push(`/auth/login`)
            }
        })
        .catch((error) => {
            showError(error)
            delete sessionStorage.user
        })
}

onMounted(async () => {
    await router.isReady()
    title.value = route.meta.title || ''

    let userFromStorage = sessionStorage.user;
    try {
        userFromStorage = JSON.parse(userFromStorage);
    } catch (error) {
        userFromStorage = null;
    }

    if (userFromStorage) {
        user.value = userFromStorage;
    } else {
        axios.get('user')
            .then((response) => {
                if (response.data.data.success === true) {
                    user.value = response.data.data.data
                    sessionStorage.user = JSON.stringify(user.value);
                } else {
                    router.push(`/auth/login`)
                }
            })
            .catch((error) => {
                showError(error)
                router.push(`/auth/login`)
            })
            .finally(() => {
                loading.value = false
            })
    }
})

const leftDrawerOpen = ref(false)
const expandedTree = ref([])

function toggleLeftDrawer () {
    leftDrawerOpen.value = !leftDrawerOpen.value
}

const menu = [
    {
        label: 'Организации',
        children: [
            {
                to: '/account/companies',
                label: 'Все организации',
            },
            {
                to: '/account/company/new',
                label: 'Добавить новую организацию',
            },
        ]
    }
]
</script>

<template>
    <q-layout view="lHh Lpr lff">

        <!-- <q-inner-loading :showing="loading" color="primary" size="128px" class="_main"/> -->

        <q-header>
            <q-toolbar>
                <q-btn flat dense round @click="toggleLeftDrawer" :icon="mdiMenu" size="16px"/>
                <q-toolbar-title>HR PLUS</q-toolbar-title>

                <q-btn
                    flat dense
                    @click="$q.fullscreen.toggle()"
                    :icon="$q.fullscreen.isActive ? mdiFullscreenExit : mdiFullscreen"
                    size="18px"
                    class="q-mr-md"
                />
                
                <q-btn-dropdown stretch flat :dropdown-icon="mdiChevronDown" class="text-lowercase">
                    <template v-slot:label>
                        <div class="row items-center no-wrap q-gutter-x-sm">
                            <q-avatar size="32px" color="white" text-color="primary">A</q-avatar>
                            <div>{{ user.email }}</div>
                        </div>
                    </template>
                    <q-list>
                        <q-item to="/account/profile">
                            <q-item-section avatar>
                                <q-icon :name="mdiAccountOutline" size="16px" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Личный профиль</q-item-label>
                                <!-- <q-item-label caption>February 22, 2016</q-item-label> -->
                            </q-item-section>
                        </q-item>
                        <q-item clickable class="text-negative">
                            <q-item-section avatar>
                                <q-icon :name="mdiExitToApp" size="16px" />
                            </q-item-section>
                            <q-item-section @click="logout">
                                <q-item-label>Выход</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-btn-dropdown>
            </q-toolbar>
        </q-header>

        <q-drawer v-model="leftDrawerOpen" show-if-above :width="250" bordered>
            <q-tree :nodes="menu" node-key="label" default-expand-all no-connectors :icon="mdiChevronRight"
                v-model:expanded="expandedTree">
                <template v-slot:default-header="prop">
                    <router-link v-if="prop.node.to" :to="prop.node.to">{{ prop.node.label }}</router-link>
                    <div v-else>
                        <q-icon :name="mdiAccountDetails" class="" />
                        {{ prop.node.label }}
                    </div>
                </template>
            </q-tree>
        </q-drawer>

        <q-page-container>
            <div class="acc-subheader bg-primary text-white">
                <h1 class="text-h5 text-weight-bold">{{ title }}</h1>
            </div>

            <div class="acc-main">
                <slot></slot>
            </div>
        </q-page-container>

        <q-footer class="bg-white text-black">
            <div class="row justify-between q-py-md q-px-lg">
                <div class="">{{ year }} © HRPLUS.</div>
                <div class="">сделано в <a href="#" class="text-primary">ТОЧКА РОСТА МАРКЕТИНГ</a></div>
            </div>
        </q-footer>

    </q-layout>
</template>


<style lang="scss">
.q-page-container {
    min-height: 100vh;
    background-color: #f4f8f9;
}

.q-toolbar {
    padding: 0 1.5rem 0 1rem;

    .q-btn-dropdown {
        padding-top: 18px;
        padding-bottom: 18px;
    }

    .q-btn-dropdown--simple * + .q-btn-dropdown__arrow {
        font-size: 16px;
    }

    .q-toolbar__title {
        @media (max-width: 575px) {
            font-size: 0;
        }
    }
}

.q-item__section--avatar {
    min-width: 0;
}

.q-tree {
    color: inherit;

    &__node-header {
        flex-direction: row-reverse;
        padding: 12px 30px;
        font-size: 15px;
        border-left: 3px solid transparent;

        &-content {
            color: inherit;

            a {
                text-decoration: none;
                color: inherit;

                &.router-link-active {
                    color: var(--q-primary);
                }
            }

            & .q-icon {
                width: 16px;
                height: 16px;
                margin: 0 10px 0 3px;

                & svg {
                    stroke: inherit;
                    color: inherit;
                }
            }
        }
    }

    &__node--child {
        padding: 8px 20px;

        div {
            padding: 0;
            font-size: 1em;
            margin: 0;
            border: none;
        }
    }

    &__children {
        padding-left: 45px;
    }
}

.acc-subheader {
    padding: 1.5rem 1.5rem 6.5rem;

    h1 {
        margin: 0;
    }
}

.acc-main {
    position: relative;
    margin: -45px 1.5rem 0;
    padding-bottom: 60px;

    // @media (max-width: 575px) {
    //     margin: -45px 0 0;
    // }
}

.box {
    position: relative;
    padding: 1.25rem;
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.03);

    h2:first-child {
        margin-top: 0;
    }
}
</style>