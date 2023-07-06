import './style.scss'
import { createApp } from 'vue';
import { createRouter, createWebHashHistory } from 'vue-router'

import { Quasar, Notify, AppFullscreen, Dialog } from 'quasar'
import langRu from 'quasar/lang/ru'
import quasarIconSet from 'quasar/icon-set/svg-mdi-v7'
import 'quasar/dist/quasar.css'

import axios from 'axios'
axios.defaults.withCredentials = true;
axios.defaults.headers.post['Content-Type'] = 'application/json';
axios.defaults.baseURL = import.meta.env.VITE_API_URL + 'api';

import App from './App.vue'
import AuthLogin from './pages/AuthLogin.vue'
import AuthRegister from './pages/AuthRegister.vue'
import AuthForgot from './pages/AuthForgot.vue'
import AuthError from './pages/AuthError.vue'

import AccountHome from './pages/AccountHome.vue'
import AccountCompanies from './pages/AccountCompanies.vue'
import AccountCompanyNew from './pages/AccountCompanyNew.vue'
import AccountCompanyEdit from './pages/AccountCompanyEdit.vue'
import AccountProfile from './pages/AccountProfile.vue'
import AccountVacancyList from './pages/AccountVacancyList.vue'
import AccountVacancyEdit from './pages/AccountVacancyEdit.vue'

const routes = [
    { path: '/', redirect: '/auth/login' },
    { path: '/auth/login', component: AuthLogin, meta: { title: 'Вход' } },
    { path: '/auth/register', component: AuthRegister, meta: { title: 'Регистрация' } },
    { path: '/auth/forgot', component: AuthForgot, meta: { title: 'Забыли пароль?' } },
    { path: '/auth/error', component: AuthError, meta: { title: 'Ошибка' } },

    { path: '/account', component: AccountHome, meta: { title: 'Личный кабинет' } },
    { path: '/account/companies', component: AccountCompanies, meta: { title: 'Все организации' } },
    { path: '/account/company/new', component: AccountCompanyNew, meta: { title: 'Добавить организацию' } },
    { path: '/account/company/:id(\\d+)', component: AccountCompanyEdit, meta: { title: 'Огранизация' } },

    { path: '/account/profile', component: AccountProfile, meta: { title: 'Мой профиль' } },

    { path: '/account/company/:company_id(\\d+)/vacancies', component: AccountVacancyList, meta: { title: 'Мои вакансии' } },
    //{ path: '/account/vacancy/new', component: AccountVacancyNew, meta: { title: 'Добавить вакансию' } },
    { path: '/account/vacancy/:id(\\d+)/edit', component: AccountVacancyEdit, meta: { title: 'Вакансия' } },
]

const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

router.beforeEach((to, from, next) => {
    if (to.meta.title) {
        document.title = to.meta.title + ' | HR PLUS'
    }
    next()
})


const app = createApp(App);
app.use(router);
app.use(Quasar, {
    lang: langRu,
    plugins: {
        Notify,
        AppFullscreen,
        Dialog
    },
    config: {
        brand: {
            primary: '#3051d3',
            secondary: '#25c2e3',
            positive: '#43d39e',
            negative: '#ff5c75',
            dark: '#4B4B5A',
        },
        notify: {}
    },
    iconSet: quasarIconSet,
})
app.mount('#app');


