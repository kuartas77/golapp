import { usePageTitle } from "@/composables/use-meta";
import { useRoute } from 'vue-router';

export function routeName() {
    const route = useRoute()
    const routeName = route.name
    usePageTitle(routeName)
}
