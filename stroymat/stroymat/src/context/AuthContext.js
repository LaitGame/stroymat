import React, { createContext, useContext, useEffect, useState } from 'react';
import { auth } from '../firebase';

const AuthContext = createContext();

export function useAuth() {
  return useContext(AuthContext);
}

export function AuthProvider({ children }) {
  const [currentUser, setCurrentUser] = useState(null);
  const [loading, setLoading] = useState(true);

  // Функция входа
  async function login(email, password) {
    try {
      const userCredential = await auth.signInWithEmailAndPassword(email, password);
      const token = await userCredential.user.getIdToken();
      
      // Сохраняем токен в localStorage
      localStorage.setItem('userToken', token);
      return userCredential;
    } catch (error) {
      console.error("Login error:", error);
      throw error;
    }
  }

  // Функция выхода (полностью обновленная)
  async function logout() {
    try {
      // 1. Выход из Firebase
      await auth.signOut();
      
      // 2. Очищаем состояние
      setCurrentUser(null);
      
      // 3. Удаляем данные из localStorage
      localStorage.removeItem('userToken');
      
      // 4. Отправляем запрос на серверный выход (если используется PHP-сессия)
      try {
        await fetch('/auth/logout.php', {
          method: 'POST',
          credentials: 'include'
        });
      } catch (serverError) {
        console.warn("Server logout failed (ignoring):", serverError);
      }
      
      console.log('Successfully logged out from all systems');
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  }

  // Проверка состояния аутентификации
  useEffect(() => {
    const unsubscribe = auth.onAuthStateChanged(async (user) => {
      if (user) {
        // Получаем свежий токен при каждом изменении состояния
        const token = await user.getIdToken();
        localStorage.setItem('userToken', token);
        setCurrentUser(user);
      } else {
        localStorage.removeItem('userToken');
        setCurrentUser(null);
      }
      setLoading(false);
    });

    // Восстановление сессии из localStorage при загрузке
    const token = localStorage.getItem('userToken');
    if (token && !currentUser) {
      // Здесь можно добавить проверку токена на сервере
    }

    return unsubscribe;
  }, []);

  const value = {
    currentUser,
    login,
    logout,
    loading
  };

  return (
    <AuthContext.Provider value={value}>
      {!loading && children}
    </AuthContext.Provider>
  );
}