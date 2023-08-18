import CostModel from './cost.model.js';
import PrerequisiteModel from './prerequisite.model.js';

export default class UnitTypeModel {
  constructor(unitType, units) {
    this.itemId = '';
    this.name = '';
    this.buildTime = 0;
    this.attackPoint = 0;
    this.defensePoint = 0;
    this.freightCapacity = 0;
    this.imgUrl = 'none.png';
    this.costs = [];
    this.prerequisites = [];
    this.units = [];

    this.fromArray(unitType, units);
  }

  fromArray(unitType, units) {
    if (unitType) {
      this.itemId = unitType.item_id ?? this.itemId;
      this.name = unitType.name ?? this.name;
      this.buildTime = unitType.build_time ?? this.buildTime;
      this.attackPoint = unitType.attack_point ?? this.attackPoint;
      this.defensePoint = unitType.defense_point ?? this.defensePoint;
      this.freightCapacity = unitType.freight_capacity ?? this.freightCapacity;
      this.imgUrl = `./assets/${unitType.img_filename ?? this.imgUrl}`;

      if (unitType.costs) {
        this.costs = unitType.costs.map((cost) => new CostModel(cost));
      }

      if (unitType.prerequisites) {
        this.prerequisites = unitType.prerequisites.map((prerequisite) => new PrerequisiteModel(prerequisite));
      }

      this.units = units.filter((unit) => unit.itemId === this.itemId);
    }
  }
}
