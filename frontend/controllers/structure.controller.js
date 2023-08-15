import UnitController from './unit.controller.js';
import StructureModel from '../models/structure.model.js';

export default class ResearchController extends UnitController {
  constructor() {
    super('structure');
    this.structures = [];
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(path) {
    await super.setupView(path);

    if (this.planet.id !== 0 && this.planet.ownerId === this.user.id) {
      // Récupération des structures de la planète
      this.structures = await this.getStructuresPlanet(this.planet.id);
      return this.view.init(this.structures);
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
   * Récupération des structures de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les structures de la planète
   */
  async getStructuresPlanet(planetId) {
    try {
      // Récupération des structures de la planète
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}/structures`);
      return jsonResponse.structures.map((structure) => new StructureModel(structure));
    }
    catch (error) {
      this.alertController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "construction d'une structure"
   */
  async addEventBuild() {
    const buildBtns = document.querySelectorAll('.structure-build-btn');

    buildBtns.forEach((buildBtn, index) => {
      buildBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        let structure = this.structures[index];
        const currentDate = new Date();

        try {
          // Finalisation de la construction
          if (structure.upgradeInProgress && structure.endTimeUpgrade <= currentDate) {
            const jsonResponse = await this.requestPut(`/galaxor/api/planets/${this.planet.id}/structures/${structure.id}/finish`);
            structure = new StructureModel(jsonResponse);
            this.view.refreshItemFinishBuild(structure, index);
          }

          // Lancement de la construction
          else {
            const jsonResponse = await this.requestPut(`/galaxor/api/planets/${this.planet.id}/structures/${structure.id}/start`);
            structure = new StructureModel(jsonResponse);
            this.view.setButtonInProgressBuild(structure, buildBtn);
          }

          this.structures[index] = structure;
        }
        catch (error) {
          this.alertController.displayErrorAlert(error);
        }
      });
    });
  }
}
