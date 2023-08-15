export default class CostModel {
  constructor(cost) {
    this.id = 0;
    this.name = '';
    this.quantity = 0;

    this.fromArray(cost);
  }

  fromArray(cost) {
    if (cost) {
      this.id = cost.resource_id ?? this.id;
      this.name = cost.resource_name ?? this.name;
      this.quantity = cost.quantity ?? this.quantity;
    }
  }
}
