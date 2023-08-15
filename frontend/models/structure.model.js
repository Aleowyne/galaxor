import CostModel from './cost.model.js';
import PrerequisiteModel from './prerequisite.model.js';

export default class StructureModel {
  constructor(structure) {
    this.id = 0;
    this.name = '';
    this.level = 0;
    this.buildTime = 0;
    this.attackPoint = 0;
    this.defensePoint = 0;
    this.freightCapacity = 0;
    this.upgradeInProgress = false;
    this.endTimeUpgrade = new Date();
    this.imgUrl = 'none.png';
    this.costs = [];
    this.prerequisites = [];

    this.fromArray(structure);
  }

  fromArray(structure) {
    if (structure) {
      this.id = structure.item_id ?? this.id;
      this.name = structure.name ?? this.name;
      this.level = structure.level ?? this.level;
      this.buildTime = structure.build_time ?? this.buildTime;
      this.attackPoint = structure.attack_point ?? this.attackPoint;
      this.defensePoint = structure.defense_point ?? this.defensePoint;
      this.freightCapacity = structure.freight_capacity ?? this.freightCapacity;
      this.upgradeInProgress = Boolean(structure.upgrade_in_progress ?? this.upgradeInProgress);
      this.endTimeUpgrade = new Date(structure.end_time_upgrade ?? this.endTimeUpgrade);
      this.imgUrl = `./assets/${structure.img_filename ?? this.imgUrl}`;

      if (structure.costs) {
        this.costs = structure.costs.map((cost) => new CostModel(cost));
      }

      if (structure.prerequisites) {
        this.prerequisites = structure.prerequisites.map((prerequisite) => new PrerequisiteModel(prerequisite));
      }
    }
  }
}
