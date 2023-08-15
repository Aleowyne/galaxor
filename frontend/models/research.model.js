import CostModel from './cost.model.js';
import PrerequisiteModel from './prerequisite.model.js';

export default class ResearchModel {
  constructor(research) {
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

    this.fromArray(research);
  }

  fromArray(research) {
    if (research) {
      this.id = research.item_id ?? this.id;
      this.name = research.name ?? this.name;
      this.level = research.level ?? this.level;
      this.buildTime = research.build_time ?? this.buildTime;
      this.attackPoint = research.attack_point ?? this.attackPoint;
      this.defensePoint = research.defense_point ?? this.defensePoint;
      this.freightCapacity = research.freight_capacity ?? this.freightCapacity;
      this.upgradeInProgress = Boolean(research.upgrade_in_progress ?? this.upgradeInProgress);
      this.endTimeUpgrade = new Date(research.end_time_upgrade ?? this.endTimeUpgrade);
      this.imgUrl = `./assets/${research.img_filename ?? this.imgUrl}`;

      if (research.costs) {
        this.costs = research.costs.map((cost) => new CostModel(cost));
      }

      if (research.prerequisites) {
        this.prerequisites = research.prerequisites.map((prerequisite) => new PrerequisiteModel(prerequisite));
      }
    }
  }
}
