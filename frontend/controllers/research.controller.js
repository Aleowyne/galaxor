import ItemController from './item.controller.js';
import ResearchModel from '../models/research.model.js';
import ResearchView from '../views/research.view.js';

export default class ResearchController extends ItemController {
  constructor() {
    super();
    this.view = new ResearchView();
    this.researches = [];
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(path) {
    await super.setupView(path);
    this.view = new ResearchView(this.template);

    if (this.planet.id !== 0 && this.planet.ownerId === this.user.id) {
      // Récupération des recherches de la planète
      this.researches = await this.getResearchesPlanet(this.planet.id);
      return this.view.init(this.researches);
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
   * Récupération des recherches de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les recherches de la planète
   */
  async getResearchesPlanet(planetId) {
    try {
      // Récupération des recherches de la planète
      const jsonResponse = await this.requestGet(`/galaxor/api/planets/${planetId}/researches`);
      return jsonResponse.researches.map((research) => new ResearchModel(research));
    }
    catch (error) {
      this.alertController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "construction d'une recherche"
   */
  async addEventBuild() {
    const buildBtns = document.querySelectorAll('.item-build-btn');

    buildBtns.forEach((buildBtn, index) => {
      buildBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        let research = this.researches[index];
        const currentDate = new Date();

        try {
          // Finalisation de la construction
          if (research.upgradeInProgress && research.endTimeUpgrade <= currentDate) {
            const jsonResponse = await this.requestPut(`/galaxor/api/planets/${this.planet.id}/researches/${research.itemId}/finish`);
            research = new ResearchModel(jsonResponse);
            this.view.refreshItemFinishBuild(research, index);
          }

          // Lancement de la construction
          else {
            const jsonResponse = await this.requestPut(`/galaxor/api/planets/${this.planet.id}/researches/${research.itemId}/start`);
            research = new ResearchModel(jsonResponse);
            this.view.setButtonInProgressBuild(research, buildBtn);
          }

          this.researches[index] = research;
        }
        catch (error) {
          this.alertController.displayErrorAlert(error);
        }
      });
    });
  }
}
