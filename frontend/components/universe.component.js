import BaseComponent from './base.component.js';
import UniverseController from '../controllers/universe.controller.js';
import UniverseModel from '../models/universe.model.js';
import UniverseView from '../views/universe.view.js';

export default class UniverseComponent extends BaseComponent {
  constructor() {
    super();
    this.view = new UniverseView();
    this.universe = new UniverseModel();
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   */
  async setupView(path) {
    await super.setupView(path);
    this.view = new UniverseView(this.template);

    // Récupération des données de l'univers
    const universeId = localStorage.getItem('universeId');
    this.universe = await UniverseController.getUniverse(universeId);

    return this.view.init(this.user, this.universe);
  }
}
