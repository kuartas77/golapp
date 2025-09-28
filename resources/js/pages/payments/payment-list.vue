<template>
    <div class="layout-px-spacing mailbox">

        <div class="row layout-top-spacing">
            <div class="col-xl-1 col-lg-1 col-md-1" ></div>
            <div class="col-xl-10 col-lg-10 col-md-10">
                <div class="row">
                    <div class="col-xl-12 col-md-12">
                        <div class="mail-box-container">
                            <div class="mail-overlay" :class="{ 'mail-overlay-show': is_show_mail_menu }"
                                @click="is_show_mail_menu = false"></div>

                            <div class="tab-title" :class="{ 'mail-menu-show': is_show_mail_menu }">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-12 text-center mail-btn-container">
                                        <div class="avatar avatar-sm me-1">
                                            <img :src="userState.user.school_logo" class="user-profile" alt="avatar" />
                                        </div>
                                    </div>
                                    <!-- MENU LATERAL -->
                                    <div class="col-md-12 col-sm-12 col-12 mail-categories-container">
                                        <perfect-scrollbar class="mail-sidebar-scroll" :options="scrollbarOptions">
                                            <ul class="nav nav-pills d-block" id="pills-tab" role="tablist">

                                                <li class="nav-item" v-for="group in groups" :key="group.id">
                                                    <a class="nav-link list-actions"
                                                        :class="{ active: selected_group?.id == group.id }"
                                                        @click="group_changed(group.id)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-users">
                                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="9" cy="7" r="4"></circle>
                                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                        </svg>
                                                        <span class="nav-names">{{ group.name }}</span>
                                                        <span class="mail-badge badge">{{ group.members_count }}</span>
                                                    </a>
                                                </li>

                                            </ul>
                                        </perfect-scrollbar>
                                    </div>
                                </div>
                            </div>

                            <div id="mailbox-inbox" class="accordion mailbox-inbox">
                                <div class="search">
                                    <a @click="is_show_mail_menu = !is_show_mail_menu">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="feather feather-menu mail-menu d-lg-none">
                                            <line x1="3" y1="12" x2="21" y2="12"></line>
                                            <line x1="3" y1="6" x2="21" y2="6"></line>
                                            <line x1="3" y1="18" x2="21" y2="18"></line>
                                        </svg>
                                    </a>
                                    <input type="text" v-model.trim="search_mail" class="input-search form-control"
                                        v-on:keyup="search_mails()" placeholder="Buscar el deportista aquí..." />
                                </div>

                                <div v-if="selected_group" class="action-center">
                                    <span class="mail-title">{{ selected_group.full_schedule_group }}</span>
                                </div>

                                <!-- LISTADO DE MENSAJES -->
                                <div v-show="selected_group" class="message-box">
                                    <perfect-scrollbar class="message-box-scroll" id="ct" :options="scrollbarOptions">
                                        <div v-for="(player, index) in players_group" :key="player.id + '' + index"
                                            class="mail-item"
                                            @click="selected_player(player)">
                                            <div class="animated fadeInUp">
                                                <div class="mb-0">
                                                    <div class="mail-item-heading">
                                                        <div class="mail-item-inner">
                                                            <div class="d-flex">

                                                                <div class="f-head">
                                                                    <img :src="player.photo_url" class="user-profile" alt="avatar" />
                                                                </div>
                                                                <div class="f-body">
                                                                    <div class="meta-mail-time">
                                                                        <p v-if="player.full_names" class="user-email">{{ player.full_names }}</p>
                                                                        <p v-else class="user-email">{{ player.email }}</p>
                                                                    </div>
                                                                    <div class="meta-title-tag">
                                                                        <p class="mail-content-excerpt">Código Único: {{ player.unique_code }}</p>

                                                                        <!-- <p class="meta-time align-self-center">{{ show_time(item) }}</p> -->
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </perfect-scrollbar>
                                </div>

                                <!-- CUANDO SE SELECCIONA UN MAIL -->
                                <div v-if="player_selected" class="content-box w-100 left-0">
                                    <div class="d-flex msg-close">
                                        <a @click="selected_player()">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-arrow-left close-message">
                                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                                <polyline points="12 19 5 12 12 5"></polyline>
                                            </svg>
                                        </a>
                                        <h2 class="mail-title">{{ player_selected.full_names }}</h2>
                                    </div>

                                    <perfect-scrollbar class="content-scroll">
                                        <div class="mail-content-container">
                                            <div class="d-flex justify-content-between mb-3">
                                                <div class="d-flex user-info">
                                                    <div class="f-head">
                                                        <img :src="player_selected.photo_url" class="user-profile" alt="avatar" />

                                                    </div>
                                                    <div class="f-body">
                                                        <div class="meta-title-tag">
                                                            <h4 class="mail-usr-name">{{ selected_group.full_group }}</h4>
                                                            <h4 class="mail-usr-name">{{ player_selected.unique_code }}</h4>
                                                        </div>
                                                        <div class="meta-mail-time">
                                                            <p class="user-email">{{ player_selected.category }}</p>
                                                            <!-- <p class="mail-content-meta-date current-recent-mail ms-1">
                                                                {{ player_selected.date }} -</p>
                                                            <p class="meta-time align-self-center ms-1">{{
                                                                player_selected.time }}</p> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- <div v-html="selected_mail.description"></div> -->

                                            <p class="mt-4">Best Regards,</p>
                                            <!-- <p v-if="selected_mail.first_name">{{ selected_mail.first_name + " " +
                                                selected_mail.last_name }}</p>
                                            <p v-else>{{ selected_mail.email }}</p>

                                            <div v-if="selected_mail.attachments && selected_mail.attachments.length"
                                                class="attachments">
                                                <h6 class="attachments-section-title">Attachments</h6>
                                                <div v-for="(files, ind) in selected_mail.attachments"
                                                    :key="'fle' + selected_mail.id + ind"
                                                    class="attachment file-pdf file-folder file-img">
                                                    <div class="media">
                                                        <template v-if="files.type == 'image'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-image">
                                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                                                </rect>
                                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                                <polyline points="21 15 16 10 5 21"></polyline>
                                                            </svg>
                                                        </template>
                                                        <template v-else-if="files.type == 'folder'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-folder">
                                                                <path
                                                                    d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                                                </path>
                                                            </svg>
                                                        </template>
                                                        <template v-else-if="files.type == 'zip'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-package">
                                                                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                                                <path
                                                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                                                </path>
                                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96">
                                                                </polyline>
                                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                            </svg>
                                                        </template>
                                                        <template v-else>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                class="feather feather-file-text">
                                                                <path
                                                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z">
                                                                </path>
                                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                                                <polyline points="10 9 9 9 8 9"></polyline>
                                                            </svg>
                                                        </template>
                                                        <div class="media-body">
                                                            <p class="file-name">{{ files.name }}</p>
                                                            <p class="file-size">{{ files.size }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> -->
                                        </div>
                                    </perfect-scrollbar>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <div class="col-xl-1 col-lg-1 col-md-1" ></div>
        </div>
    </div>
    <breadcrumb :parent="'Plataforma'" :current="'Asistencias'" />
</template>
<script>
export default {
    name: 'payment-list'
}
</script>
<script setup>
import "@/assets/sass/apps/mailbox.scss";
import { computed, onMounted, ref } from "vue";
import useSettings from "@/composables/settingsComposable";
import { usePageTitle } from "@/composables/use-meta";
import api from "@/utils/axios";
import { useAuthUser } from '@/store/auth-user'

usePageTitle('Mensualidades')

const userState = useAuthUser()
const { settings } = useSettings();

const scrollbarOptions = {
suppressScrollX: true,
}

const selected_group = ref(null)
const groups = settings.groups
const players_group = ref([]);
const group_changed = (id) => {
    selected_group.value = groups.find((group) => group.id === id);
    // search_mails();
    is_show_mail_menu.value = false;

    api.get(`/api/v2/training_groups/${id}`).then(response => {
        players_group.value = []
        players_group.value = response.data.data.players
    }).catch(_ => {
        players_group.value = []
    })
};
const player_selected = ref(null)
const selected_player = (player) => {
    player_selected.value = player
}




















const default_data = { id: null, from: "info@mail.com", to: "", cc: "", title: "", file: null, description: "" };

const is_show_mail_menu = ref(false);
let mail_list = ref([]);
const filtered_mail_list = ref([]);
const search_mail = ref("");
const selected_tab = ref("inbox");
const selected_mail = ref(null);
const params = ref(default_data);
const mail_popup_type = ref(null);
const ids = ref([]);
const editor_options = ref({
    modules: {
        toolbar: [[{ header: [1, 2, false] }], ["bold", "italic", "underline"], ["image", "code-block"]],
    },
    placeholder: "Compose an epic...",
});

const composeMailModal = ref(null);

// computed
const check_all_checkbox = computed(() => {
    if (filtered_mail_list.value.length && ids.value.length == filtered_mail_list.value.length) {
        return true;
    } else {
        return false;
    }
});

onMounted(() => {
    initTooltip();
    // initPopup();
    bind_mail_list();
});

const initTooltip = () => {
    setTimeout(() => {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map((tooltipTriggerEl) => {
            return new window.bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
};
// const initPopup = () => {
//     composeMailModal.value = new window.bootstrap.Modal(document.getElementById("composeMailModal"));
// };

const bind_mail_list = () => {
    const c_dt = new Date();
    mail_list.value = [
        {
            id: 1,
            path: "profile-16.jpeg",
            first_name: "Laurie",
            last_name: "Fox",
            email: "laurieFox@mail.com",
            date: c_dt.getMonth() + 1 + "/" + c_dt.getDate() + "/" + c_dt.getFullYear(),
            time: "2:00 PM",
            title: "Promotion Page",
            display_description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pulvinar feugiat consequat. Duis lacus nibh, sagittis id varius vel, aliquet non augue.",
            type: "inbox",
            is_important: false,
            group: "social",
            is_unread: false,
            attachments: [
                { name: "Confirm File.txt", size: "450KB", type: "file" },
                { name: "Important Docs.xml", size: "2.1MB", type: "file" },
            ],
            description: `
      <p class="mail-content"> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. </p>
      <div class="gallery text-center">
          <img alt="image-gallery" src="/assets/images/scroll-6.jpeg" class="img-fluid mb-4 mt-4" style="width: 250px; height: 180px;">
          <img alt="image-gallery" src="/assets/images/scroll-7.jpeg" class="img-fluid mb-4 mt-4" style="width: 250px; height: 180px;">
          <img alt="image-gallery" src="/assets/images/scroll-8.jpeg" class="img-fluid mb-4 mt-4" style="width: 250px; height: 180px;">
      </div>
      <p>Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
      `,
        },
        {
            id: 2,
            path: "profile-19.jpeg",
            first_name: "Andy",
            last_name: "King",
            email: "kingAndy@mail.com",
            date: c_dt.getMonth() + 1 + "/" + c_dt.getDate() + "/" + c_dt.getFullYear(),
            time: "6:28 PM",
            title: "Hosting Payment Reminder",
            display_description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pulvinar feugiat consequat. Duis lacus nibh, sagittis id varius vel, aliquet non augue.",
            type: "inbox",
            is_important: false,
            group: "",
            is_unread: false,
            description: `
      <p class="mail-content"> Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. </p>
      <p>Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
      `,
        },
        {
            id: 3,
            path: "",
            first_name: "Kristen",
            last_name: "Beck",
            email: "kirsten.beck@mail.com",
            date: c_dt.getMonth() + 1 + "/" + c_dt.getDate() + "/" + c_dt.getFullYear(),
            time: "11:09 AM",
            title: "Verification Link",
            display_description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pulvinar feugiat consequat. Duis lacus nibh, sagittis id varius vel, aliquet non augue.",
            type: "inbox",
            is_important: false,
            group: "social",
            is_unread: true,
            description: `
      <p class="mail-content"> Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. </p>
      <p>Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
      `,
        },
        {
            id: 4,
            path: "profile-34.jpeg",
            first_name: "Christian",
            last_name: "",
            email: "christian@mail.com",
            date: "11/30/2021",
            time: "2:00 PM",
            title: "New Updates",
            display_description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pulvinar feugiat consequat. Duis lacus nibh, sagittis id varius vel, aliquet non augue.",
            type: "inbox",
            is_important: false,
            group: "private",
            is_unread: false,
            attachments: [{ name: "update.zip", size: "1.38MB", type: "zip" }],
            description: `
      <p class="mail-content"> Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. </p>
      <p>Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
      `,
        },
        {
            id: 5,
            path: "profile-31.jpeg",
            first_name: "Roxanne",
            last_name: "",
            email: "roxanne@mail.com",
            date: "11/15/2021",
            time: "2:00 PM",
            title: "Schedular Alert",
            display_description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi pulvinar feugiat consequat. Duis lacus nibh, sagittis id varius vel, aliquet non augue.",
            type: "inbox",
            is_important: true,
            group: "personal",
            is_unread: true,
            description: `
      <p class="mail-content"> Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. </p>
      <p>Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
      `,
        }
    ];

    search_mails();
};
const tab_changed = (type) => {
    selected_tab.value = type;
    search_mails();
    is_show_mail_menu.value = false;
};

const search_mails = () => {
    let res;
    if (selected_tab.value == "important") {
        res = mail_list.value.filter((d) => d.is_important);
    } else if (selected_tab.value == "personal" || selected_tab.value == "work" || selected_tab.value == "social" || selected_tab.value == "private") {
        res = mail_list.value.filter((d) => d.group == selected_tab.value);
    } else {
        res = mail_list.value.filter((d) => d.type == selected_tab.value);
    }
    filtered_mail_list.value = res.filter(
        (d) =>
            (d.title && d.title.toLowerCase().includes(search_mail.value)) ||
            (d.first_name && d.first_name.toLowerCase().includes(search_mail.value)) ||
            (d.last_name && d.last_name.toLowerCase().includes(search_mail.value)) ||
            (d.display_description && d.display_description.toLowerCase().includes(search_mail.value))
    );

    clear_selection();
};

const select_mail = (item) => {
    if (item) {
        if (item.type != "draft") {
            if (item && item.is_unread) {
                item.is_unread = false;
            }
            selected_mail.value = item;
        } else {
            open_mail("draft", item);
        }
    } else {
        selected_mail.value = "";
    }

    initTooltip();
};
const show_time = (item) => {
    const display_dt = new Date(item.date);
    const c_dt = new Date();
    if (display_dt.toDateString() == c_dt.toDateString()) {
        return item.time;
    } else {
        if (display_dt.getFullYear() == c_dt.getFullYear()) {
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            return monthNames[display_dt.getMonth()] + " " + String(display_dt.getDate()).padStart(2, "0");
        } else {
            return String(display_dt.getMonth() + 1).padStart(2, "0") + "/" + String(display_dt.getDate()).padStart(2, "0") + "/" + display_dt.getFullYear();
        }
    }
};
const check_all = (is_checked) => {
    if (is_checked) {
        ids.value = filtered_mail_list.value.map((d) => {
            return d.id;
        });
    } else {
        clear_selection();
    }
};
const clear_selection = () => {
    ids.value = [];
};

const set_group = (group) => {
    if (ids.value.length) {
        let items = filtered_mail_list.value.filter((d) => ids.value.includes(d.id));
        for (let item of items) {
            item.group = group;
        }

        showMessage(ids.value.length + " Mail Grouped as " + group.toUpperCase());
        clear_selection();
    }
};
const set_important = () => {
    if (ids.value.length) {
        let items = filtered_mail_list.value.filter((d) => ids.value.includes(d.id));
        for (let item of items) {
            item.is_important = !item.is_important;
        }
        if (selected_tab.value == "important") {
            showMessage(ids.value.length + " Mail removed from Important.");
        } else {
            showMessage(ids.value.length + " Mail added to Important.");
        }
        tab_changed("important");
    }
};

const set_spam = () => {
    if (ids.value.length) {
        let items = filtered_mail_list.value.filter((d) => ids.value.includes(d.id));
        for (let item of items) {
            item.type = item.type == "spam" ? "inbox" : "spam";
        }

        if (selected_tab.value == "spam") {
            showMessage(ids.value.length + " Mail removed from Spam.");
        } else {
            showMessage(ids.value.length + " Mail added to Spam.");
        }
        tab_changed("spam");
    }
};

const set_action = (type) => {
    if (ids.value.length) {
        let items = filtered_mail_list.value.filter((d) => ids.value.includes(d.id));
        for (let item of items) {
            if (type == "trash") {
                item.type = "trash";
                showMessage(ids.value.length + " Mail deleted.");
                tab_changed("trash");
            } else if (type == "read") {
                item.is_unread = false;
                showMessage(ids.value.length + " Mail marked as Read.");
            } else if (type == "unread") {
                item.is_unread = true;
                showMessage(ids.value.length + " Mail marked as UnRead.");
            }
            //restore & permanent delete
            else if (type == "restore") {
                item.type = "inbox";
                showMessage(ids.value.length + " Mail Restored.");
                tab_changed("inbox");
            } else if (type == "delete") {
                mail_list = mail_list.value.filter((d) => d.id != item.id);
                showMessage(ids.value.length + " Mail Permanently Deleted.");
                tab_changed("trash");
            }
        }
        clear_selection();
    }
};

const open_mail = (type, item) => {
    mail_popup_type.value = type;
    if (type == "add") {
        params.value = JSON.parse(JSON.stringify(default_data));
    } else if (type == "draft") {
        let data = JSON.parse(JSON.stringify(item));
        params.value = data;
        params.value.from = default_data.from;
        params.value.to = data.email;
    } else if (type == "reply") {
        let data = JSON.parse(JSON.stringify(item));
        params.value = data;
        params.value.from = default_data.from;
        params.value.to = data.email;
        params.value.title = "Re: " + params.value.title;
    } else if (type == "forward") {
        let data = JSON.parse(JSON.stringify(item));
        params.value = data;
        params.value.from = default_data.from;
        params.value.to = data.email;
        params.value.title = "Fwd: " + params.value.title;
    }

    // composeMailModal.value.show();
};
const save_mail = (type) => {
    if (!params.value.to) {
        showMessage("To email address is required.", "error");
        return true;
    }
    if (!params.value.title) {
        showMessage("Subject is required.", "error");
        return true;
    }

    let max_id = 0;
    if (!params.value.id) {
        max_id = mail_list.value.reduce((max, character) => (character.id > max ? character.id : max), mail_list.value[0].id);
    }
    let c_dt = new Date();

    let obj = {
        id: max_id + 1,
        path: "",
        first_name: "",
        last_name: "",
        email: params.value.to,
        date: c_dt.getMonth() + 1 + "/" + c_dt.getDate() + "/" + c_dt.getFullYear(),
        time: c_dt.toLocaleTimeString(),
        title: params.value.title,
        display_description: '',
        type: "draft",
        is_important: false,
        group: "",
        is_unread: false,
        description: params.value.description,
        attachments: [],
    };
    if (params.value.file && params.value.file.length) {
        for (let file of params.value.file) {
            let fl_obj = { name: file.name, size: get_file_size(file.size), type: get_file_type(file.type) };
            obj.attachments.push(fl_obj);
        }
    }

    if (type == "save" || type == "save_reply" || type == "save_forward") {
        //saved to draft

        obj.type = "draft";
        mail_list.value.splice(0, 0, obj);
        tab_changed("draft");

        showMessage("Successfully Saved to Draft.");
    } else if (type == "send" || type == "reply" || type == "forward") {
        //saved to sent mail

        obj.type = "sent_mail";
        mail_list.value.splice(0, 0, obj);
        tab_changed("sent_mail");

        showMessage("Mail Sent Successfully.");
    }

    selected_mail.value = null;
    // composeMailModal.value.hide();
};

const get_file_type = (file_type) => {
    let type = "file";
    if (file_type.includes("image/")) {
        type = "image";
    } else if (file_type.includes("application/x-zip")) {
        type = "zip";
    }
    return type;
};
const get_file_size = (total_bytes) => {
    let size = "";
    if (total_bytes < 1000000) {
        size = Math.floor(total_bytes / 1000) + "KB";
    } else {
        size = Math.floor(total_bytes / 1000000) + "MB";
    }
    return size;
};
const showMessage = (msg = "", type = "success") => {
    const toast = window.Swal.mixin({ toast: true, position: "top", showConfirmButton: false, timer: 3000 });
    toast.fire({ icon: type, title: msg, padding: "10px 20px" });
};
</script>