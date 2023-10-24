import ItemView from './item.view.js';

export default class UnitView extends ItemView {
  /**
   * Affichage des types d'unités
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   */
  setItems(unitTypes) {
    const itemList = this.target.querySelector('.item-list');
    const itemTemplate = this.mainView.template.querySelector('.item-row');
    itemList.innerHTML = '';

    if (!unitTypes.length) {
      itemList.remove();
      return;
    }

    unitTypes.forEach((unitType) => {
      const itemRow = itemTemplate.cloneNode(true);
      const nbUnits = unitType.units.filter((unit) => !unit.createInProgress).length;

      itemRow.innerHTML = itemTemplate.innerHTML
        .replace('{{name}}', unitType.name)
        .replace('{{quantity}}', nbUnits)
        .replace('{{imageUrl}}', unitType.imgUrl)
        .replace('{{imageTxt}}', unitType.name);

      const itemBuildBtn = itemRow.querySelector('.item-build-btn');
      const currentDate = new Date();

      const unit = unitType.units.find((unit) => unit.createInProgress);

      if (unit) {
        // Upgrade à terminer
        if (unit.endTimeCreate <= currentDate) {
          this.setButtonFinishBuild(itemBuildBtn);
        }
        // Upgrade en cours
        else {
          this.setButtonInProgressBuild(unit, itemBuildBtn);
        }
      }
      // Pas d'upgrade en cours
      else {
        this.setButtonStartBuild(unitType, itemBuildBtn);
      }

      // S'il y a des prérequis, alors le bouton de construction est désactivé
      if (unitType.prerequisites.length !== 0) {
        itemBuildBtn.disabled = true;
      }

      // Affichage des coûts
      this.setCosts(unitType.costs, itemRow);

      // Affichage des prérequis
      this.setPrerequisites(unitType.prerequisites, itemRow);

      itemList.appendChild(itemRow);
    });
  }

  /**
   * Mise à jour du type d'unité à la finalisation de la construction
   * @param {UnitTypeModel} unitType Type d'unités
   * @param {number} itemIndex Position du type d'unité sur la page
   */
  refreshItemFinishBuild(unitType, itemIndex) {
    const itemRow = this.target.querySelectorAll('.item-row')[itemIndex];
    const itemQtyTxt = itemRow.querySelector('.item-qantity');
    const itemBuildBtn = itemRow.querySelector('.item-build-btn');

    itemQtyTxt.innerHTML = unitType.units.length;

    // Affichage du bouton
    this.setButtonStartBuild(unitType, itemBuildBtn);

    // Affichage des coûts
    this.setCosts(unitType.costs, itemRow);
  }

  /**
   * Gestion du bouton pour indiquer que la construction d'une unité est en cours
   * @param {UnitModel} unit Unité
   * @param {Element} unitBuildBtn Bouton de construction de l'unité
   */
  setButtonInProgressBuild(unit, unitBuildBtn) {
    const currentDate = new Date();
    const leftTime = Math.ceil((unit.endTimeCreate - currentDate) / 1000);

    super.setButtonInProgressBuild(unitBuildBtn, leftTime);
  }

  /**
   * Gestion du bouton pour indiquer que la construction d'une unité peut être commencée
   * @param {UnitTypeModel} unitType Type d'unité
   * @param {Element} unitBuildBtn Bouton de construction de l'unité
   */
  setButtonStartBuild(unit, unitBuildBtn) {
    const buttonTxt = `Construire <br/>${this.mainView.displayTime(unit.buildTime)}`;
    super.setButtonStartBuild(unitBuildBtn, buttonTxt);
  }
}
