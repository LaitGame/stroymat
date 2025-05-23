import React from 'react';
import { useAuth } from '../context/AuthContext';
import { useHistory } from 'react-router-dom';

function LogoutButton() {
  const { logout } = useAuth();
  const history = useHistory();

  const handleLogout = async () => {
    try {
      await logout();
      history.push('/login');
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  return (
    <button onClick={handleLogout} className="logout-button">
      Выйти
    </button>
  );
}

export default LogoutButton;