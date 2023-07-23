import BaseComponent from './base.component.js';
import UserController from '../controllers/user.controller.js';
import UniverseController from '../controllers/universe.controller.js';
import HomeView from '../views/home.view.js';

export default class HomeComponent extends BaseComponent {
  constructor() {
    super();
    this.view = new HomeView();
    this.universes = [];
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   */
  async setupView(path) {
    super.setupView(path);
    this.view = new HomeView(this.template);

    try {
      // Récupération des univers
      this.universes = await UniverseController.getUniverses();
    }
    catch (error) {
      this.alertController.displayErrorAlert(`Erreur à la récupération des univers : ${error} `);
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

        try {
          const user = await UserController.login(mailAddress, password);

          const loginEvent = new CustomEvent('login', { detail: user });
          document.body.dispatchEvent(loginEvent);

          localStorage.setItem('universeId', universeId);

          this.alertController.displaySuccessAlert('Connexion réussie');
          document.location.href = '#universe';
        }
        catch (error) {
          this.alertController.displayErrorAlert(`Erreur à la connexion : ${error} `);
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

        try {
          await UserController.signup(mailAddress, name, password);
          this.alertController.displaySuccessAlert('Inscription réussie. Connectez-vous pour jouer.');
        }
        catch (error) {
          this.alertController.displayErrorAlert(`Erreur à l'inscription : ${error} `);
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

        const universe = await UniverseController.createUniverse();
        this.view.addUniverse(universe);

        this.loader.style.display = 'none';

        this.alertController.displaySuccessAlert(`Univers ${universe.name} créé`);
      }
      catch (error) {
        this.alertController.displayErrorAlert(`Erreur à la création de l'univers : ${error} `);
      }
    });
  }
}
