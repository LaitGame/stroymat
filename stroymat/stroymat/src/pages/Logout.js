import React, { useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useHistory } from 'react-router-dom';

function Logout() {
  const { logout } = useAuth();
  const history = useHistory();

  useEffect(() => {
    async function performLogout() {
      await logout();
      history.push('/login');
    }
    performLogout();
  }, [logout, history]);

  return (
    <div className="logout-page">
      <p>Выход из системы...</p>
    </div>
  );
}

export default Logout;