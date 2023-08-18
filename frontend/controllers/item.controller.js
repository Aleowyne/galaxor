import BaseController from './base.controller.js';
import PlanetModel from '../models/planet.model.js';

export default class ItemController extends BaseController {
  constructor() {
    super();
    this.planet = new PlanetModel();
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   */
  async setupView(path) {
    await super.setupView(path);

    const planetId = localStorage.getItem('planetId');

    // Récupération des données de la planète
    this.planet = await this.getPlanet(planetId);
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
      this.alertController.displayErrorAlert(error);
      return new PlanetModel();
    }
  }
}
