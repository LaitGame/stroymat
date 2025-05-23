import React from 'react';
import { useAuth } from '../context/AuthContext';
import { useHistory } from 'react-router-dom';

function LogoutButton() {
  const { logout } = useAuth();
  const history = useHistory();

  const handleLogout = async () => {
    try {
      // 1. Выход из Firebase
      await logout();
      
      // 2. Отправляем запрос на серверный выход
      const response = await fetch('/auth/logout.php');
      const result = await response.json();
      
      if (result.status === 'success') {
        history.push('/login');
      } else {
        console.error('Server logout failed');
      }
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  return (
    <button onClick={handleLogout} className="logout-button">
      Выйти
    </button>
  );
}

export default LogoutButton;