import { BrowserRouter as Router, Switch, Route } from 'react-router-dom';
import PrivateRoute from './components/PrivateRoute';
import Login from './Login';
import Home from './Home';
import Logout from './pages/Logout';

function App() {
  return (
    <Router>
      <Switch>
        <Route path="/login" component={Login} />
        <PrivateRoute exact path="/" component={Home} />
        <Route path="*" component={() => <Redirect to="/login" />} />
        <Route path="/logout" component={Logout} />
      </Switch>
    </Router>
  );
}

export default App;