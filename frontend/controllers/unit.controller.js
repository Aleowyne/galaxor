import UnitModel from '../models/unit.model.js';
import UnitTypeModel from '../models/unittype.model.js';
import UnitView from '../views/unit.view.js';

export default class UnitController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.unitTypes = [];
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new UnitView(this.mainController.view);

    if (this.mainController.planet.id && this.mainController.planet.ownerId === this.mainController.user.id) {
      // Récupération des unités de la planète
      this.units = await this.getUnitsPlanet(this.mainController.planet.id);

      // Récupération des types d'unités
      this.unitTypes = await this.getUnitTypesPlanet(this.mainController.planet.id);

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
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/planets/${planetId}/units`);
      return jsonResponse.units.map((unit) => new UnitModel(unit));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
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
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/planets/${planetId}/unittypes`);
      return jsonResponse.unit_types.map((unitType) => new UnitTypeModel(unitType, this.units));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
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
        const planetId = this.mainController.planet.id;

        try {
          // Vérification de l'existence d'une unité en cours de création
          const unitIndex = unitType.units.findIndex((unit) => unit.createInProgress);
          let unit = unitType.units[unitIndex];

          // Finalisation de la construction
          if (unit && unit.endTimeCreate <= currentDate) {
            const jsonResponse = await this.mainController.requestPut(`/galaxor/api/planets/${planetId}/units/${unit.id}`);
            unit = new UnitModel(jsonResponse);

            this.view.refreshItemFinishBuild(unitType, index);

            this.units[unitIndex] = unit;
          }

          // Lancement de la construction
          else if (!unit) {
            const bodyRequest = {
              item_id: unitType.itemId,
            };

            const jsonResponse = await this.mainController.requestPost(`/galaxor/api/planets/${planetId}/units`, bodyRequest);
            unit = new UnitModel(jsonResponse);

            this.view.setButtonInProgressBuild(unit, buildBtn);

            this.unitTypes[index].units.push(unit);

            // Refresh des ressources
            await this.mainController.refreshResources();
          }
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
        }
      });
    });
  }
}
