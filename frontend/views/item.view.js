export default class ItemView {
  constructor(mainView) {
    this.mainView = mainView;
  }

  /**
   * Initialisation de la page
   * @param {ItemModel} items Liste des items
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(items) {
    const { template } = this.mainView;

    // Liste des items
    this.setItems(items, template);

    return template;
  }

  /**
   * Affichage des items
   * @param {ItemModel[]} items Liste des items
   * @param {Node} target Noeud HTML
   */
  setItems(items, target = document) {
    const itemList = target.getElementById('item-list');
    const itemTemplateRow = target.querySelector('.item-table-row');

    itemTemplateRow.remove();

    items.forEach((item) => {
      const itemRow = itemTemplateRow.cloneNode(true);
      const itemImage = itemRow.querySelector('.item-image');
      const itemNameTxt = itemRow.querySelector('.item-name');
      const itemLvlTxt = itemRow.querySelector('.item-lvl');
      const itemBuildBtn = itemRow.querySelector('.item-build-btn');

      itemImage.src = item.imgUrl;
      itemImage.alt = item.name;
      itemLvlTxt.innerHTML = item.level;
      itemNameTxt.innerHTML = item.name;

      const currentDate = new Date();

      if (item.upgradeInProgress) {
        // Upgrade à terminer
        if (item.endTimeUpgrade <= currentDate) {
          this.setButtonFinishBuild(itemBuildBtn);
        }
        // Upgrade en cours
        else {
          this.setButtonInProgressBuild(item, itemBuildBtn);
        }
      }
      // Pas d'upgrade en cours
      else {
        this.setButtonStartBuild(item, itemBuildBtn);
      }

      // S'il y a des prérequis, alors le bouton de construction est désactivé
      if (item.prerequisites.length !== 0) {
        itemBuildBtn.disabled = true;
      }

      // Affichage des coûts
      this.setCosts(item.costs, itemRow);

      // Affichage des prérequis
      this.setPrerequisites(item.prerequisites, itemRow);

      itemList.appendChild(itemRow);
    });
  }

  /**
   * Mise à jour d'un item (structure, recherche, unité) à la finalisation de la construction
   * @param {StructureModel|ResearchModel} item Item
   * @param {number} itemIndex Position de l'item sur la page
   */
  refreshItemFinishBuild(item, itemIndex) {
    const itemRow = document.querySelectorAll('.item-table-row')[itemIndex];
    const itemLvlTxt = itemRow.querySelector('.item-lvl');
    const itemBuildBtn = itemRow.querySelector('.item-build-btn');

    itemLvlTxt.innerHTML = item.level;

    // Affichage du bouton
    this.setButtonStartBuild(item, itemBuildBtn);

    // Affichage des coûts
    this.setCosts(item.costs, itemRow);
  }

  /**
   * Affichage des coûts sur la page
   * @param {CostModel[]} costs Coûts de l'item
   * @param {Node} target Noeud HTML
   */
  setCosts(costs, target) {
    const costTemplateRow = target.querySelector('.item-cost-row');
    const costRows = target.querySelectorAll('.item-cost-row');
    const itemTxt = target.querySelector('.item-txt');

    costs.forEach((cost) => {
      const costRow = costTemplateRow.cloneNode(true);
      const costNameTxt = costRow.querySelector('.item-cost-name');
      const costQuantityTxt = costRow.querySelector('.item-cost-qty');

      costNameTxt.innerHTML = cost.name;
      costQuantityTxt.innerHTML = cost.quantity;

      itemTxt.appendChild(costRow);
    });

    costRows.forEach((costRow) => {
      costRow.remove();
    });
  }

  /**
   * Affichage des prérequis sur la page
   * @param {PrerequisiteModel[]} prerequisites Prérequis de l'item
   * @param {Node} target Noeud HTML
   */
  setPrerequisites(prerequisites, target) {
    const prerequisiteTemplateRow = target.querySelector('.item-prerequisite-row');
    const prerequisiteRows = target.querySelectorAll('.item-prerequisite-row');
    const prerequisiteList = target.querySelector('.item-prerequisite-banner');

    prerequisites.forEach((prerequisite) => {
      const prerequisiteRow = prerequisiteTemplateRow.cloneNode(true);
      const prerequisiteNameTxt = prerequisiteRow.querySelector('.item-prerequisite-name');
      const prerequisiteLvlTxt = prerequisiteRow.querySelector('.item-prerequisite-lvl');

      prerequisiteNameTxt.innerHTML = prerequisite.name;
      prerequisiteLvlTxt.innerHTML = prerequisite.level;

      prerequisiteList.appendChild(prerequisiteRow);
    });

    prerequisiteRows.forEach((prerequisiteRow) => {
      prerequisiteRow.remove();
    });
  }

  /**
   * Gestion du bouton pour indiquer que la construction de l'item (structure, recherche, unité)
   * peut être terminée
   * @param {Element} itemBuildBtn Bouton de construction de l'item
   */
  setButtonFinishBuild(itemBuildBtn) {
    const itemButton = itemBuildBtn;

    itemButton.innerHTML = 'Terminer';
    itemButton.classList.add('btn-finish');
    itemButton.disabled = false;
  }

  /**
   * Gestion du bouton pour indiquer que la construction de l'item est en cours
   * @param {Element} itemBuildBtn Bouton de construction de la structure/recherche
   * @param {number} time Temps restant en secondes, jusqu'à la fin de la construction
   */
  setButtonInProgressBuild(itemBuildBtn, time) {
    const itemButton = itemBuildBtn;
    let leftTime = time;

    // Timer
    const timerId = setInterval(() => {
      leftTime -= 1;
      itemButton.innerHTML = `En cours <br/>${this.mainView.displayTime(leftTime)}`;
      itemButton.disabled = true;

      if (leftTime <= 0) {
        itemButton.innerHTML = 'Terminer';
        itemButton.disabled = false;
        itemButton.classList.add('btn-finish');
        clearInterval(timerId);
      }
    }, 1000);
  }

  /**
   * Gestion du bouton pour indiquer que la construction de l'item peut être commencée
   * @param {Element} itemBuildBtn Bouton de construction de l'item
   * @param {string} buttonTxt Texte du bouton
   */
  setButtonStartBuild(itemBuildBtn, buttonTxt) {
    const itemButton = itemBuildBtn;

    itemButton.innerHTML = buttonTxt;
    itemButton.classList.remove('btn-finish');
    itemButton.disabled = false;
  }
}
