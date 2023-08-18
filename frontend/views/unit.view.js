import ItemView from './item.view.js';

export default class UnitView extends ItemView {
  /**
   * Affichage des types d'unités
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   * @param {Node} target Noeud HTML
   */
  setItems(unitTypes, target = document) {
    const itemList = target.getElementById('item-list');
    const itemTemplateRow = target.querySelector('.item-table-row');

    itemTemplateRow.remove();

    unitTypes.forEach((unitType) => {
      const itemRow = itemTemplateRow.cloneNode(true);
      const itemImage = itemRow.querySelector('.item-image');
      const itemNameTxt = itemRow.querySelector('.item-name');
      const itemQtyTxt = itemRow.querySelector('.item-qty');
      const itemBuildBtn = itemRow.querySelector('.item-build-btn');

      itemImage.src = unitType.imgUrl;
      itemImage.alt = unitType.name;
      itemQtyTxt.innerHTML = unitType.units.filter((unit) => !unit.createInProgress).length;
      itemNameTxt.innerHTML = unitType.name;

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
    const itemRow = document.querySelectorAll('.item-table-row')[itemIndex];
    const itemQtyTxt = itemRow.querySelector('.item-qty');
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
    const buttonTxt = `Construire <br/>${this.displayTime(unit.buildTime)}`;
    super.setButtonStartBuild(unitBuildBtn, buttonTxt);
  }
}
