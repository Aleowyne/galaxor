export default class ErrorView {
  constructor(mainView) {
    this.mainView = mainView;
    this.target = this.mainView.template.cloneNode(true);
  }

  /**
   * Initialisation de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init() {
    return this.target;
  }
}
