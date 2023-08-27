import StructureModel from '../models/structure.model.js';
import StructureView from '../views/structure.view.js';

export default class StructureController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.structures = [];
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new StructureView(this.mainController.view);

    // Récupération des structures de la planète
    this.structures = await this.getStructuresPlanet();
    return this.view.init(this.structures);
  }

  /**
   * Traitement
   */
  process() {
    // Gestion de la construction
    this.addEventBuild();
  }

  /**
   * Récupération des structures de la planète
   * @returns Les structures de la planète
   */
  async getStructuresPlanet() {
    try {
      // Récupération des structures de la planète
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/planets/${this.mainController.planet.id}/structures`);
      return jsonResponse.structures.map((structure) => new StructureModel(structure));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "construction d'une structure"
   */
  async addEventBuild() {
    const buildBtns = document.querySelectorAll('.item-build-btn');

    buildBtns.forEach((buildBtn, index) => {
      buildBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        let structure = this.structures[index];
        const currentDate = new Date();
        const planetId = this.mainController.planet.id;

        try {
          // Finalisation de la construction
          if (structure.upgradeInProgress && structure.endTimeUpgrade <= currentDate) {
            const jsonResponse = await this.mainController.requestPut(`/galaxor/api/planets/${planetId}/structures/${structure.itemId}/finish`);
            structure = new StructureModel(jsonResponse);
            this.view.refreshItemFinishBuild(structure, index);
          }

          // Lancement de la construction
          else {
            const jsonResponse = await this.mainController.requestPut(`/galaxor/api/planets/${planetId}/structures/${structure.itemId}/start`);
            structure = new StructureModel(jsonResponse);
            this.view.setButtonInProgressBuild(structure, buildBtn);

            // Refresh des ressources
            await this.mainController.refreshResources();
          }

          this.structures[index] = structure;
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
        }
      });
    });
  }
}
