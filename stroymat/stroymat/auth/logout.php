import LogoutButton from './components/LogoutButton';

function LogoutButton() {
  const { logout } = useAuth();

  return (
    <button onClick={logout}>
      Выйти
    </button>
  );
}