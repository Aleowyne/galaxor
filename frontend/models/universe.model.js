import GalaxyModel from './galaxy.model.js';

export default class UniverseModel {
  constructor(universe) {
    this.id = 0;
    this.name = '';
    this.galaxies = [];

    this.fromArray(universe);
  }

  fromArray(universe) {
    if (universe) {
      this.id = universe.id ?? this.id;
      this.name = universe.name ?? this.name;

      if (universe.galaxies) {
        this.galaxies = universe.galaxies.map((galaxy) => new GalaxyModel(galaxy));
      }
    }
  }
}
