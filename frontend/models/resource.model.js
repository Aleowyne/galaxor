export default class ResourceModel {
  constructor(resource) {
    this.id = 0;
    this.name = '';
    this.bonus = 0;
    this.quantity = 0;
    this.lastTimeCalc = new Date();
    this.production = 0;

    this.fromArray(resource);
  }

  fromArray(resource) {
    if (resource) {
      this.id = resource.id ?? this.id;
      this.name = resource.name ?? this.name;
      this.bonus = resource.bonus ?? this.bonus;
      this.quantity = resource.quantity ?? this.quantity;
      this.lastTimeCalc = new Date(resource.last_time_calc ?? this.lastTimeCalc);
      this.production = resource.production ?? this.production;
    }
  }
}
