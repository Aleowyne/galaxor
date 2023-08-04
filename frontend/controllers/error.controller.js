import BaseController from './base.controller.js';
import ErrorView from '../views/error.view.js';

export default class ErrorController extends BaseController {
  constructor() {
    super();
    this.view = new ErrorView();
  }

  /**
   * Construction de la vue
   * @param {string} path Chemin de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(path) {
    super.setupView(path);
    this.view = new ErrorView(this.template);
    return this.view.init();
  }
}
