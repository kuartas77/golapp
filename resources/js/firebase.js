import { initializeApp } from 'firebase/app'
import { getMessaging } from "firebase/messaging";

const firebaseConfig = {
    apiKey: "AIzaSyD6atp1_iRjd7Addq7I-3JodXKZijPU1vA",
    authDomain: "asistencias-golapp.firebaseapp.com",
    projectId: "asistencias-golapp",
    storageBucket: "asistencias-golapp.firebasestorage.app",
    messagingSenderId: "860118696484",
    appId: "1:860118696484:web:976606d8faad57d5184eb1",
    measurementId: "G-W93R1RNC6W"
};

const app = initializeApp(firebaseConfig)
const messaging = getMessaging(app);
export default messaging