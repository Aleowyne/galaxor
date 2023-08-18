import ItemModel from './item.model.js';

export default class UnitModel extends ItemModel {
  constructor(unit) {
    super();
    this.id = 0;
    this.createInProgress = false;
    this.endTimeCreate = new Date();

    this.fromArray(unit);
  }

  fromArray(unit) {
    if (unit) {
      super.fromArray(unit);

      this.id = unit.id ?? this.id;
      this.createInProgress = Boolean(unit.create_in_progress ?? this.createInProgress);
      this.endTimeCreate = new Date(unit.end_time_create ?? this.endTimeCreate);
    }
  }
}
