import BaseController from './base.controller.js';
import UserModel from '../models/user.model.js';

export default class MainController extends BaseController {
  constructor() {
    super();
    this.addEventLinkLogout();
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
  }

  /**
   * Gestion de l'évènement "déconnexion du joueur"
   */
  addEventLinkLogout() {
    const logoutLink = document.getElementById('logout-link');

    logoutLink.addEventListener('click', async (event) => {
      event.preventDefault();

      try {
        await this.requestPost('/galaxor/api/users/logout');
        document.location.href = '';
      }
      catch (error) {
        this.alertController.displayErrorAlert(error);
      }
    });
  }

  /**
   * Détermination du chemin d'accès
   * @returns {Promise<string>} Chemin d'accès
   */
  async determinePath() {
    const path = window.location.hash.substring(1);

    try {
      const jsonResponse = await this.requestPost('/galaxor/api/users/login');
      const user = new UserModel(jsonResponse);

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
