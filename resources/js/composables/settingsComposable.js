import { ref, onMounted } from 'vue';
import { useSetting } from '@/store/settings-store';

export default function useSettings() {

    const settings = useSetting()

    onMounted(async() => {
        settings.getSettings()
    });

}
