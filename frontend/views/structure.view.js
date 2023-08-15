import BaseView from './base.view.js';

export default class StructureView extends BaseView {
  /**
   * Initialisation de la page
   * @param {StructureModel[]} structures Structures de la planète
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(structures) {
    const template = await super.init();

    // Liste des structures
    this.setStructures(structures, template);

    return template;
  }

  /**
   * Affichage des structures
   * @param {StructureModel[]} structures Liste des structures
   * @param {Node} target Noeud HTML
   */
  setStructures(structures, target = document) {
    const structureList = target.getElementById('structure-list');
    const structureTemplateRow = target.querySelector('.structure-table-row');

    structureTemplateRow.remove();

    structures.forEach((structure) => {
      const structureRow = structureTemplateRow.cloneNode(true);
      const structureImage = structureRow.querySelector('.structure-image');
      const structureNameTxt = structureRow.querySelector('.structure-name');
      const structureLvlTxt = structureRow.querySelector('.structure-lvl');
      const structureBuildBtn = structureRow.querySelector('.structure-build-btn');

      structureImage.src = structure.imgUrl;
      structureImage.alt = structure.name;
      structureNameTxt.innerHTML = structure.name;
      structureLvlTxt.innerHTML = structure.level;

      const currentDate = new Date();

      // Upgrade à terminer
      if (structure.upgradeInProgress && structure.endTimeUpgrade <= currentDate) {
        this.setButtonFinishBuild(structureBuildBtn);
      }
      // Upgrade en cours
      else if (structure.upgradeInProgress && structure.endTimeUpgrade > currentDate) {
        this.setButtonInProgressBuild(structure, structureBuildBtn);
      }
      // Pas d'upgrade en cours
      else {
        this.setButtonStartBuild(structure, structureBuildBtn);
      }

      // S'il y a des prérequis, alors le bouton de construction est désactivé
      if (structure.prerequisites.length !== 0) {
        structureBuildBtn.disabled = true;
      }

      // Affichage des coûts
      this.setCosts(structure.costs, structureRow);

      // Affichage des prérequis
      this.setPrerequisites(structure.prerequisites, structureRow);

      structureList.appendChild(structureRow);
    });
  }

  /**
   * Mise à jour d'une structure à la finalisation de la construction
   * @param {StructureModel} structure Structure
   * @param {number} index Position de la structure sur la page
   */
  async refreshStructureFinishBuild(structure, index) {
    const structureRow = document.querySelectorAll('.structure-table-row')[index];
    const structureLvlTxt = structureRow.querySelector('.structure-lvl');
    const structureBuildBtn = structureRow.querySelector('.structure-build-btn');

    structureLvlTxt.innerHTML = structure.level;

    // Affichage du bouton
    this.setButtonStartBuild(structure, structureBuildBtn);

    // Affichage des coûts
    this.setCosts(structure.costs, structureRow);
  }

  /**
   * Affichage des coûts sur la page
   * @param {CostModel[]} costs Coûts de la structure
   * @param {Node} target Noeud HTML
   */
  setCosts(costs, target) {
    const costTemplateRow = target.querySelector('.structure-cost-row');
    const costRows = target.querySelectorAll('.structure-cost-row');
    const structureTxt = target.querySelector('.structure-txt');

    costs.forEach((cost) => {
      const costRow = costTemplateRow.cloneNode(true);
      const costNameTxt = costRow.querySelector('.structure-cost-name');
      const costQuantityTxt = costRow.querySelector('.structure-cost-qty');

      costNameTxt.innerHTML = cost.name;
      costQuantityTxt.innerHTML = cost.quantity;

      structureTxt.appendChild(costRow);
    });

    costRows.forEach((costRow) => {
      costRow.remove();
    });
  }

  /**
   * Affichage des prérequis sur la page
   * @param {PrerequisiteModel[]} prerequisites Prérequis de la structure
   * @param {Node} target Noeud HTML
   */
  setPrerequisites(prerequisites, target) {
    const prerequisiteTemplateRow = target.querySelector('.structure-prerequisite-row');
    const prerequisiteRows = target.querySelectorAll('.structure-prerequisite-row');
    const prerequisiteList = target.querySelector('.structure-prerequisite-banner');

    prerequisites.forEach((prerequisite) => {
      const prerequisiteRow = prerequisiteTemplateRow.cloneNode(true);
      const prerequisiteNameTxt = prerequisiteRow.querySelector('.structure-prerequisite-name');
      const prerequisiteLvlTxt = prerequisiteRow.querySelector('.structure-prerequisite-lvl');

      prerequisiteNameTxt.innerHTML = prerequisite.name;
      prerequisiteLvlTxt.innerHTML = prerequisite.level;

      prerequisiteList.appendChild(prerequisiteRow);
    });

    prerequisiteRows.forEach((prerequisiteRow) => {
      prerequisiteRow.remove();
    });
  }

  /**
   * Gestion du bouton pour indiquer que la construction de la structure peut être terminée
   * @param {Element} structureBuildBtn Bouton de construction de la structure
   */
  setButtonFinishBuild(structureBuildBtn) {
    const structureButton = structureBuildBtn;

    structureButton.innerHTML = 'Terminer';
    structureButton.classList.add('btn-finish');
    structureButton.disabled = false;
  }

  /**
   * Gestion du bouton pour indiquer que la construction de la structure est en cours
   * @param {StructureModel} structure Structure
   * @param {Element} structureBuildBtn Bouton de construction de la structure
   */
  setButtonInProgressBuild(structure, structureBuildBtn) {
    const structureButton = structureBuildBtn;
    const currentDate = new Date();
    let leftTime = Math.trunc((structure.endTimeUpgrade - currentDate) / 1000);

    // Timer
    const timerId = setInterval(() => {
      leftTime -= 1;
      structureButton.innerHTML = `En cours <br/>${this.displayTime(leftTime)}`;
      structureButton.disabled = true;

      if (leftTime <= 0) {
        structureButton.innerHTML = 'Terminer';
        structureButton.disabled = false;
        structureButton.classList.add('btn-finish');
        clearInterval(timerId);
      }
    }, 1000);
  }

  /**
   * Gestion du bouton pour indiquer que la construction de la structure peut être commencée
   * @param {StructureModel} structure Structure
   * @param {Element} structureBuildBtn Bouton de construction de la structure
   */
  setButtonStartBuild(structure, structureBuildBtn) {
    const structureButton = structureBuildBtn;

    structureButton.innerHTML = `Construire <br/>${this.displayTime(structure.buildTime)}`;
    structureButton.classList.remove('btn-finish');
    structureButton.disabled = false;
  }
}
