import { createStore } from "vuex";
import appState from "./appState";

export default new createStore({
    modules: {
        appState: appState
    }
});
