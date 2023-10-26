export default class FightView {
  constructor(mainView) {
    this.mainView = mainView;
    this.target = this.mainView.template.cloneNode(true);
  }

  /**
   * Initialisation de la page
   * @param {FightModel[]} fights Liste des items
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(fights) {
    // Liste des combats
    this.setFights(fights);

    return this.target;
  }

  /**
   * Affichage des combats
   * @param {FightModel[]} fights Liste des combats
   */
  setFights(fights) {
    const fightList = this.target.getElementById('fight-list');
    const fightTemplate = this.mainView.template.querySelector('#fight-list .fight-item');

    if (!fights.length) {
      const fightReport = this.target.getElementById('fight-report');

      fightList.remove();
      fightReport.remove();
      return;
    }

    fightList.innerHTML = '';

    fights.forEach((fight) => {
      const fightRow = fightTemplate.cloneNode(true);

      const fightDate = fight.timeFight
        .toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });

      const fightTime = fight.timeFight
        .toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', hour12: false });

      fightRow.innerHTML = fightTemplate.innerHTML
        .replace('{{fightDate}}', fightDate)
        .replace('{{fightTime}}', fightTime)
        .replace('{{opponentPlanetName}}', fight.opponentPlanet.name);

      fightRow.dataset.fightid = fight.id;

      fightList.appendChild(fightRow);
    });

    if (fights.length !== 0) {
      this.setReport(fights[0]);
    }
  }

  /**
   * Affichage du rapport
   * @param {FightModel} fight Combat
   */
  setReport(fight) {
    const fightReport = this.target.getElementById('fight-report');
    const reportTemplate = this.mainView.template.getElementById('fight-report');

    const fightDate = fight.timeFight
      .toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });

    const fightTime = fight.timeFight
      .toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit', hour12: false });

    const fightResult = {
      WIN: 'Victoire !',
      LOSE: 'Défaite !',
      DRAW: 'Match nul !',
    }[fight.result];

    fightReport.innerHTML = reportTemplate.innerHTML
      .replace('{{fightDate}}', fightDate)
      .replace('{{fightTime}}', fightTime)
      .replace('{{opponentPlanetName}}', fight.opponentPlanet.name)
      .replace('{{fightResult}}', fightResult);

    // Affichage des unités attaquantes
    this.setUnits(fight.attackUnits, 'fight-attack');

    // Affichage des unités/structures défensives
    this.setUnits(fight.defenseUnits.concat(fight.defenseStructures), 'fight-defense');

    // Affichage des ressources acquises
    this.setResources(fight.acquiredResources, 'fight-resource');
  }

  /**
   * Affichage des unités
   * @param {(UnitModel|StructureModel)[]} units Liste d'unités
   * @param {string} unitListId ID pour la liste des unités (ul)
   */
  setUnits(units, unitListId) {
    const unitList = this.target.getElementById(unitListId);

    if (!units.length) {
      unitList.innerHTML = 'Aucun';
      return;
    }

    unitList.innerHTML = '';

    const unitTemplate = this.mainView.template.querySelector(`#${unitListId} .fight-item`);
    const unitCount = new Map();

    units.forEach((unit) => {
      unitCount.set(unit.name, (unitCount.get(unit.name) || 0) + 1);
    });

    unitCount.forEach((quantity, name) => {
      const unitRow = unitTemplate.cloneNode(true);

      unitRow.innerHTML = unitTemplate.innerHTML
        .replace('{{name}}', name)
        .replace('{{quantity}}', quantity);

      unitList.appendChild(unitRow);
    });
  }

  /**
   * Affichage des ressources
   * @param {ResourceModel[]} resources Liste des ressources
   * @param {string} resourceListId ID pour la liste des ressources (ul)
   */
  setResources(resources, resourceListId) {
    const resourceList = this.target.getElementById(resourceListId);

    if (!resources.length) {
      resourceList.innerHTML = 'Aucune ressource';
      return;
    }

    resourceList.innerHTML = '';

    const resourceTemplate = this.mainView.template.querySelector(`#${resourceListId} .fight-item`);

    resources.forEach((resource) => {
      const resourceRow = resourceTemplate.cloneNode(true);

      resourceRow.innerHTML = resourceTemplate.innerHTML
        .replace('{{name}}', resource.name)
        .replace('{{quantity}}', resource.quantity);

      resourceList.appendChild(resourceRow);
    });
  }
}
