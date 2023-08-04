import BaseController from './base.controller.js';
import UserModel from '../models/user.model.js';
import UniverseModel from '../models/universe.model.js';
import HomeView from '../views/home.view.js';

export default class HomeController extends BaseController {
  constructor() {
    super();
    this.view = new HomeView();
    this.universes = [];
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(path) {
    super.setupView(path);
    this.view = new HomeView(this.template);

    try {
      // Récupération des univers
      const jsonResponse = await this.requestGet('/galaxor/api/universes');
      this.universes = jsonResponse.universes.map((universe) => new UniverseModel(universe));
    }
    catch (error) {
      this.alertController.displayErrorAlert(error);
    }

    return this.view.init(this.universes);
  }

  /**
   * Traitement
   */
  process() {
    // Connexion du joueur
    this.addEventButtonLogin();

    // Inscription du joueur
    this.addEventButtonSignup();

    // Création d'un univers
    this.addEventButtonCreateUniverse();
  }

  /**
   * Gestion de l'évènement "connexion du joueur"
   */
  addEventButtonLogin() {
    const loginButton = document.getElementById('login-btn');
    const loginForm = document.querySelector('.login-form');

    loginButton.addEventListener('click', async (event) => {
      if (loginForm.checkValidity()) {
        event.preventDefault();

        const mailAddress = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const universeId = document.getElementById('login-universe').value;

        const bodyRequest = {
          mail_address: mailAddress,
          password,
        };

        try {
          // Connexion de l'utilisateur
          const jsonResponse = await this.requestPost('/galaxor/api/users/login', bodyRequest);
          const user = new UserModel(jsonResponse);

          const loginEvent = new CustomEvent('login', { detail: user });
          document.body.dispatchEvent(loginEvent);

          localStorage.setItem('universeId', universeId);

          this.alertController.displaySuccessAlert('Connexion réussie');
          document.location.href = '#universe';
        }
        catch (error) {
          this.alertController.displayErrorAlert(error);
        }
      }
    });
  }

  /**
   * Gestion de l'évènement "inscription du joueur"
   */
  addEventButtonSignup() {
    const signupButton = document.getElementById('signup-btn');
    const signupForm = document.querySelector('.signup-form');

    signupButton.addEventListener('click', async (event) => {
      if (signupForm.checkValidity()) {
        event.preventDefault();

        const mailAddress = document.getElementById('signup-email').value;
        const name = document.getElementById('signup-name').value;
        const password = document.getElementById('signup-password').value;

        const bodyRequest = {
          mail_address: mailAddress,
          name,
          password,
        };

        try {
          // Inscription de l'utilisateur
          await this.requestPost('/galaxor/api/users/register', bodyRequest);

          this.alertController.displaySuccessAlert('Inscription réussie. Connectez-vous pour jouer.');
        }
        catch (error) {
          this.alertController.displayErrorAlert(error);
        }
      }
    });
  }

  /**
   * Gestion de l'évènement "création d'un univers"
   */
  addEventButtonCreateUniverse() {
    const createButton = document.getElementById('create-universe-btn');

    createButton.addEventListener('click', async (event) => {
      event.preventDefault();

      try {
        this.loader.style.display = 'flex';

        // Création de l'univers
        const jsonResponse = await this.requestPost('/galaxor/api/universes');
        const universe = new UniverseModel(jsonResponse);

        this.view.addUniverse(universe);

        this.loader.style.display = 'none';

        this.alertController.displaySuccessAlert(`Univers ${universe.name} créé`);
      }
      catch (error) {
        this.alertController.displayErrorAlert(error);
      }
    });
  }
}
