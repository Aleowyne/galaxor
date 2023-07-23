import AlertComponent from './alert.component.js';
import UserController from '../controllers/user.controller.js';

export default class MainComponent {
  constructor() {
    this.alertComponent = new AlertComponent();
  }

  /**
   * Initialisation de la vue de la page
   * @param {string} path Chemin d'accès
   */
  setupView(path) {
    const headerNav = document.querySelector('header');

    // Header non visible si page d'accueil
    if (path === '') {
      headerNav.style.display = 'none';
    }
    else {
      headerNav.style.display = 'flex';
    }

    // Déconnexion du joueur
    this.addEventLinkLogout();
  }

  /**
   * Gestion de l'évènement "déconnexion du joueur"
   */
  addEventLinkLogout() {
    const logoutLink = document.getElementById('logout-link');

    logoutLink.addEventListener('click', (event) => {
      event.preventDefault();

      UserController.logout()
        .then(() => {
          document.location.href = '';
        });
    });
  }

  /**
   * Détermination du chemin d'accès
   * @returns {Promise<string>} Chemin d'accès
   */
  async determinePath() {
    const path = window.location.hash.substring(1);

    try {
      const user = await UserController.login();

      const loginEvent = new CustomEvent('login', { detail: user });
      document.body.dispatchEvent(loginEvent);

      // Si l'utilisateur vient de se connecter, il sera redirigé vers la page des univers
      if (path === '') {
        document.location.href = '#universe';
        return 'redirect';
      }
    }
    catch {
      /* Si l'utilisateur souhaite accéder à une page, alors que la connexion a échoué,
         alors il sera redirigé sur la page de connexion */
      if (path !== '') {
        document.location.href = '';
        return 'redirect';
      }
    }

    return path;
  }
}
