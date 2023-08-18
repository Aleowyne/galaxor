import ItemView from './item.view.js';

export default class ResearchView extends ItemView {
  /**
   * Gestion du bouton pour indiquer que la construction d'une recherche est en cours
   * @param {ResearchModel} research Recherche
   * @param {Element} researchBuildBtn Bouton de construction de la recherche
   */
  setButtonInProgressBuild(research, researchBuildBtn) {
    const currentDate = new Date();
    const leftTime = Math.ceil((research.endTimeUpgrade - currentDate) / 1000);

    super.setButtonInProgressBuild(researchBuildBtn, leftTime);
  }

  /**
   * Gestion du bouton pour indiquer que la construction d'une recherche peut être commencée
   * @param {ResearchModel} research Recherche
   * @param {Element} researchBuildBtn Bouton de construction de la recherche
   */
  setButtonStartBuild(research, researchBuildBtn) {
    const buttonTxt = `Rechercher <br/>${this.displayTime(research.buildTime)}`;
    super.setButtonStartBuild(researchBuildBtn, buttonTxt);
  }
}
