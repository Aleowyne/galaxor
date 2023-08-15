import BaseView from './base.view.js';

export default class UnitView extends BaseView {
  constructor(template, itemType) {
    super(template);
    this.itemType = itemType;
  }

  /**
   * Initialisation de la page
   * @param {StructureModel[]|ResearchModel[]} items Structures/recherches de la planète
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(items) {
    const template = await super.init();

    // Liste des structures
    this.setItems(items, template);

    return template;
  }

  /**
   * Affichage des structures/recherches
   * @param {StructureModel[]|ResearchModel[]} items Liste des structures/recherches
   * @param {Node} target Noeud HTML
   */
  setItems(items, target = document) {
    const itemList = target.getElementById(`${this.itemType}-list`);
    const itemTemplateRow = target.querySelector(`.${this.itemType}-table-row`);

    itemTemplateRow.remove();

    items.forEach((item) => {
      const itemRow = itemTemplateRow.cloneNode(true);
      const itemImage = itemRow.querySelector(`.${this.itemType}-image`);
      const itemNameTxt = itemRow.querySelector(`.${this.itemType}-name`);
      const itemLvlTxt = itemRow.querySelector(`.${this.itemType}-lvl`);
      const itemBuildBtn = itemRow.querySelector(`.${this.itemType}-build-btn`);

      itemImage.src = item.imgUrl;
      itemImage.alt = item.name;
      itemNameTxt.innerHTML = item.name;
      itemLvlTxt.innerHTML = item.level;

      const currentDate = new Date();

      // Upgrade à terminer
      if (item.upgradeInProgress && item.endTimeUpgrade <= currentDate) {
        this.setButtonFinishBuild(itemBuildBtn);
      }
      // Upgrade en cours
      else if (item.upgradeInProgress && item.endTimeUpgrade > currentDate) {
        this.setButtonInProgressBuild(item, itemBuildBtn);
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
   * Mise à jour d'une structure à la finalisation de la construction
   * @param {StructureModel|ResearchModel} item Structure/recherche
   * @param {number} index Position de la structure/recherche sur la page
   */
  async refreshItemFinishBuild(item, index) {
    const itemRow = document.querySelectorAll(`.${this.itemType}-table-row`)[index];
    const itemLvlTxt = itemRow.querySelector(`.${this.itemType}-lvl`);
    const itemBuildBtn = itemRow.querySelector(`.${this.itemType}-build-btn`);

    itemLvlTxt.innerHTML = item.level;

    // Affichage du bouton
    this.setButtonStartBuild(item, itemBuildBtn);

    // Affichage des coûts
    this.setCosts(item.costs, itemRow);
  }

  /**
   * Affichage des coûts sur la page
   * @param {CostModel[]} costs Coûts de la structure/recherche
   * @param {Node} target Noeud HTML
   */
  setCosts(costs, target) {
    const costTemplateRow = target.querySelector(`.${this.itemType}-cost-row`);
    const costRows = target.querySelectorAll(`.${this.itemType}-cost-row`);
    const itemTxt = target.querySelector(`.${this.itemType}-txt`);

    costs.forEach((cost) => {
      const costRow = costTemplateRow.cloneNode(true);
      const costNameTxt = costRow.querySelector(`.${this.itemType}-cost-name`);
      const costQuantityTxt = costRow.querySelector(`.${this.itemType}-cost-qty`);

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
   * @param {PrerequisiteModel[]} prerequisites Prérequis de la structure/recherche
   * @param {Node} target Noeud HTML
   */
  setPrerequisites(prerequisites, target) {
    const prerequisiteTemplateRow = target.querySelector(`.${this.itemType}-prerequisite-row`);
    const prerequisiteRows = target.querySelectorAll(`.${this.itemType}-prerequisite-row`);
    const prerequisiteList = target.querySelector(`.${this.itemType}-prerequisite-banner`);

    prerequisites.forEach((prerequisite) => {
      const prerequisiteRow = prerequisiteTemplateRow.cloneNode(true);
      const prerequisiteNameTxt = prerequisiteRow.querySelector(`.${this.itemType}-prerequisite-name`);
      const prerequisiteLvlTxt = prerequisiteRow.querySelector(`.${this.itemType}-prerequisite-lvl`);

      prerequisiteNameTxt.innerHTML = prerequisite.name;
      prerequisiteLvlTxt.innerHTML = prerequisite.level;

      prerequisiteList.appendChild(prerequisiteRow);
    });

    prerequisiteRows.forEach((prerequisiteRow) => {
      prerequisiteRow.remove();
    });
  }

  /**
   * Gestion du bouton pour indiquer que la construction de la structure/recherche peut être terminée
   * @param {Element} itemBuildBtn Bouton de construction de la structure/recherche
   */
  setButtonFinishBuild(itemBuildBtn) {
    const itemButton = itemBuildBtn;

    itemButton.innerHTML = 'Terminer';
    itemButton.classList.add('btn-finish');
    itemButton.disabled = false;
  }

  /**
   * Gestion du bouton pour indiquer que la construction de la structure/recherche est en cours
   * @param {StructureModel|ResearchModel} item Structure/recherche
   * @param {Element} itemBuildBtn Bouton de construction de la structure/recherche
   */
  setButtonInProgressBuild(item, itemBuildBtn) {
    const itemButton = itemBuildBtn;
    const currentDate = new Date();
    let leftTime = Math.ceil((item.endTimeUpgrade - currentDate) / 1000);

    // Timer
    const timerId = setInterval(() => {
      leftTime -= 1;
      itemButton.innerHTML = `En cours <br/>${this.displayTime(leftTime)}`;
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
   * Gestion du bouton pour indiquer que la construction de la structure/recherche peut être commencée
   * @param {StructureModel|ResearchModel} item Structure/recherche
   * @param {Element} itemBuildBtn Bouton de construction de la structure/recherche
   */
  setButtonStartBuild(item, itemBuildBtn) {
    const itemButton = itemBuildBtn;

    itemButton.innerHTML = `Construire <br/>${this.displayTime(item.buildTime)}`;
    itemButton.classList.remove('btn-finish');
    itemButton.disabled = false;
  }
}
