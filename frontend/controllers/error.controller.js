import ErrorView from '../views/error.view.js';

export default class ErrorController {
  constructor() {
    this.mainController = null;
    this.view = null;
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new ErrorView(this.mainController.view);

    return this.view.init();
  }
}
