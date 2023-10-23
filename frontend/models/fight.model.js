import UnitModel from './unit.model.js';
import StructureModel from './structure.model.js';
import ResourceModel from './resource.model.js';

export default class FightModel {
  constructor(fight, opponentPlanet) {
    this.id = 0;
    this.timeFight = new Date();
    this.result = '';
    this.opponentPlanet = opponentPlanet;
    this.attackUnits = [];
    this.defenseUnits = [];
    this.defenseStructures = [];
    this.acquiredResources = [];

    this.fromArray(fight);
  }

  fromArray(fight) {
    if (fight) {
      this.id = fight.id ?? this.id;
      this.timeFight = new Date(fight.time_fight ?? this.timeFight);
      this.result = fight.result ?? this.result;

      if (fight.attack_units) {
        this.attackUnits = fight.attack_units.map((unit) => new UnitModel(unit));
      }

      if (fight.defense_units) {
        this.defenseUnits = fight.defense_units.map((unit) => new UnitModel(unit));
      }

      if (fight.defense_structures) {
        this.defenseStructures = fight.defense_structures.map((structure) => new StructureModel(structure));
      }

      if (fight.acquired_resources) {
        this.acquiredResources = fight.acquired_resources.map((resource) => new ResourceModel(resource));
      }
    }
  }
}
