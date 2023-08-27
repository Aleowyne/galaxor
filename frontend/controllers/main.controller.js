import AlertController from './alert.controller.js';
import UserModel from '../models/user.model.js';
import UniverseModel from '../models/universe.model.js';
import PlanetModel from '../models/planet.model.js';
import ResourceModel from '../models/resource.model.js';
import UnitModel from '../models/unit.model.js';
import UnitTypeModel from '../models/unittype.model.js';
import MainView from '../views/main.view.js';

export default class MainController extends AlertController {
  constructor() {
    super();
    this.loader = document.querySelector('.loader');
    this.view = new MainView();
    this.user = new UserModel();
    this.universe = new UniverseModel();
    this.planet = new PlanetModel();
    this.resources = [];
    this.resourceTimerId = 0;

    // Déconnexion du joueur
    this.addEventLinkLogout();
  }

  /**
   * Initialisation de la vue
   * @param {string} path Chemin d'accès
   */
  async setupView(path) {
    const planetId = Number(localStorage.getItem('planetId'));
    const universeId = Number(localStorage.getItem('universeId'));

    if (universeId) {
      // Récupération des données de l'univers
      this.universe = await this.getUniverse(universeId);
    }

    if (!planetId || path === 'universe') {
      this.planet = new PlanetModel();
      this.resources = [];
      localStorage.removeItem('planetId');

      // Refresh des ressources de la planète
      this.refreshResources();
    }
    else if (this.planet.id !== planetId) {
      // Récupération des données de la planète
      this.planet = await this.getPlanet(planetId);

      // Refresh des ressources de la planète
      this.refreshResources();
    }

    await this.view.init(path, this.planet.name);
  }

  /**
   * Récupération des données de l'univers
   * @param {number} universeId Identifiant de l'univers
   * @returns Les données de l'univers
   */
  async getUniverse(universeId) {
    try {
      // Récupération des données de l'univers
      const jsonResponse = await this.requestGet(`/galaxor/api/universes/${universeId}`);
      return new UniverseModel(jsonResponse);
    }
    catch (error) {
      this.displayErrorAlert(error);
      return new UniverseModel();
    }
  }

  /**
   * Refresh des données de l'univers
   */
  async refreshUniverse() {
    this.universe = await this.getUniverse(this.universe.id);
  }

  /**
   * Récupération des données de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les données de la planète
   */
  async getPlanet(planetId) {
    try {
      // Récupération des données de la planète
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}`);
      return new PlanetModel(jsonResponse);
    }
    catch (error) {
      this.displayErrorAlert(error);
      return new PlanetModel();
    }
  }

  /**
   * Récupération des types d'unités de la planète
   * @returns Les types d'unités de la planète
   */
  async getUnitTypesPlanet() {
    try {
      // Récupération des unités de la planète
      let jsonResponse = await this.requestGet(`/galaxor/api/planets/${this.planet.id}/units`);
      const units = jsonResponse.units.map((unit) => new UnitModel(unit));

      // Récupération des types d'unités de la planète
      jsonResponse = await this.requestGet(`/galaxor/api/planets/${this.planet.id}/unittypes`);
      return jsonResponse.unit_types.map((unitType) => new UnitTypeModel(unitType, units));
    }
    catch (error) {
      this.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Récupération des ressources d'une planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les ressources de la planète
   */
  async getResources(planetId) {
    try {
      // Récupération des resources
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}/resources`);
      return jsonResponse.resources.map((resource) => new ResourceModel(resource));
    }
    catch (error) {
      this.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Refresh des ressources
   */
  async refreshResources() {
    clearInterval(this.resourceTimerId);

    if (this.planet.id) {
      // Récupération des ressources de la planète
      this.resources = await this.getResources(this.planet.id);
    }

    // Affichage des ressources
    this.view.displayResources(this.resources);

    // Refresh des ressources toutes les minutes
    this.resourceTimerId = setInterval(() => {
      this.resources = this.resources.map((resource) => {
        const newResource = resource;
        newResource.quantity += resource.production;
        return newResource;
      });

      this.view.displayResources(this.resources);
    }, 60000);
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
        this.user = new UserModel();
        localStorage.clear();

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
    const universeId = Number(localStorage.getItem('universeId'));
    const planetId = Number(localStorage.getItem('planetId'));

    try {
      const jsonResponse = await this.requestPost('/galaxor/api/users/login');
      this.user = new UserModel(jsonResponse);

      // Si l'utilisateur vient de se connecter, il sera redirigé vers la page des univers
      if ((!universeId && !planetId && path !== 'universe') || path === '') {
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

  /**
   * Affichage d'une boîte de dialogue
   * @param {string} message Message à afficher
   */
  displayDialog(message) {
    const dialog = document.getElementById('app-dialog');
    const txtDialog = document.getElementById('app-dialog-txt');
    txtDialog.innerHTML = message;
    dialog.style.display = 'flex';
  }

  /**
   * Exécution d'une requête GET HTTP
   * @param {string} url URL de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestGet(url) {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }

  /**
   * Exécution d'une requête POST HTTP
   * @param {string} url URL de la requête
   * @param {object} body Corps de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestPost(url, body = {}) {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify(body),
    });

    if (response.status === 204) {
      return Promise.resolve('');
    }

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }

  /**
   * Exécution d'une requête PUT HTTP
   * @param {string} url URL de la requête
   * @param {object} body Corps de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestPut(url, body = {}) {
    const response = await fetch(url, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify(body),
    });

    if (response.status === 204) {
      return Promise.resolve('');
    }

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }
}
