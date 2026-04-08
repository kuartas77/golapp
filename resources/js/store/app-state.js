import { defineStore } from "pinia";

export const useAppState = defineStore('app-state', {

    state: () => ({
        current_layout: "app",
        is_show_sidebar: true,
        is_show_search: false,
        is_dark_mode: false,
        dark_mode: "light",
        locale: null,
        menu_style: "vertical",
        layout_style: "full",
        countryList: [
            { code: "en", name: "English" },
            { code: "es", name: "Spanish" },
        ],
    }),
    getters: {
        layout: (state) => state.current_layout
    },
    actions: {
        setLayout (payload) {
            this.current_layout = payload
        },
        toggleSideBar (value) {
            this.is_show_sidebar = value
        },
        toggleSearch (value) {
            this.is_show_search = value
        },
        toggleLocale (value) {
            value = value || "es";
            localStorage.setItem("i18n_locale", value);
            this.locale = value;
        },
        toggleDarkMode(value) {
            //light|dark|system
            value = value || "light";
            localStorage.setItem("dark_mode", value);
            this.dark_mode = value;
            if (value == "light") {
                this.is_dark_mode = false;
            } else if (value == "dark") {
                this.is_dark_mode = true;
            } else if (value == "system") {
                if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    this.is_dark_mode = true;
                } else {
                    this.is_dark_mode = false;
                }
            }

            if (this.is_dark_mode) {
                document.querySelector("body").classList.add("dark");
            } else {
                document.querySelector("body").classList.remove("dark");
            }
        },
        toggleMenuStyle(value) {
            //horizontal|vertical|collapsible-vertical
            value = value || "";
            localStorage.setItem("menu_style", value);
            this.menu_style = value;
            if (!value || value === "vertical") {
                this.is_show_sidebar = true;
            } else if (value === "collapsible-vertical") {
                this.is_show_sidebar = false;
            }
        },
        toggleLayoutStyle(value) {
            //boxed-layout|large-boxed-layout|full
            value = value || "";
            localStorage.setItem("layout_style", value);
            this.layout_style = value;
        },
        clearState () {
            this.$reset()
        }
    }
})