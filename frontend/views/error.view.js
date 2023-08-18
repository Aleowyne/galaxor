import BaseView from './base.view.js';

export default class ErrorView extends BaseView {
  /**
   * Initialisation de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init() {
    const template = await super.init();
    return template;
  }
}
