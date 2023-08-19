import ItemView from './item.view.js';

export default class StructureView extends ItemView {
  /**
   * Gestion du bouton pour indiquer que la construction d'une structure est en cours
   * @param {StructureModel} structure Structure
   * @param {Element} structureBuildBtn Bouton de construction de la structure
   */
  setButtonInProgressBuild(structure, structureBuildBtn) {
    const currentDate = new Date();
    const leftTime = Math.ceil((structure.endTimeUpgrade - currentDate) / 1000);

    super.setButtonInProgressBuild(structureBuildBtn, leftTime);
  }

  /**
   * Gestion du bouton pour indiquer que la construction d'une structure peut être commencée
   * @param {StructureModel} structure Structure
   * @param {Element} structureBuildBtn Bouton de construction de la structure
   */
  setButtonStartBuild(structure, structureBuildBtn) {
    const buttonTxt = `Construire <br/>${this.mainView.displayTime(structure.buildTime)}`;
    super.setButtonStartBuild(structureBuildBtn, buttonTxt);
  }
}
