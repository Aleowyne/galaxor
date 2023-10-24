export default class ItemView {
  constructor(mainView) {
    this.mainView = mainView;
    this.target = this.mainView.template.cloneNode(true);
  }

  /**
   * Initialisation de la page
   * @param {ItemModel[]} items Liste des items
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(items) {
    // Liste des items
    this.setItems(items);

    return this.target;
  }

  /**
   * Affichage des items
   * @param {ItemModel[]} items Liste des items
   */
  setItems(items) {
    const itemList = this.target.querySelector('.item-list');
    const itemTemplate = this.mainView.template.querySelector('.item-row');
    itemList.innerHTML = '';

    if (!items.length) {
      itemList.remove();
      return;
    }

    items.forEach((item) => {
      const itemRow = itemTemplate.cloneNode(true);

      itemRow.innerHTML = itemTemplate.innerHTML
        .replace('{{name}}', item.name)
        .replace('{{level}}', item.level)
        .replace('{{imageUrl}}', item.imgUrl)
        .replace('{{imageTxt}}', item.name);

      const itemBuildBtn = itemRow.querySelector('.item-build-btn');
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
    const itemRow = this.target.querySelectorAll('.item-row')[itemIndex];
    const itemLvlTxt = itemRow.querySelector('.item-level');
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
    const costList = target.querySelector('.item-cost-list');
    const costTemplate = this.mainView.template.querySelector('.item-cost-resource');
    costList.innerHTML = '';

    if (!costs.length) {
      costList.remove();
      return;
    }

    costs.forEach((cost) => {
      const costRow = costTemplate.cloneNode(true);

      costRow.innerHTML = costTemplate.innerHTML
        .replace('{{resourceName}}', cost.name)
        .replace('{{resourceQuantity}}', cost.quantity);

      costList.appendChild(costRow);
    });
  }

  /**
   * Affichage des prérequis sur la page
   * @param {PrerequisiteModel[]} prerequisites Prérequis de l'item
   * @param {Node} target Noeud HTML
   */
  setPrerequisites(prerequisites, target) {
    const prerequisiteList = target.querySelector('.item-prerequisite-list');
    const prerequisiteTemplate = this.mainView.template.querySelector('.item-prerequisite');
    prerequisiteList.innerHTML = '';

    if (!prerequisites.length) {
      prerequisiteList.remove();
      return;
    }

    prerequisites.forEach((prerequisite) => {
      const prerequisiteRow = prerequisiteTemplate.cloneNode(true);

      prerequisiteRow.innerHTML = prerequisiteTemplate.innerHTML
        .replace('{{itemPrerequisiteName}}', prerequisite.name)
        .replace('{{itemPrerequisiteLevel}}', prerequisite.level);

      prerequisiteList.appendChild(prerequisiteRow);
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
