import { initializeApp } from "firebase/app";
import { getAuth } from "firebase/auth";

const firebaseConfig = {
  apiKey: "ваш-api-key",
  authDomain: "ваш-auth-domain.firebaseapp.com",
  projectId: "ваш-project-id",
  storageBucket: "ваш-storage-bucket.appspot.com",
  messagingSenderId: "ваш-messaging-sender-id",
  appId: "ваш-app-id"
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);