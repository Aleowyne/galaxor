export default class PrerequisiteModel {
  constructor(prerequisite) {
    this.id = 0;
    this.name = '';
    this.level = 0;

    this.fromArray(prerequisite);
  }

  fromArray(prerequisite) {
    if (prerequisite) {
      this.id = prerequisite.required_item_id ?? this.id;
      this.name = prerequisite.required_item_name ?? this.name;
      this.level = prerequisite.level ?? this.level;
    }
  }
}
