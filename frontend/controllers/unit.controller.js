import ItemController from './item.controller.js';
import UnitModel from '../models/unit.model.js';
import UnitTypeModel from '../models/unittype.model.js';
import UnitView from '../views/unit.view.js';

export default class UnitController extends ItemController {
  constructor() {
    super();
    this.view = new UnitView();
    this.units = [];
    this.unitTypes = [];
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(path) {
    await super.setupView(path);
    this.view = new UnitView(this.template);

    if (this.planet.id !== 0 && this.planet.ownerId === this.user.id) {
      // Récupération des unités de la planète
      this.units = await this.getUnitsPlanet(this.planet.id);

      // Récupération des types d'unités
      this.unitTypes = await this.getUnitTypesPlanet(this.planet.id);

      return this.view.init(this.unitTypes);
    }

    return document.createElement('div');
  }

  /**
   * Traitement
   */
  process() {
    // Gestion de la construction
    this.addEventBuild();
  }

  /**
   * Récupération des unités de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les unités de la planète
   */
  async getUnitsPlanet(planetId) {
    try {
      // Récupération des unités de la planète
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}/units`);
      return jsonResponse.units.map((unit) => new UnitModel(unit));
    }
    catch (error) {
      this.alertController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Récupération des types d'unités de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les types d'unités de la planète
   */
  async getUnitTypesPlanet(planetId) {
    try {
      // Récupération des unités de la planète
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}/unittypes`);
      return jsonResponse.unit_types.map((unitType) => new UnitTypeModel(unitType, this.units));
    }
    catch (error) {
      this.alertController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "construction d'une unité"
   */
  async addEventBuild() {
    const buildBtns = document.querySelectorAll('.item-build-btn');

    buildBtns.forEach((buildBtn, index) => {
      buildBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        const unitType = this.unitTypes[index];
        const currentDate = new Date();

        try {
          // Vérification de l'existence d'une unité en cours de création
          const unitIndex = unitType.units.findIndex((unit) => unit.createInProgress);
          let unit = unitType.units[unitIndex];

          // Finalisation de la construction
          if (unit && unit.endTimeCreate <= currentDate) {
            const jsonResponse = await this.requestPut(`/galaxor/api/planets/${this.planet.id}/units/${unit.id}`);
            unit = new UnitModel(jsonResponse);

            this.view.refreshItemFinishBuild(unitType, index);

            this.units[unitIndex] = unit;
          }

          // Lancement de la construction
          else if (!unit) {
            const bodyRequest = {
              item_id: unitType.itemId,
            };

            const jsonResponse = await this.requestPost(`/galaxor/api/planets/${this.planet.id}/units`, bodyRequest);
            unit = new UnitModel(jsonResponse);

            this.view.setButtonInProgressBuild(unit, buildBtn);

            this.unitTypes[index].units.push(unit);
          }
        }
        catch (error) {
          this.alertController.displayErrorAlert(error);
        }
      });
    });
  }
}
