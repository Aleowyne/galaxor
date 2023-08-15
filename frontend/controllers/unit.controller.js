import BaseController from './base.controller.js';
import PlanetModel from '../models/planet.model.js';
import UnitView from '../views/unit.view.js';

export default class UnitController extends BaseController {
  constructor(itemType) {
    super();
    this.itemType = itemType;
    this.view = new UnitView();
    this.planet = new PlanetModel();
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   */
  async setupView(path) {
    await super.setupView(path);
    this.view = new UnitView(this.template, this.itemType);

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
