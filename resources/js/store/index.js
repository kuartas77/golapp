import { createStore } from "vuex";
import appState from "./appState";
import authState from "./authState";

export default new createStore({
    modules: {
        appState: appState,
        auth: authState,
    }
});
