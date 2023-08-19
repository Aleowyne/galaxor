export default class ErrorView {
  constructor(mainView) {
    this.mainView = mainView;
  }

  /**
   * Initialisation de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init() {
    return this.mainView.template;
  }
}
