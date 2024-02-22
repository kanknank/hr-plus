import { reactive, ref } from 'vue'

const store = reactive({
    route: {}
});

// const getRoute = async function() {
//     const route = useRoute()
//     const router = useRouter()

//     await router.isReady()

//     router.beforeEach((to, from, next) => {
//         if (to.meta.title) {
//             document.title = to.meta.title + ' | HR PLUS'
//         }
//         next()
//     })
//     console.log(route.path)
// }

export { store }