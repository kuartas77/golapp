import { useAuthUser } from '@/store/auth-user'

const vHasRol = {
    // called before bound element's attributes
    // or event listeners are applied
    // created(el, binding, vnode) {
    // see below for details on arguments
    //},
    // called right before the element is inserted into the DOM.
    // beforeMount(el, binding, vnode) {},
    // called when the bound element's parent component
    // and all its children are mounted.
    mounted(el, binding, vnode) {
        const { value: { roles } } = binding
        const userStore = useAuthUser()
        const userRoleName = userStore.user.role.name
        roles.includes(userRoleName) ? null: el.remove()
    },
    // called before the parent component is updated
    // beforeUpdate(el, binding, vnode, prevVnode) {},
    // called after the parent component and
    // all of its children have updated
    updated(el, binding, vnode, prevVnode) {
        const { value: { roles } } = binding
        const userStore = useAuthUser()
        const userRoleName = userStore.user.role.name
        roles.includes(userRoleName) ? null: el.remove()
    },
    // called before the parent component is unmounted
    // beforeUnmount(el, binding, vnode) {},
    // called when the parent component is unmounted
    // unmounted(el, binding, vnode) {}
}

export default vHasRol