import { useAppState } from '@/store/app-state'
import { $themeConfig } from "@/theme.config";

export default {
    init() {
        const appState = useAppState()
        // set default styles
        let val = localStorage.getItem("dark_mode"); // light, dark, system
        if (!val) {
            val = $themeConfig.theme;
        }
        appState.toggleDarkMode(val);

        val = localStorage.getItem("menu_style"); // vertical, collapsible-vertical, horizontal
        if (!val) {
            val = $themeConfig.navigation;
        }
        appState.toggleMenuStyle(val);

        val = localStorage.getItem("layout_style"); // full, boxed-layout, large-boxed-layout
        if (!val) {
            val = $themeConfig.layout;
        }
        appState.toggleLayoutStyle(val);

        val = 'es'// en, da, de, el, es, fr, hu, it, ja, pl, pt, ru, sv, tr, zh
        if (!val) {
            val = $themeConfig.lang;

            const list = appState.countryList;
            const item = list.find((item) => item.code === val);
            if (item) {
                this.toggleLanguage(item);
            }
        }
    },

    toggleLanguage(item) {
        const appState = useAppState()
        let lang = null;
        if (item) {
            lang = item;
        } else {
            let code = appState.locale;
            if (!code) {
                code = localStorage.getItem("i18n_locale");
            }

            item = appState.countryList.find((d) => d.code === code);
            if (item) {
                lang = item;
            }
        }

        if (!lang) {
            lang = appState.countryList.find((d) => d.code === "en");
        }

        appState.toggleLocale(lang.code);
        return lang;
    },

    toggleMode(mode) {
        const appState = useAppState()
        if (!mode) {
            let val = localStorage.getItem("dark_mode"); //light|dark|system
            mode = val;
            if (!val) {
                mode = "light";
            }
        }
        appState.toggleDarkMode( mode || "light");
        return mode;
    },
};
