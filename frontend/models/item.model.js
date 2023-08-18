import CostModel from './cost.model.js';
import PrerequisiteModel from './prerequisite.model.js';

export default class ItemModel {
  constructor(item) {
    this.itemId = 0;
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

    this.fromArray(item);
  }

  fromArray(item) {
    if (item) {
      this.itemId = item.item_id ?? this.itemId;
      this.name = item.name ?? this.name;
      this.level = item.level ?? this.level;
      this.buildTime = item.build_time ?? this.buildTime;
      this.attackPoint = item.attack_point ?? this.attackPoint;
      this.defensePoint = item.defense_point ?? this.defensePoint;
      this.freightCapacity = item.freight_capacity ?? this.freightCapacity;
      this.upgradeInProgress = Boolean(item.upgrade_in_progress ?? this.upgradeInProgress);
      this.endTimeUpgrade = new Date(item.end_time_upgrade ?? this.endTimeUpgrade);
      this.imgUrl = `./assets/${item.img_filename ?? this.imgUrl}`;

      if (item.costs) {
        this.costs = item.costs.map((cost) => new CostModel(cost));
      }

      if (item.prerequisites) {
        this.prerequisites = item.prerequisites.map((prerequisite) => new PrerequisiteModel(prerequisite));
      }
    }
  }
}
