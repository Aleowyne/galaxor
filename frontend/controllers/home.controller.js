import UserModel from '../models/user.model.js';
import UniverseModel from '../models/universe.model.js';
import HomeView from '../views/home.view.js';

export default class HomeController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.universes = [];
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new HomeView(this.mainController.view);

    // Récupération des univers
    this.universes = await this.getUniverses();

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
   * Récupération des univers
   * @returns La liste des univers
   */
  async getUniverses() {
    try {
      // Récupération des univers
      const jsonResponse = await this.mainController.requestGet('/galaxor/api/universes');
      return jsonResponse.universes.map((universe) => new UniverseModel(universe));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
      return [];
    }
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
          const jsonResponse = await this.mainController.requestPost('/galaxor/api/users/login', bodyRequest);
          this.mainController.user = new UserModel(jsonResponse);

          localStorage.clear();
          localStorage.setItem('universeId', universeId);

          this.mainController.displaySuccessAlert('Connexion réussie');
          document.location.href = '#universe';
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
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
          await this.mainController.requestPost('/galaxor/api/users/register', bodyRequest);

          this.mainController.displaySuccessAlert('Inscription réussie. Connectez-vous pour jouer.');
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
        }
      }
    });
  }

  /**
   * Gestion de l'évènement "création d'un univers"
   */
  addEventButtonCreateUniverse() {
    const createBtn = document.getElementById('create-universe-btn');

    createBtn.addEventListener('click', async (event) => {
      event.preventDefault();

      try {
        this.mainController.loader.style.display = 'flex';

        // Création de l'univers
        const jsonResponse = await this.mainController.requestPost('/galaxor/api/universes');
        const universe = new UniverseModel(jsonResponse);

        this.view.addUniverse(universe);

        this.mainController.displaySuccessAlert(`Univers ${universe.name} créé`);
      }
      catch (error) {
        this.mainController.displayErrorAlert(error);
      }

      this.mainController.loader.style.display = 'none';
    });
  }
}
